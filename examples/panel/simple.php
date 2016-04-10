<?php

require_once __DIR__ . '/../../src/Panel.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



$panel = new Combine\Panel('resource-name', '?param=1');

$content = 'Panel custom content';

$panel->setContent($content);
echo $panel->render();