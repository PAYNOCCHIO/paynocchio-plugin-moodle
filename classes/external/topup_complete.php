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

class topup_complete extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'uuid' => new external_value(PARAM_TEXT, 'The uuid coming back from Paynocchio'),
            'request_uuid' => new external_value(PARAM_TEXT, 'The request_uuid coming back from Paynocchio'),
            'environment_uuid' => new external_value(PARAM_TEXT, 'The uuid coming back from Paynocchio'),
            'user_uuid' => new external_value(PARAM_TEXT, 'The user_uuid coming back from Paynocchio'),
            'wallet_uuid' => new external_value(PARAM_TEXT, 'The wallet_uuid coming back from Paynocchio'),
            'amount' => new external_value(PARAM_FLOAT, 'The amount coming back from Paynocchio'),
            'currency' => new external_value(PARAM_TEXT, 'The currency coming back from Paynocchio'),
            'type_operation' => new external_value(PARAM_TEXT, 'The type_operation coming back from Paynocchio'),
            'status_type' => new external_value(PARAM_TEXT, 'The status type coming back from Paynocchio'),
            'order_uuid' => new external_value(PARAM_RAW, 'The order_uuid id coming back from Paynocchio'),
            'external_order_uuid' => new external_value(PARAM_RAW, 'The order id coming back from Paynocchio'),
        ]);
    }

    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     * This function does not take cost as a parameter as we cannot rely on any provided value.
     *
     * @param string $uuid Paynocchio uuid
     * @param string $request_uuid Paynocchio request_uuid
     * @param string $environment_uuid Paynocchio environment_uuid
     * @param string $user_uuid Paynocchio user_uuid
     * @param string $wallet_uuid Paynocchio wallet_uuid
     * @param float $amount Paynocchio amount
     * @param string $type_operation Paynocchio type_operation
     * @param string $status_type Paynocchio status type
     * @return array
     */
    public static function execute(
         $uuid,
         $request_uuid,
         $environment_uuid,
         $user_uuid,
         $wallet_uuid,
         $amount,
         $type_operation,
         $status_type,
        $order_uuid,
        $external_order_uuid,
    ): array {
        global $DB;

        self::validate_parameters(self::execute_parameters(), [
            'uuid' => $uuid,
            'request_uuid' => $request_uuid,
            'environment_uuid' => $environment_uuid,
            'user_uuid' => $user_uuid,
            'wallet_uuid' => $wallet_uuid,
            'amount' => $amount,
            'type_operation' => $type_operation,
            'status_type' => $status_type,
            'order_uuid' => $order_uuid,
            'external_order_uuid' => $external_order_uuid,
        ]);

        $wallet = $DB->get_record('paygw_paynocchio_wallets', ['useruuid' => $user_uuid]);

        if($wallet) {
            paynocchio_helper::registerTransaction((int) $wallet->userid, 'topup', (float)$amount, 0, null);

            $paymentuser = $DB->get_record('user', ['id' => $wallet->userid]);
            $supportuser = core_user::get_support_user();

            email_to_user($paymentuser, $supportuser, get_string('paynocchio_topup_subject', 'paygw_paynocchio'), get_string('paynocchio_topup_message', 'paygw_paynocchio', ['username' => $paymentuser->firstname . ' ' . $paymentuser->lastname, 'sum' => $amount]));

            return [
                'success' => true,
                'message' => 'You have successfully replenished your wallet!',
            ];
            } else {
                return [
                    'success' => false,
                    'message' => 'An error occurred during the replenishment. Please try again.',
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
