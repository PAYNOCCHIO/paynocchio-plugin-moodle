<?php
$capabilities = array(

    'paygw/paynocchio:managepayments' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/site:config',
    )
);
