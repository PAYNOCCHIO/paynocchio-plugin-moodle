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
$cardBg = get_config('paygw_paynocchio', 'paynocchiocardbg');
$PAGE->set_url('/payment/gateway/paynocchio/my_paynocchio_wallet.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title($brandName);
$PAGE->navbar->add(get_string('profile'), new moodle_url('/user/profile.php', array('id' => $USER->id)));
$PAGE->navbar->add($brandName);
$success = optional_param('success', 0, PARAM_BOOL);

echo $OUTPUT->header();

$user = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);

if($success) {
    \core\notification::success('You have successfully replenished your wallet!');
    
}

if($user && $user->useruuid && $user->walletuuid) {

    $wallet = new paynocchio_helper($user->useruuid);
    $walletIsHealthy = $wallet->isWalletHealthy();

    if($walletIsHealthy) {

        $envStructure = $wallet->getEnvironmentStructure();
        $wallet_balance_response = $wallet->getWalletBalance($user->walletuuid);
        $wallet_balance = $wallet_balance_response['balance'];
        $wallet_bonuses = $wallet_balance_response['bonuses'];
        $wallet_response_code = $wallet_balance_response['code'];
        $minimum_topup_amount = $envStructure['minimum_topup_amount'];
        $card_balance_limit = $envStructure['card_balance_limit'];

        if($wallet_response_code === "ACTIVE") {
            $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_topup', 'init', [
                'pay' => false,
                'minimum_topup_amount' => $minimum_topup_amount,
                'card_balance_limit' => $card_balance_limit,
                'balance' => $wallet_balance,
            ]);

            $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_withdraw', 'init', [
                'balance' => $wallet_balance,
            ]);
        }

        $data = [
        'wallet_balance' => $wallet_balance,
        'wallet_bonuses' => $wallet_bonuses,
        'wallet_card' => chunk_split($wallet_balance_response['number'], 4, ' '),
        'wallet_status' => $wallet_balance_response['status'],
        'wallet_code' => $wallet_response_code,
        'wallet_blocked' => $wallet_response_code === "BLOCKED",
        'wallet_active' => $wallet_response_code === "ACTIVE",
        'minimum_topup_amount' => $envStructure['minimum_topup_amount'],
        'bonus_conversion_rate' => $envStructure['bonus_conversion_rate'],
        'allow_withdraw' => $wallet_response_code === "ACTIVE" && $envStructure['allow_withdraw'] && $wallet_balance > 0,
        'cardBg' => $cardBg,
        'logo' => paynocchio_helper::custom_logo(),
        'wallet_activated' => true,
        'username' => $USER->firstname . ' ' . $USER->lastname,
        'isAdmin' => is_siteadmin($USER->id),
        ];

        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_all_in_one_cabinet', $data);

        $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_status_control', 'init', ['wallet_uuid' => $user->walletuuid]);
        echo $OUTPUT->render_from_template('paygw_paynocchio/wallet_status_control', $data);

        $transactions = $DB->get_records('paygw_paynocchio_transactions', ['userid'  => $USER->id], 'timecreated DESC');
        $count_transactions = $DB->count_records('paygw_paynocchio_transactions', ['userid'  => $USER->id]);
        $wallet_transactions_data = [
            'transactions' => array_values($transactions),
            'hastransactions' => $count_transactions > 0,
        ];
        echo $OUTPUT->render_from_template('paygw_paynocchio/wallet_transactions', $wallet_transactions_data);
    } else {
        echo $OUTPUT->render_from_template('paygw_paynocchio/server_error', []);
    }


    if(is_siteadmin($USER->id)) {
        echo '<!--';
        echo 'env_uuid: '. $wallet->get_env(). '<br/>';
        echo 'user_uuid: '. $user->useruuid. '<br/>';
        echo 'wallet_uuid: '. $user->walletuuid. '<br/>';
        echo 'secret: '. $wallet->get_secret(). '<br/>';
        echo 'wallet signature: '. $wallet->getSignature(). '<br/>';
        echo 'company signature: '. $wallet->getSignature(true). '<br/>';
        echo 'generated signature: '. hash("sha256", $wallet->get_secret() . "|" . $wallet->get_env() . "|" . $user->useruuid). '<br/>';
        echo '<br/>';
        echo 'Card balance limit: '. $envStructure['card_balance_limit']. '<br/>';


        echo '<pre>';
        print_r($envStructure['rewarding_group']);
        echo '</pre>';
        echo '-->';
    }

} else {
    $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_activation', 'init', ['user_id' => $USER->id]);
    $useruuid = \core\uuid::generate();
    $wallet = new paynocchio_helper($useruuid);
    if($wallet->isWalletHealthy()) {
        $data = [
            'wallet_balance' => 0,
            'wallet_bonuses' => 0,
            'wallet_card' => false,
            'wallet_status' => '',
            'wallet_code' => '',
            'wallet_uuid' => '',
            'user_uuid' => '',
            'user_id' => $USER->id,
            'brandname' => get_config('paygw_paynocchio', 'brandname'),
            'cardBg' => $cardBg,
            'logo' => paynocchio_helper::custom_logo(),
            'username' => $USER->firstname . ' ' . $USER->lastname,
        ];

        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_all_in_one_cabinet', $data);
        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_congratz', $data);
    } else {
        echo $OUTPUT->render_from_template('paygw_paynocchio/server_error', []);
    }
}

echo $OUTPUT->footer();