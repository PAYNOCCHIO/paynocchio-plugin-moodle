<?php

use core_payment\helper;
use paygw_bank\bank_helper;
use paygw_bank\pay_form;
use paygw_bank\attachtransfer_form;

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
$mform = new pay_form(null, array('confirm' => 1, 'component' => $component, 'paymentarea' => $paymentarea, 'itemid' => $itemid, 'description' => $description));
$mform->set_data($params);
$at_form = new attachtransfer_form();
$at_form->set_data($params);
$dataform = $mform->get_data();
$at_dataform = $at_form->get_data();
$confirm = 0;
if ($dataform != null) {
    $component = $dataform->component;
    $paymentarea = $dataform->paymentarea;
    $itemid = $dataform->itemid;
    $description = $dataform->description;
    $confirm = $dataform->confirm;
}
if ($at_dataform != null) {
    $component = $at_dataform->component;
    $paymentarea = $at_dataform->paymentarea;
    $itemid = $at_dataform->itemid;
    $description = $at_dataform->description;
    $confirm = $at_dataform->confirm;
}

$context = context_system::instance(); // Because we "have no scope".
$PAGE->set_context($context);

$PAGE->set_url('/payment/gateway/paynocchio/pay.php', $params);
$PAGE->set_pagelayout('report');
$pagetitle = $description;
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'bank');
$payable = helper::get_payable($component, $paymentarea, $itemid);
$currency = $payable->get_currency();
$bank_entry = null;

// Add surcharge if there is any.
$surcharge = helper::get_gateway_surcharge('bank');
$amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('gatewayname', 'paygw_bank'), 2);
echo '<div class="card">';
echo '<div class="card-body">';
echo '<ul class="list-group list-group-flush">';
echo '<li class="list-group-item"><h5 class="card-title">' . get_string('concept', 'paygw_bank') . ':</h5>';
?>

    <div class="paynocchio-card" style="color:#ffffff; background:#3b82f6">
        <img src="http://kopybara.com/wp-content/uploads/2024/03/cropped-favicon.png" class="on_card_embleme">
        <div class="paynocchio-balance-bonuses">
            <div class="paynocchio-balance">
                <div>
                    Balance
                </div>
                <div class="amount">
                    $<span class="paynocchio-numbers paynocchio-balance-value" data-balance="0">0</span>
                </div>
            </div>
            <div class="paynocchio-bonuses">
                <div>
                    Bonuses
                </div>
                <div class="amount">
                    <span class="paynocchio-numbers paynocchio-bonus-value" data-bonus="0">0</span>
                </div>
            </div>
        </div>
        <div class="paynocchio-card-number">
            <div>6829</div><div>5232</div><div>1251</div><div>1356</div><div></div>
        </div>
        <div class="paynocchio_payment_card_button">
            <a href="#" class="paynocchio_button cfps-flex cfps-flex-row cfps-items-center" data-modal=".topUpModal" style="color:#3b82f6; background:#ffffff">
                <svg class="cfps-h-[20px] cfps-w-[20px] cfps-mr-1 cfps-inline-block" enable-background="new 0 0 50 50" version="1.1" viewBox="0 0 50 50" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                        <rect fill="none" height="50" width="50"></rect>
                    <line fill="none" stroke="#1a85f9" stroke-miterlimit="10" stroke-width="4" x1="9" x2="41" y1="25" y2="25"></line>
                    <line fill="none" stroke="#1a85f9" stroke-miterlimit="10" stroke-width="4" x1="25" x2="25" y1="9" y2="41"></line>
                                    </svg>
                Add money
            </a>
        </div>
    </div>

<?php
echo '<div>' . $description. '</div>';
echo "</li>";
$aceptform = "";
$instructions = "";
print_r(get_config('paygw_paynocchio'));
print 'asfdasdf';
$instructions = format_text($config->instructionstext['text']);

if (bank_helper::has_openbankentry($itemid, $USER->id)) {
    $bank_entry = bank_helper::get_openbankentry($itemid, $USER->id);
    $amount = $bank_entry->totalamount;
    $confirm = 0;
} else {

    if ($confirm != 0) {
        $totalamount = $amount;
        $data = $mform->get_data();
        $bank_entry = bank_helper::create_bankentry($itemid, $USER->id, $totalamount, $currency, $component, $paymentarea, $description);
        \core\notification::info(get_string('transfer_process_initiated', 'paygw_bank'));
        $confirm = 0;
    }
}

if ($surcharge && $surcharge > 0 && $bank_entry == null) {
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('cost', 'paygw_bank') . ':</h4>';
    echo '<div id="price">' . helper::get_cost_as_string($payable->get_amount(), $currency) . '</div>';
    echo '</li>';
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('surcharge', 'core_payment') . ':</h4>';
    echo '<div id="price">' . $surcharge. '%</div>';
    echo '<div id="explanation">' . get_string('surcharge_desc', 'core_payment') . '</div>';
    echo '</li>';
    
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('total_cost', 'paygw_bank') . ':</h4>';
    echo '<div id="price">' .helper::get_cost_as_string($amount, $currency). ' ' . $currency . '</div>';
    echo '</li>';
} else {
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('total_cost', 'paygw_bank') . ':</h4>';
    echo '<div id="price">' . helper::get_cost_as_string($amount, $currency). ' ' . $currency . '</div>';
    echo '</li>';
}
if ($bank_entry != null) {
    echo '<li class="list-group-item"><h4 class="card-title">' . get_string('transfer_code', 'paygw_bank') . ':</h4>';
    echo '<div id="transfercode">' . $bank_entry->code . '</div>';
    echo '</li>';
    $instructions = format_text($config->postinstructionstext['text']);
}
echo "</ul>";
echo '<div id="bankinstructions">' . $instructions . '</div>';
if ($confirm == 0 && !bank_helper::has_openbankentry($itemid, $USER->id)) {
    $mform->display();
}
echo "</div>";
echo "</div>";
echo $OUTPUT->footer();
