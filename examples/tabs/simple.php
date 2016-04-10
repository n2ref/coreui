<?php

require_once __DIR__ . '/../../src/Tabs.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



$tabs = new Combine\Tabs('resource-name', '?param=1');
$tabs->addTab('Tab title 1', 'item1');
$tabs->addTab('Tab title 2', 'item2', true);
$tabs->addComboTab('Combo tab')
    ->addItem('Tab title 3', 'item3')
    ->addItem('Tab title 4', 'item4')
    ->addBreak()
    ->addItem('Tab title 5', 'item5');

$content = '';

switch ($tabs->getActiveTab()) {
    case 'item1':
        $content = 'content 1';
        break;

    case 'item2':
        $content = 'content 2';
        break;

    case 'item3':
        $content = 'content 3';
        break;

    case 'item4':
        $content = 'content 4';
        break;

    case 'item5':
        $content = 'content 5';
        break;
}


$tabs->setContent($content);
echo $tabs->render();