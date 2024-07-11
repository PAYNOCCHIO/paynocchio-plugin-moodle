<?php
// This file is part of the Paynocchio payments module for Moodle - http://moodle.org/
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
 * Settings for the Paynocchio payment gateway
 *
 * @package    paygw_paynocchio
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\notification;
use core\uuid;
use core_payment\helper;
use paygw_paynocchio\paynocchio_helper;

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    global $USER;
    $PAGE->requires->js_call_amd('paygw_paynocchio/install_plugin', 'init');

    $settings->add(new admin_setting_heading('paygw_paynocchio_settings', '', get_string('pluginname_desc', 'paygw_paynocchio')));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/baseurl', get_string('baseurl', 'paygw_paynocchio'), get_string('baseurl', 'paygw_paynocchio'), 'https://wallet.stage.paynocchio.com', PARAM_TEXT));
    $settings->add(new admin_setting_configcheckbox('paygw_paynocchio/testmode', get_string('testmode', 'paygw_paynocchio'), get_string('testmode_help', 'paygw_paynocchio'), 0));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/brandname', get_string('brandname', 'paygw_paynocchio'), get_string('brandname_help', 'paygw_paynocchio'), 'Campus.Pay'));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/paynocchiocardbg', get_string('paynocchiocardbg', 'paygw_paynocchio'), get_string('paynocchiocardbg_help', 'paygw_paynocchio'), '#3b82f6', PARAM_TEXT));

    $settings->add(new admin_setting_configstoredfile('paygw_paynocchio/brandlogo', get_string('brandlogo', 'paygw_paynocchio'), get_string('brandlogo_help', 'paygw_paynocchio'), 'brandlogoimage'));
    $env = new admin_setting_configtext('paygw_paynocchio/environmentuuid', get_string('environment_uuid', 'paygw_paynocchio'), get_string('environment_uuid_help', 'paygw_paynocchio'), '', PARAM_TEXT);
    $settings->add($env);

    $secret = new admin_setting_configtext('paygw_paynocchio/paynocchiosecret', get_string('paynocchio_secret', 'paygw_paynocchio'), get_string('secret_help', 'paygw_paynocchio'), '', PARAM_TEXT);
    $settings->add($secret);

    $wallet = new paynocchio_helper(uuid::generate());

    $wallet_response = $wallet->getCurrencies();
    $json_response = json_decode($wallet_response['response']);
    $currencies = [];
    if($wallet_response['status_code'] === 200) {
        foreach ($json_response->currencies as $obj) {
            $currencies[$obj->alphabetic_code] = $obj->alphabetic_code;
        }
    } else {
        $currencies = ['USD' => 'USD', 'SGD' => 'SGD'];
    }
    $settings->add(new admin_setting_configselect('paygw_paynocchio/currency', 'Currency', '', 'USD', $currencies));

    $settings->add(new admin_setting_configtextarea('paygw_paynocchio/terms', get_string('terms', 'paygw_paynocchio'), '', get_string('terms_help', 'paygw_paynocchio')));
    $settings->add(new admin_setting_configtextarea('paygw_paynocchio/privacy', get_string('privacy', 'paygw_paynocchio'), '', get_string('privacy_help', 'paygw_paynocchio')));

    $secret->set_updatedcallback(function () {
        global $USER;
        if(is_siteadmin($USER->id)){
            $wallet = new paynocchio_helper(uuid::generate());
            $wallet_response = $wallet->healtCheck();
            if($wallet_response['status_code'] === 200) {
                $json_response = json_decode($wallet_response['response']);
                if($json_response->status_code === 200) {
                    notification::success('Secret key is good. Integrated with Paynocchio successfully.');
                    set_config('paynocchiointegrated', 'true', 'paygw_paynocchio');
                } else {
                    set_config('paynocchiointegrated', 0, 'paygw_paynocchio');
                }
            } else {
                set_config('paynocchiointegrated', 0, 'paygw_paynocchio');
            }
        }
    });

    $env->set_updatedcallback(function () {
        global $USER;
        if(is_siteadmin($USER->id)){
            $wallet = new paynocchio_helper(uuid::generate());
            $wallet_response = $wallet->healtCheck();
            if($wallet_response['status_code'] === 200) {
                $json_response = json_decode($wallet_response['response']);
                if($json_response->status_code === 200) {
                    notification::success('Environment ID is good. Integrated with Paynocchio successfully.');
                    set_config('paynocchiointegrated', 'true', 'paygw_paynocchio');
                } else {
                    set_config('paynocchiointegrated', 0, 'paygw_paynocchio');
                }
            } else {
                set_config('paynocchiointegrated', 0, 'paygw_paynocchio');
            }
        }
    });

    helper::add_common_gateway_settings($settings, 'paygw_paynocchio');
}
$systemcontext = \context_system::instance();
$node = new admin_category('paynocchio', get_config('paygw_paynocchio', 'brandname'));
$ADMIN->add('root', $node);

$ADMIN->add(
    'paynocchio', new admin_externalpage(
        'managepayments',
        get_string('manage', 'paygw_paynocchio'),
        new moodle_url('/payment/gateway/paynocchio/manage.php'), 'paygw/paynocchio:managepayments'
    )
);

