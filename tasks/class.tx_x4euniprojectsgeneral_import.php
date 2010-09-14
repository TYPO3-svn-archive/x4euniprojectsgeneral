<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/fileadmin/localconfs/project_import_localconf.php');	

class tx_x4euniprojectsgeneral_import extends tx_scheduler_Task {
	
	var $dbgMsg = array();
	
	var $_EXTKEY = 'x4euniprojectsgeneral';
	
	// Fields set by 'additional field provider'
	/**
	 * Pid for projects 
	 * @var string
	 */
	var $projpid;

	/**
	 * username for oai
	 * @var string
	 */
	var $oaiuser;
	
	/**
	 * password for oai
	 * @var string
	 */
	var $oaipw;
	
	/**
	 * url to oai
	 * @var string
	 */
	var $oaiurl;
	
	/** 
	 * if value is set, all project will be imported
	 * @var boolean
	 */
	var $getall;
	
	// Properties set by project_import_localconf located in 'fileadmin/localconfs/'
	/**
	 * contains category matching, title, pid's and orgid range.
	 * @var array
	 */
	var $mapping;
	
	/**
	 * contains the configuration, e.g. table names etc.
	 * @var array
	 */
	var $config;
	
	/**
	 * OAI url
	 * @var string
	 */
	var $url;
	
	/**
	 * resumtion url, used to resume a import, because results are divided in pages (200 records each)
	 * @var string
	 */
	var $resumptionUrl;
	
	
	
