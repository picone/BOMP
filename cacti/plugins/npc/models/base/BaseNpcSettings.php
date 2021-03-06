<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseNpcSettings extends Doctrine_Record
{

  public function setTableDefinition()
  {
    $this->setTableName('npc_settings');
    $this->hasColumn('user_id', 'integer', 3, array (
  'alltypes' => 
  array (
    0 => 'integer',
  ),
  'ntype' => 'mediumint(8) unsigned',
  'unsigned' => 1,
  'values' => 
  array (
  ),
  'primary' => true,
  'default' => '',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('settings', 'string', null, array (
  'alltypes' => 
  array (
    0 => 'string',
    1 => 'clob',
  ),
  'ntype' => 'text',
  'fixed' => false,
  'values' => 
  array (
  ),
  'primary' => false,
  'notnull' => false,
  'autoincrement' => false,
));
  }


}