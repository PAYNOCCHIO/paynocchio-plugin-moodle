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

class check_withdrawal extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'amount' => new external_value(PARAM_FLOAT, 'Current input sum'),
        ]);
    }

    /**
     * Returns the config values required by the Paynocchio JavaScript SDK.
     * @param float $amount
     */
    public static function execute(float $amount): array
    {
        global $DB, $USER;
        self::validate_parameters(self::execute_parameters(), [
            'amount' => $amount,
        ]);

        $paynocchio_data = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);
        $user_uuid = $paynocchio_data->useruuid;
        $walletuuid = $paynocchio_data->walletuuid;

        $wallet = new paynocchio_helper($user_uuid);
        $walletIsHealthy = $wallet->isWalletHealthy();

        if(!$walletIsHealthy) {
            return [
                'error' => true,
                'status' => 'Wallet error. Please contact the support.',
                'commission' => 0,
                'amount_without_commission' => 0,
            ];
        }

        $envStructure = $wallet->getEnvironmentStructure();
        $wallet_balance_response = $wallet->getWalletBalance($walletuuid);


        if($amount > $wallet_balance_response['balance']) {
            return [
                'error' => true,
                'status' => 'Insufficient funds. Please check the wallet balance.',
                'commission' => 0,
                'amount_without_commission' => 0,
            ];
        }

        $wallet_response_code = $wallet_balance_response['code'];
        $withdrawalIsAllowed = $wallet_response_code === "ACTIVE" && $envStructure['allow_withdraw'] && $wallet_balance_response['balance'] > 0;

        if(!$withdrawalIsAllowed) {
            return [
                'error' => true,
                'status' => 'Withdrawal is not allowed.',
                'commission' => 0,
                'amount_without_commission' => 0,
            ];
        }

        $commission = $wallet->calculateCommissionForAmount($amount);

        return [
            'error' => false,
            'status' => 'OK',
            'commission' => $commission,
            'amount_without_commission' => round($amount - $commission, 2),
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'error' => new external_value(PARAM_BOOL, 'True / False'),
            'status' => new external_value(PARAM_TEXT, 'Status message'),
            'commission' => new external_value(PARAM_FLOAT, 'Commission'),
            'amount_without_commission' => new external_value(PARAM_FLOAT, 'Sum without commission'),
        ]);
    }
}
