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
 * @package    paygw_paypal
 * @copyright  2019 Shamim Rezaie <shamim@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('paygw_paynocchio_settings', '', get_string('pluginname_desc', 'paygw_paynocchio')));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/environmentuuid', get_string('environment_uuid', 'paygw_paynocchio'), get_string('environment_uuid_help', 'paygw_paynocchio'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/paynocchiosecret', get_string('paynocchio_secret', 'paygw_paynocchio'), get_string('secret_help', 'paygw_paynocchio'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configcheckbox('paygw_paynocchio/sendconfmail', get_string('send_confirmation_mail', 'paygw_paynocchio'), '', 0));
    $settings->add(new admin_setting_configcheckbox('paygw_paynocchio/senddenmail', get_string('send_denied_mail', 'paygw_paynocchio'), '', 0));
    $settings->add(new admin_setting_heading('paygw_paynocchio/notemails', get_string('email_notifications', 'paygw_paynocchio'), get_string('email_notifications_help', 'paygw_paynocchio'),));
    $settings->add(new admin_setting_configtext('paygw_paynocchio/notificationsaddress', get_string('email_to_notify', 'paygw_paynocchio'), '', '', PARAM_EMAIL));

    $settings->add(new admin_setting_configcheckbox('paygw_paynocchio/sendnewrequestmail', get_string('send_new_request_mail', 'paygw_paynocchio'), '', 0));
    $settings->add(new admin_setting_configcheckbox('paygw_paynocchio/sendnewattachmentsmail', get_string('send_new_attachments_mail', 'paygw_paynocchio'), '', 0));
    $settings->add(new admin_setting_configcheckbox('paygw_paynocchio/senconfirmailtosupport', get_string('send_confirm_mail_to_support', 'paygw_paynocchio'), '', 0));


    \core_payment\helper::add_common_gateway_settings($settings, 'paygw_paynocchio');
}
$systemcontext = \context_system::instance();
$node = new admin_category('paynocchio', get_string('pluginname', 'paygw_paynocchio'));
$ADMIN->add('root', $node);
$ADMIN->add(
    'paynocchio', new admin_externalpage(
        'managepaynocchio',
        get_string('manage', 'paygw_paynocchio'),
        new moodle_url('/payment/gateway/paynocchio/manage.php'), 'paygw/paynocchio:managepayments'
    )
);