	/**
	 * The main class of the scheduler task
	 * @return boolean true if operation succeded. otherwise false
	 */
	public function execute(){
		$this->mapping = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->_EXTKEY]['projectsMapping'][$this->projpid];
		$this->config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->_EXTKEY]['configProjectsImport'];
		
		$xmlArr = array();
		
		if(empty($this->mapping) || empty($this->config)) {
			$parentObject->addMessage('Mapping or config empty', t3lib_FlashMessage::ERROR);
			return false;
		}
		
		$this->dbgMsg[] = 'Running import for "'.$this->mapping['title'].'"';

		$this->url = $this->oaiurl . '&metadataPrefix=fdb_proj&filterStatus=PBL&filterOrgExt=' . $this->mapping['rdborgid_from'] . '&filterOrgExtTo=' .$this->mapping['rdborgid_to'];
		$this->resumptionUrl = $this->oaiurl .'&resumptionToken=';
		
		$xmlArr = $this->getDataFromOai();
		
		$importData = array();
		foreach ($xmlArr as $xml){
			$importData = array_merge($importData, $this->oaiXmlToArr($xml));
		}
			
		$ret = $this->doImport($importData);
		$this->dbgMsg[] = "Inserted: " . $ret['inserted'] . " | Updated: " . $ret['updated'] . " | Deleted: " . $ret['deleted']. " | Failed: " . $ret['failed'];
	
		t3lib_div::debug($this->dbgMsg);
		
		if(intval($ret['failed']) > 0 ) {
			return false;
		} else {
			return true;
		}
	}	
	
	
	
	/**
	 * retrieves xml data from oai
	 * @return array array of xml files
	 */
	function getDataFromOai(){
		$xmlArr = array();
		// Get xmlstr over oai
		$xmlstr = $this->file_post_contents($this->url,false,$this->oaiuser,$this->oaipw);
		$xmlArr[0] = new SimpleXMLElement($xmlstr);

		// Used to limit the oai calls
		$count = 0;
		
		// if a resumptionToken is set, get further data
		while($xmlArr[$count]->ListRecords->resumptionToken != '' && $count < $this->config['maxresumptionToken']){
			$url = $this->resumptionUrl . $xmlArr[$count]->ListRecords->resumptionToken[0];
			$xmlstr = $this->file_post_contents($url,false,$this->oaiuser,$this->oaipw);
			$count++;
			$xmlArr[$count] = new SimpleXMLElement($xmlstr);
		}
		
		return $xmlArr;
	}
	
	
	
	/**
	 * parses the oai xml
	 * @return array of xml contents
	 */
	function oaiXmlToArr($xml){
		$importData = array();
		foreach ($xml->ListRecords->record as $record) {
			$ns_dc = $record->metadata->children('http://purl.org/forschdb_project/');
			$ns['dc'] = $ns_dc->children('http://purl.org/dc/elements/1.1/');
			$ns['fdb'] = $ns_dc->children('http://purl.org/forschdb_project/');
			
			//insert xml children in array, depending on key
			$tmpArray = array();
			foreach($ns as $nsid => $nsel){
			
				foreach($nsel as $skey => $svalue) {
					switch($skey){
						case 'principalinvestigator_dni':
						case 'coprincipalinvestigator_dni':
						case 'projectmember_dni':
						case 'principalinvestigator_mcssid':
						case 'projectmember_mcssid':
						case 'coprincipalinvestigator_dni':
							if(trim($svalue)!=''){
								if (array_key_exists($skey,$tmpArray)) $tmpArray[$skey] .= ",".trim($svalue);
								else $tmpArray[$skey] = trim($svalue);
							}
						break;
						
						case 'type':
							foreach($svalue->attributes() as $okey => $ovalue){
								if($this->config['u8toIso']) $ovalue = utf8_decode($ovalue);
								$ovalue = html_entity_decode($ovalue);
								if (array_key_exists($okey,$tmpArray)) $tmpArray[$okey] .= trim((string)($ovalue));
								else $tmpArray[$okey] = trim((string)($ovalue));
						}
						break;
						case 'rdborgid':
							if($this->config['u8toIso']) $svalue = utf8_decode($svalue);
							if (array_key_exists($skey,$tmpArray)) $tmpArray[$skey] .= "," . trim((string)($svalue));
							else $tmpArray[$skey] = trim((string)($svalue));
						break;
						default:
							$svalue = str_replace('&#8260;', '/', $svalue);
							$svalue = html_entity_decode($svalue, ENT_QUOTES, "utf-8");
							if($this->config['u8toIso']) $svalue = utf8_decode($svalue);
							if (array_key_exists($skey,$tmpArray)) $tmpArray[$skey] .= trim((string)($svalue));
							else $tmpArray[$skey] = trim((string)($svalue));
						break;
					}
				}
			}
			$importData[]= $tmpArray;
		}
		return $importData;
	}
	
	
	
	/**
	 * mapps fields and writes data to db
	 * @return array assoc array with counters
	 */
	function doImport($input){
		$count = array( 'updated' => 0, 'inserted' => 0, 'failed' => 0, 'deleted' => 0);
		//Alle fdb_ids aus der ProjektDB zur überprüfung ob bereits vorhanden
		$fdb_ids = array();
		$ccc =0;
		$fdb = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('fdb_id',$this->config['tableProjDb'],'fdb_id != 0 AND deleted = 0','','fdb_id ASC');
		
		//used to check if record is still in FDB
		$procFdbIds = array();
		foreach($fdb as $f) $fdb_ids[] = $f['fdb_id'];
		foreach($input as $record){
			$mArr = array();

			// Match persons
			$principalInvestigator = $this->matchPersons($record['principalinvestigator_dni'], $record['principalinvestigator_mcssid'], $this->mapping['pid_pers']);
			$coPrincipalInvestigator = $this->matchPersons($record['coprincipalinvestigator_dni'], $record['coprincipalinvestigator_mcssid'], $this->mapping['pid_pers']);
			$projectMember = $this->matchPersons($record['projectmember_dni'], $record['projectmember_mcssid'], $this->mapping['pid_pers']);
			$personsinvolved = $coPrincipalInvestigator;
			if($personsinvolved != '' && $projectMember != '') $personsinvolved .= ",";
			$personsinvolved .= $projectMember; 
			
			//$contact = '';
			
			$mArr['pid'] = $this->mapping['pid_proj'];
			$mArr['tstamp'] = ($record['lastupdate']) ?  strtotime($record['lastupdate']): '';
			$mArr['crdate'] = ($record['creationdate']) ?  strtotime($record['creationdate']): '';
			$mArr['projecttitle'] = ($record['title']) ?  $record['title']: '';
			$mArr['projectmanagement'] = $principalInvestigator;
			//$mArr['externalprojectmanagement'] = '';
			$mArr['personsinvolved'] = $personsinvolved;
			//$mArr['externalpersonsinvolved'] = '';
			$mArr['start'] = ($record['startdate']) ?  $record['startdate']: '';
			$mArr['end'] = ($record['enddate']) ?  $record['enddate']: '';
			$mArr['financing'] = ($record['financedby']) ?  $record['financedby']: '';
			//$mArr['collaboration'] = '';
			//$mArr['volume'] = '';
			$mArr['link1'] = ($record['url']) ?  $record['url']: '';
			//$mArr['link2'] = '';
			$mArr['description'] = ($record['description']) ?  $record['description']: '';
			//$mArr['comment'] = '';
			//$mArr['methodology'] = '';
			//$mArr['picture'] = '';
			//$mArr['finished'] = '';
			$mArr['category'] = '';
			//$mArr['category'] = ($this->mapping['cat_matching'][$record['pubtype_weboffice']])?$this->mapping['cat_matching'][$record['pubtype_weboffice']]:0;
			//if($mArr['category'] == 0) $mArr['category'] = ($this->mapping['cat_matching'][$record['otid']])?$this->mapping['cat_matching'][$record['otid']]:0;
			//$mArr['contact'] = '';
			$mArr['rdborgid'] = $record['rdborgid'];
			// fdb identifier
			$mArr['fdb_id'] = $record['identifier'];
			
			//insert or update mArr to projDB
			if (in_array($mArr['fdb_id'], $fdb_ids)){
				if($GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->config['tableProjDb'],'fdb_id = '. $mArr['fdb_id'],$mArr)){
					$count['updated']++;
				} else {
					t3lib_div::debug($mArr['fdb_id'].": FAILED ", "Error");
					$count['failed']++;
				}
			} else {
				$mArr['crdate'] = time();
				if($GLOBALS['TYPO3_DB']->exec_INSERTquery($this->config['tableProjDb'],$mArr)){
					$count['inserted']++;
				} else {
					t3lib_div_debug($mArr['fdb_id'].": FAILED","Error");
					$count['failed']++;
				}
			}
			$procFdbIds[] = $mArr['fdb_id'];
		}
		//delete publications
		$count['deleted'] = $this->deleteOldPublications($procFdbIds);
		return $count;
		
	}
	
	
	
	/**
	 * matches fdb users to local users. 
	 * fdb_id (dni) needs to be set before import.
	 * @return string matched person in comma seperated list
	 */
	function matchPersons($int_ids, $int_mcssids, $pid = -1){
		$int = '';
		$int_ids = ($int_ids) ? $int_ids : -1;
		$int_mcssids = ($int_mcssids) ? $int_mcssids : -1;
		
		$pers_int = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid',$this->config['tablePersDb'],'pid = ' . $pid .' AND (dni IN ('.$int_ids.') OR mcss_id IN ('.$int_mcssids.')) AND deleted = 0 AND hidden = 0');
		$persons = '';
		if(is_array($pers_int)){
			foreach ($pers_int as $v){
				$persons[] = $v['uid'];
			}
		}
		
		if(is_array($persons)) $persons = implode(",", $persons);

		return $persons;
	}
	
	
	
	/**
	 * Gets xml string from oai
	 * @return string with xml data
	 */
	function file_post_contents($url,$headers=false,$user='',$pass='') {
		$url = parse_url($url);
		
		if (!isset($url['port'])) {
			if ($url['scheme'] == 'http') { $url['port']=80; }
			elseif ($url['scheme'] == 'https') { $url['port']=443; }
		}
		$url['query']=isset($url['query'])?$url['query']:'';
	
		$url['protocol']=$url['scheme'].'://';
		$eol="\r\n";
	
		$headers =  "GET ".$url['protocol'].$url['host'].$url['path']."?".$url['query']." HTTP/1.0".$eol.
	                "Host: ".$url['host'].$eol.
	                "Referer: ".$url['protocol'].$url['host'].$url['path'].$eol.
	                "Content-Type: application/x-www-form-urlencoded".$eol.
	                "Content-Length: ".strlen($url['query']).$eol.
	                "Authorization: Basic ".base64_encode("$user:$pass").$eol.
	                $eol.$url['query'];
		$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

		if($fp) {
			fputs($fp, $headers);
			$result = '';
			while(!feof($fp)) { $result .= fgets($fp, 128); }
			fclose($fp);
	
			$pattern="/^(.+?)\r\n\r\n(.+)/s"; //as per RFC
			//print_r($result);
			$result=preg_match($pattern,$result,$matches);
			if (!empty($matches[1])) $headers=$matches[1];
			if (!empty($matches[2])) return $matches[2];
		//return $result;
		}
	}
	
	
	
	/**
	* Shows set additional fields in scheduler overview
	*/
	public function getAdditionalInformation() {
		return 'PROJPID: '.$this->projpid .
		', OAIUSER: ' . $this->oaiuser . 
        ', OAIPW: ' . ' *****';

	}
	
	
	
	
	
	
	
	
	
	
	// Obsolete functions.  used in publication import
