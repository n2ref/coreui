<?php

require_once __DIR__ . '/../../src/Alert.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



echo Combine\Alert::getSuccess('Success message');
echo Combine\Alert::getDanger('Danger message');
echo Combine\Alert::getWarning('Warning message');
echo Combine\Alert::getInfo('Info message');
echo Combine\Alert::get('custom', 'Custom message');