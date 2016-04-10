<?php

require_once __DIR__ . '/../../src/Table.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



$data = array(
    array(
        'id'          => '1',
        'title'       => 'Title 1',
        'description' => 'Description 1',
        'author'      => 'Author 1',
        'version'     => 'Version 1'
    ),
    array(
        'id'          => '2',
        'title'       => 'Title 2',
        'description' => 'Description 2',
        'author'      => 'Author 2',
        'version'     => 'Version 2'
    ),
    array(
        'id'          => '3',
        'title'       => 'Title 3',
        'description' => 'Description 3',
        'author'      => 'Author 3',
        'version'     => 'Version 3'
    ),
);



$table = new Combine\Table('resource_name');
$table->setData($data);

$table->addColumn('Title',       'title',       'text', '200px');
$table->addColumn('Description', 'description', 'text', '');
$table->addColumn('Author',      'author',      'text', '140px');
$table->addColumn('Version',     'version',     'text', '140px');

$table->setEditUrl('#theme=TCOL_ID');

echo $table->render();