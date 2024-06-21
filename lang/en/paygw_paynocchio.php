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
$string['paynocchiocardbg'] = 'The background color of your branded Card.';
$string['paynocchiocardbg_help'] = 'Color name (red) or hex (#ff0000).';
$string['paynocchio_activation_subject'] = 'Your Wallet successfully activated';
$string['paynocchio_activation_message'] = '<h3>Dear {$a->username}!</h3><p>Congratulations! Your new '. get_config('paygw_paynocchio', 'brandname') .' Wallet has been successfully activated. Now you can make a deposit on '. $GLOBALS['SITE']->fullname .'.</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['paynocchio_topup_subject'] = 'Your Wallet has been successfully topped up';
$string['paynocchio_topup_message'] = '<h3>Dear {$a->username}!</h3><p>Congratulations! You have successfully topped up your '. get_config('paygw_paynocchio', 'brandname') .' Wallet with {$a->sum}. Now you can check your rewards and spend it on '. $GLOBALS['SITE']->fullname .'.</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['paynocchio_withdraw_subject'] = 'Successful withdrawal from your Wallet';
$string['paynocchio_withdraw_message'] = '<h3>Dear {$a->username}!</h3><p>Congratulations! You have successfully withdrawn {$a->sum} from your '. get_config('paygw_paynocchio', 'brandname') .' Wallet. The money will be deposited into your account in accordance with the rules of your bank and Stripe.</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['paynocchio_transaction_subject'] = 'Successful payment with your Wallet';
$string['paynocchio_transaction_message'] = '<h3>Dear {$a->username}!</h3><p>Congratulations! You have successfully paid {$a->sum} from your '. get_config('paygw_paynocchio', 'brandname') .' Wallet. The order details will be emailed to you soon.</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['paynocchio_confirmation_subject'] = 'Successful enrollment in the course';
$string['paynocchio_confirmation_message'] = '<h3>Dear {$a->username}!</h3><p>Congratulations! Your order has been confirmed and you have been enrolled in the course.</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['paynocchio_suspend_subject'] = 'Your Wallet has been temporarily deactivated';
$string['paynocchio_suspend_message'] = '<h3>Dear {$a->username}!</h3><p>Your '. get_config('paygw_paynocchio', 'brandname') .' Wallet has been deactivated, but rest assured, all your money is safe. You can reactivate your wallet at any time.</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['paynocchio_reactivate_subject'] = 'Your Wallet has been reactivated';
$string['paynocchio_reactivate_message'] = '<h3>Dear {$a->username}!</h3><p>Congratulations! You have successfully reactivated your '. get_config('paygw_paynocchio', 'brandname') .' Wallet. You can now enjoy spending money and earning rewards again!</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['paynocchio_block_subject'] = 'Your Wallet has been blocked';
$string['paynocchio_block_message'] = '<h3>Dear {$a->username}!</h3><p>You have successfully blocked your '. get_config('paygw_paynocchio', 'brandname') .' Wallet. You will not be able to use it from now on.</p><p>If you blocked your wallet by mistake, please contact the '. $GLOBALS['SITE']->fullname .' support team.</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['paynocchio_delete_subject'] = 'Your Wallet has been deleted';
$string['paynocchio_delete_message'] = '<h3>Dear {$a->username}!</h3><p>You have successfully deleted your '. get_config('paygw_paynocchio', 'brandname') .' Wallet. We hope to see you as our client again soon.</p><p>Best wishes, '. $GLOBALS['SITE']->fullname .' team.</p>';
$string['brandlogo'] = 'Your custom payment Logo';
$string['brandlogo_help'] = 'User your own brand style';
$string['cannotfetchorderdatails'] = 'Could not fetch payment details from Paynocchio. Your account has not been debited.';
$string['testmode'] = 'Test mode';
$string['testmode_help'] = 'Set this to Test mode if you are using test environment at Paynocchio.';
$string['manage'] = 'Manage Transfers';
$string['paynocchio:managepayments'] = 'Manage Transfers';
$string['managepayments'] = 'Manage Transfers';
$string['gatewaydescription'] = 'We offer exceptional long-term rewards where you can earn up to 10% in reward points per top up and transaction.';
$string['gatewayname'] = 'Campus.Pay';
$string['internalerror'] = 'An internal error has occurred. Please contact us.';
$string['paymentnotcleared'] = 'payment not cleared by Paynocchio.';
$string['pluginname'] = 'Paynocchio';
$string['pluginname_desc'] = 'The Paynocchio plugin allows you to receive payments via Paynocchio.';
$string['privacy:metadata'] = 'The Paynocchio plugin does not store any personal data.';
$string['repeatedorder'] = 'This order has already been processed earlier.';
$string['paynocchio_secret'] = 'Secret key';
$string['environment_uuid'] = 'Environment id';
$string['environment_uuid_help'] = 'The environment id could be obtained at the Paynocchio Control Panel in the Environment management section.';
$string['useruuid'] = 'Paynocchio user UUID';
$string['walletuuid'] = 'Paynocchio Wallet UUID';
$string['pending_payments'] = 'Pending transfer payments';
$string['my_paynocchio_wallet'] = 'My rewarding wallet';
$string['paynocchio'] = 'Paynocchio';
$string['paynocchiodescription'] = 'Unleash the ultimate cashback opportunities with';
$string['paynocchio_wallet'] = 'Rewarding Wallet';
$string['pay'] = 'Pay';
$string['applybonuses'] = 'Apply available bonuses';
$string['applybonuses_help'] = 'Your order will be partially covered by bonuses.';
$string['secret_help'] = 'The secret key could be obtained at the Paynocchio Control Panel in the Environment management section.';
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
$string['terms'] = 'Your Site Terms and Conditions';
$string['terms_help'] = 'Your Site Terms and Conditions concerning Paynocchio Pay method.';
$string['privacy'] = 'Your site Privacy Policy';
$string['privacy_help'] = 'Your site Privacy Policy concerning Paynocchio Pay method.';
$string['cost'] = 'Cost';
$string['total_cost'] = 'Total cost';
$string['paynocchio_integrated'] = 'Integration status';
$string['paynocchio_integrated_help'] = 'True if integration with Paynocchio was successful';
