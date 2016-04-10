<?php

require_once __DIR__ . '/../../src/Registry.php';
require_once __DIR__ . '/../../src/Form.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



$action = '/form/action';

$form = new Combine\Form('resource_name');
$form->setBackUrl('/url/redirect/after/success/save');
$form->setAjaxRequest(true);
$form->setAttribs(array(
    'action' => $action,
    'method' => 'post',
));

$form->addControl('Text', 'text', 'name_text')
    ->setAttr('value', 'required text')
    ->setRequired();

$form->addControl('Textarea', 'textarea', 'name_textarea')
    ->setValue('any text for textarea')
    ->setAttribs(array(
        'style'       => 'width:400px;min-height:50px',
        'rows'        => 2,
        'placeholder' => 'Default',
    ));

$form->addControl('Custom control', 'custom')->setHtml('your custom html');


$form->addSubmit('Save');
$form->addButton('Back')->setAttr('onclick', "history.back()");
echo $form->render();