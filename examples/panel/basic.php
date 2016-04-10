<?php

require_once __DIR__ . '/../../src/Panel.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



$panel = new Combine\Panel('resource-name', '?param=1');
$panel->addTab('Tab title 1', 'tab1');
$panel->addTab('Tab title 2', 'tab2');

$content = '';

switch ($panel->getActiveTab()) {
    case 'tab1':
        $content = 'content 1';
        break;

    case 'tab2':
        $content = 'content 2';
        break;
}

$panel->setContent($content);
echo $panel->render();