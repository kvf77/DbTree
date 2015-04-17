<?php
/**
 * Copyright (C) 2015 Kuzma Feskov <kfeskov@gmail.com>
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 2 as published by the Free
 * Software Foundation and appearing in the file LICENSE included in
 * the packaging of this file.
 *
 * This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
 * THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE.
 *
 * The "GNU General Public License" (GPL) is available at
 * http:*www.gnu.org/copyleft/gpl.html.
 */

ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>DdTree 4.2 - Demo</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="author" content="Kuzma Feskov (kuzma@russofile.ru)">
</head>
<body>
<h2>DbTree 4.2 class demo by Kuzma Feskov</h2>
[<a href="dbtree_demo.php">Manage demo</a>] [<a href="dbtree_visual_demo.php?mode=map">Visual demo (Map)</a>] [<a href="dbtree_visual_demo.php?mode=ajar">Visual demo (Ajar)</a>] [<a href="dbtree_visual_demo.php?mode=branch">Visual demo (Branch)</a>]
<?php

require_once('../safemysql.class.php');
require_once('../DbTree.class.php');
require_once('../DbTreeExt.class.php');

// Data base connect
$dsn['user'] = 'root';
$dsn['pass'] = '';
$dsn['host'] = 'localhost';
$dsn['db'] = 'sesmikcms';
$dsn['charset'] = 'utf8';
$dsn['errmode'] = 'exception';

define('DEBUG_MODE', false);

$db = new SafeMySQL($dsn);

$sql = 'SET NAMES utf8';
$db->query($sql);

$tree_params = array(
    'table' => 'test_sections',
    'id' => 'section_id',
    'left' => 'section_left',
    'right' => 'section_right',
    'level' => 'section_level'
);

$dbtree = new DbTreeExt($tree_params, $db);

/* ------------------------ NAVIGATOR ------------------------ */
$navigator = 'You are here: ';
if (!empty($_GET['section_id'])) {
    $parents = $dbtree->Parents((int)$_GET['section_id'], array('section_id', 'section_name'));

    foreach($parents as $item) {
        if (@$_GET['section_id'] <> $item['section_id']) {
            $navigator .= '<a href="dbtree_visual_demo.php?mode=' . $_GET['mode'] . '&section_id=' . $item['section_id'] . '">' . $item['section_name'] . '</a> > ';
        } else {
            $navigator .= '<strong>' . $item['section_name'] . '</strong>';
        }
    }
}

/* ------------------------ BRANCH ------------------------ */
if (!empty($_GET['mode']) && 'branch' == $_GET['mode']) {

    if (!isset($_GET['section_id'])) {
        $_GET['section_id'] = 1;
    }
    
    // Prepare data to view ajar tree
    $branch = $dbtree->Branch((int)$_GET['section_id'], array('section_id', 'section_level', 'section_name'));

    ?>
    <h3>Manage tree (BRANCH):</h3>
    <table border="1" cellpadding="5" width="100%">
        <tr>
            <td>

        <?php
        echo $navigator . '<br><br>';
        foreach($branch as $item) {
            if (@$_GET['section_id'] <> $item['section_id']) {
                echo str_repeat('&nbsp;', 6 * $item['section_level']) . '<a href="dbtree_visual_demo.php?mode=branch&section_id=' . $item['section_id'] . '">' . $item['section_name'] . '</a><br>';
            } else {
                echo str_repeat('&nbsp;', 6 * $item['section_level']) . '<strong>' . $item['section_name'] . '</strong><br>';
            }
        }

        ?>
            </td>
        </tr>
    </table>
    
    <?php
}

/* ------------------------ AJAR ------------------------ */
if (!empty($_GET['mode']) && 'ajar' == $_GET['mode']) {

    if (!isset($_GET['section_id'])) {
        $_GET['section_id'] = 1;
    }
    
    // Prepare data to view ajar tree
    $ajar = $dbtree->Ajar((int)$_GET['section_id'], array('section_id', 'section_level', 'section_name'));

    ?>
    <h3>Manage tree (AJAR):</h3>
    <table border="1" cellpadding="5" width="100%">
        <tr>
            <td>

        <?php
        echo $navigator . '<br><br>';
        foreach($ajar as $item) {
            if (@$_GET['section_id'] <> $item['section_id']) {
                echo str_repeat('&nbsp;', 6 * $item['section_level']) . '<a href="dbtree_visual_demo.php?mode=ajar&section_id=' . $item['section_id'] . '">' . $item['section_name'] . '</a><br>';
            } else {
                echo str_repeat('&nbsp;', 6 * $item['section_level']) . '<strong>' . $item['section_name'] . '</strong><br>';
            }
        }

        ?>
            </td>
        </tr>
    </table>
    
    <?php
}

/* ------------------------ MAP ------------------------ */
if (!empty($_GET['mode']) && 'map' == $_GET['mode']) {

    // Prepare data to view all tree
    $full = $dbtree->Full();

    ?>
    <h3>Manage tree (MAP):</h3>
    <table border="1" cellpadding="5" width="100%">
        <tr>
            <td>

        <?php
        echo $navigator . '<br><br>';
        foreach($full as $item) {
            if (@$_GET['section_id'] <> $item['section_id']) {
                echo str_repeat('&nbsp;', 6 * $item['section_level']) . '<a href="dbtree_visual_demo.php?mode=map&section_id=' . $item['section_id'] . '">' . $item['section_name'] . '</a><br>';
            } else {
                echo str_repeat('&nbsp;', 6 * $item['section_level']) . '<strong>' . $item['section_name'] . '</strong><br>';
            }
        }

        ?>
            </td>
        </tr>
    </table>
    
    <?php
}

    ?>
</body>
</html>
<?php
ob_flush();
?>