<?php

/**
 * @name	-	DurableDNS Handler
 * @desc	-	integrate via SOAP with DurableDNS API
 * @version	-	1.0.0
 * @author	-	John Wamer (john@happyblender.com)
 * @date	-	11/20/2010

 * @methods:	
 				name						desc
 				========================|===========================================
 				
 				listZones			  	|	Retrieve a list of DNS zones associated with your account
 				getZone					|	Retrieve a DNS zone
 				createZone				|	Create a new DNS zone
 				updateZone				|	Update a DNS zone
 				deleteZone				|	Delete a DNS zone
 				listRecords				|	Retrieve a list of records for a specified DNS zone
 				getRecord				|	Retrieve a DNS record
 				createRecord			|	Create a DNS record
 				updateRecord			|	Update a DNS record
 				deleteRecord			|	Delete a DNS record
 				getBackupMX				|	Get status of Backup MX service for a specified zone
 				enableBackupMX			|	Enable Backup MX service for a specified zone
 				disableBackupMX			|	Disable Backup MX service for a specified zone
 				listJobs				|	List monitoring jobs associated with your account
 				getJob					|	Retrieve a monitoring job
 				createJob				|	Create a monitoring job
 				updateJob				|	Update a monitoring job
 				deleteJob				|	Delete a monitoring job
	
 				
 * @parameters:
 
				
 				parameter					type		desc
 				========================|========================================================================================
 				
 				apiuser					|	(string)	Value for authentication &#65533; Provided by DurableDNS on the Dashboard
 				apikey					|	(string)	Value for authentication &#65533; Provided by DurableDNS on the Dashboard
 				zonename				|	(string)	Name of zone, followed by a dot (.). Example: &#65533;example.com.&#65533;
 				ns						|	(string)	Fully-qualified domain name of primary name server for zone. Example: &#65533;ns1.alwaysdnsllc.com.&#65533;
 				mbox					|	(string)	Email address of person responsible for the zone. Example: &#65533;support.durabledns.com&#65533; (Replace the &#65533;@&#65533; with a &#65533;.&#65533;)
 				refresh					|	(integer)	Zone refresh time in seconds
 				retry					|	(integer)	Zone retry time in seconds
 				expire					|	(integer)	Zone expiration time in seconds
 				minimum					|	(integer)	Minimum TTL for records within zone, in seconds
 				ttl						|	(integer)	TTL for zone (SOA record), or record, depending on use context
 				xfer					|	(string)	AXFR allow list, comma separated IP addresses in CIDR format. Example: &#65533;127.0.0.1/32,127.0.0.100/32&#65533;
 				update_acl				|	(string)	DDNS update ACL, comma separated IP addresses in CIDR format. Example: &#65533;127.0.0.1/32,127.0.0.100/32&#65533;
 				recordid				|	(integer)	Unique numeric ID of DNS record
 				recordname				|	(string)	Name of record to create. Example: &#65533;www&#65533; or &#65533;www.example.com.&#65533;
 				recordtype				|	(string)	DNS record type &#65533; A, AAAA, CNAME, HINFO, MX, NS, PTR, RP, SRV, or TXT
 				recorddata				|	(string)	Data for record, dependent on record type, could be IP Address, hostname, etc..
 				recordaux				|	(integer)	Preference, priority, or weight of record (optional)(default=0)
 				ddns_enabled			|	(Y/N)		Allow Dynamic DNS API Updates (Y or N) (default = N)
 				jobid					|	(integer)	Unique numeric ID of job.
 				jobname					|	(string)	Name of job to create
 				monitor_type			|	(integer)	Port check = 2, Ping check = 3
 				port					|	(integer)	If monitor_type_id is '2', then this is the TCP port to check. Otherwise, this is null (default = 80 when in use)
 				interval				|	(integer)	Number of minutes between checking the server
 				email_on_failure		|	(Y/N)		Should DurableDNS email you when the server fails? Y or N
 				email_to				|	(string)	Email address to email if email_on_failure = Y. Otherwise, this is null
 				dns_on_failure			|	(Y/N)		Should DurableDNS change the IP address of the record if the server fails? Y or N
 				change_dns_to			|	(string)	IP Address to change record data to if server fails if dns_on_failure = Y. Otherwise this is null.
 				failure_threshold		|	(integer)	Number of sequential check failures before determining the server has failed.
 				backupmx				|	(Y/N)		indicates if backup mx is enabled for specific domain
 				primarymx				|	(string)	Primary mail server to deliver deferred email to				
 				
 				
 
 * @requires:	PHP version 5.2.9 (only tested on this version, any 5.2 version should work, however there have been changes to the soap extension during some releases)
 				SOAP extension enabled
 				Openssl installed and enabled
 				
 				please do not contact author for assistance with installation/configuration issues as they will be marked as spam and ignored
 				
 				This class is given to you with no promise of support or functionality, however it does work and is used in an application under the required conditions
 
 
 
 * @Legal:	The DurableDNS SOAP API is subject to the Terms and Conditions of Service published on the website (http://durabledns.com/tos.html). 
 			The SOAP API Documentation is provided for the benefit of the customers of DurableDNS and easyGeek Group LLC. 
 			The SOAP API Documentation is provided without warranty, and easyGeek Group LLC shall not be held liable for any loss due to errors or omissions in the Documentation. 
 		
 			This class is released under the MIT License
 			
 			The MIT License

			Copyright (c) 2010 HappyBlender (www.happyblender.com)
			
			Permission is hereby granted, free of charge, to any person obtaining a copy
			of this software and associated documentation files (the "Software"), to deal
			in the Software without restriction, including without limitation the rights
			to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
			copies of the Software, and to permit persons to whom the Software is
			furnished to do so, subject to the following conditions:
			
			The above copyright notice and this permission notice shall be included in
			all copies or substantial portions of the Software.
			
			THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
			IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
			FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
			AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
			LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
			OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
			THE SOFTWARE.
			
			
 */

