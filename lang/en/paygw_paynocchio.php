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
 * Strings for component 'paygw_paynocchio', language 'en'
 *
 * @package    paygw_paynocchio
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['baseurl'] = 'Paynocchio wallet API url';
$string['modulename'] = 'Paynocchio';
$string['amountmismatch'] = 'The amount you attempted to pay does not match the required fee. Your account has not been debited.';
$string['authorising'] = 'Authorising the payment. Please wait...';
$string['brandname'] = 'Brand name';
$string['brandname_help'] = 'An optional label that overrides the business name for the Paynocchio account on the site.';
$string['cannotfetchorderdatails'] = 'Could not fetch payment details from Paynocchio. Your account has not been debited.';
$string['testmode'] = 'Test mode';
$string['testmode_help'] = 'You can set this to Test mode if you are using test accounts (for testing purpose only).';
$string['manage'] = 'Manage Transfers';
$string['paynocchio:managepayments'] = 'Manage Transfers';
$string['managepayments'] = 'Manage Transfers';
$string['gatewaydescription'] = 'Paynocchio is an authorised payment gateway provider for processing credit card transactions.';
$string['gatewayname'] = 'Paynocchio';
$string['internalerror'] = 'An internal error has occurred. Please contact us.';
$string['paymentnotcleared'] = 'payment not cleared by Paynocchio.';
$string['pluginname'] = 'Paynocchio';
$string['pluginname_desc'] = 'The Paynocchio plugin allows you to receive payments via Paynocchio.';
$string['privacy:metadata'] = 'The Paynocchio plugin does not store any personal data.';
$string['repeatedorder'] = 'This order has already been processed earlier.';
$string['paynocchio_secret'] = 'Secret';
$string['environment_uuid'] = 'Environment UUID';
$string['environment_uuid_help'] = 'The Environment UUID that Paynocchio generated for your application.';
$string['useruuid'] = 'Paynocchio user UUID';
$string['walletuuid'] = 'Paynocchio Wallet UUID';
$string['pending_payments'] = 'Pending transfer payments';
$string['my_paynocchio_wallet'] = 'My Paynocchio wallet';
$string['paynocchio'] = 'Paynocchio';
$string['paynocchiodescription'] = 'Paynocchio is the awesome way to pay and get rewarded';
$string['paynocchio_wallet'] = 'Paynocchio Wallet';
$string['secret_help'] = 'The secret that Paynocchio generated for your application.';
$string['send_confirmation_mail'] = 'Send confirmation email';
$string['email_notifications'] = 'Email internal notifications';
$string['email_notifications_help'] = 'An external email address can be notified when a new payment is queued or their status change';
$string['email_to_notify'] = 'Email to send notifications';
$string['email_notifications_subject_new'] = 'New bank payment entry';
$string['email_notifications_subject_attachments'] = 'A payment entry has new attachments';
$string['email_notifications_subject_confirm'] = 'A payment entry has approved';
$string['email_notifications_new_request'] = 'There is a new bank payment request. code: {$a->code}';
$string['email_notifications_new_attachments'] = 'The bank payment entry with code {$a->code} has new attachments';
$string['email_notifications_confirm'] = 'The bank payment entry with code {$a->code} is approved';
$string['additional_currencies'] = 'Aditional Currencies';
$string['additional_currencies_help'] = 'A comma separated list of currency codes. You can consult the codes in https://en.wikipedia.org/wiki/ISO_4217#Active_codes';
