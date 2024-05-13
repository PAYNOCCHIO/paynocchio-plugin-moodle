<?php

use core\uuid;
use core_payment\helper;
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

$PAGE->requires->js_call_amd('paygw_paynocchio/wallet_activation', 'init', ['user_id' => $USER->id]);


echo $OUTPUT->header();

$user_uuid = uuid::generate();

//$wallet = new paynocchio_helper($user_uuid);

$user = $DB->get_record('paygw_paynocchio_data', ['userid'  => $USER->id]);
print_r($user);

if($user && $user->useruuid && $user->walletuuid) {
    $data = [
        'user_uuid' => $user->useruuid,
        'wallet_uuid' => $user->walletuuid,
    ];
    echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet', $data);
} else {
    $data = [
        'user_id' => $USER->id,
    ];

    echo $OUTPUT->render_from_template('paygw_paynocchio/paynocchio_wallet_activation', $data);
}

echo $OUTPUT->footer();