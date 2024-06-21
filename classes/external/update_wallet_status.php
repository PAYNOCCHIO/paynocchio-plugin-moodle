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
use core_user;
use paygw_paynocchio\paynocchio_helper;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

class update_wallet_status extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'status' => new external_value(PARAM_TEXT, 'Status'),
        ]);
    }

    /**
     * Returns the config values required by the Paynocchio JavaScript SDK.
     * @param int $userId
     * @return string[]
     */
    public static function execute(string $status): array
    {
        global $DB, $USER;
        self::validate_parameters(self::execute_parameters(), [
            'status' => $status,
        ]);

        $paynocchio_data = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);
        $user_uuid = $paynocchio_data->useruuid;
        $wallet_uuid = $paynocchio_data->walletuuid;
        $paynocchio_wallet = $DB->get_record('paygw_paynocchio_wallets', ['walletuuid'  => $paynocchio_data->walletuuid]);

        if($wallet_uuid) {
            $wallet = new paynocchio_helper($user_uuid);
            $wallet_statuses = $wallet->getWalletStatuses();
            $wallet_response = $wallet->updateWalletStatus($wallet_uuid, $wallet_statuses[$status]);

            if($wallet_response['status_code'] === 200) {

                $updated = paynocchio_helper::updateWalletDBStatus((int) $paynocchio_wallet->id, $paynocchio_data->walletuuid, $status);

                $wallet_balance_response = $wallet->getWalletBalance($wallet_uuid);

                if($wallet_balance_response && $updated) {
                    notification::success('Status has been successfully changed!');

                    $current_status = $wallet_balance_response['status'];

                    try {
                        $paymentuser = $DB->get_record('user', ['id' => $USER->id]);
                        $supportuser = core_user::get_support_user();

                        if ($current_status == 'BLOCKED') {
                            email_to_user($paymentuser, $supportuser, get_string('paynocchio_block_subject', 'paygw_paynocchio'), get_string('paynocchio_block_message', 'paygw_paynocchio', ['username' => $paymentuser->firstname . ' ' . $paymentuser->lastname]));
                        }

                        if ($current_status == 'SUSPEND') {
                            email_to_user($paymentuser, $supportuser, get_string('paynocchio_suspend_subject', 'paygw_paynocchio'), get_string('paynocchio_suspend_message', 'paygw_paynocchio', ['username' => $paymentuser->firstname . ' ' . $paymentuser->lastname]));
                        }

                        if ($current_status == 'ACTIVE') {
                            email_to_user($paymentuser, $supportuser, get_string('paynocchio_reactivate_subject', 'paygw_paynocchio'), get_string('paynocchio_reactivate_message', 'paygw_paynocchio', ['username' => $paymentuser->firstname . ' ' . $paymentuser->lastname]));
                        }
                    } catch (\Exception $e) {
                        return [
                            'success' => true,
                            'wallet_status' => $wallet_balance_response['status'],
                            'wallet_code' => $wallet_balance_response['code'],
                        ];
                    }

                    return [
                        'success' => true,
                        'wallet_status' => $wallet_balance_response['status'],
                        'wallet_code' => $wallet_balance_response['code'],
                    ];
                }
            } else {
                notification::error('Please reload and try again!');
                return [
                    'success' => false,
                    'wallet_status' => 'ERROR',
                    'wallet_code' => 404,
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
            'wallet_status' => new external_value(PARAM_TEXT, 'Paynocchio wallet status'),
            'wallet_code' => new external_value(PARAM_TEXT, 'Paynocchio wallet code'),
        ]);
    }
}
