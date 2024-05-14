<?php

use paygw_paynocchio\paynocchio_helper;

require_once __DIR__ . '/../../../config.php';
require_once './lib.php';
require_once("$CFG->dirroot/user/profile/lib.php");
require_login();

$context = context_system::instance(); // Because we "have no scope".
$PAGE->set_context(context_user::instance($USER->id));
$canuploadfiles=get_config('paygw_paynocchio', 'usercanuploadfiles');
$PAGE->set_url('/payment/gateway/paynocchio/my_paynocchio_wallet.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('my_paynocchio_wallet', 'paygw_paynocchio'));
//$PAGE->navigation->extend_for_user($USER->id);
$PAGE->navbar->add(get_string('profile'), new moodle_url('/user/profile.php', array('id' => $USER->id)));
$PAGE->navbar->add(get_string('my_paynocchio_wallet', 'paygw_paynocchio'));

echo $OUTPUT->header();

//$wallet = new paynocchio_helper($user_uuid);

$user = $DB->get_record('paygw_paynocchio_data', ['userid'  => $USER->id]);

if($user && $user->useruuid && $user->walletuuid) {
    $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_topup', 'init');

    $wallet = new paynocchio_helper($user->useruuid);
    $wallet_balance_response = $wallet->getWalletBalance($user->walletuuid);

    $data = [
        'wallet_balance' => $wallet_balance_response['balance'],
        'wallet_bonuses' => $wallet_balance_response['bonuses'],
        'wallet_card' => $wallet_balance_response['number'],
        'wallet_status' => $wallet_balance_response['status'],
        'wallet_code' => $wallet_balance_response['code'],
    ];

    echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet', $data);
} else {
    $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_activation', 'init', ['user_id' => $USER->id]);

    $data = [
        'user_id' => $USER->id,
    ];

    echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_activation', $data);
}

echo $OUTPUT->footer();