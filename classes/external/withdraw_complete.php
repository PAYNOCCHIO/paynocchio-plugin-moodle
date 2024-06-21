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
 * This class contains a list of webservice functions related to the PayPal payment gateway.
 *
 * @package    paygw_paynocchio
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace paygw_paynocchio\external;

use core_external\external_api;
use core_external\external_value;
use core_external\external_function_parameters;
use core_payment\helper as payment_helper;
use core_user;
use paygw_paynocchio\paynocchio_helper;

class withdraw_complete extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'uuid' => new external_value(PARAM_TEXT, 'The uuid coming back from Paynocchio'),
            'external_request_id' => new external_value(PARAM_TEXT, 'The external_request_id coming back from Paynocchio'),
            'created_at' => new external_value(PARAM_TEXT, 'The created_at coming back from Paynocchio'),
            'company_id' => new external_value(PARAM_TEXT, 'The company_id coming back from Paynocchio'),
            'payment_method' => new external_value(PARAM_TEXT, 'The payment_method coming back from Paynocchio'),
            'amount' => new external_value(PARAM_TEXT, 'The amount coming back from Paynocchio'),
            'currency_id' => new external_value(PARAM_TEXT, 'The currency_id coming back from Paynocchio'),
            'wallet_uuid' => new external_value(PARAM_TEXT, 'The wallet_uuid coming back from Paynocchio'),
            'user_uuid' => new external_value(PARAM_TEXT, 'The user_uuid coming back from Paynocchio'),
            'status_type' => new external_value(PARAM_TEXT, 'The status type coming back from Paynocchio'),
        ]);
    }

    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     * This function does not take cost as a parameter as we cannot rely on any provided value.
     *
     * @param string $uuid Paynocchio uuid
     * @param string $external_request_id Paynocchio external_request_id
     * @param string $created_at Paynocchio created_at
     * @param string $company_id Paynocchio company_id
     * @param string $payment_method Paynocchio payment_method
     * @param string $amount Paynocchio amount
     * @param string $currency_id Paynocchio currency_id
     * @param string $wallet_uuid Paynocchio wallet_uuid
     * @param string $user_uuid Paynocchio user_uuid
     * @param string $status_type Paynocchio status type
     * @return array
     */
    public static function execute(
        string $uuid,
        string $external_request_id,
        string $created_at,
        string $company_id,
        string $payment_method,
        string $amount,
        string $currency_id,
        string $wallet_uuid,
        string $user_uuid,
        string $status_type
    ): array {
        global $DB, $USER;

        self::validate_parameters(self::execute_parameters(), [
            'uuid' => $uuid,
            'external_request_id' => $external_request_id,
            'created_at' => $created_at,
            'company_id' => $company_id,
            'payment_method' => $payment_method,
            'amount' => $amount,
            'currency_id' => $currency_id,
            'wallet_uuid' => $wallet_uuid,
            'user_uuid' => $user_uuid,
            'status_type' => $status_type,
        ]);

        $user = $DB->get_record('paygw_paynocchio_wallets', ['useruuid' => $user_uuid]);

        if($user) {
            paynocchio_helper::registerTransaction((int) $user->id, 'withdrawn', (float)$amount, 0, null);

            $paymentuser = $DB->get_record('user', ['id' => $user->userid]);
            $supportuser = core_user::get_support_user();

            email_to_user($paymentuser, $supportuser, get_string('paynocchio_withdraw_subject', 'paygw_paynocchio'), get_string('paynocchio_withdraw_message', 'paygw_paynocchio', ['username' => $USER->firstname . ' ' . $USER->lastname, 'sum' => $amount ]));

            return [
                'success' => true,
                'message' => 'Order updated as completed',
            ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Some error',
                ];
            }
    }

    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters
     */
    public static function execute_returns() {
        return new external_function_parameters([
            'success' => new external_value(PARAM_BOOL, 'Whether everything was successful or not.'),
            'message' => new external_value(PARAM_RAW, 'Message (usually the error message).'),
        ]);
    }
}
