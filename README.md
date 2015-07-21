# DbTree - Nested Sets

This class can be used to manipulate nested sets of database table records as an hierarchical tree.

It can initialize a tree, insert nodes in specific  positions of the tree, retrieve node and it's parent records, change nodes position and delete nodes..

This source file is part of the SESMIK CMS.

##Methods:

- `GetNode` - Receive all data for node with number $nodeId.
- `GetParent` - Receive data of closest parent for node with number $nodeId.
- `Insert` - Add new child element to node with number $parentId.
- `InsertNear` - Add a new element into the tree near node with number $nodeId.
- `MoveAll` - Assigns another parent ($parentId) to a node ($nodeId) with all its children.
- `ChangePosition` - Change position of nodes. Nodes have to have same parent and same level of nesting.
- `ChangePositionAll` - Swapping nodes with it's children. Nodes have to have same parent and same level of nesting. $nodeId1 can be placed "before" or "after" $nodeId2.
- `Delete` - Deletes element with number $nodeId from the tree without deleting it's children. All it's children will move up one level.
- `DeleteAll` - Deletes element with number $nodeId from the tree and all it children.
- `Full` - Returns all elements of the tree sorted by "left".
- `Branch` - Returns all elements of a branch starting from an element with number $nodeId.
- `Parents` - Returns all parents of element with number $nodeId.
- `Ajar` - Returns a slightly opened tree from an element with number $nodeId.
- `SortChildren` - Sort children in a tree for $orderField in alphabetical order.
- `MakeUlList` - Makes UL/LI html from nested sets tree with links (if needed). UL id named as table_name + _tree.

## History
- v4.4 - MakeUlList modified
- v4.3 - Added new method MakeUlList
- v4.2 - added fully functional demo samples
- v4.1 - added new method SortChildren

russian dicumentation [http://www.sesmikcms.ru/pages/read/biblioteka-dlja-raboty-s-derevjami-nested-sets/](http://www.sesmikcms.ru/pages/read/biblioteka-dlja-raboty-s-derevjami-nested-sets/)

##Author        Kuzma Feskov <kfeskov@gmail.com>
##Copyright © 2015, Kuzma Feskov
