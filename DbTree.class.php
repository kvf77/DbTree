<?php


/**
 * DbTree.class.php
 * Nested sets tree management layer.
 *
 * @package SESMIK CMS
 * @author Kuzma Feskov <kfeskov@gmail.com>
 * @link http://www.sesmikcms.ru homesite (russian lang)
 * @link https://github.com/kvf77/DbTree GitHub (english lang)
 * @copyright (c) by Kuzma Feskov
 * @version 4.3, 2015-06-09
 *
 * CLASS DESCRIPTION:
 * This class can be used to manipulate nested sets of database table
 * records as an hierarchical tree.
 *
 * It can initialize a tree, insert nodes
 * in specific  positions of the tree, retrieve node and it's parent records,
 * change nodes position and delete nodes.
 *
 * This source file is part of the SESMIK CMS.
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
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * CHANGELOG:
 *
 * v4.4 - MakeUlList modified
 * v4.3 - Added new method MakeUlList
 * v4.2 - Added fully functional demo samples.
 * v4.1 - Correction of the documentation.
 *        Added new method SortChildren
 * v4.0
 */
class DbTree
{
    /**
     * Name of the table where tree is stored.
     *
     * @var string
     */
    public $table = '';

    /**
     * Unique number of node.
     *
     * @var string
     */
    public $tableId = '';

    /**
     * Level of nesting.
     *
     * @var string
     */
    public $tableLevel = '';

    /**
     * Database layer object.
     *
     * @var db
     */
    protected $db;

    /**
     * @var string
     */
    public $tableLeft = '';

    /**
     * @var string
     */
    public $tableRight = '';

    /**
     * Constructor.
     *
     * @param array $fields See description of class properties
     * @param object $db Database layer
     * @param string $lang Current language for messaging
     */
    public function __construct($fields, $db, $lang = 'en')
    {
        $lang_file = dirname(__FILE__) . '/language/dbtree.lang-' . $lang . '.php';

        if (is_file($lang_file)) {
            require_once($lang_file);
        }

        $this->db = $db;
        $this->table = $fields['table'];
        $this->tableId = isset ($fields['id']) ? $fields['id'] : 'id';
        $this->tableLeft = isset ($fields['left']) ? $fields['left'] : 'left';
        $this->tableRight = isset ($fields['right']) ? $fields['right'] : 'right';
        $this->tableLevel = isset ($fields['level']) ? $fields['level'] : 'level';
    }

    /**
     * Converts array of selected fields into part of SELECT query.
     *
     * @param string|array $fields Fields to be selected
     * @param string $table - Table or alias to select form
     * @return string - Part of SELECT query
     */
    protected function PrepareSelectFields($fields = '*', $table = null)
    {
        if (!empty($table)) {
            $table .= '.';
        }

        if (is_array($fields)) {
            $fields = $table . implode(', ' . $table, $fields);
        } else {
            $fields = $table . $fields;
        }

        return $fields;
    }

    /**
     * Receive all data for node with number $nodeId.
     *
     * @param int $nodeId Unique node id
     * @param string|array $fields Fields to be selected
     * @return array All node data
     * @throws USER_Exception
     */
    public function GetNode($nodeId, $fields = '*')
    {
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A WHERE A.' . $this->tableId . ' = ' . (int)$nodeId;
        $result = $this->db->getRow($sql);

        if (false === $result) {
            throw new USER_Exception(DBTREE_NO_ELEMENT, 0);
        }

        return $result;
    }

    /**
     * Receive data of closest parent for node with number $nodeId.
     *
     * @param int $nodeId
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array All node data
     * @throws USER_Exception
     */
    public function GetParent($nodeId, $fields = '*', $condition = '')
    {
        $condition = $this->PrepareCondition($condition, false, 'A.');
        $fields = $this->PrepareSelectFields($fields, 'A');

        $node_info = $this->GetNode($nodeId);

        $left_id = $node_info[$this->tableLeft];
        $right_id = $node_info[$this->tableRight];
        $level = $node_info[$this->tableLevel];
        $level--;

        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A';
        $sql .= ' WHERE ' . $this->tableLeft . ' < ' . $left_id . ' AND ' . $this->tableRight . ' > ' . $right_id . ' AND ' . $this->tableLevel . ' = ' . $level . ' ';
        $sql .= $condition . ' ORDER BY ' . $this->tableLeft;
        $result = $this->db->getRow($sql);

        if (empty($result)) {
            throw new USER_Exception(DBTREE_NO_ELEMENT, 0);
        }

        return $result;
    }

