<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseNpcHostGraphs extends Doctrine_Record
{

  public function setTableDefinition()
  {
    $this->setTableName('npc_host_graphs');
    $this->hasColumn('host_graph_id', 'integer', 4, array (
  'alltypes' => 
  array (
    0 => 'integer',
  ),
  'ntype' => 'int(11)',
  'unsigned' => 0,
  'values' => 
  array (
  ),
  'primary' => true,
  'notnull' => true,
  'autoincrement' => true,
));
    $this->hasColumn('host_object_id', 'integer', 4, array (
  'alltypes' => 
  array (
    0 => 'integer',
  ),
  'ntype' => 'int(11)',
  'unsigned' => 0,
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('local_graph_id', 'integer', 3, array (
  'alltypes' => 
  array (
    0 => 'integer',
  ),
  'ntype' => 'mediumint(8) unsigned',
  'unsigned' => 1,
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('pri', 'integer', 1, array (
  'alltypes' => 
  array (
    0 => 'integer',
    1 => 'boolean',
  ),
  'ntype' => 'tinyint(1)',
  'unsigned' => 0,
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '1',
  'notnull' => false,
  'autoincrement' => false,
));
  }


}
