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

use core\uuid;
use paygw_paynocchio\paynocchio_helper;

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $PAGE->requires->js_call_amd('paygw_paynocchio/install_plugin', 'init');

    $settings->add(new admin_setting_heading('paygw_paynocchio_settings', '', get_string('pluginname_desc', 'paygw_paynocchio')));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/baseurl', get_string('baseurl', 'paygw_paynocchio'), get_string('baseurl', 'paygw_paynocchio'), 'https://wallet.stage.paynocchio.com', PARAM_TEXT));
    $settings->add(new admin_setting_configcheckbox('paygw_paynocchio/testmode', get_string('testmode', 'paygw_paynocchio'), get_string('testmode_help', 'paygw_paynocchio'), 0));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/brandname', get_string('brandname', 'paygw_paynocchio'), get_string('brandname_help', 'paygw_paynocchio'), 'Campus.Pay'));
    $settings->add(new admin_setting_configstoredfile('paygw_paynocchio/brandlogo', get_string('brandlogo', 'paygw_paynocchio'), get_string('brandlogo_help', 'paygw_paynocchio'), 'brandlogoimage'));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/environmentuuid', get_string('environment_uuid', 'paygw_paynocchio'), get_string('environment_uuid_help', 'paygw_paynocchio'), '', PARAM_TEXT));

    $secret = new admin_setting_configtext('paygw_paynocchio/paynocchiosecret', get_string('paynocchio_secret', 'paygw_paynocchio'), get_string('secret_help', 'paygw_paynocchio'), '', PARAM_TEXT);
    $secret->set_updatedcallback(function () {
        global $USER;
        if(is_siteadmin($USER->id)){
            $wallet = new paynocchio_helper(uuid::generate());
            $wallet_response = $wallet->createWallet();
            $json_response = json_decode($wallet_response);
            if($json_response->status === 'success') {
                \core\notification::success('Integrated with Paynocchio successfully.');
                set_config('paynocchiointegrated', true, 'paygw_paynocchio');
            } else {
                set_config('paynocchiointegrated', false, 'paygw_paynocchio');
            }
        }
    });

    $settings->add($secret);
    $settings->add(new admin_setting_configcheckbox('paygw_paynocchio/sendconfmail', get_string('send_confirmation_mail', 'paygw_paynocchio'), '', 0));
    $settings->add(new admin_setting_configtextarea('paygw_paynocchio/terms', get_string('terms', 'paygw_paynocchio'), '', get_string('terms_help', 'paygw_paynocchio')));
    $settings->add(new admin_setting_configtextarea('paygw_paynocchio/privacy', get_string('privacy', 'paygw_paynocchio'), '', get_string('privacy_help', 'paygw_paynocchio')));

    $integrated = new admin_setting_configcheckbox('paygw_paynocchio/paynocchiointegrated', get_string('paynocchio_integrated', 'paygw_paynocchio'), get_string('paynocchio_integrated_help', 'paygw_paynocchio'), false, PARAM_BOOL);
    $integrated->set_locked_flag_options(admin_setting_flag::ENABLED, false);
    $settings->add($integrated);

    \core_payment\helper::add_common_gateway_settings($settings, 'paygw_paynocchio');
}
$systemcontext = \context_system::instance();
$node = new admin_category('paynocchio', get_config('paygw_paynocchio', 'brandname'));
$ADMIN->add('root', $node);

/*$ADMIN->add(
    'paynocchio', new admin_externalpage(
        'managepaynocchio',
        get_string('manage', 'paygw_paynocchio'),
        new moodle_url('/payment/gateway/paynocchio/manage.php'), 'paygw/paynocchio:managepayments'
    )
);*/


