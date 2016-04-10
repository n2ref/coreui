<?php

require_once __DIR__ . '/../../src/Tree.php';

echo '<link href="/ext/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css"/>';
echo '<link href="/ext/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>';
echo '<script src="/ext/js/jquery-1.11.0.min.js"></script>';
echo '<script src="/ext/js/jquery-ui.min.js"></script>';



$pages = array(
    array(
        'id'        => 1,
        'title'     => 'Page 1',
        'url'       => '#page_id=1',
        'parent_id' => null,
        'options'   => array(
            'selected' => true,
        ),
        'is_active' => ''
    ),
    array(
        'id'        => 2,
        'title'     => 'Page 2',
        'url'       => '#page_id=2',
        'parent_id' => null,
        'options'   => array(
            'opened'   => true,
            'selected' => false,
        ),
        'is_active' => ''
    ),
    array(
        'id'        => 3,
        'title'     => 'Page 3',
        'url'       => '#page_id=3',
        'parent_id' => null,
        'is_active' => ''
    ),
    array(
        'id'        => 4,
        'title'     => 'Page 4',
        'url'       => '#page_id=4',
        'parent_id' => 2,
        'is_active' => ''
    ),
    array(
        'id'        => 5,
        'title'     => 'Page 5',
        'url'       => '#page_id=5',
        'parent_id' => 2,
        'is_active' => ''
    ),
);



$tree = new Combine\Tree('pages');
$tree->setData($pages);
$tree->setOptions(array(
    'plugins' => array('types'),
));

echo $tree->render();