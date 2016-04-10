<?php

require_once __DIR__ . '/../../src/Registry.php';
require_once __DIR__ . '/../../src/Form.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



$db_connection = 'your db connection (PDO or Mysqli)';
Combine\Registry::setDbConnection($db_connection);



$record_id = 123;


$form = new Combine\Form\Db('resource_name');
$form->setBackUrl('index.php#module=pages');
$form->setAjaxRequest(true);
$form->setPrimaryKey('id', $record_id);
$form->setAttribs(array(
    'action'  => 'index.php?module=pages',
    'method'  => 'post',
));

$form->setQuery("
    SELECT title,
           uri,
           content,
           template_id,
           meta_description,
           meta_keywords,
           is_active
    FROM system_pages
    WHERE id = ?
", $page_id);

$data      = $form->fetchData();
$templates = array(
    1 => 'tpl1',
    2 => 'tpl2',
    3 => 'tpl3',
);


$form->addControl('Title',    'text',   'title')->setAttr('style', 'width:400px')->setRequired();
$form->addControl('Url page', 'text',   'uri')->setAttr('style',   'width:400px')->setRequired();
$form->addControl('Template', 'select', 'template_id')
    ->setOptions($templates)
    ->setAttr('style', 'width:400px')
    ->setRequired();

$form->addControl('Content',          'wysiwyg',  'content')->setConfig('standard');
$form->addControl('Meta description', 'textarea', 'meta_description')
    ->setAttribs(array(
        'style'       => 'width:400px;min-height:50px',
        'rows'        => 2,
        'placeholder' => 'Default',
    ));


$form->addControl('Meta keywords', 'text', 'meta_keywords')
    ->setAttribs(array(
        'style'       => 'width:400px',
        'placeholder' => 'Default',
    ));


$is_active = isset($data['is_active']) ? $data['is_active'] : 'Y';


$form->addSubmit('Save');
$form->addButtonSwitched('is_active', $is_active, 'Y', 'N');
$form->addButton('Back')->setAttr('onclick', "history.back()");

echo $form->render();