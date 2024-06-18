<?php

use core\uuid;
use core_payment\helper;
use paygw_paynocchio\pay_form;
use paygw_paynocchio\paynocchio_helper;

require_once __DIR__ . '/../../../config.php';
require_once './lib.php';
$environment_uuid = get_config('paygw_paynocchio', 'environment_uuid');
require_login();
$context = context_system::instance(); // Because we "have no scope".
$PAGE->set_context($context);
$component = required_param('component', PARAM_COMPONENT);
$paymentarea = required_param('paymentarea', PARAM_AREA);
$itemid = required_param('itemid', PARAM_INT);
$cardBg = get_config('paygw_paynocchio', 'paynocchiocardbg');
$description = required_param('description', PARAM_TEXT);
$description = json_decode('"'.$description.'"');
$params = [
    'component' => $component,
    'paymentarea' => $paymentarea,
    'itemid' => $itemid,
    'description' => $description
];
$PAGE->set_url('/payment/gateway/paynocchio/pay.php', $params);

$PAGE->set_pagelayout('report');

$pagetitle = $description;

$PAGE->set_title($pagetitle);

$PAGE->set_heading($pagetitle);

$config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'paynocchio');
$payable = helper::get_payable($component, $paymentarea, $itemid);

$currency = $payable->get_currency();

// Add surcharge if there is any.
$surcharge = helper::get_gateway_surcharge('paynocchio');

$amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

echo $OUTPUT->header();

if(paynocchio_helper::has_enrolled($itemid, (int) $USER->id)) {
    $record = $DB->get_record('paygw_paynocchio_payments', ['userid'  => $USER->id, 'itemid' => $itemid]);
    $data = [
        'timecreated' => $record->timecreated,
        'totalamount' => $record->totalamount,
        'paid' => $record->paid,
        'bonuses_used' => $record->bonuses_used,
        'status' => $record->status === 'C' ? 'Completed' : 'Pending',
        'completed' => $record->status === 'C',
    ];
    echo $OUTPUT->render_from_template('paygw_paynocchio/enrolled_already', ['data' => $data]);

} else {
    echo '<div class="paynocchio-container">';
    echo '<div class="paynocchio-container-body">';
    echo '<a onclick="window.history.go(-1)" class="back_button"><img src="/payment/gateway/paynocchio/pix/back.png" alt="Backward button" width="30" height="30" /> Back</a>';

    $user = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);

    if($user && $user->useruuid) {
        $wallet = new paynocchio_helper($user->useruuid);
    } else {
        $wallet = new paynocchio_helper(uuid::generate());
    }

    $conversion_rate = $wallet->getEnvironmentStructure()['bonus_conversion_rate'];
    $minimum_topup_amount = $wallet->getEnvironmentStructure()['minimum_topup_amount'];

    if($user && $user->useruuid && $user->walletuuid) {

        $wallet_balance_response = $wallet->getWalletBalance($user->walletuuid);

        $max_bonuses_to_spend = $wallet_balance_response['bonuses'] * $conversion_rate;

        if($wallet_balance_response['code'] === "ACTIVE") {
            $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_topup', 'init', [
                'pay' => true,
                'minimum_topup_amount' => $minimum_topup_amount,
            ]);
        }

        $PAGE->requires->js_call_amd('paygw_paynocchio/paynocchio_pay', 'init', [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'fullAmount' => $amount,
            'balance' => $wallet_balance_response['balance'],
            'bonuses_conversion_rate' => $conversion_rate,
        ]);

        if($max_bonuses_to_spend && $max_bonuses_to_spend < $amount) {
            $max_bonus = $max_bonuses_to_spend;
        } else {
            $max_bonus = $amount;
        }

        $rewarding_rate = 0.1;
        $rewarding_for_topup = 1 + $rewarding_rate * $conversion_rate;
        $need_to_topup = ceil(($amount - floor($wallet_balance_response['balance']) - floor($max_bonuses_to_spend)) / $rewarding_for_topup);

        $data = [
            'wallet_balance' => $wallet_balance_response['balance'] ?? 0,
            'wallet_bonuses' => $wallet_balance_response['bonuses'] ?? 0,
            'bonus_conversion_rate' => $wallet->getEnvironmentStructure()['bonus_conversion_rate'],
            'bonus_conversion_rate_equal' => $wallet->getEnvironmentStructure()['bonus_conversion_rate'] === 1,
            'bonus_to_spend' => $max_bonuses_to_spend,
            'wallet_card' => chunk_split($wallet_balance_response['number'], 4, ' '),
            'wallet_status' => $wallet_balance_response['status'],
            'wallet_code' => $wallet_balance_response['code'],
            'wallet_uuid' => $user->walletuuid,
            'user_uuid' => $user->useruuid,
            'max_bonus' => floor($max_bonus) ?? 0,
            'full_amount' => $amount,
            'bonuses_amount' => $need_to_topup * $rewarding_rate,
            'bonuses_to_get' => $amount * 0.1,
            'need_to_topup' => $need_to_topup,
            'total_with_bonuses' => $need_to_topup + $need_to_topup * $rewarding_rate,
            'bottom_line' => $amount - $need_to_topup + $need_to_topup * $rewarding_rate,
            'can_pay' => $wallet_balance_response['balance'] + $max_bonuses_to_spend >= $amount,
            'wallet_active' => $wallet_balance_response['code'] === "ACTIVE",
            'logo' => paynocchio_helper::custom_logo(),
            'description' => $pagetitle,
            'wallet_activated' => true,
            'cardBg' => $cardBg,
            'brandname' => get_config('paygw_paynocchio', 'brandname'),
        ];
        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_all_in_one_payment', $data);
        $PAGE->requires->js_call_amd('paygw_paynocchio/terms_and_conditions', 'init', []);
        echo $OUTPUT->render_from_template('paygw_paynocchio/terms_and_conditions', []);
    } else {

        $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_activation', 'init', ['user_id' => $USER->id]);
        $need_to_topup = $amount;
        $data = [
            'wallet_balance' => 0,
            'wallet_bonuses' => 0,
            'wallet_card' => '',
            'wallet_status' => '',
            'wallet_code' => '',
            'wallet_uuid' => '',
            'user_uuid' => '',
            'max_bonus' => $max_bonus ?? 0,
            'bonuses_amount' => $need_to_topup * $conversion_rate,
            'bonuses_to_get' => $amount * $conversion_rate,
            'need_to_topup' => $need_to_topup,
            'total_with_bonuses' => $need_to_topup + $need_to_topup * $conversion_rate,
            'bottom_line' => $amount - $need_to_topup + $need_to_topup * $conversion_rate,
            'can_pay' => false,
            'wallet_active' => '',
            'user_id' => $USER->id,
            'logo' => paynocchio_helper::custom_logo(),
            'full_amount' => $amount,
            'new_amount' => $amount * $conversion_rate,
            'brandname' => get_config('paygw_paynocchio', 'brandname'),
            'itemid' => $itemid,
            'description' => $pagetitle,
        ];
        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_all_in_one_payment', $data);
    }

    echo "</div>";
    echo "</div>";
}

echo $OUTPUT->footer();
