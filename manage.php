<?php

use core\notification;
use core_payment\helper;
use paygw_paynocchio\paynocchio_helper;

require_once __DIR__ . '/../../../config.php';
require_once './lib.php';
require_login();
$context = context_system::instance(); // Because we "have no scope".
$PAGE->set_context($context);
$systemcontext = \context_system::instance();
$PAGE->set_url('/payment/gateway/paynocchio/manage.php');
$PAGE->set_pagelayout('report');
$pagetitle = get_string('manage', 'paygw_paynocchio');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add(get_string('pluginname', 'paygw_paynocchio'), $PAGE->url);
$confirm = optional_param('confirm', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);

echo $OUTPUT->header();

require_capability('paygw/paynocchio:managepayments', $systemcontext);

echo $OUTPUT->heading(get_string('pending_payments', 'paygw_paynocchio'), 2);
if ($confirm == 1 && $id > 0) {
    require_sesskey();
    if ($action == 'A') {
        paynocchio_helper::aprobe_pay($id);
        $OUTPUT->notification("aprobed");
        notification::info("aprobed");
    }
    if ($action == 'D') {
        paynocchio_helper::deny_pay($id);
        notification::info("denied");
        $OUTPUT->notification("denied");
    }
}

$post_url= new moodle_url($PAGE->url, array('sesskey'=>sesskey()));
$payments = paynocchio_helper::get_pending();
if (!$payments) {
    $match = array();
    echo $OUTPUT->heading(get_string('noentriesfound', 'paygw_paynocchio'));

    $table = null;
} else {
    $table = new html_table();
    $table->head = array(
        get_string('date'), get_string('username'),  get_string('email'),
        get_string('concept', 'paygw_paynocchio'), get_string('total_cost', 'paygw_paynocchio'), get_string('currency'), get_string('actions')
    );

    foreach ($payments as $payment) {
        $config = (object) helper::get_gateway_configuration($payment->component, $payment->paymentarea, $payment->itemid, 'bank');
        $payable = helper::get_payable($payment->component, $payment->paymentarea, $payment->itemid);
        $currency = $payable->get_currency();
        $customer = $DB->get_record('user', array('id' => $payment->userid));
        $fullname = fullname($customer, true);

        // Add surcharge if there is any.
        $surcharge = helper::get_gateway_surcharge('paynocchio');
        $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
        $buttonaprobe = '<form name="formapprovepay' . $payment->id . '" method="POST">
        <input type="hidden" name="sesskey" value="' .sesskey(). '">
        <input type="hidden" name="id" value="' . $payment->id . '">
        <input type="hidden" name="action" value="A">
        <input type="hidden" name="confirm" value="1">
        <input class="btn btn-primary btn-sm btn-success form-submit" type="submit" value="' . get_string('approve', 'paygw_paynocchio') . '"></input>
        </form>';
        $buttondeny = '<form name="formaprovepay' . $payment->id . '" method="POST">
        <input type="hidden" name="sesskey" value="' .sesskey(). '">
        <input type="hidden" name="id" value="' . $payment->id . '">
        <input type="hidden" name="action" value="D">
        <input type="hidden" name="confirm" value="1">
        <input class="btn btn-primary btn-sm btn-danger form-submit" type="submit" value="' . get_string('deny', 'paygw_paynocchio') . '"></input>
        </form>';


        $table->data[] = array(
            date('Y-m-d', $payment->timecreated), $fullname, $customer->email, $payment->description,
            $amount, $currency, '<div class="d-flex gap-3">' . $buttonaprobe . $buttondeny . '</div>'
        );
    }
    echo html_writer::table($table);
    echo 'Please be careful denying payments! Payments may be pending because site didn\'t recieve webhook but the money was actually payed!';
}

echo $OUTPUT->footer();
