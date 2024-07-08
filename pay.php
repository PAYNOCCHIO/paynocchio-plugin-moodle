<?php

use core\notification;
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
$success = optional_param('success', 0, PARAM_BOOL);
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

if($success) {
    notification::success('You have successfully replenished your wallet!');
}

// Add surcharge if there is any.
$surcharge = helper::get_gateway_surcharge('paynocchio');

echo $OUTPUT->header();

if(paynocchio_helper::user_has_payed($itemid, (int) $USER->id)) {
    $record = $DB->get_record('paygw_paynocchio_payments', ['userid'  => $USER->id, 'itemid' => $itemid]);
    $data = [
        'timecreated' => $record->timecreated,
        'totalamount' => $record->totalamount,
        'paid' => $record->paid,
        'bonuses_used' => $record->bonuses_used,
        'status' => $record->status === 'C' ? 'Completed' : 'Pending',
        'completed' => $record->status === 'C',
    ];
    if($record->status !== 'C') {
        $PAGE->requires->js_call_amd('paygw_paynocchio/check_transaction_complete', 'init', ['paymentid' => $record->paymentid]);
    }
    echo $OUTPUT->render_from_template('paygw_paynocchio/enrolled_already', ['data' => $data]);

} else {
    echo '<div class="paynocchio-container">';
    echo '<div class="paynocchio-container-body">';
    echo '<a onclick="window.history.go(-1)" class="back_button"><img src="/payment/gateway/paynocchio/pix/back.png" alt="Backward button" width="30" height="30" /> Back</a>';

    $user = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);

    if($user && $user->useruuid && $user->walletuuid) {
        $wallet = new paynocchio_helper($user->useruuid);
        $wallet_uuid = $user->walletuuid;
        $useruuid = $user->useruuid;
    } else {
        $useruuid = uuid::generate();
        $wallet = new paynocchio_helper($useruuid);
        $wallet_uuid = 0;
    }

    $environment_structure = $wallet->getEnvironmentStructure();

    $conversion_rate_when_payment = $environment_structure['bonus_conversion_rate'] ?: 1;
    $minimum_topup_amount = $environment_structure['minimum_topup_amount'];
    $card_balance_limit = $environment_structure['card_balance_limit'];

    $course_rounded_cost = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

    $calculateNeedToTopUpWithCommission = $wallet->calculateNeedToTopUpWithCommission($course_rounded_cost);
    $calculateRewardsAndCommissions = $wallet->calculateRewardsAndCommissions($calculateNeedToTopUpWithCommission['need_to_topup_with_commission'], 'payment_operation_add_money');

    print_r($calculateNeedToTopUpWithCommission);

    $wallet_balance_response = $wallet->getWalletBalance($wallet_uuid) ?: 0;
    $wallet_balance = $wallet_balance_response['balance'];
    $max_bonuses_to_spend = $wallet_balance_response['bonuses'];
    $money_bonuses_equivalent = $max_bonuses_to_spend * $conversion_rate_when_payment;
    $wallet_response_code = $wallet_balance_response['code'];
    $rewarding_rules = $environment_structure['rewarding_group']->rewarding_rules;


    if ($wallet_response_code == 'SUSPEND') {
        $wallet_status_readable = 'Wallet suspended';
    } elseif ($wallet_response_code == 'BLOCKED') {
        $wallet_status_readable = 'Wallet blocked';
    } else {
        $wallet_status_readable = 'Wallet activated';
    }

    if ($wallet_balance_response['number']) {
        $wallet_card = chunk_split($wallet_balance_response['number'], 4, ' ');
    } else {
        $wallet_card = false;
    }

    $data = [
        'wallet_balance' => $wallet_balance ?? 0,
        'wallet_bonuses' => $calculateNeedToTopUpWithCommission['max_bonuses_to_spend'] ?? 0,
        'bonus_conversion_rate' => $environment_structure['bonus_conversion_rate'],
        'bonus_conversion_rate_equal' => $environment_structure['bonus_conversion_rate'] === 1,
        'wallet_card' => $wallet_card,
        'wallet_status' => $wallet_balance_response['status'],
        'wallet_status_readable' => $wallet_status_readable,
        'wallet_code' => $wallet_response_code,
        'wallet_uuid' => $wallet_uuid,
        'user_uuid' => $useruuid,
        'max_bonus' => $calculateNeedToTopUpWithCommission['max_bonus'] ?? 0,
        'full_amount' => $course_rounded_cost,
        'bonuses_for_topup' => $calculateRewardsAndCommissions['bonuses_to_get'],
        'bonuses_for_topup_in_dollar' => $calculateRewardsAndCommissions['bonuses_in_dollar'],
        'bonuses_for_payment' => $calculateNeedToTopUpWithCommission['bonuses_for_payment'],
        'need_to_topup' => $calculateNeedToTopUpWithCommission['need_to_topup_with_commission'],
        'can_pay' => $wallet_balance + $money_bonuses_equivalent >= $course_rounded_cost,
        'wallet_active' => $wallet_response_code === "ACTIVE",
        'wallet_suspend' => $wallet_response_code === "SUSPEND",
        'wallet_blocked' => $wallet_response_code === "BLOCKED",
        'logo' => paynocchio_helper::custom_logo(),
        'description' => $pagetitle,
        'cardBg' => $cardBg,
        'brandname' => get_config('paygw_paynocchio', 'brandname'),
        'username' => $USER->firstname . ' ' . $USER->lastname,
    ];

    echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_all_in_one_payment', $data);

    if($user && $useruuid && $wallet_uuid) {

        if($wallet_response_code === "ACTIVE") {
            $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_topup', 'init', [
                'pay' => true,
                'minimum_topup_amount' => $minimum_topup_amount,
                'card_balance_limit' => $card_balance_limit,
                'balance' => $wallet_balance,
                'rewarding_rules' => $rewarding_rules,
            ]);
        }

        $PAGE->requires->js_call_amd('paygw_paynocchio/paynocchio_pay', 'init', [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'description' => $description,
            'itemid' => $itemid,
            'fullAmount' => $course_rounded_cost,
            'balance' => $wallet_balance,
            'bonuses_conversion_rate' => $conversion_rate_when_payment,
        ]);



        $PAGE->requires->js_call_amd('paygw_paynocchio/terms_and_conditions', 'init', []);
        echo $OUTPUT->render_from_template('paygw_paynocchio/terms_and_conditions', []);

        $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_status_control', 'init', ['wallet_uuid' => $user->walletuuid]);

    } else {

        $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_activation', 'init', ['user_id' => $USER->id]);

        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_congratz', $data);
    }

    echo "</div>";
    echo "</div>";
}

echo $OUTPUT->footer();
