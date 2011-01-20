<?php

require_once('class.durablednshandler.php');

/**
 * @name	-	DurableDNS EXAMPLES
 * @desc	-	integrate via SOAP with DurableDNS API
 * @version	-	1.0.0
 * @author	-	John Wamer (john@happyblender.com)
 * @date	-	11/20/2010

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




//ListZones Method
/*
$dns		=	new DurableDNSHandler;

$dns		->	ListZones();

	if(!$dns->error){
		print_r($dns->zone_list);
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//GetZone Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."

$dns				->	GetZone();

	if(!$dns->error){
		print_r($dns->zone_info);
	}
	else{
		print_r($dns->errors);
	}//end else

*/
//=============================================================================================================================//
//CreateZone Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."

$dns				->	CreateZone();

	if(!$dns->error){
		print_r($dns->zone_id);
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//UpdateZone Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."
$dns->ns			=	"XXXXXXXXXXXXXXX";

$dns				->	UpdateZone();

	if($dns->update){
		echo "Update Complete";
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//DeleteZone Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."

$dns				->	DeleteZone();

	if($dns->delete){
		echo "Delete Complete";
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//ListRecords Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."

$dns				->	ListRecords();

	if(!$dns->error){
		print_r($dns->zone_records);
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//GetRecord Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."
$dns->recordid		=	'XXXXXXXXXXXXXXX';

$dns				->	GetRecord();

	if(!$dns->error){
		print_r($dns->record_info);
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//CreateRecord Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."
$dns->recordname	=	"XXXXXXXXXXXXXXX";
$dns->recordtype	=	"XXXXXXXXXXXXXXX";
$dns->recorddata	=	"XXXXXXXXXXXXXXX";
$dns->ttl			=	XXXXXXXXXXXXXXX;


$dns				->	CreateRecord();

	if(!$dns->error){
		print_r($dns->recordid);
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//UpdateRecord Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."
$dns->recordid		=	'XXXXXXXXXXXXXXX';
$dns->recordname	=	"XXXXXXXXXXXXXXX";
$dns->recorddata	=	"XXXXXXXXXXXXXXX";
$dns->ttl			=	XXXXXXXXXXXXXXX;


$dns				->	UpdateRecord();

	if($dns->update){
		echo "Update Complete";
	}
	else{
		print_r($dns->errors);
	}//end else
*/	
//=============================================================================================================================//
//DeleteRecord Method
/*
$dns				=	new DurableDNSHandler;

$dns->zonename		=	"XXXXXXXXXXXXXXX";	//zone name here "example.com."	Do not forget the trailing dot "."
$dns->recordid		=	'XXXXXXXXXXXXXXX';


$dns				->	DeleteRecord();

	if($dns->delete){
		echo "Delete Complete";
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//ListJobs Method
/*
$dns				=	new DurableDNSHandler;

$dns				->	ListJobs();

	if(!$dns->error){
		print_r($dns->job_list);
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//
//ListJobs Method
/*
$dns				=	new DurableDNSHandler;

$dns->jobid			=	'XXXXXXXXXXXXXXX';

$dns				->	GetJob();

	if(!$dns->error){
		print_r($dns->job_info);
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//	
//CreateJob Method
/*
$dns						=	new DurableDNSHandler;

$dns->jobname				=	'XXXXXXXXXXXXXXX';
$dns->zonename				=	'XXXXXXXXXXXXXXX';
$dns->recordid				=	'XXXXXXXXXXXXXXX';
$dns->monitor_type			=	XXXXXXXXXXXXXXX;
$dns->port					=	XXXXXXXXXXXXXXX;
$dns->interval				=	XXXXXXXXXXXXXXX;
$dns->email_on_failure		=	"XXXXXXXXXXXXXXX";
$dns->email_to				=	'XXXXXXXXXXXXXXX';
$dns->dns_on_failure		=	"XXXXXXXXXXXXXXX";
$dns->dns_to				=	"XXXXXXXXXXXXXXX";
$dns->failure_threshold		=	XXXXXXXXXXXXXXX;


$dns						->	CreateJob();

	if($dns->create){
		echo "Job Created";
	}
	else{
		print_r($dns->errors);
	}//end else

*/
//=============================================================================================================================//	
//UpdateJob Method
/*
$dns						=	new DurableDNSHandler;

$dns->jobname				=	'XXXXXXXXXXXXXXX';
$dns->jobid					=	'XXXXXXXXXXXXXXX';
$dns->zonename				=	'XXXXXXXXXXXXXXX';
$dns->recordid				=	'XXXXXXXXXXXXXXX';
$dns->monitor_type			=	XXXXXXXXXXXXXXX;
$dns->port					=	XXXXXXXXXXXXXXX;
$dns->interval				=	XXXXXXXXXXXXXXX;
$dns->email_on_failure		=	"XXXXXXXXXXXXXXX";
$dns->email_to				=	'XXXXXXXXXXXXXXX';
$dns->dns_on_failure		=	"XXXXXXXXXXXXXXX";
$dns->dns_to				=	"XXXXXXXXXXXXXXX";
$dns->failure_threshold		=	XXXXXXXXXXXXXXX;


$dns						->	UpdateJob();

	if($dns->update){
		echo "Job Updated";
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//	
//DeleteJob Method
/*
$dns						=	new DurableDNSHandler;

$dns->jobid					=	'XXXXXXXXXXXXXXX';

$dns						->	DeleteJob();

	if($dns->delete){
		echo "Job Deleted";
	}
	else{
		print_r($dns->errors);
	}//end else
	
*/
//=============================================================================================================================//	
//GetBackupMX Method
/*
$dns						=	new DurableDNSHandler;

$dns->zonename				=	'XXXXXXXXXXXXXXX';

$dns						->	GetBackupMX();

	if(!$dns->error){
		echo "BackupMX Enabled: {$dns->backupmx}";
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//	
//EnableBackupMX Method
/*
$dns						=	new DurableDNSHandler;

$dns->zonename				=	'XXXXXXXXXXXXXXX';
$dns->primarymx				=	'XXXXXXXXXXXXXXX';

$dns						->	EnableBackupMX();

	if($dns->update){
		echo "BackupMX Enabled";
	}
	else{
		print_r($dns->errors);
	}//end else
*/
//=============================================================================================================================//	
//DisableBackupMX Method
/*
$dns						=	new DurableDNSHandler;

$dns->zonename				=	'XXXXXXXXXXXXXXX';

$dns						->	DisableBackupMX();

	if($dns->update){
		echo "BackupMX Disabled";
	}
	else{
		print_r($dns->errors);
	}//end else
*/


