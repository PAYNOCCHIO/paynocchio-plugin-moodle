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

class transaction_complete extends external_api {

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
            'amount' => new external_value(PARAM_TEXT, 'The amount coming back from Paynocchio'),
            'currency' => new external_value(PARAM_TEXT, 'The currency coming back from Paynocchio'),
            'type_operation' => new external_value(PARAM_TEXT, 'The type_operation coming back from Paynocchio'),
            'status_type' => new external_value(PARAM_TEXT, 'The status type coming back from Paynocchio'),
            'order_uuid' => new external_value(PARAM_TEXT, 'The order_uuid id coming back from Paynocchio'),
            'external_order_uuid' => new external_value(PARAM_TEXT, 'The order id coming back from Paynocchio'),
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
     * @param string $amount Paynocchio amount
     * @param string $currency Paynocchio currency
     * @param string $type_operation Paynocchio type_operation
     * @param string $status_type Paynocchio status type
     * @param string $order_uuid Paynocchio order_uuid ID
     * @param string $external_order_uuid Paynocchio external_order_uuid ID
     * @return array
     */
    public static function execute(
        string $uuid,
        string $request_uuid,
        string $environment_uuid,
        string $user_uuid,
        string $wallet_uuid,
        string $amount,
        string $currency,
        string $type_operation,
        string $status_type,
        string $order_uuid,
        string $external_order_uuid): array {
        global $DB;

        self::validate_parameters(self::execute_parameters(), [
            'uuid' => $uuid,
            'request_uuid' => $request_uuid,
            'environment_uuid' => $environment_uuid,
            'user_uuid' => $user_uuid,
            'wallet_uuid' => $wallet_uuid,
            'amount' => $amount,
            'currency' => $currency,
            'type_operation' => $type_operation,
            'status_type' => $status_type,
            'order_uuid' => $order_uuid,
            'external_order_uuid' => $external_order_uuid,
        ]);

        $order = $DB->get_record('paygw_paynocchio_payments', ['orderuuid' => $external_order_uuid]);

        if($order) {
            if($status_type === 'complete') {
                $order->status = 'C';
                $order->timeupdated = time();

                paynocchio_helper::registerTransaction((int) $order->userid, 'payment', (float)$amount, 0, $order->paymentid);
                payment_helper::deliver_order($order->component, $order->paymentarea, (int) $order->itemid, (int) $order->paymentid, (int) $order->userid);

                $DB->update_record('paygw_paynocchio_payments', $order);

                $paymentuser = $DB->get_record('user', ['id' => $order->userid]);
                $supportuser = core_user::get_support_user();

                email_to_user($paymentuser, $supportuser, 'Payment complete', 'Your order has been confirmed and you have been enrolled in the course');

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
        return [
            'success' => false,
            'message' => 'Order not found',
        ];
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
