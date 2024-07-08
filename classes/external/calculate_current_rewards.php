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

class calculate_current_rewards extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'amount' => new external_value(PARAM_FLOAT, 'Current input sum'),
            'operationType' => new external_value(PARAM_TEXT, 'operationType'),
        ]);
    }

    /**
     * Returns the config values required by the Paynocchio JavaScript SDK.
     * @param float $amount
     * @param string $operationType
     */
    public static function execute(float $amount, string $operationType): array
    {
        global $DB, $USER;
        self::validate_parameters(self::execute_parameters(), [
            'amount' => $amount,
            'operationType' => $operationType,
        ]);

        $paynocchio_data = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);
        $user_uuid = $paynocchio_data->useruuid;

        $wallet = new paynocchio_helper($user_uuid);
        $data = $wallet->calculateRewardsAndCommissions($amount, $operationType);

        return [
            'bonuses_to_get' => $data['bonuses_to_get'],
            'commission' => $data['commission'],
            'sum_without_commission' => $data['sum_without_commission'],
            'sum_with_commission' => $data['sum_with_commission'],
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'bonuses_to_get' => new external_value(PARAM_TEXT, 'Type'),
            'commission' => new external_value(PARAM_TEXT, 'Commission'),
            'sum_without_commission' => new external_value(PARAM_TEXT, 'Sum without commission'),
            'sum_with_commission' => new external_value(PARAM_TEXT, 'Sum with commission'),
        ]);
    }
}
