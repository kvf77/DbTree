<?php


/**
 * DbTreeExt.class.php
 * DbTree extension class.
 *
 * @package SESMIK CMS
 * @author Kuzma Feskov <kfeskov@gmail.com>
 * @link http://www.sesmikcms.ru homesite (russian lang)
 * @link https://github.com/kvf77/DbTree GitHub (english lang)
 * @copyright (c) by Kuzma Feskov
 * @version 4.3, 2015-06-09
 *
 * CLASS DESCRIPTION:
 * This class extends basic functions of DbTree class.
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

require_once(dirname(__FILE__) . '/DbTree.class.php');

class DbTreeExt extends DbTree
{
    /**
     * Database layer object.
     *
     * @var db
     */
    protected $db;

    public $joinFilter = null;

    /**
     * Constructor.
     *
     * @param array $fields See description of "DbTree" class properties
     * @param object $db Database layer
     * @param string $lang Current language for messaging
     */
    public function __construct($fields, $db, $lang = 'en')
    {
        $this->db = $db;

        parent:: __construct($fields, $db, $lang);
    }

    /**
     * Returns all elements of the tree sorted by "left".
     *
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array Needed fields
     */
    function Full($fields = '*', $condition = '')
    {
        $condition = $this->PrepareCondition($condition, true, 'A.');
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT ' . $fields . ' FROM ' . $this->table . ' AS A';
        $sql .= $condition;
        $sql .= ' ORDER BY ' . $this->tableLeft;
        $result = $this->db->getInd($this->tableId, $sql);

        return $result;
    }

    /**
     * Returns all elements of a branch starting from an element with number $nodeId.
     *
     * @param integer $nodeId Node unique id
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array Needed fields
     */
    function Branch($nodeId, $fields = '*', $condition = '')
    {

        $condition = $this->PrepareCondition($condition, false, 'A.');
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT A.' . $this->tableId . ', A.' . $this->tableLeft . ', A.' . $this->tableRight . ', A.' . $this->tableLevel . ', ' . $fields . ', CASE WHEN A.' . $this->tableLeft . ' + 1 < A.' . $this->tableRight . ' THEN 1 ELSE 0 END AS nflag ';
        $sql .= 'FROM ' . $this->table . ' B, ' . $this->table . ' A ';
        $sql .= 'WHERE B.' . $this->tableId . ' = ' . (int)$nodeId . ' AND A.' . $this->tableLeft . ' >= B.' . $this->tableLeft . ' AND A.' . $this->tableRight . ' <= B.' . $this->tableRight;
        $sql .= $condition;
        $sql .= ' ORDER BY A.' . $this->tableLeft;
        $result = $this->db->getInd($this->tableId, $sql);

        return $result;
    }

    /**
     * Returns all parents of element with number $nodeId.
     *
     * @param integer $nodeId Node unique id
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array Needed fields
     */
    function Parents($nodeId, $fields = '*', $condition = '')
    {
        $condition = $this->PrepareCondition($condition, false, 'A.');
        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT A.' . $this->tableId . ', A.' . $this->tableLeft . ', A.' . $this->tableRight . ', A.' . $this->tableLevel . ', ' . $fields . ', CASE WHEN A.' . $this->tableLeft . ' + 1 < A.' . $this->tableRight . ' THEN 1 ELSE 0 END AS nflag ';
        $sql .= 'FROM ' . $this->table . ' B, ' . $this->table . ' A ';
        $sql .= 'WHERE B.' . $this->tableId . ' = ' . (int)$nodeId . ' AND B.' . $this->tableLeft . ' BETWEEN A.' . $this->tableLeft . ' AND A.' . $this->tableRight;
        $sql .= $condition;
        $sql .= ' ORDER BY A.' . $this->tableLeft;
        $result = $this->db->getInd($this->tableId, $sql);

        return $result;
    }

    /**
     * Returns a slightly opened tree from an element with number $nodeId.
     *
     * @param integer $nodeId Node unique id
     * @param string|array $fields Fields to be selected
     * @param string|array $condition array key - condition (AND, OR, etc), value - condition string
     * @return array Needed fields
     * @throws USER_Exception
     */
    function Ajar($nodeId, $fields = '*', $condition = '')
    {
        $condition = $this->PrepareCondition($condition, false, 'A.');

        $sql = 'SELECT A.' . $this->tableLeft . ', A.' . $this->tableRight . ', A.' . $this->tableLevel . ' ';
        $sql .= 'FROM ' . $this->table . ' A, ' . $this->table . ' B ';
        $sql .= 'WHERE B.' . $this->tableId . ' = ' . $nodeId . ' ';
        $sql .= 'AND B.' . $this->tableLeft . ' BETWEEN A.' . $this->tableLeft . ' ';
        $sql .= 'AND A.' . $this->tableRight;
        $sql .= $condition;
        $sql .= ' ORDER BY A.' . $this->tableLeft;
        $res = $this->db->query($sql);

        if (0 == $this->db->numRows($res)) {
            throw new USER_Exception(DBTREE_NO_ELEMENT, 0);
        }

        $alen = $this->db->numRows($res);
        $i = 0;

        $fields = $this->PrepareSelectFields($fields, 'A');

        $sql = 'SELECT A.' . $this->tableId . ', A.' . $this->tableLeft . ', A.' . $this->tableRight . ', A.' . $this->tableLevel . ', ' . $fields . ' ';
        $sql .= 'FROM ' . $this->table . ' A ';
        $sql .= 'WHERE (' . $this->tableLevel . ' = 1';
        while ($row = $this->db->fetch($res)) {
            if ((++$i == $alen) && ($row[$this->tableLeft] + 1) == $row[$this->tableRight]) {
                break;
            }
            $sql .= ' OR (' . $this->tableLevel . ' = ' . ($row[$this->tableLevel] + 1) . ' AND ' . $this->tableLeft . ' > ' . $row[$this->tableLeft] . ' AND ' . $this->tableRight . ' < ' . $row[$this->tableRight] . ')';
        }
        $sql .= ') ' . $condition;
        $sql .= ' ORDER BY ' . $this->tableLeft;

        $result = $this->db->getInd($this->tableId, $sql);

        return $result;
    }

    /**
     * Sort children in a tree for $orderField in alphabetical order.
     *
     * @param integer $id - Parent's ID.
     * @param string $orderField - the name of the field on which sorting will go
     */
    public function SortChildren($id, $orderField)
    {
        $node = $this->GetNode($id);
        $data = $this->Branch(
            $id,
            array(
                $this->tableId
            ), array(
                'and' => array(
                    $this->tableLevel . ' = ' . ($node[$this->tableLevel] + 1)
                )
            )
        );

        if (!empty($data)) {
            $sql = 'SELECT ' . $this->tableId . ' FROM ' . $this->table . ' WHERE ' . $this->tableId . ' IN(?a) ORDER BY ' . $orderField;
            $sorted_data = $this->db->getAll($sql, array_keys($data));

            $data = array_values($data);

            $last_coincidence = true;
            foreach ($sorted_data as $key => $value) {
                if ($data[$key][$this->tableId] == $value[$this->tableId] && $last_coincidence !== false) {
                    continue;
                } else {
                    $last_coincidence = false;

                    if ($key == 0) {
                        $this->ChangePositionAll($value[$this->tableId], $data[$key][$this->tableId], 'before');
                    } else {
                        $this->ChangePositionAll($sorted_data[($key)][$this->tableId], $sorted_data[($key - 1)][$this->tableId], 'after');
                    }
                }
            }
        }
    }

    /**
     * Makes UL/LI html from nested sets tree with links (if needed). UL id named as table_name + _tree.
     *
     * @param array $tree - nested sets tree array
     * @param string $nameField - name of field that contains title of URL
     * @param array $linkField - name of field that contains URL (if needed)
     * @param null|string $linkPrefix - URL prefix (if needed)
     * @param string $delimiter - linkField delimiter
     * @return string - UL/LI html code
     */
    public function MakeUlList($tree, $nameField, $linkField = array(), $linkPrefix = null, $delimiter = '')
    {
        $current_depth = 0;
        $node_depth = 0;
        $counter = 0;

        $result = '<ul id="' . $this->table . '_tree">';

        foreach ($tree as $node) {
            $node_depth = $node[$this->tableLevel];
            $node_name = $node[$nameField];

            if ($node_depth == $current_depth) {
                if ($counter > 0) $result .= '</li>';
            } elseif ($node_depth > $current_depth) {
                $result .= '<ul>';
                $current_depth = $current_depth + ($node_depth - $current_depth);
            } elseif ($node_depth < $current_depth) {
                $result .= str_repeat('</li></ul>', $current_depth - $node_depth) . '</li>';
                $current_depth = $current_depth - ($current_depth - $node_depth);
            }

            $result .= '<li>';

            if (!empty($linkField)) {
                $link_data = array();
                $linkField = !is_array($linkField) ? array($linkField) : $linkField;
                foreach($linkField as $field) {
                    $link_data[] = $node[$field];
                }

                $link = !is_null($linkPrefix) ? $linkPrefix . implode($delimiter, $link_data) : implode($delimiter, $link_data);

                $result .= '<a href="' . $link . '">' . $node_name . '</a>';
            } else {
                $result .= $node_name;
            }
            ++$counter;
        }

        $result .= str_repeat('</li></ul>', $node_depth) . '</li>';

        $result .= '</ul>';

        return $result;
    }
}

?>