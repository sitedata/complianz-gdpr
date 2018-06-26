<?php
defined('ABSPATH') or die("you do not have acces to this page!");

$this->warning_types = array(
    'wizard-incomplete' => array(
        'label_ok' => __('The wizard has been completed.', 'complianz'),
        'label_error' => __('The wizard is not completed yet.', 'complianz')
    ),
    'cookies-changed' => array(
        'label_ok' => __('No cookie changes have been detected.', 'complianz'),
        'label_error' => __('Cookie changes have been detected.', 'complianz') . " " . sprintf(__('Please review step %s of the wizard for changes in cookies.', 'complianz'), STEP_COOKIES),
    ),
    'no-ssl' => array(
        'label_ok' => __("Great! You're already on SSL!", 'complianz'),
        'label_error' => sprintf(__("You don't have SSL on your site yet. Most hosting companies can install SSL for you, which you can quickly enable with %sReally Simple SSL%s", 'complianz'), '<a target="_blank" href="https://wordpress.org/plugins/really-simple-ssl/">', '</a>'),
    ),
    'plugins-changed' => array(
        'label-free' => __('label free','complianz'),
        'label_ok' => __('No plugin changes have been detected.', 'complianz'),
        'label_error' => __('Plugin changes have been detected.', 'complianz') . " " . sprintf(__('Please review step %s and %s of the wizard for changes in plugin privacy statements and cookies.', 'complianz'), STEP_PLUGINS, STEP_COOKIES),
    ),
    'ga-needs-configuring' => array(
        'label_ok' => __('No issues with statistics tracking have been detected.', 'complianz'),
        'label_error' => __('Google Analytics is being used, but is not configured in Complianz.', 'complianz'),
    ),
);