//============================================================================================================================================

class DurableDNSHandler{

	private $apiuser;
	private $apikey;
	
	//class objects
	public	$errors				=	array();						//	will contain a list of errors if they occur
	public	$zone_list			=	array();						//	will contain a list of zones
	public 	$zone_info			=	array();						//	will contain list of zone parameters
	public	$zone_records		=	array();						//	will contain a list of records for requested zone
	public	$record_info		=	array();						//	will contain a list of record parameters
	public	$job_list			=	array();						//	list of monitoring jobs for account
	public	$job_info			=	array();
	
	
	
	
	//parameters
	public	$error				=	false;							//will set to true on error
	public	$create				=	false;							//flag indicates if certain create operations have completed
	public	$update				=	false;							//flag to indicate if a update operation was successful
	public	$delete				=	false;							//flag to indicate if a delete operation was successful
	
	public	$zonename;
	public	$ns					=	'ns1.alwaysdnsllc.com.';
	public	$refresh			=	28800;
	public	$retry				=	7200;
	public	$expire				=	604800;
	public	$minimum			=	86400;
	public	$ttl				=	86400;
	public	$mbox				=	'menno.vanens.com';
	public	$xfer				=	null;
	public	$update_acl			=	null;
	
	public	$recordid;
	public	$recordname;
	public	$recordtype;
	public	$recorddata;
	public	$recordaux			=	10;
	public	$ddns_enabled		=	'N';
	
	public	$jobid;
	public	$jobname;
	public	$monitor_type;
	public	$port;
	public	$interval;
	public	$email_on_failure;
	public	$email_to;
	public	$dns_on_failure;
	public	$dns_to;
	public	$failure_threshold;
	
	public	$backupmx;
	public	$primarymx;
	
	
	
	function DurableDNSHandler() {
		$this->apiuser    = 'fJK8GQk6zDoF';
		$this->apikey     = 'N8FcuKdQUWxrzaOkb6';
	}  //  end function
	
	
	
	function ListZones() {
    //  listZones
		$sc			=	new SoapClient('https://durabledns.com/services/dns/listZones.php?wsdl', array('exceptions' => false));
		$return		=	$sc->ListZones($this->apiuser,$this->apikey);
		
		if(is_soap_fault($return)){
			$this->error     =	true;
			$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
			$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
		
		} //  end if
		
		else {
		
			if(count($return) > 0){
			
				foreach($return as $zone){
				
					$this->zone_list[]	=	$zone->origin;	
				
				}//end foreach
			
			}//end if
			else{
				
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	'0001';
				$this->errors[count($this->errors)]['Msg']		=	'No zones returned for specified account';

			}//end else
			
		}//end else
	
	}//end function
	
	function GetZone(){
	
		//getZone
		$sc			=	new SoapClient('https://durabledns.com/services/dns/getZone.php?wsdl', array('exceptions' => false));
		
		$return		=	$sc->GetZone($this->apiuser,$this->apikey,$this->zonename);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			
			else{
				
				if(count($return) > 0){
					
					$this->zone_info		=	$return;
					
				}//end if
				else{
					
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0002';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error no zone info returned';
					
				}//end else
				
			
			}//end else

	}//end function
	
