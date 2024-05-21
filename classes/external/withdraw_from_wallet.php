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
use paygw_paynocchio\paynocchio_helper;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

class withdraw_from_wallet extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'amount' => new external_value(PARAM_FLOAT, 'Amount to topup'),
        ]);
    }

    /**
     * Returns the config values required by the Paynocchio JavaScript SDK.
     * @param int $userId
     * @return string[]
     */
    public static function execute(float $amount): array
    {
        global $DB, $USER;
        self::validate_parameters(self::execute_parameters(), [
            'amount' => $amount,
        ]);

        $paynocchio_data = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);
        $user_uuid = $paynocchio_data->useruuid;
        $wallet_uuid = $paynocchio_data->walletuuid;

        if($wallet_uuid) {
            $wallet = new paynocchio_helper($user_uuid);
            $wallet_response = $wallet->withdrawFromWallet($wallet_uuid, $amount);

            if($wallet_response['status_code'] === 200) {

                paynocchio_helper::registerTransaction((int) $USER->id, 'withdrawn', $amount, 0, null);

                $wallet_balance_response = $wallet->getWalletBalance($wallet_uuid);

                if($wallet_balance_response) {
                    $transactions = $DB->get_records('paygw_paynocchio_transactions', ['userid'  => $USER->id], 'timecreated DESC', 'id,timecreated,type,totalamount');
                    $count_transactions = $DB->count_records('paygw_paynocchio_transactions', ['userid'  => $USER->id]);
                    notification::success('Withdrawal has been successfully changed!');

                    return [
                        'success' => true,
                        'balance' => $wallet_balance_response['balance'],
                        'bonuses' => $wallet_balance_response['bonuses'],
                        'card_number' => $wallet_balance_response['number'],
                        'wallet_status' => $wallet_balance_response['status'],
                        'wallet_code' => $wallet_balance_response['code'],
                        'transactions' => json_encode(array_values($transactions)),
                        'hastransactions' => $count_transactions > 0,
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'balance' => 0,
                    'bonuses' => 0,
                    'card_number' => 0,
                    'wallet_status' => 'ERROR',
                    'wallet_code' => 404,
                    'transactions' => null,
                    'hastransactions' => false,
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
            'balance' => new external_value(PARAM_FLOAT, 'Paynocchio wallet balance'),
            'bonuses' => new external_value(PARAM_FLOAT, 'Paynocchio wallet bonus balance'),
            'card_number' => new external_value(PARAM_INT, 'Paynocchio card number'),
            'wallet_status' => new external_value(PARAM_TEXT, 'Paynocchio wallet status'),
            'wallet_code' => new external_value(PARAM_TEXT, 'Paynocchio wallet code'),
            'transactions' => new external_value(PARAM_RAW, 'Paynocchio wallet code'),
            'hastransactions' => new external_value(PARAM_BOOL, 'Paynocchio wallet code'),
        ]);
    }
}
