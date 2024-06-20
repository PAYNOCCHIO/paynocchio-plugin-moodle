<?php

use paygw_paynocchio\paynocchio_helper;

require_once __DIR__ . '/../../../config.php';
require_once './lib.php';
$context = context_system::instance(); // Because we "have no scope".
$PAGE->set_context($context);

$PAGE->set_url('/payment/gateway/paynocchio/about.php', []);

$PAGE->set_pagelayout('report');

$pagetitle = 'Study well, earn and get rewarded with our loyalty program';

$PAGE->set_title($pagetitle);

$PAGE->set_heading($pagetitle);

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('paygw_paynocchio/about', ['logo' => paynocchio_helper::custom_logo(),]);


echo $OUTPUT->footer();
