<?php

use paygw_paynocchio\paynocchio_helper;

global $CFG, $PAGE, $USER, $OUTPUT, $DB;

require_once __DIR__ . '/../../../config.php';
require_once './lib.php';
require_once("$CFG->dirroot/user/profile/lib.php");
require_login();

$context = context_system::instance(); // Because we "have no scope".
$PAGE->set_context(context_user::instance($USER->id));
$brandName = get_config('paygw_paynocchio', 'brandname');
$PAGE->set_url('/payment/gateway/paynocchio/my_paynocchio_wallet.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title($brandName);
$PAGE->navbar->add(get_string('profile'), new moodle_url('/user/profile.php', array('id' => $USER->id)));
$PAGE->navbar->add($brandName);

echo $OUTPUT->header();

$user = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);

if($user && $user->useruuid && $user->walletuuid) {
    $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_topup', 'init');

    $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_withdraw', 'init', [
        'pay' => true
    ]);

    $wallet = new paynocchio_helper($user->useruuid);
    $wallet_balance_response = $wallet->getWalletBalance($user->walletuuid);

    $data = [
        'wallet_balance' => $wallet_balance_response['balance'],
        'wallet_bonuses' => $wallet_balance_response['bonuses'],
        'wallet_card' => chunk_split($wallet_balance_response['number'], 4, ' '),
        'wallet_status' => $wallet_balance_response['status'],
        'wallet_code' => $wallet_balance_response['code'],
        'wallet_blocked' => $wallet_balance_response['code'] === "BLOCKED",
        'wallet_active' => $wallet_balance_response['code'] === "ACTIVE",
        'logo' => paynocchio_helper::custom_logo(),
    ];

    echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet', $data);
    if($wallet_balance_response['code'] === "ACTIVE") {
        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_actions_buttons', $data);
    }

    $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_status_control', 'init', ['wallet_uuid' => $user->walletuuid]);
    echo $OUTPUT->render_from_template('paygw_paynocchio/wallet_status_control', $data);

    $transactions = $DB->get_records('paygw_paynocchio_transactions', ['userid'  => $USER->id], 'timecreated DESC');
    $count_transactions = $DB->count_records('paygw_paynocchio_transactions', ['userid'  => $USER->id]);
    $wallet_transactions_data = [
        'transactions' => array_values($transactions),
        'hastransactions' => $count_transactions > 0,
    ];
    echo $OUTPUT->render_from_template('paygw_paynocchio/wallet_transactions', $wallet_transactions_data);

if(is_siteadmin($USER->id)) {
    echo 'user_uuid: '. $user->useruuid. '<br/>';
    echo 'wallet_uuid: '. $user->walletuuid. '<br/>';
    echo 'secret: '. $wallet->get_secret(). '<br/>';
    echo 'env_uuid: '. $wallet->get_env(). '<br/>';
    echo 'wallet signature: '. $wallet->getSignature(). '<br/>';
    echo 'company signature: '. $wallet->getSignature(true). '<br/>';
    echo 'generated signature: '. hash("sha256", $wallet->get_secret() . "|" . $wallet->get_env() . "|" . $user->useruuid). '<br/>';
}

} else {
    $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_activation', 'init', ['user_id' => $USER->id]);

    $data = [
        'user_id' => $USER->id,
        'logo' => paynocchio_helper::custom_logo(),
        'brandname' => get_config('paygw_paynocchio', 'brandname')
    ];

    echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_activation', $data);
}

echo $OUTPUT->footer();