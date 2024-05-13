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
 * paygw_paynocchio installer script.
 *
 * @package    paygw_paynocchio
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_paygw_paynocchio_install() {
    global $CFG, $DB;

    // Enable the Paypal payment gateway on installation. It still needs to be configured and enabled for accounts.
    $order = (!empty($CFG->paygw_plugins_sortorder)) ? explode(',', $CFG->paygw_plugins_sortorder) : [];
    set_config('paygw_plugins_sortorder', join(',', array_merge($order, ['paynocchio'])));

    //$fieldsTable = 'user_info_field';

    // USER UUID
    /*if (!$DB->record_exists($fieldsTable, array('shortname' => 'paynocchio_user_uuid'))) {
        $user_uuid = new stdClass();
        $user_uuid->shortname = 'paynocchio_user_uuid';
        $user_uuid->name = get_string('useruuid', 'paygw_paynocchio');
        $user_uuid->datatype = 'text';
        $user_uuid->categoryid = 1;
        $user_uuid->description = '';
        $user_uuid->sortorder = 1;
        $user_uuid->required = 0;
        $user_uuid->locked = 1;
        $user_uuid->visible = 1;
        $user_uuid->forceunique = 0;
        $user_uuid->signup = 0;
        $user_uuid->param1 = 30;
        $user_uuid->param2 = 9;
        $user_uuid->param3 = 0;
        $user_uuid->param4 = null;
        $user_uuid->param5 = null;
        $DB->insert_record($fieldsTable, $user_uuid, false);
    }*/

    // WALLET UUID
    /*if (!$DB->record_exists($fieldsTable, array('shortname' => 'paynocchio_wallet_uuid'))) {
        $wallet_uuid = new stdClass();
        $wallet_uuid->shortname = 'paynocchio_wallet_uuid';
        $wallet_uuid->name = get_string('walletuuid', 'paygw_paynocchio');
        $wallet_uuid->datatype = 'text';
        $wallet_uuid->categoryid = 1;
        $wallet_uuid->description = '';
        $wallet_uuid->sortorder = 1;
        $wallet_uuid->required = 0;
        $wallet_uuid->locked = 1;
        $wallet_uuid->visible = 1;
        $wallet_uuid->forceunique = 0;
        $wallet_uuid->signup = 0;
        $wallet_uuid->param1 = 30;
        $wallet_uuid->param2 = 9;
        $wallet_uuid->param3 = 0;
        $wallet_uuid->param4 = null;
        $wallet_uuid->param5 = null;
        $DB->insert_record($fieldsTable, $wallet_uuid, false);
    }*/
}
