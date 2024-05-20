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

use core\uuid;
use core_payment\helper;
use core_payment\helper as payment_helper;
use core_reportbuilder\local\filters\number;
use mod_lti\local\ltiservice\response;
use paygw_paynocchio\paynocchio_helper;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use stdClass;

class make_payment extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'The component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'The item id in the context of the component area'),
            'fullAmount' => new external_value(PARAM_FLOAT, 'Full order amount'),
            'bonuses' => new external_value(PARAM_FLOAT, 'Bonuses used to pay for the Order'),
        ]);
    }

    /**
     * Returns the config values required by the Paynocchio JavaScript SDK.
     *
     * @return string[]
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function execute(string $component, string $paymentarea, int $itemid, float $fullAmount, float $bonuses): array
    {
        global $DB, $USER;

        $userid = (int) $USER->id;

        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'fullAmount' => $fullAmount,
            'bonuses' => $bonuses,
        ]);

        $payable = payment_helper::get_payable($component, $paymentarea, $itemid);
        $currency = $payable->get_currency();

        $paynocchio_data = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $userid]);
        $user_uuid = $paynocchio_data->useruuid;
        $wallet_uuid = $paynocchio_data->walletuuid;

        if($wallet_uuid) {
            $wallet = new paynocchio_helper($user_uuid);
            $wallet_balance_response = $wallet->getWalletBalance($wallet_uuid);

            if($fullAmount > floatval($wallet_balance_response['balance']) + floatval($wallet_balance_response['bonuses'])) {
                return [
                    'success' => false,
                    'message' => 'Insufficient funds',
                    'balance' => $wallet_balance_response['balance'],
                    'bonuses' => $wallet_balance_response['bonuses'],
                    'card_number' => 0,
                    'wallet_status' => 'Insufficient funds',
                    'wallet_code' => 'error',
                ];
            }

            $originalAmount = $fullAmount;
            $amount = $fullAmount;
            $bonusAmount = $bonuses ?: null;

            if(!$bonusAmount) {
                $fullAmount = null;
            } else {
                $amount = $fullAmount - $bonusAmount;
            }

            $orderuuid = uuid::generate();

            $wallet_response = $wallet->makePayment($wallet_uuid, $fullAmount, $amount, $orderuuid, (float) $bonuses);

            if($wallet_response['status_code'] === 200) {

                $paymentid = payment_helper::save_payment($payable->get_account_id(), $component, $paymentarea,
                    $itemid, $userid, $originalAmount, $currency, 'paynocchio');

                //payment_helper::deliver_order($component, $paymentarea, $itemid, $paymentid, $userid);
                //paynocchio_helper::registerTransaction($userid, 'payment', $amount, $bonuses, $paymentid);
                //paynocchio_helper::registerPayment($paymentid, $component, $paymentarea, $itemid, $orderuuid, $userid, $originalAmount, 'P');

                if($wallet_balance_response) {
                    return [
                        'success' => true,
                        'balance' => $wallet_balance_response['balance'],
                        'bonuses' => $wallet_balance_response['bonuses'],
                        'card_number' => $wallet_balance_response['number'],
                        'wallet_status' => $wallet_balance_response['status'],
                        'wallet_code' => $wallet_balance_response['code'],
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
        ]);
    }
}
