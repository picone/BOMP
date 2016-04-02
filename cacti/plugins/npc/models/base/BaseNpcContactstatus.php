<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseNpcContactstatus extends Doctrine_Record
{

  public function setTableDefinition()
  {
    $this->setTableName('npc_contactstatus');
    $this->hasColumn('contactstatus_id', 'integer', 4, array (
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
    $this->hasColumn('instance_id', 'integer', 2, array (
  'alltypes' => 
  array (
    0 => 'integer',
  ),
  'ntype' => 'smallint(6)',
  'unsigned' => 0,
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '0',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('contact_object_id', 'integer', 4, array (
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
  'default' => '0',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('status_update_time', 'timestamp', null, array (
  'alltypes' => 
  array (
    0 => 'timestamp',
  ),
  'ntype' => 'datetime',
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '0000-00-00 00:00:00',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('host_notifications_enabled', 'integer', 2, array (
  'alltypes' => 
  array (
    0 => 'integer',
  ),
  'ntype' => 'smallint(6)',
  'unsigned' => 0,
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '0',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('service_notifications_enabled', 'integer', 2, array (
  'alltypes' => 
  array (
    0 => 'integer',
  ),
  'ntype' => 'smallint(6)',
  'unsigned' => 0,
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '0',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('last_host_notification', 'timestamp', null, array (
  'alltypes' => 
  array (
    0 => 'timestamp',
  ),
  'ntype' => 'datetime',
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '0000-00-00 00:00:00',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('last_service_notification', 'timestamp', null, array (
  'alltypes' => 
  array (
    0 => 'timestamp',
  ),
  'ntype' => 'datetime',
  'values' => 
  array (
  ),
  'primary' => false,
  'default' => '0000-00-00 00:00:00',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('modified_attributes', 'integer', 4, array (
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
  'default' => '0',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('modified_host_attributes', 'integer', 4, array (
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
  'default' => '0',
  'notnull' => true,
  'autoincrement' => false,
));
    $this->hasColumn('modified_service_attributes', 'integer', 4, array (
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
  'default' => '0',
  'notnull' => true,
  'autoincrement' => false,
));
  }


}