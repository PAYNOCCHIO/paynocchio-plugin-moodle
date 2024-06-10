<?php

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
$description = required_param('description', PARAM_TEXT);
$description=json_decode('"'.$description.'"');
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
        'reward' => $record->totalamount * 0.1,
        'status' => $record->status,
        'completed' => $record->status === 'C',
    ];
    echo $OUTPUT->render_from_template('paygw_paynocchio/enrolled_already', ['data' => $data]);

} else {
    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<ul class="list-group list-group-flush">';
    if ($surcharge && $surcharge > 0) {

        echo '<li class="list-group-item">' . get_string('cost', 'paygw_paynocchio') . ':';
        echo '<h4 id="price">' . helper::get_cost_as_string($payable->get_amount(), $currency) . '</h4>';
        echo '</li>';
        echo '<li class="list-group-item"><h4>' . get_string('surcharge', 'core_payment') . ':</h4>';
        echo '<div id="price">' . $surcharge. '%</div>';
        echo '<div id="explanation">' . get_string('surcharge_desc', 'core_payment') . '</div>';
        echo '</li>';

        echo '<li class="list-group-item"><h4>' . get_string('total_cost', 'paygw_paynocchio') . ':</h4>';
        echo '<div id="price">' . helper::get_cost_as_string($amount, $currency). ' ' . $currency . '</div>';
        echo '</li>';
    } else {
        echo '<li class="list-group-item">' . get_string('total_cost', 'paygw_paynocchio') . ':';
        echo '<h4 id="price">' . helper::get_cost_as_string($amount, $currency). ' ' . $currency . '</h4>';
        echo '</li>';
    }
    echo "</ul>";

    $user = $DB->get_record('paygw_paynocchio_wallets', ['userid'  => $USER->id]);

    if($user && $user->useruuid && $user->walletuuid) {

        $wallet = new paynocchio_helper($user->useruuid);

        $wallet_balance_response = $wallet->getWalletBalance($user->walletuuid);

        $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_topup', 'init', [
            'pay' => true
        ]);
        $PAGE->requires->js_call_amd('paygw_paynocchio/paynocchio_pay', 'init', [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'fullAmount' => $amount,
            'balance' => $wallet_balance_response['balance'],
        ]);

        if($wallet_balance_response['bonuses'] && $wallet_balance_response['bonuses'] < $amount) {
            $max_bonus = $wallet_balance_response['bonuses'];
        } else {
            $max_bonus = $amount;
        }

        $need_to_topup = ceil(($amount - floor($wallet_balance_response['balance']) - floor($wallet_balance_response['bonuses']))/1.1);

        $data = [
            'wallet_balance' => $wallet_balance_response['balance'] ?? 0,
            'wallet_bonuses' => $wallet_balance_response['bonuses'] ?? 0,
            'wallet_card' => chunk_split($wallet_balance_response['number'], 4, ' '),
            'wallet_status' => $wallet_balance_response['status'],
            'wallet_code' => $wallet_balance_response['code'],
            'wallet_uuid' => $user->walletuuid,
            'user_uuid' => $user->useruuid,
            'max_bonus' => $max_bonus ?? 0,
            'full_amount' => $amount,
            'bonuses_amount' => $need_to_topup * 0.1,
            'need_to_topup' => $need_to_topup,
            'total_with_bonuses' => $need_to_topup + $need_to_topup * 0.1,
            'bottom_line' => $amount - $need_to_topup + $need_to_topup * 0.1,
            'can_pay' => $wallet_balance_response['balance'] + $wallet_balance_response['bonuses'] >= $amount,
            'wallet_active' => $wallet_balance_response['code'] === "ACTIVE",
            'logo' => paynocchio_helper::custom_logo(),
        ];

        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_payment_wallet', $data);

        $PAGE->requires->js_call_amd('paygw_paynocchio/terms_and_conditions', 'init', []);
        echo $OUTPUT->render_from_template('paygw_paynocchio/terms_and_conditions', []);

    } else {
        $PAGE->requires->js_call_amd('paygw_paynocchio/wallet_activation', 'init', ['user_id' => $USER->id]);

        $data = [
            'user_id' => $USER->id,
            'logo' => paynocchio_helper::custom_logo(),
            'full_amount' => $amount,
            'new_amount' => $amount * 0.1,
            'brandname' => get_config('paygw_paynocchio', 'brandname'),
        ];

        echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_activation', $data);
    }

    echo "</div>";
    echo "</div>";
}

echo $OUTPUT->footer();