    /**
     * Add new child element to node with number $parentId.
     *
     * @param int $parentId Id of a parental element
     * @param array $data Contains parameters for additional fields of a tree (if is): 'filed name' => 'importance'
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return int Inserted element id
     */
    public function Insert($parentId, $data = array(), $condition = '')
    {
        $node_info = $this->GetNode($parentId);

        $right_id = $node_info[$this->tableRight];
        $level = $node_info[$this->tableLevel];

        $condition = $this->PrepareCondition($condition);

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . '=CASE WHEN ' . $this->tableLeft . '>' . $right_id . ' THEN ' . $this->tableLeft . '+2 ELSE ' . $this->tableLeft . ' END, ';
        $sql .= $this->tableRight . '=CASE WHEN ' . $this->tableRight . '>=' . $right_id . ' THEN ' . $this->tableRight . '+2 ELSE ' . $this->tableRight . ' END ';
        $sql .= 'WHERE ' . $this->tableRight . '>=' . $right_id;
        $sql .= $condition;
        $this->db->query($sql);

        $data[$this->tableLeft] = $right_id;
        $data[$this->tableRight] = $right_id + 1;
        $data[$this->tableLevel] = $level + 1;

        $sql = 'INSERT INTO ?p SET ?u';
        $this->db->query($sql, $this->table, $data);

        $node_id = $this->db->insertId();

        return $node_id;
    }

    /**
     * Add a new element into the tree near node with number $nodeId.
     *
     * @param int $nodeId Id of a node after which new node will be inserted (new node will have same level of nesting)
     * @param array $data Contains parameters for additional fields of a tree (if is): 'filed name' => 'importance'
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return int Inserted element id
     */
    public function InsertNear($nodeId, $data = array(), $condition = '')
    {
        $node_info = $this->GetNode($nodeId);

        $right_id = $node_info[$this->tableRight];
        $level = $node_info[$this->tableLevel];

        $condition = $this->PrepareCondition($condition);

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' > ' . $right_id . ' THEN ' . $this->tableLeft . ' + 2 ELSE ' . $this->tableLeft . ' END, ';
        $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . '> ' . $right_id . ' THEN ' . $this->tableRight . ' + 2 ELSE ' . $this->tableRight . ' END ';
        $sql .= 'WHERE ' . $this->tableRight . ' > ' . $right_id;
        $sql .= $condition;
        $this->db->query($sql);

        $data[$this->tableLeft] = $right_id + 1;
        $data[$this->tableRight] = $right_id + 2;
        $data[$this->tableLevel] = $level;

        $sql = 'INSERT INTO ?p SET ?u';
        $this->db->query($sql, $this->table, $data);

        $node_id = $this->db->insertId();

        return $node_id;
    }

