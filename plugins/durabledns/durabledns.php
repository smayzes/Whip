TEST<?php

/**
 * Durabledns plugin.
 * 
 * Interface to the DurableDNS SOAP API
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */
class Durabledns extends SingletonWhipPlugin {
    
    const ZONE_REGEX    = '/^([a-z0-9-]+\.)+[a-z]{2,5}\.$/';
    const MONTH         = 2629744;
    const WEEK          = 604800;
    const DAY           = 86400;
    const HOUR          = 3600;
    const MINUTE        = 60;
    
    
    private $_soap = null;
    
    /**
     * _init_soap function.
     * 
     * @access private
     * @return void
     */
    private function _init_soap($function) {
        //if (null != $this->_soap) return;
        $this->_soap = new SoapClient(
            'https://durabledns.com/services/dns/'.$function.'.php?wsdl',
            array(
                'exceptions' => true
            )
        );
    }   //  function _init_soap
    

    /**
     * get_zones function.
     * 
     * @access public
     * @return void
     */
    function get_zones() {
        $this->_init_soap('listZones');
        try {
            $return = $this->_soap->ListZones(
                $this->_config['apiuser'],
                $this->_config['apikey']
            );
        }
        catch(Exception $e) {
        //  Exception occurred.
            throw $e;
        }
        $zones = array();
        if(count($return) > 0) {
            foreach($return as $soap_zone) {
                $zone = new Zone();
                $zone->name = $soap_zone->origin;
                $zone->mark_all_clean();
                $zones[] = $zone;
            }   //  each zone
        }   //  if return
    //  Return zones
        return $zones;
    }   //  function get_zones
    
    
    /**
     * get_zone function.
     * 
     * @access public
     * @return void
     */
    public function get_zone($name) {
        $this->_init_soap('getZone');
    //  Get zone
        $return = $this->_soap->GetZone(
            $this->_config['apiuser'],
            $this->_config['apikey'],
            $name
        );
        if (count($return)) {
            $zone = new Zone();
            $zone->name         = $return['origin'];
            $zone->ns           = $return['ns'];
            $zone->mbox         = $return['mbox'];
            $zone->serial       = $return['serial'];
            $zone->refresh      = $return['refresh'];
            $zone->retry        = $return['retry'];
            $zone->expire       = $return['expire'];
            $zone->minimum      = $return['minimum'];
            $zone->ttl          = $return['ttl'];
            $zone->xfer         = $return['xfer'];
            $zone->update_acl   = $return['update_acl'];
            $zone->mark_all_clean();
            return $zone;
        }
        return false;
    }   //  function get_zone
    
    
    /**
     * get_records function.
     * 
     * @access public
     * @param mixed $name
     * @return void
     */
    public function get_records($name) {
    //  Get records
        $this->_init_soap('listRecords');
        $return = $this->_soap->listRecords(
            $this->_config['apiuser'],
            $this->_config['apikey'],
            $name
        );
    //  Convert to Record models
        $records = array();
        if (is_array($return)) {
            foreach($return as $soap_record) {
                $record = new Record();
                $record->id     = $soap_record->id;
                $record->name   = $soap_record->name;
                $record->type   = $soap_record->type;
                $record->data   = $soap_record->data;
                $record->mark_all_clean();
                $records[] = $record;
            }   //  each record
        }   //  if records
    //  Return
        return $records;
    }   //  function get_records
    
    
    /**
     * get_record function.
     * 
     * @access public
     * @param mixed Record $record
     * @return void
     */
    function get_record(Zone $zone, Record $record) {
        $this->_init_soap('getRecord');
        $soap_record = $this->_soap->getRecord(
            $this->_config['apiuser'],
            $this->_config['apikey'],
            $zone->name,
            $record->id
        );
        if (count($soap_record)) {
            $record = new Record();
            $record->id     = $soap_record['id'];
            $record->name   = $soap_record['name'];
            $record->type   = $soap_record['type'];
            $record->data   = $soap_record['data'];
            $record->aux    = $soap_record['aux'];
            $record->ttl    = $soap_record['ttl'];
            $record->mark_all_clean();
            return $record;
        }   //  if records
        return false;
    }   //  function get_record
    
    
    
    /**
     * create_zone function.
     * 
     * @access public
     * @param mixed Zone $zone
     * @return void
     */
    function create_zone(Zone $zone) {
    //  Create zone
        $this->_init_soap('createZone');
        $return = $this->_soap->createZone(
            $this->_config['apiuser'],
            $this->_config['apikey'],
            $zone->name,
            $zone->ns,
            $zone->mbox,
            $zone->refresh,
            $zone->retry,
            $zone->expire,
            $zone->minimum,
            $zone->ttl,
            $zone->xfer,
            $zone->update_acl
        );
        if(is_numeric($return)){
            return $return;
        }
        return false;
    }   //  create zone
    
    
    /**
     * create_record function.
     * 
     * @access public
     * @return void
     */
    function create_record(Record $record) {
    //  TODO
    //  TODO
    //  TODO
        $this->_init_soap('createRecord');
        $return = $this->_soap->createRecord(
            $this->_config['apiuser'],
            $this->_config['apikey'],
            $record->zonename,
            $record->name,
            $record->type,
            $record->data,
            $record->aux,
            $record->ttl,
            $record->ddns_enabled
        );
        if(is_numeric($return)){
            return $return;
        }
        return false;
    }   //  create record
    
    
    /**
     * delete_zone function.
     * 
     * @access public
     * @return void
     */
    function delete_zone(Zone $zone) {
        $this->_init_soap('deleteZone');
        $return = $this->_soap->deleteZone(
            $this->_config['apiuser'],
            $this->_config['apikey'],
            $zone->name
        );
        if($return == 'Success') {
            return true;
        }
        return false;
    }   //  delete zone
    
    
    
    /**
     * delete_record function.
     * 
     * @access public
     * @param mixed Record $record
     * @return void
     */
    function delete_record(Zone $zone, Record $record) {
        $this->_init_soap('deleteRecord');
        $return = $this->_soap->deleteRecord(
            $this->_config['apiuser'],
            $this->_config['apikey'],
            $zone->name,
            $record->durabledns_id
        );
        if($return == 'Success') {
            return true;
        }
        return false;
    }   //  delete record
    
    
    

}   //  class Durabledns