	function CreateZone(){
	
		//createZone
		$sc				=	new SoapClient('https://durabledns.com/services/dns/createZone.php?wsdl', array('exceptions' => false));

		$return			=	$sc->createZone($this->apiuser,$this->apikey,$this->zonename,$this->ns,$this->mbox, $this->refresh,$this->retry,$this->expire,$this->minimum,$this->ttl,$this->xfer,$this->update_acl);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			else{
			
				if(is_numeric($return)){
				
					$this->zone_id		=	$return;
				
				}//end if
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0005';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, invalid zone id returned';
				
				}//end else
			
			}//end else
	
	}//end function
	
	function UpdateZone(){
	
		//updateZone
		$sc				=	new SoapClient('https://durabledns.com/services/dns/updateZone.php?wsdl', array('exceptions' => false));

		$return			=	$sc->updateZone($this->apiuser,$this->apikey,$this->zonename,$this->ns,$this->mbox, $this->refresh,$this->retry,$this->expire,$this->minimum,$this->ttl,$this->xfer,$this->update_acl);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			else{
				
				if((string)$return === 'Success'){
				
					$this->update		=	true;
				
				}//end if
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0006';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, zone update did not complete successfully';
				
				}//end else
			
			}//end else
	
	}//end function
	
	function DeleteZone(){
	
		//getZone
		$sc			=	new SoapClient('https://durabledns.com/services/dns/deleteZone.php?wsdl', array('exceptions' => false));
		
		$return		=	$sc->DeleteZone($this->apiuser,$this->apikey,$this->zonename);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			else{
				
				if((string)$return === 'Success'){
				
					$this->delete		=	true;
				
				}//end if
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0007';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, zone delete operation did not complete successfully';
				
				}//end else
			
			}//end else

	}//end function
	
	function ListRecords(){
	
		//listRecords
		$sc				=	new SoapClient('https://durabledns.com/services/dns/listRecords.php?wsdl', array('exceptions' => false));
		
		$return			=	$sc->listRecords($this->apiuser,$this->apikey,$this->zonename);
		
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			
			else{
				
				if(count($return) > 0){
					
					$i		=	0;
					
					foreach($return as $record){
					
						$this->zone_records[$i]['record_id']			=	$record->id;
						$this->zone_records[$i]['record_name']			=	$record->name;
						$this->zone_records[$i]['record_type']			=	$record->type;
						$this->zone_records[$i]['record_data']			=	$record->data;
						
					$i++;	
					}//end foreach
						
				}//end if
				else{

					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0003';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error no zone records returned';
					
				}//end else
				
			
			}//end else

	}//end function
	
	function GetRecord(){
		
		$sc				=	new SoapClient('https://durabledns.com/services/dns/getRecord.php?wsdl', array('exceptions' => false));

		$return			=	$sc->getRecord($this->apiuser,$this->apikey,$this->zonename,$this->recordid);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			
			else{
				
				if(count($return) > 0){
					
					$this->record_info		=	$return;
					
				}//end if
				else{
					
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0004';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error no record info returned';
					
				}//end else
				
			
			}//end else

	}//end function
	
	function CreateRecord(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/dns/createRecord.php?wsdl', array('exceptions' => false));

		$return			=	$sc->createRecord($this->apiuser,$this->apikey,$this->zonename,$this->recordname,$this->recordtype,$this->recorddata,$this->recordaux,$this->ttl,$this->ddns_enabled);
			
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			else{
			
				if(is_numeric($return)){
				
					$this->recordid		=	$return;
				
				}//end if
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0008';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, invalid record id returned';
				
				}//end else
			
			}//end else
	
	}//end function
	
	function UpdateRecord(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/dns/updateRecord.php?wsdl', array('exceptions' => false));

		$return			=	$sc->updateRecord($this->apiuser,$this->apikey,$this->zonename,$this->recordid,$this->recordname,$this->recordaux,$this->recorddata,$this->ttl,$this->ddns_enabled);
			
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			else{
							
				if((string)$return === 'Success'){
				
					$this->update		=	true;
				
				}//end if
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0009';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, record update did not complete successfully';
				
				}//end else	
						
			}//end else
	
	}//end function
	
	function DeleteRecord(){
		
		$sc				=	new SoapClient('https://durabledns.com/services/dns/deleteRecord.php?wsdl', array('exceptions' => false));

		$return			=	$sc->deleteRecord($this->apiuser,$this->apikey,$this->zonename,$this->recordid);
			
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			else{
							
				if((string)$return === 'Success'){
				
					$this->delete		=	true;
				
				}//end if
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0010';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, record delete operation did not complete successfully';
				
				}//end else	
						
			}//end else
	
	}//end function
	
	function ListJobs(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/monitoring/job.php?wsdl', array('exceptions' => false));

		$return			=	$sc->listJobs($this->apiuser,$this->apikey);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			
			else{
				
				if(count($return) > 0){
					
					$i		=	0;
					
					foreach($return as $job){
					
						$this->job_list[$i]['job_id']			=	$job->id;
						$this->job_list[$i]['job_name']			=	$job->name;
						
					$i++;	
					}//end foreach
						
				}//end if
				else{

					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0011';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error no jobs returned';
					
				}//end else
				
			
			}//end else

	
	}//end function
	
	function GetJob(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/monitoring/job.php?wsdl', array('exceptions' => false));

		$return			=	$sc->getJob($this->apiuser,$this->apikey,$this->jobid);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
			
			}//end if
			
			else{
				
				if(count($return) > 0){
					
					$this->job_info		=	$return;
						
				}//end if
				else{

					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0012';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error no job info returned';
					
				}//end else
				
			
			}//end else

	}//end function
	
	function CreateJob(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/monitoring/job.php?wsdl', array('exceptions' => false));

		$return			=	$sc->createJob($this->apiuser,$this->apikey,$this->jobname,$this->zonename,$this->recordid,$this->monitor_type,$this->port,$this->interval,$this->email_on_failure,$this->email_to,$this->dns_on_failure,$this->dns_to,$this->failure_threshold);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
				
				if((string)$return->faultstring === 'Success'){
				
					$this->create		=	true;
				
				}//end if
			
			}//end if
			
			else{
							
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	'0013';
				$this->errors[count($this->errors)]['Msg']		=	'Unknown error, create job operation did not complete successfully';
						
			}//end else

	
	
	}//end function
	
	function UpdateJob(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/monitoring/job.php?wsdl', array('exceptions' => false));

		$return			=	$sc->updateJob($this->apiuser,$this->apikey,$this->jobid,$this->jobname,$this->zonename,$this->recordid,$this->monitor_type,$this->port,$this->interval,$this->email_on_failure,$this->email_to,$this->dns_on_failure,$this->dns_to,$this->failure_threshold);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
				
				
			
			}//end if			
			else{
				if((string)$return === 'Success'){
				
					$this->update		=	true;
				
				}//end if
				
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0014';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, update job operation did not complete successfully';
				}//end else	
					
			}//end else

	
	
	}//end function
	
	function DeleteJob(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/monitoring/job.php?wsdl', array('exceptions' => false));

		$return			=	$sc->deleteJob($this->apiuser,$this->apikey,$this->jobid);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
				
				
			
			}//end if			
			else{
				if((string)$return === 'Success'){
				
					$this->delete		=	true;
				
				}//end if
				
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0015';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, delete job operation did not complete successfully';
				}//end else	
					
			}//end else

	
	
	}//end function
	
	function GetBackupMX(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/email/getBackupMX.php?wsdl', array('exceptions' => false));

		$return			=	$sc->getBackupMX($this->apiuser,$this->apikey,$this->zonename);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
				
				
			
			}//end if			
			else{
				if((string)$return === 'Y' || (string)$return === 'N'){
				
					$this->backupmx		=	$return;
				
				}//end if
				
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0016';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, unknown BackupMX status returned';
				}//end else	
					
			}//end else

	
	}//end function
	
	function EnableBackupMX(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/email/enableBackupMX.php?wsdl', array('exceptions' => false));

		$return			=	$sc->enableBackupMX($this->apiuser,$this->apikey,$this->zonename,$this->primarymx);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
				
				
			
			}//end if			
			else{
				if((string)$return === 'Success'){
				
					$this->update		=	true;
				
				}//end if
				
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0017';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, operation did not complete successfully';
				}//end else	
					
			}//end else

	
	}//end function
	
	function DisableBackupMX(){
	
		$sc				=	new SoapClient('https://durabledns.com/services/email/disableBackupMX.php?wsdl', array('exceptions' => false));

		$return			=	$sc->disableBackupMX($this->apiuser,$this->apikey,$this->zonename);
		
			if(is_soap_fault($return)){
			
				$this->error	=	true;
				$this->errors[count($this->errors)]['Code']		=	$return->faultcode;
				$this->errors[count($this->errors)]['Msg']		=	$return->faultstring;
				
				
			
			}//end if			
			else{
				if((string)$return === 'Success'){
				
					$this->update		=	true;
				
				}//end if
				
				else{
				
					$this->error	=	true;
					$this->errors[count($this->errors)]['Code']		=	'0018';
					$this->errors[count($this->errors)]['Msg']		=	'Unknown error, operation did not complete successfully';
				}//end else	
					
			}//end else

	
	}//end function



}//end class