    /**
     * Assigns another parent ($parentId) to a node ($nodeId) with all its children.
     *
     * @param int $nodeId Movable node id
     * @param int $parentId Id of a new parent node
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return bool True if successful, false otherwise.
     * @throws USER_Exception
     */
    public function MoveAll($nodeId, $parentId, $condition = '')
    {
        $node_info = $this->GetNode($nodeId);

        $left_id = $node_info[$this->tableLeft];
        $right_id = $node_info[$this->tableRight];
        $level = $node_info[$this->tableLevel];

        $node_info = $this->GetNode($parentId);

        $left_idp = $node_info[$this->tableLeft];
        $right_idp = $node_info[$this->tableRight];
        $levelp = $node_info[$this->tableLevel];

        if ($nodeId == $parentId || $left_id == $left_idp || ($left_idp >= $left_id && $left_idp <= $right_id) || ($level == $levelp + 1 && $left_id > $left_idp && $right_id < $right_idp)) {
            throw new USER_Exception(DBTREE_CANT_MOVE, 0);
        }

        $condition = $this->PrepareCondition($condition);

        $sql = 'UPDATE ' . $this->table . ' SET ';
        if ($left_idp < $left_id && $right_idp > $right_id && $levelp < $level - 1) {
            $sql .= $this->tableLevel . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLevel . sprintf('%+d', -($level - 1) + $levelp) . ' ELSE ' . $this->tableLevel . ' END, ';
            $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_idp - 1) . ' THEN ' . $this->tableRight . '-' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableRight . '+' . ((($right_idp - $right_id - $level + $levelp) / 2) * 2 + $level - $levelp - 1) . ' ELSE ' . $this->tableRight . ' END, ';
            $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_idp - 1) . ' THEN ' . $this->tableLeft . '-' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLeft . '+' . ((($right_idp - $right_id - $level + $levelp) / 2) * 2 + $level - $levelp - 1) . ' ELSE ' . $this->tableLeft . ' END ';
            $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . ($left_idp + 1) . ' AND ' . ($right_idp - 1);
        } elseif ($left_idp < $left_id) {
            $sql .= $this->tableLevel . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLevel . sprintf('%+d', -($level - 1) + $levelp) . ' ELSE ' . $this->tableLevel . ' END, ';
            $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $right_idp . ' AND ' . ($left_id - 1) . ' THEN ' . $this->tableLeft . '+' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLeft . '-' . ($left_id - $right_idp) . ' ELSE ' . $this->tableLeft . ' END, ';
            $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' BETWEEN ' . $right_idp . ' AND ' . $left_id . ' THEN ' . $this->tableRight . '+' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableRight . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableRight . '-' . ($left_id - $right_idp) . ' ELSE ' . $this->tableRight . ' END ';
            $sql .= 'WHERE (' . $this->tableLeft . ' BETWEEN ' . $left_idp . ' AND ' . $right_id . ' ';
            $sql .= 'OR ' . $this->tableRight . ' BETWEEN ' . $left_idp . ' AND ' . $right_id . ')';
        } else {
            $sql .= $this->tableLevel . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLevel . sprintf('%+d', -($level - 1) + $levelp) . ' ELSE ' . $this->tableLevel . ' END, ';
            $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $right_id . ' AND ' . $right_idp . ' THEN ' . $this->tableLeft . '-' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLeft . '+' . ($right_idp - 1 - $right_id) . ' ELSE ' . $this->tableLeft . ' END, ';
            $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' BETWEEN ' . ($right_id + 1) . ' AND ' . ($right_idp - 1) . ' THEN ' . $this->tableRight . '-' . ($right_id - $left_id + 1) . ' ';
            $sql .= 'WHEN ' . $this->tableRight . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableRight . '+' . ($right_idp - 1 - $right_id) . ' ELSE ' . $this->tableRight . ' END ';
            $sql .= 'WHERE (' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_idp . ' ';
            $sql .= 'OR ' . $this->tableRight . ' BETWEEN ' . $left_id . ' AND ' . $right_idp . ')';
        }
        $sql .= $condition;
        $this->db->query($sql);

        return true;
    }

    /**
     * Change position of nodes. Nodes have to have same parent and same level of nesting.
     *
     * @param integer $nodeId1 first node id
     * @param integer $nodeId2 second node id
     * @return bool true if successful, false otherwise.
     */
    public function ChangePosition($nodeId1, $nodeId2)
    {
        $node_info = $this->GetNode($nodeId1);

        $left_id1 = $node_info[$this->tableLeft];
        $right_id1 = $node_info[$this->tableRight];
        $level1 = $node_info[$this->tableLevel];

        $node_info = $this->GetNode($nodeId2);

        $left_id2 = $node_info[$this->tableLeft];
        $right_id2 = $node_info[$this->tableRight];
        $level2 = $node_info[$this->tableLevel];

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . ' = ' . $left_id2 . ', ';
        $sql .= $this->tableRight . ' = ' . $right_id2 . ', ';
        $sql .= $this->tableLevel . ' = ' . $level2 . ' ';
        $sql .= 'WHERE ' . $this->tableId . ' = ' . $nodeId1;
        $this->db->query($sql);

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . ' = ' . $left_id1 . ', ';
        $sql .= $this->tableRight . ' = ' . $right_id1 . ', ';
        $sql .= $this->tableLevel . ' = ' . $level1 . ' ';
        $sql .= 'WHERE ' . $this->tableId . ' = ' . $nodeId2;
        $this->db->query($sql);

        return true;
    }

    /**
     * Swapping nodes with it's children. Nodes have to have same parent and same level of nesting.
     * $nodeId1 can be placed "before" or "after" $nodeId2.
     *
     * @param int $nodeId1 first node id
     * @param int $nodeId2 second node id
     * @param string $position 'before' or 'after' (default) $nodeId2
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return bool true if successful, false otherwise.
     * @throws USER_Exception
     */
    public function ChangePositionAll($nodeId1, $nodeId2, $position = 'after', $condition = '')
    {
        if ($position != 'after' && $position != 'before') {
            throw new USER_Exception(DBTREE_INCORRECT_POSITION, 0);
        }

        $node_info = $this->GetNode($nodeId1);

        $left_id1 = $node_info[$this->tableLeft];
        $right_id1 = $node_info[$this->tableRight];
        $level1 = $node_info[$this->tableLevel];

        $node_info = $this->GetNode($nodeId2);

        $left_id2 = $node_info[$this->tableLeft];
        $right_id2 = $node_info[$this->tableRight];
        $level2 = $node_info[$this->tableLevel];

        if ($level1 <> $level2) {
            throw new USER_Exception(DBTREE_CANT_CHANGE_POSITION, 0);
        }

        $sql = 'UPDATE ' . $this->table . ' SET ';
        if ('before' == $position) {
            if ($left_id1 > $left_id2) {
                $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableRight . ' - ' . ($left_id1 - $left_id2) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id2 . ' AND ' . ($left_id1 - 1) . ' THEN ' . $this->tableRight . ' +  ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableRight . ' END, ';
                $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableLeft . ' - ' . ($left_id1 - $left_id2) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id2 . ' AND ' . ($left_id1 - 1) . ' THEN ' . $this->tableLeft . ' + ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableLeft . ' END ';
                $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . $left_id2 . ' AND ' . $right_id1;
            } else {
                $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableRight . ' + ' . (($left_id2 - $left_id1) - ($right_id1 - $left_id1 + 1)) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id1 + 1) . ' AND ' . ($left_id2 - 1) . ' THEN ' . $this->tableRight . ' - ' . (($right_id1 - $left_id1 + 1)) . ' ELSE ' . $this->tableRight . ' END, ';
                $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableLeft . ' + ' . (($left_id2 - $left_id1) - ($right_id1 - $left_id1 + 1)) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id1 + 1) . ' AND ' . ($left_id2 - 1) . ' THEN ' . $this->tableLeft . ' - ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableLeft . ' END ';
                $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . ($left_id2 - 1);
            }
        }

        if ('after' == $position) {
            if ($left_id1 > $left_id2) {
                $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableRight . ' - ' . ($left_id1 - $left_id2 - ($right_id2 - $left_id2 + 1)) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id2 + 1) . ' AND ' . ($left_id1 - 1) . ' THEN ' . $this->tableRight . ' +  ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableRight . ' END, ';
                $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableLeft . ' - ' . ($left_id1 - $left_id2 - ($right_id2 - $left_id2 + 1)) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id2 + 1) . ' AND ' . ($left_id1 - 1) . ' THEN ' . $this->tableLeft . ' + ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableLeft . ' END ';
                $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . ($right_id2 + 1) . ' AND ' . $right_id1;
            } else {
                $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableRight . ' + ' . ($right_id2 - $right_id1) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id1 + 1) . ' AND ' . $right_id2 . ' THEN ' . $this->tableRight . ' - ' . (($right_id1 - $left_id1 + 1)) . ' ELSE ' . $this->tableRight . ' END, ';
                $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id1 . ' THEN ' . $this->tableLeft . ' + ' . ($right_id2 - $right_id1) . ' ';
                $sql .= 'WHEN ' . $this->tableLeft . ' BETWEEN ' . ($right_id1 + 1) . ' AND ' . $right_id2 . ' THEN ' . $this->tableLeft . ' - ' . ($right_id1 - $left_id1 + 1) . ' ELSE ' . $this->tableLeft . ' END ';
                $sql .= 'WHERE ' . $this->tableLeft . ' BETWEEN ' . $left_id1 . ' AND ' . $right_id2;
            }
        }

        $condition = $this->PrepareCondition($condition);

        $sql .= $condition;
        $this->db->query($sql);

        return true;
    }

    /**
     * Deletes element with number $nodeId from the tree without deleting it's children
     * All it's children will move up one level.
     *
     * @param integer $nodeId Node id
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return bool true if successful, false otherwise.
     */
    public function Delete($nodeId, $condition = '')
    {
        $node_info = $this->GetNode($nodeId);

        $condition = $this->PrepareCondition($condition);

        $left_id = $node_info[$this->tableLeft];
        $right_id = $node_info[$this->tableRight];

        $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->tableId . ' = ' . $nodeId;
        $this->db->query($sql);

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLevel . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLevel . ' - 1 ELSE ' . $this->tableLevel . ' END, ';
        $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableRight . ' - 1 ';
        $sql .= 'WHEN ' . $this->tableRight . ' > ' . $right_id . ' THEN ' . $this->tableRight . ' - 2 ELSE ' . $this->tableRight . ' END, ';
        $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id . ' THEN ' . $this->tableLeft . ' - 1 ';
        $sql .= 'WHEN ' . $this->tableLeft . ' > ' . $right_id . ' THEN ' . $this->tableLeft . ' - 2 ELSE ' . $this->tableLeft . ' END ';
        $sql .= 'WHERE ' . $this->tableRight . ' > ' . $left_id;
        $sql .= $condition;
        $this->db->query($sql);

        return true;
    }

    /**
     * Deletes element with number $nodeId from the tree and all it children.
     *
     * @param integer $nodeId Node id
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return bool true if successful, false otherwise.
     */
    public function DeleteAll($nodeId, $condition = '')
    {
        $node_info = $this->GetNode($nodeId);

        $left_id = $node_info[$this->tableLeft];
        $right_id = $node_info[$this->tableRight];

        $condition = $this->PrepareCondition($condition);

        $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->tableLeft . ' BETWEEN ' . $left_id . ' AND ' . $right_id;
        $sql .= $condition;
        $this->db->query($sql);

        $delta_id = (($right_id - $left_id) + 1);
        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sql .= $this->tableLeft . ' = CASE WHEN ' . $this->tableLeft . ' > ' . $left_id . ' THEN ' . $this->tableLeft . ' - ' . $delta_id . ' ELSE ' . $this->tableLeft . ' END, ';
        $sql .= $this->tableRight . ' = CASE WHEN ' . $this->tableRight . ' > ' . $left_id . ' THEN ' . $this->tableRight . ' - ' . $delta_id . ' ELSE ' . $this->tableRight . ' END ';
        $sql .= 'WHERE ' . $this->tableRight . ' > ' . $right_id;
        $sql .= $condition;
        $this->db->query($sql);

        return true;
    }

    /**
     * Transforms array with conditions to SQL query
     * Array structure:
     * array('and' => array('id = 0', 'id2 >= 3'), 'or' => array('sec = \'www\'', 'sec2 <> \'erere\'')), etc
     * where array key - condition (AND, OR, etc), value - condition string.
     *
     * @param array $condition
     * @param string $prefix
     * @param bool $where - true - yes, false (dafault) - not
     * @return string
     */
    protected function PrepareCondition($condition, $where = false, $prefix = '')
    {
        if (empty ($condition)) {
            return '';
        }

        if (!is_array($condition)) {
            return $condition;
        }

        $sql = ' ';

        if (true === $where) {
            $sql .= 'WHERE ' . $prefix;
        }

        $keys = array_keys($condition);

        for ($counter = count($keys), $i = 0; $i < $counter; $i++) {
            if (false === $where || (true === $where && $i > 0)) {
                $sql .= ' ' . strtoupper($keys[$i]) . ' ' . $prefix;
            }

            $sql .= implode(' ' . strtoupper($keys[$i]) . ' ' . $prefix, $condition[$keys[$i]]);
        }

        return $sql;
    }
}

?>