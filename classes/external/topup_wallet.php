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

use paygw_paynocchio\paynocchio_helper;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

class topup_wallet extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'amount' => new external_value(PARAM_FLOAT, 'Amount to topup'),
            'redirect_url' => new external_value(PARAM_TEXT, 'Redirect url from Stripe'),
        ]);
    }

    /**
     * Returns the config values required by the Paynocchio JavaScript SDK.
     * @param float $amount
     * @param string $redirect_url
     * @return string[]
     */
    public static function execute(float $amount, string $redirect_url): array
    {
        global $DB, $USER;
        self::validate_parameters(self::execute_parameters(), [
            'amount' => $amount,
            'redirect_url' => $redirect_url,
        ]);

        $paynocchio_data = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);
        $user_uuid = $paynocchio_data->useruuid;
        $wallet_uuid = $paynocchio_data->walletuuid;

        if($wallet_uuid) {
            $wallet = new paynocchio_helper($user_uuid);
            $wallet_response = $wallet->topUpWallet($wallet_uuid, $amount, $redirect_url);
            $json_response = json_decode($wallet_response['response']);

            if($wallet_response['status_code'] === 200) {

                if(!$json_response->schemas->is_error && $json_response->schemas->url) {

                    return [
                        'success' => true,
                        'is_error' => $json_response->schemas->is_error,
                        'message' => $json_response->schemas->message,
                        'url' => $json_response->schemas->url,
                        'type_interactions' => $json_response->type_interactions,
                        'interaction' => $json_response->interaction,
                    ];
                } else {
                    \core\notification::error($json_response->schemas->message);
                    return [
                        'success' => false,
                        'is_error' => $json_response->schemas->is_error,
                        'message' => $json_response->schemas->message,
                        'url' => '',
                        'type_interactions' => $json_response->type_interactions,
                        'interaction' => $json_response->interaction,
                    ];
                }

            } else {
                \core\notification::error('An error occurred during the replenishment. Please try again.');
                return [
                    'success' => false,
                    'is_error' => $wallet_response['status_code'],
                    'message' => '🤔 Something went wrong. Please try again!',
                    'url' => '',
                    'type_interactions' => $json_response->type_interactions,
                    'interaction' => $json_response->interaction,
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
            'is_error' => new external_value(PARAM_TEXT, 'Stripe payment url'),
            'message' => new external_value(PARAM_TEXT, 'Stripe payment url'),
            'url' => new external_value(PARAM_TEXT, 'Stripe payment url'),
            'type_interactions' => new external_value(PARAM_TEXT, 'Stripe payment url'),
            'interaction' => new external_value(PARAM_TEXT, 'Stripe payment url'),
        ]);
    }
}
