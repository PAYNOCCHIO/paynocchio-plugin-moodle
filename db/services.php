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
 * External functions and service definitions for the Paynocchio payment gateway plugin.
 *
 * @package    paygw_paynocchio
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'paygw_paynocchio_get_config_for_js' => [
        'classname'   => 'paygw_paynocchio\external\get_config_for_js',
        'classpath'   => '',
        'description' => 'Returns the configuration settings to be used in js',
        'type'        => 'read',
        'ajax'        => true,
    ],

    'paygw_paynocchio_activate_wallet' => [
        'classname'   => 'paygw_paynocchio\external\activate_wallet',
        'classpath'   => '',
        'description' => 'Generates UUID for user, registers Wallet UUID.',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_topup_wallet' => [
        'classname'   => 'paygw_paynocchio\external\topup_wallet',
        'classpath'   => '',
        'description' => 'Adds money to Paynocchio Wallet.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_withdraw_from_wallet' => [
        'classname'   => 'paygw_paynocchio\external\withdraw_from_wallet',
        'classpath'   => '',
        'description' => 'Withdraws money to Paynocchio Wallet.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_make_payment' => [
        'classname'   => 'paygw_paynocchio\external\make_payment',
        'classpath'   => '',
        'description' => 'Make payment with Paynocchio Wallet.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_update_wallet_status' => [
        'classname'   => 'paygw_paynocchio\external\update_wallet_status',
        'classpath'   => '',
        'description' => 'Update Paynocchio Wallet status.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_delete_wallet' => [
        'classname'   => 'paygw_paynocchio\external\delete_wallet',
        'classpath'   => '',
        'description' => 'Delete Paynocchio Wallet status.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_get_conf' => [
        'classname'   => 'paygw_paynocchio\external\get_conf',
        'classpath'   => '',
        'description' => 'Get plugin conf.',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_create_transaction_complete' => [
        'classname'   => 'paygw_paynocchio\external\transaction_complete',
        'classpath'   => '',
        'description' => 'Takes care of what needs to be done when a Paynocchio transaction comes back as complete.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_create_topup_complete' => [
        'classname'   => 'paygw_paynocchio\external\topup_complete',
        'classpath'   => '',
        'description' => 'Takes care of what needs to be done when a Paynocchio topup comes back as complete.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],

    'paygw_paynocchio_create_withdraw_complete' => [
        'classname'   => 'paygw_paynocchio\external\withdraw_complete',
        'classpath'   => '',
        'description' => 'Takes care of what needs to be done when a Paynocchio withdraw comes back as complete.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],
];