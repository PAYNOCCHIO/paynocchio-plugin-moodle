<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This class contains a list of webservice functions related to the Paynocchio payment gateway.
 *
 * @package    paygw_paynocchio
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace paygw_paynocchio\external;

use core\notification;
use core\uuid;
use core_payment\helper;
use core_user;
use paygw_paynocchio\paynocchio_helper;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use stdClass;

class activate_wallet extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userId' => new external_value(PARAM_INT, 'An identifier for User'),
        ]);
    }

    /**
     * Returns the config values required by the Paynocchio JavaScript SDK.
     * @param int $userId
     * @return string[]
     */
    public static function execute(int $userId): array
    {
        global $DB, $USER;
        self::validate_parameters(self::execute_parameters(), [
            'userId' => $userId,
        ]);

        $user_uuid = uuid::generate();

        if($user_uuid) {
            $wallet = new paynocchio_helper($user_uuid);
            $wallet_response = $wallet->createWallet();
            $json_response = json_decode($wallet_response);
            if($json_response->status === 'success') {
                $record = new stdClass();
                $record->userid = $userId;
                $record->useruuid = $user_uuid;
                $record->walletuuid = $json_response->wallet;
                $record->status = 'Active';
                $record->timecreated = time();

                if(!$DB->record_exists('paygw_paynocchio_wallets', ['userid' => $userId])) {
                    $DB->insert_record('paygw_paynocchio_wallets', $record);

                    notification::success('Your rewarding wallet has been activated');

                    $wallet_balance_response = $wallet->getWalletBalance($json_response->wallet);

                    try{
                        $paymentuser = $DB->get_record('user', ['id' => $userId]);
                        $supportuser = core_user::get_support_user();

                        email_to_user($paymentuser, $supportuser, get_string('paynocchio_activation_subject', 'paygw_paynocchio'), get_string('paynocchio_activation_message', 'paygw_paynocchio', ['username' => $paymentuser->firstname . ' ' . $paymentuser->lastname ]));

                    } catch (\Exception $e) {
                        // On localhost email sending fails
                        return [
                            'success' => true,
                            'wallet_uuid' => $json_response->wallet,
                            'card_number' => chunk_split(strval($wallet_balance_response['number']), 4, ' '),
                        ];
                    }
                    // SUCCESS
                    return [
                        'success' => true,
                        'wallet_uuid' => $json_response->wallet,
                        'card_number' => chunk_split(strval($wallet_balance_response['number']), 4, ' '),
                    ];
                } else {
                    // Database record already exists
                    return [
                        'success' => false,
                        'wallet_uuid' => $json_response->code,
                        'card_number' => 'Database error while writing, please reload and try again',
                    ];
                }
                // Error response from backend
            } else {
                return [
                    'success' => false,
                    'wallet_uuid' => $json_response->code,
                    'card_number' => 'error',
                ];
            }
        }

    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Paynocchio success status'),
            'wallet_uuid' => new external_value(PARAM_TEXT, 'Paynocchio client ID'),
            'card_number' => new external_value(PARAM_TEXT, 'Paynocchio card_number'),
        ]);
    }
}
