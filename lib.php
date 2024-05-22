<?php
// This file is part of the Paynocchio paymnts module for Moodle - http://moodle.org/
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
 * Plugin version and other meta-data are defined here.
 *
 * @package    paygw_paynocchio
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use paygw_paynocchio\paynocchio_helper;

defined('MOODLE_INTERNAL') || die();

function paygw_paynocchio_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course)
{
    $files = paynocchio_helper::files();
    $logo_url = moodle_url::make_pluginfile_url(
        $files[0]->get_contextid(),
        $files[0]->get_component(),
        $files[0]->get_filearea(),
        $files[0]->get_itemid(),
        $files[0]->get_filepath(),
        $files[0]->get_filename(),
        false                     // Do not force download of the file.
    );
    $url = new moodle_url('/payment/gateway/paynocchio/my_paynocchio_wallet.php');
    $category = new core_user\output\myprofile\category('paynocchio_wallet', get_config('paygw_paynocchio', 'brandname'), null);
    $node = new core_user\output\myprofile\node(
        'paynocchio_wallet',
        'my_paynocchio_wallet',
        get_string('my_paynocchio_wallet', 'paygw_paynocchio'),
        null,
        $url,
        '<div class="paynocchio-payment-description"><img width="100" src="'.$logo_url.'" alt="'.get_string('paynocchio', 'paygw_paynocchio').'" />  '.get_string('paynocchiodescription', 'paygw_paynocchio'). ' ' . get_config('paygw_paynocchio', 'brandname').'</div>'
    );
    $tree->add_category($category);
    $tree->add_node($node);
}

function paygw_paynocchio_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    if ($filearea !== 'brandlogoimage') {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login();
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'paygw_paynocchio', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering. 
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($str, $end)
    {
        return (@substr_compare($str, $end, -strlen($end)) == 0);
    }
}

function paygw_paynocchio_moove_additional_header() {

    global $DB, $USER;
    $user = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);

    $files = paynocchio_helper::files();
    $logo_url = moodle_url::make_pluginfile_url(
        $files[0]->get_contextid(),
        $files[0]->get_component(),
        $files[0]->get_filearea(),
        $files[0]->get_itemid(),
        $files[0]->get_filepath(),
        $files[0]->get_filename(),
        false                     // Do not force download of the file.
    );

    if($user && $user->useruuid && $user->walletuuid) {

        $wallet = new paynocchio_helper($user->useruuid);

        $wallet_balance_response = $wallet->getWalletBalance($user->walletuuid);

        return '<div class="paynocchio-mini-block status-'.$wallet_balance_response['status'].'">
    <a href="/payment/gateway/paynocchio/my_paynocchio_wallet.php" title="Rewarding wallet"><img decoding="async" src="'.$logo_url.'" class="on_card_embleme" alt=""></a>
     <div role="button" class="amount" tabindex="0" data-toggle="popover" data-trigger="click, hover, focus" data-content="Wallet balance">
                <i class="fa-solid fa-wallet"></i> 
                $<span class="numbers alance-value" data-balance="0">'.$wallet_balance_response['balance'].'</span>
            </div>
        <div role="button" class="bonuses" tabindex="0" data-toggle="popover" data-trigger="click, hover, focus" data-content="Rewarding balance">
            <i class="fa-solid fa-star"></i>
            <span class="numbers bonus-value">'.$wallet_balance_response['bonuses'].'</span>
        </div>
</div>';
    } else {
        if($USER->id){
            return '<a class="paynocchio-mini-block" href="/payment/gateway/paynocchio/my_paynocchio_wallet.php" title="Rewarding wallet">
        <div class="bonuses">
            <i class="fa-solid fa-star"></i>
            Start earning rewards with '.get_config('paygw_paynocchio', 'brandname').'!
        </div>
</a>';
        }
    }

}

