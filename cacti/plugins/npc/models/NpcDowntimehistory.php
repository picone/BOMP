<?php

class NpcDowntimehistory extends BaseNpcDowntimehistory
{
    public function setUp()
    {
        $this->hasOne('NpcObjects as Object', array('local' => 'object_id', 'foreign' => 'object_id'));
        $this->hasOne('NpcInstances as Instance', array('local' => 'instance_id', 'foreign' => 'instance_id'));
        $this->hasOne('NpcServices as Service', array('local' => 'object_id', 'foreign' => 'service_object_id'));
        $this->hasOne('NpcHosts as Host', array('local' => 'object_id', 'foreign' => 'host_object_id'));
    }
}
