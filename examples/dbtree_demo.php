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
        <title>DdTree 4.2 - Demo sample</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta name="author" content="Kuzma Feskov (kfeskov@gmail.com)">
    </head>
    <body>
    <h2>DbTree 4.2 class demo by Kuzma Feskov</h2>
    [<a href="dbtree_visual_demo.php?mode=map">Visual demo (Site map)</a>] [<a href="dbtree_visual_demo.php?mode=ajar">Visual
        demo (Ajar tree)</a>] [<a href="dbtree_visual_demo.php?mode=branch">Visual demo (Branch)</a>]
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

    /* ------------------------ MOVE ------------------------ */

    /* ------------------------ MOVE 2 ------------------------ */

    // Method 2: Assigns a node with all its children to another parent.
    if (!empty($_GET['action']) && 'move_2' == $_GET['action']) {

        // Move node ($_GET['section_id']) and its children to new parent ($_POST['section2_id'])
        $dbtree->MoveAll((int)$_GET['section_id'], (int)$_POST['section2_id']);

        header('Location:dbtree_demo.php');
        exit;
    }

    /* ------------------------ MOVE 1 ------------------------ */

    // Method 1: Swapping nodes within the same level and limits of one parent with all its children.
    if (!empty($_GET['action']) && 'move_1' == $_GET['action']) {

        // Change node ($_GET['section_id']) position and all its childrens to
        // before or after ($_POST['position']) node 2 ($_POST['section2_id'])
        $dbtree->ChangePositionAll((int)$_GET['section_id'], (int)$_POST['section2_id'], $_POST['position']);

        header('Location:dbtree_demo.php');
        exit;
    }

    /* ------------------------ MOVE FORM------------------------ */

    // Move section form
    if (!empty($_GET['action']) && 'move' == $_GET['action']) {

        // Prepare the restrictive data for the first method:
        // Swapping nodes within the same level and limits of one parent with all its children
        $current_section = $dbtree->GetNode((int)$_GET['section_id']);
        $parents = $dbtree->Parents((int)$_GET['section_id'], array('section_id'), array('and' => array('section_level = ' . ($current_section['section_level'] - 1))));

        $item = current($parents);
        $branch = $dbtree->Branch($item['section_id'], array('section_id', 'section_name'), array('and' => array('section_level = ' . $current_section['section_level'])));

        // Create form
        ?>
        <table border="1" cellpadding="5" align="center">
            <tr>
                <td>
                    Move section
                </td>
            </tr>
            <tr>
                <td>
                    <form action="dbtree_demo.php?action=move_1&section_id=<?= $_GET['section_id'] ?>" method="POST">
                        <strong>1) Swapping nodes within the same level and limits of one parent with all its
                            children.</strong><br>
                        Choose second section:
                        <select name="section2_id">
                            <?php

                            foreach($branch as $item) {

                                ?>
                                <option
                                    value="<?= $item['section_id'] ?>"><?= $item['section_name'] ?> <?php echo $item['section_id'] == (int)$_GET['section_id'] ? '<<<' : '' ?></option>
                                <?php

                            }

                            ?>
                        </select><br>
                        Choose position:
                        <select name="position">
                            <option value="after">After</option>
                            <option value="before">Before</option>
                        </select><br>
                        <center><input type="submit" value="Apply"></center>
                        <br>
                    </form>
                    <form action="dbtree_demo.php?action=move_2&section_id=<?= $_GET['section_id'] ?>" method="POST">
                        <strong>2) Assigns a node with all its children to another parent.</strong><br>
                        Choose second section:
                        <select name="section2_id">
                            <?php

                            // Prepare the data for the second method:
                            // Assigns a node with all its children to another parent
                            $full = $dbtree->Full(array('section_id', 'section_level', 'section_name'), array('or' => array('section_left <= ' . $current_section['section_left'], 'section_right >= ' . $current_section['section_right'])));

                            foreach ($full as $item) {

                                ?>
                                <option
                                    value="<?= $item['section_id'] ?>"><?= str_repeat('&nbsp;', 6 * $item['section_level']) ?><?= $item['section_name'] ?> <?php echo $item['section_id'] == (int)$_GET['section_id'] ? '<<<' : '' ?></option>
                                <?php

                            }

                            ?>
                        </select><br>
                        <center><input type="submit" value="Apply"></center>
                        <br>
                    </form>
                </td>
            </tr>
        </table>
        <?php

    }

    /* ------------------------ DELETE ------------------------ */

    // Delete node ($_GET['section_id']) from the tree wihtout deleting it's children
    // All children apps to one level
    if (!empty($_GET['action']) && 'delete' == $_GET['action']) {
        $dbtree->Delete((int)$_GET['section_id']);

        header('Location:dbtree_demo.php');
        exit;
    }

    /* ------------------------ EDIT ------------------------ */

    /* ------------------------ EDIT OK ------------------------ */

    // Update node ($_GET['section_id']) info
    if (!empty($_GET['action']) && 'edit_ok' == $_GET['action']) {
        $sql = 'SELECT * FROM test_sections WHERE section_id = ' . (int)$_GET['section_id'];
        $section = $db->getRow($sql);

        if (false == $section) {
            echo 'section_not_found';
            exit;
        }

        $sql = 'UPDATE test_sections SET ?u WHERE section_id = ?i';
        $db->query($sql, $_POST['section'], $_GET['section_id']);

        header('Location:dbtree_demo.php');
        exit;
    }

    /* ------------------------ EDIT FORM ------------------------ */

    // Node edit form
    if (!empty($_GET['action']) && 'edit' == $_GET['action']) {
        $sql = 'SELECT section_name FROM test_sections WHERE section_id = ' . (int)$_GET['section_id'];
        $section = $db->getOne($sql);

        ?>
        <table border="1" cellpadding="5" align="center">
            <tr>
                <td>
                    Edit section
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form action="dbtree_demo.php?action=edit_ok&section_id=<?= $_GET['section_id'] ?>" method="POST">
                        Section name:<br>
                        <input type="text" name="section[section_name]" value="<?= $section ?>"><br><br>
                        <input type="submit" name="submit" value="Submit">
                    </form>
                </td>
            </tr>
        </table>
        <?php
    }

    /* ------------------------ ADD ------------------------ */

    /* ------------------------ ADD OK ------------------------ */

    // Add new node as children to selected node ($_GET['section_id'])
    if (!empty($_GET['action']) && 'add_ok' == $_GET['action']) {

        // Add new node
        $dbtree->Insert((int)$_GET['section_id'], $_POST['section']);

        header('Location:dbtree_demo.php');
        exit;
    }

    /* ------------------------ ADD FORM ------------------------ */

    // Add new node form
    if (!empty($_GET['action']) && 'add' == $_GET['action']) {

        ?>
        <table border="1" cellpadding="5" align="center">
            <tr>
                <td>
                    New section
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form action="dbtree_demo.php?action=add_ok&section_id=<?= $_GET['section_id'] ?>" method="POST">
                        Section name:<br>
                        <input type="text" name="section[section_name]" value=""><br><br>
                        <input type="submit" name="submit" value="Submit">
                    </form>
                </td>
            </tr>
        </table>
        <?php

    }

    /* ------------------------ LIST ------------------------ */

    // Prepare data to view all tree
    $full = $dbtree->Full();

    ?>
    <h3>Manage tree:</h3>
    <table border="1" cellpadding="5" width="100%">
        <tr>
            <td width="100%">Section name</td>
            <td colspan="4">Actions</td>
        </tr>
        <?php

        $counter = 1;
        foreach($full as $item) {
            if ($counter % 2) {
                $bgcolor = 'lightgreen';
            } else {
                $bgcolor = 'yellow';
            }
            $counter++;

            ?>
            <tr>
                <td bgcolor="<?= $bgcolor ?>">
                    <?= str_repeat('&nbsp;', 6 * $item['section_level']) . '<strong>' . $item['section_name'] ?></strong>
                    [<strong><?= $item['section_left'] ?></strong>, <strong><?= $item['section_right'] ?></strong>,
                    <strong><?= $item['section_level'] ?></strong>]
                </td>
                <td bgcolor="<?= $bgcolor ?>">
                    <a href="dbtree_demo.php?action=add&section_id=<?= $item['section_id'] ?>">Add</a>
                </td>
                <td bgcolor="<?= $bgcolor ?>">
                    <a href="dbtree_demo.php?action=edit&section_id=<?= $item['section_id'] ?>">Edit</a>
                </td>
                <td bgcolor="<?= $bgcolor ?>">

                    <?php
                    if (0 == $item['section_level']) {
                        echo 'Delete';
                    } else {

                        ?>
                        <a href="dbtree_demo.php?action=delete&section_id=<?= $item['section_id'] ?>">Delete</a>
                        <?php
                    }
                    ?>

                </td>
                <td bgcolor="<?= $bgcolor ?>">

                    <?php
                    if (0 == $item['section_level']) {
                        echo 'Move';
                    } else {

                        ?>
                        <a href="dbtree_demo.php?action=move&section_id=<?= $item['section_id'] ?>">Move</a>
                        <?php
                    }
                    ?>

                </td>
            </tr>
            <?php
        }

        ?>
    </table>
    </body>
    </html>
<?php
ob_flush();
?>