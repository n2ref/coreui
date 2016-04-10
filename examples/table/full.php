<?php

require_once __DIR__ . '/../../src/Registry.php';
require_once __DIR__ . '/../../src/Table.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



$db_connection = 'your db connection (PDO or Mysqli)';
Combine\Registry::setDbConnection($db_connection);




$table = new \Combine\Table\Db('modules');
$table->setPrimaryKey('id');

$table->addSearch('Title',   'title',     'text');
$table->addSearch('Name',    'name',      'text');
$table->addSearch('Version', 'is_active', 'select')
    ->setData(array('Y' => 'Active', 'N' => 'Inactive'));

$table->setQuery("
    SELECT id,
           title,
           name,
           version,
           is_active
    FROM system_modules
    ORDER BY seq
");

$table->addColumn('Title',   'title',     'text');
$table->addColumn('Name',    'name',      'text');
$table->addColumn('Version', 'version',   'text');
$table->addColumn('',        'is_active', 'status', '1%');

$table->setAddUrl('#id=0');
$table->setEditUrl('#id=TCOL_ID');

return $table->render();