/*
	function cond_explode($token, $str, $cond = '&'){
		if(strpos($str, $cond)>0){
			$pos = 0;
			$offset = 0;
			$arr = array(); 
			$tpos = strpos($str,$token,$offset);
			$max = 0;
			
			while ($tpos != false && $max++ < 5){
				$t = substr($str, $pos, $tpos-$pos);
				if(preg_match('/[0-9]$/', $t)==0){
					$arr[] = $t;
					$pos = $tpos+1;
					$offset = $pos;
				}else {
					$offset = $tpos+1;
				}
				$tpos = strpos($str,$token,$offset);
			}
			if(sizeof($arr) == 0) $arr[] = $str;
			return $arr;
		} else {
			return explode($token, $str);
		}
	}
	
	
	function xml2phpArray($xml,$arr){
	    $iter = 0;
	        foreach($xml->children() as $b){
	                $a = $b->getName();
	                if(!$b->children()){
	                        $arr[$a] = trim($b[0]);
	                }
	                else{
	                        $arr[$a][$iter] = array();
	                        $arr[$a][$iter] = xml2phpArray($b,$arr[$a][$iter]);
	                }
	        $iter++;
	        }
	        return $arr;
	} 
	
	// Es werden alle Publicationen gelöscht welche eine fdb_id besitzen aber nicht im array $fdbids vorhanden sind
	// Dies muss beachtet werden falls der Import angepasst wird, zB nur abrufen von bestimmten Publicationen.
	function deleteOldPublications($fdbids){
		$count = 0;
		$WHERE = 'pid = '.$this->mapping['pid_publ'] . ' AND fdb_id != 0 AND fdb_id NOT IN ('.implode(",", $fdbids).')';
		$delPubs = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('fdb_id',$this->config['tablePublDb'],$WHERE);
		
		if($GLOBALS['TYPO3_DB']->exec_DELETEquery($this->config['tablePublDb'],$WHERE )){
			foreach($delPubs as $pub){
				if ($this->verbose)echo $pub['fdb_id'].": DELETED </br>";
				$count++;
			}
		}
		
		return $count;
	}
	
	*/
	
	/*
	function parseNames($cStr = ''){
		if ($cStr == '') return;
	//	$cArr = explode(';', trim($cStr));
		$cArr = $this->cond_explode(';', trim($cStr), '&');
		$names = array();
		if (is_array($cArr)){
			foreach($cArr as $key => $value){
				$p = explode(',', trim($value));
				//$p[0] = str_replace(chr(20), "", ($p[0]));
				$names[] = array('lastname' => trim($p[0], "\xc2\xa0"), 'firstname' => trim($p[1], "\xc2\xa0"));
			}
		} else {
			$p = explode(',', trim($cArr));
			$names[] = array('lastname' => $p[0], 'firstname' => $p[1]); 
		}
		return $names;
	}
	*/
	
	/*
	function omatchPersons($persons, $int_ids, $int_mcssids){
		$sorting = '';
		$sorting_text = array();
		$ext = '';
		$int = '';
		$int_ids = ($int_ids) ? $int_ids : -1;
		$int_mcssids = ($int_mcssids) ? $int_mcssids : -1;
		$ext_ct = 1;
		$pers_int = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid, lastname, firstname',$this->config['tablePersDb'],'(dni IN ('.$int_ids.') OR mcss_id IN ('.$int_mcssids.')) AND deleted = 0 AND hidden = 0');
		foreach($pers_int as $i){
			$int .= $i['uid'].",";
		}
		
		if ($persons != ''){
			foreach($persons as $key => $person){
				$is_int = false;
				// Check if name matches
				
				foreach($pers_int as $i){
					if (trim(strtolower(($i['lastname']))) == trim(strtolower($person['lastname'])) && trim(strtolower(($i['firstname']))) == trim(strtolower($person['firstname']))){
						$sorting .= $i['uid'].",";
						$sorting_text[] = ($i['lastname']).", ".($i['firstname']);
						$is_int = true;
						break;
					}
				}
				if (!$is_int){
					// If name not matched before check for short firstnames
					foreach($pers_int as $i){
						if (trim(strtolower(($i['lastname']))) == trim(strtolower($person['lastname'])) && substr(trim(strtolower(($i['firstname']))),0,1) == substr(trim(strtolower($person['firstname'])),0,1)){
							$sorting .= $i['uid'].",";
							$sorting_text[] = ($i['lastname']).", ".($i['firstname']);
							$is_int = true;
							break;
						}
					}
				}
				if(!$is_int){
					$sorting .= 'ext-'.$ext_ct.",";
					$sorting_text[] = $person['lastname'].",".$person['firstname'];
					$ext .= $person['lastname'].",".$person['firstname'].",".$ext_ct."\n";
					$ext_ct++;
				}
			}
		}
		
		return array('sorting' => trim($sorting, ',') , 'sorting_text' => implode("; ",$sorting_text), 'ext' => $ext, 'int' => $int);
	}
	*/
	
	
	
	}

?>