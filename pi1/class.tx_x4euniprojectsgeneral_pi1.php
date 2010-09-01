<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Andi Keller (andi@4eyes.ch)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Project list and detail view' for the 'x4euniprojects' extension.
 *
 * @author	Andi Keller <andi-at-4eyes.ch>
 */

require_once(t3lib_extMgm::extPath('x4epibase').'class.x4epibase.php');
class tx_x4euniprojectsgeneral_pi1 extends x4epibase {
	var $prefixId = 'tx_x4euniprojectsgeneral_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.x4euniprojectsgeneral_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'x4euniprojectsgeneral';	// The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * Name of the person extension
	 * @var string
	 */
	var $persExtKey = 'x4epersdb';
	
	/**
	 * Name of the table containing persons
	 * @var string
	 */
	var $personTable = 'tx_x4epersdb_person';
	
	/**
	 * Name of firstname field in person extension
	 * @var string
	 */
	var $firstNameField = 'firstname';
	
	/**
	 * Name of lastname field in person extension
	 * @var string
	 */
	var $lastNameField = 'lastname';
	
	/**
	 * Name of title field in person extension
	 * @var string
	 */
	var $titleField = 'title';
	
	/**
	 * Name of title_after field in person extension
	 * @var string
	 */
	var $titleAfterField = 'title_after';
	
	/**
	 * Name of the table containing projects
	 *
	 * @todo: why are there two variables? find out and remove the unncessary
	 *
	 * @var string
	 */
	var $table = 'tx_x4euniprojectsgeneral_list';
	var $tableName = 'tx_x4euniprojectsgeneral_list';
	
	/**
	 * Name of the table containing categories
	 * @var string
	 */
	var $categoryTable = 'tx_x4euniprojectsgeneral_category';

	/**
	 * Sets inital variables form typoscript and flexform
	 *
	 * @param $content	string	Deprecated, not used
	 * @param $conf		array	Typoscript configuration array
	 * @return void
	 */
	function init($content,$conf) {
		parent::init($content,$conf);
		$this->manualFieldOrder_list = t3lib_div::trimExplode(',',$this->getTSFFvar('fieldsList'),1);
		$this->projectCategory = $this->conf['projectCategory'];

		$this->conf=$conf;
		$this->internal = $this->conf['listView.'];
		$this->cols = 0;
		$this->detailview = 0;
		$this->colCount = 0;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values
		$this->pi_initPIflexform();

		if (isset($this->conf['orderByList']) && ($this->conf['orderByList'] != '')) {
			$this->internal['orderByList'] = $this->conf['orderByList'];
		} else {
			$this->internal['orderByList'] = $this->conf['searchFieldList'];
		}

		$this->internal['descFlag'] = $this->conf['orderDesc'];

		if ($this->conf['persDB.']) {

			$p = $this->conf['persDB.'];
			$this->persExtKey = $p['extKey'];
			if($p['table'] != ''){
				$this->personTable = $p['table'];
			}
			$this->firstNameField = $p['firstNameField'];
			$this->lastNameField = $p['lastNameField'];
			$this->titleAfterField = $p['titleAfterField'];
			$this->titleField = $p['titleField'];
		}

		if ($this->conf['table'] != '') {
			$this->table = $this->conf['table'];
		}
	}

	/**
	 * Main function, calls init and then decides which content to display
	 *
	 * @param string 	$content
	 * @param string 	$conf		Typoscript
	 *
	 * @return string	HTML-String, extension output
	 */
	function main($content,$conf)	{
		$this->init($content,$conf);

		if ($this->getTSFFvar('modeSelection') == 'contact') {
			$out = $this->getContactInfo();
		} else {
			if (strstr($this->cObj->currentRecord,'tt_content'))	{
				$this->conf['pidList'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'pages');
				if ($this->conf['pidList'] == '')  $this->conf['pidList'] = $this->getTSFFvar('pages');

				$this->conf['recursive'] = $this->cObj->data['recursive'];
			}
			$out = $this->getView();
		}
		return $this->pi_wrapInBaseClass($out);
	}


	/**
	 * Function to create a default table-like list view
	 *
	 * @param	String 	$addWhere	Additional where condition to select the records
	 * @return	String				HTML-View of list
	 */
	function listView($addWhere='')	{
		global $TCA;
		$this->internal['currentTable'] = $this->table;

		if ($this->template == '') {
			$this->template = $this->cObj->fileResource($this->conf['templateList']);
		}

		if ($this->listT == '') {
			$this->listT = $this->cObj->getSubpart($this->template,'###list###');
		}
		$this->rowsT = $this->cObj->getSubpart($this->listT,'###rows###');
		$this->rowT[0] = $this->cObj->getSubpart($this->rowsT,'###row0###');
		$this->rowT[1] = $this->cObj->getSubpart($this->rowsT,'###row1###');
		$this->cellT[0] = $this->cObj->getSubpart($this->rowT[0],'###cell###');
		$this->cellT[1] = $this->cObj->getSubpart($this->rowT[1],'###cell###');

		$this->fields = t3lib_div::trimExplode(',', $this->getTSFFvar('fieldsList'));
		$this->personSingleUid = $this->conf['personSingleUid'];

		$lConf = $this->conf['listView.'];	// Local settings for the listView function

		// decide whether single view or list view has to be displayed
		if($this->piVars['showUid'] != ''){
			$this->detailview = 1;
			return $this->singleView($content,$conf);
		} else {
			// get uid of page where single view is diplayed
    		$this->singleUid = $this->getTSFFvar('detailPageUid');
    		if($this->singleUid == ''){
    			$this->singleUid = $GLOBALS['TSFE']->id;
    		}
			$pUid = $_GET['tx_'.$this->persExtKey.'_pi1']['showUid'];

  			if($pUid != 0){
  				return $this->makePersonelList($pUid);
  			} else {

	  			// get mode to display. 0 for current, 1 for terminated and 2 for all
	  			$mode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'modeSelection');

	  			// Make Where Statement
	  			$WHERE = '';

				if($mode == 1 || $mode == "pm_finished"){
	  				$WHERE .= 'AND finished = 1 ';
	  			} else if ($mode == 0 || $mode == "pm_running"){
	    			$WHERE .= 'AND finished = 0 ';
				}

				$WHERE .= $addWhere;

	    		if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;

	    			// Initializing the query parameters:
				if ($this->piVars['sort'] == ""){
					$this->piVars['sort'] = "projecttitle";
				}

	    		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);

				$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,5);		// Number of results to show in a listing.
	    		$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
	    		$this->internal['searchFieldList']='projecttitle,projectmanagement,personsinvolved,externalprojectmanagement,externalpersonsinvolved';
	    		$this->internal['orderByList']='projecttitle, projectmanagement, personsinvolved';

				//ugly workaround for searching ids in blob:
				$Wuids = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid',$this->personTable,'1 '.$this->cObj->enableFields($this->personTable).$this->cObj->searchWhere($GLOBALS['TYPO3_DB']->escapeStrForLike($this->piVars['sword'],$this->personTable),$this->lastNameField.','.$this->firstNameField,$this->personTable));
				$pm_or = '';
				$pi_or = '';
				foreach ($Wuids as $w){
					$pm_or .= ' OR (FIND_IN_SET('.$w[uid].',projectmanagement) > 0) ';
					$pi_or .= ' OR (FIND_IN_SET('.$w[uid].',personsinvolved) > 0) ';
				}

	    		// Make Where Statement
	    		$WHERE .= 'AND (1 '.$this->cObj->searchWhere($GLOBALS['TYPO3_DB']->escapeStrForLike($this->piVars['sword'],$this->personTable),$this->internal['searchFieldList'],$this->table).chr(10);
				$WHERE .= $pm_or . $pi_or;
				$WHERE .= ')';

				if ($this->projectCategory != '') $WHERE .= ' AND FIND_IN_SET('.$this->projectCategory.', category) > 0';
	  			$backupSearchWord = $this->piVars['sword'];
	  			$this->piVars['sword'] = '';

	    		// get number of results
				$res = $this->pi_exec_query($this->table,1,$WHERE);

	    		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

	    		// Make listing query, pass query to SQL database:

				t3lib_div::loadTCA($this->table);
				$res = $this->getListResultSet($this->table, $WHERE, 'name');

	    		$this->piVars['sword'] = $backupSearchWord;

	    		// Put the whole list together:
	    		$fullTable='';	// Clear var;

	    		// Adds the search box:
	    		$fullTable.=$this->pi_list_searchBox();

	    		// Adds the result browser:
				$mArr['###pageBrowser###'] = $this->pi_list_browseresults(1,'',$this->conf['listView.']);
	    		// Adds the listsview
	    		$fullTable .= $this->pi_list_makelist($res);

				// Returns the content from the plugin.
	    		return $this->cObj->substituteMarkerArray($fullTable,$mArr);
  			}
		}
	}


	/**
	 *
	 * @param object $res	SQL-result object
	 * @return string HTML formatted text
	 */
	function makelist($res)	{
		$items=Array();
			// Make list table rows
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[]=$this->makeListItem();
		}

		$out = '<div'.$this->pi_classParam('listrow').'>
			'.implode(chr(10),$items).'
			</div>';
		return $out;
	}


	/**
	 * Displays single view of a record. It's possible to give a record,
	 * otherwise, the function gets the one in the piVars['showUid']
	 *
	 * @return	string				HTML-View of record
	 */
	function singleView()	{
		// get template File for single view
		$this->templateSingle = $this->cObj->fileResource($this->conf['templateDetail']);

		// This sets the title of the page for use in indexed search results:
		if ($this->internal['currentRow']['title'])	$GLOBALS['TSFE']->indexedDocTitle=$this->internal['currentRow']['title'];

		// very important! if not reset to zero sql query has bad wrong 'limits'
		$this->piVars['pointer'] = 0;
		// Make listing query, pass query to SQL database:
		$WHERE = 'AND uid = '.intval($this->piVars['showUid']);
		$res = $this->pi_exec_query($this->table,0,$WHERE);
		$this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		/**
		 * Changed by alessandro@4eyes.ch on 22.07.2010 to implement method getBoxedFieldContent instead of getFieldContent...
		 */
		$sub = array();
		$mArr = array();
		$this->addLanguageLabels($mArr);
		// get fields to display
		foreach($this->internal['currentRow'] as $k => $v){
			$sub['###'.$k.'Box###'] = $this->getBoxedFieldContent($k);
		}
		$mArr['###back###'] = '<a href="javascript:history.back()">'.$this->pi_getLL('pi_list_back_to_list').'</a>';
		return $this->cObj->substituteMarkerArrayCached($this->templateSingle,$mArr,$sub);
	}

	/**
	 * Returns boxed content (or nothing, if field is empty)
	 *
	 * @param	string		Fieldname
	 * @return	string		Content, ready for HTML output.
	 */
	function getBoxedFieldContent($fN){
		$tmpl = $this->cObj->getSubpart($this->templateSingle,'###'.$fN.'Box###');
		if (($tmpl != '') && ($this->internal['currentRow'][$fN]!='') && $this->checkDisplayField($fN)) {
			$mArr[$fN] = $this->getFieldContent($fN);
			$mArr[$fN.'Label'] = $this->pi_getLL($fN.'Label');
			if ($mArr[$fN.'Label'] == '') {
				$mArr[$fN.'Label'] = $this->pi_getLL('listFieldHeader_'.$fN);
			}
			if($mArr[$fN]!=''){
				return $this->cObj->substituteMarkerArray($tmpl,$mArr,'###|###');
			}else{
				return '';
			}
		} else {
			return '';
		}
	}

	/**
	 * Retrieves field content, processed, prepared for HTML output.
	 *
	 * @param	string		Fieldname
	 * @return	string		Content, ready for HTML output.
	 */
	function getFieldContent($key)	{
		$this->cols++;
		switch($key) {
				case 'internallink':
					$values .= $this->pi_linkToPage('mehr...',$this->internal['currentRow'][$key]);
				break;

				case 'start':
				case 'end':
					// Changed by alessandro@4eyes.ch on 22.07.2010 to avoid to display empty fields..
					if(intval($this->internal['currentRow'][$key]) != 0){
						$values .= strftime('%d. %b %Y',$this->internal['currentRow'][$key]);
					} else {
						return '';
					}
				break;

				case 'uid':
						$params[$this->prefixId.'[showUid]']= $this->internal['currentRow'][$key];
						$params['tx_'.$this->persExtKey.'_pi1[showUid]']=$_GET['tx_'.$this->persExtKey.'_pi1']['showUid'];
						$values .= $this->pi_linkTP(htmlentities($this->pi_getLL('pi_list_searchBox_more')),$params,1,$this->singleUid);
				break;

				case 'projecttitle':
					if($this->detailview){
						$values .= $this->internal['currentRow'][$key];
					} else {
						$params[$this->prefixId.'[showUid]']= $this->internal['currentRow']['uid'];
						$params['tx_'.$this->persExtKey.'_pi1[showUid]']=$_GET['tx_'.$this->persExtKey.'_pi1']['showUid'];
						$values .= $this->pi_linkTP(($this->internal['currentRow'][$key]),$params,1,$this->singleUid);
					}
				break;

				case 'projectmanagement':
				case 'personsinvolved':
					$personArray = array();
					if($this->internal['currentRow'][$key] != ''){
						$users = array();

						foreach(explode(",",$this->internal['currentRow'][$key]) as $userId){
							if($this->detailview){
								$user = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,'.$this->titleField.','.$this->firstNameField.','.$this->lastNameField.','.$this->titleAfterField,$this->personTable,'uid = ' . $userId . $this->cObj->enableFields($this->personTable));
							} else {
								$user = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,'.$this->firstNameField.','.$this->lastNameField,$this->personTable,'uid = '.$userId.$this->cObj->enableFields($this->personTable), '','');  // changed for bug(id=0001714)
							}

							if(!empty($user)){
								$users = array_merge($users, $user);
							}
						}


						$out = '';
						foreach($users as $entry => $userArray){
							foreach($userArray as $k => $user){
								if($k == 'uid'){
									$params['tx_'.$this->persExtKey.'_pi1[showUid]'] = $user;
								} else {
									$out .= $user.' ';
								}
							}
							$persLinkText = trim($out);
							unset($out);

							$persListId = $this->conf['persDB.']['personListUid'];

							if(intval($persListId)){
								array_push($personArray,$this->pi_linkTP($persLinkText,$params,1,$persListId));
							} else {
								array_push($personArray,$this->pi_linkTP($persLinkText,$params,1,$this->personSingleUid));
							}
						}
					}

					if (!$this->detailview) {
						if ($key == 'projectmanagement') {
							$tmp = t3lib_div::trimExplode("\n",$this->internal['currentRow']['externalprojectmanagement'],1);
						} else {
							$tmp = t3lib_div::trimExplode("\n",$this->internal['currentRow']['externalpersonsinvolved'],1);
						}

						foreach($tmp as $v) {
							array_push($personArray,$v);
						}
					}
					if(!empty($personArray)){
						if($this->detailview){
							$values .= implode('<br />',$personArray);
						} else {


							$values .= implode('; ',$personArray);
						}
					}else{
						$values .= '&nbsp;';
					}
				break;

				case 'link1':
						if($this->internal['currentRow'][$key] == ''){
							$values = '';
						} else {
							$values .= '<h2>URL</h2>'.
										'<p class="bodytext">'.
										$this->cObj->getTypoLink($this->pi_getLL('link1'),$this->internal['currentRow'][$key]).
										'</p>';
						}
				break;

				case 'finished':
						if($this->internal['currentRow'][$key] == 0){
							$values .= '';
						} else {
							$values .= 'x';
						}
				break;

				case 'externalpersonsinvolved':
						if($this->internal['currentRow'][$key] == ''){
							$values = '';
						} else {
							$values = nl2br($this->internal['currentRow'][$key]);
							if($this->internal['currentRow']['personsinvolved'] != ''){
								$values = '<br />'.trim($values);
							}
						}
				break;

				case 'externalprojectmanagement':
						if($this->internal['currentRow'][$key] == ''){
							$values = '';
						} else {
							$values = '<br />'.nl2br($this->internal['currentRow'][$key]);
						}
				break;

				case 'picture':
						if($this->internal['currentRow'][$key] == ''){
							$values = '';
						} else {
							$imgTSConfig = $this->conf['images.'];
							$imgTSConfig['file'] = 'uploads/tx_'.$this->extKey.'/'.$this->internal['currentRow']['picture'];
							$values = $this->cObj->IMAGE($imgTSConfig);
						}
				break;

				case 'description':
					$values = $this->pi_RTEcssText($this->internal['currentRow'][$key]);
				break;

				default:
					//$values .= $this->internal['currentRow'][$key];
					$values = parent::getFieldContent($key);
				break;
		}

		return $values;
	}

	/**
	 * Field header name, but wrapped in a link for sorting by column.
	 *
	 * @param	string		Fieldname
	 * @return	string		Content, ready for HTML output.
	 */
	function getFieldHeader_sortLink($fN)	{
		$params[$this->prefixId.'[sort]'] = $fN.':'.($this->internal['descFlag']?0:1);
		$params[$this->prefixId.'[showUid]'] = $this->internal['currentRow'][$key];
		$params['tx_'.$this->persExtKey.'_pi1[showUid]'] =$_GET['tx_'.$this->persExtKey.'_pi1']['showUid'];
		switch($fN){
			default:
				return $this->pi_linkTP($this->getFieldHeader($fN),$params,1);
		}
	}

	/**
	 * Returns a list row. Get data from $this->internal['currentRow'];
	 *
	 * @param integer $c	Row number
	 * @return string	html, one record as a row
	 */
	function pi_list_row($c) {
		$this->cols = 0;
		$values = '';
		foreach($this->fields as $v){
			$values .= $this->cObj->substituteMarker($this->cellT[$c%2],'###content###',$this->getFieldContent($v));
		}
		$this->colCount = $this->cols;
		return $this->cObj->substituteSubpart($this->rowT[$c%2],'###cell###',$values);
	}

	/**
	 * Generates list of projects
	 * @param object $res	SQL result object
	 * @return array
	 */
	function pi_list_makelistPersonel($res)	{
		// get all templates
		if ($this->manualFieldOrder_list == ''){
			$this->manualFieldOrder_list = $this->fields;
		}
		if ($this->listTP == '') {
			$this->listTP = $this->cObj->getSubpart($this->template,'###listPersonel###');
		}
		$this->rowSet = $this->cObj->getSubpart($this->listTP,'###rowSet###');
		$this->rowsT = $this->cObj->getSubpart($this->rowSet,'###rows###');
		$this->rowT[0] = $this->cObj->getSubpart($this->rowsT,'###row0###');
		$this->rowT[1] = $this->cObj->getSubpart($this->rowsT,'###row1###');
		$this->cellT[0] = $this->cObj->getSubpart($this->rowT[0],'###cell###');
		$this->cellT[1] = $this->cObj->getSubpart($this->rowT[1],'###cell###');

		// put the link-fields in appropriate array
		if (!is_array($this->conf['listView.']['detailLinkFields'])) {
			$this->conf['listView.']['detailLinkFields'] = t3lib_div::trimExplode(',',$this->conf['listView.']['detailLinkFields']);
		}

		// Make list table header:
		$tRows=array();
		$this->internal['currentRow']='';
		// get header and replace marker
		$out = $this->cObj->substituteSubpart($this->rowSet,'###headRow###',$this->pi_list_header());
			// Make list table rows
		$c=0;
		$rows = '';
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$rows .= $this->pi_list_row($c);
			$c++;
		}

		$rowsNout['rows'] = $rows;
		$rowsNout['out'] = $out;
		return $rowsNout;

	}


	function makePersonelList($pUid){
		$this->internal['maxPages'] = 999;
		$listCurrent = array();
		$list = array();

		// select current projects
		$WHERE = ' AND ( FIND_IN_SET('.$pUid.','.$this->table.'.projectmanagement) OR FIND_IN_SET('.$pUid.','.$this->table.'.personsinvolved))';

		$WHERE .= ' AND '.$this->table.'.finished = 0 ';
		$res = $this->pi_exec_query($this->table,0,$WHERE);

		$listCurrent = $this->pi_list_makelistPersonel($res);

		if($listCurrent['rows'] != ''){
			$list = $this->cObj->substituteSubpart($listCurrent['out'],'###rows###',$listCurrent['rows']);
			$markerArray['###span###'] = $this->colCount;
			$markerArray['###header###'] = $this->pi_getLL('title_current');

			$list = $this->cObj->substituteMarkerArray($list,$markerArray);
		} else {
			$list = '';
		}

		// select terminated projects
		$WHERE = ' AND ('.$pUid.' IN ('.$this->table.'.projectmanagement) OR FIND_IN_SET('.$pUid.','.$this->table.'.personsinvolved))';
		$WHERE .= ' AND '.$this->table.'.finished = 1 ';
		$res = $this->pi_exec_query($this->table,0,$WHERE);

		$listTerm = $this->pi_list_makelistPersonel($res);

		// get persons name
		$name = '';
		if (intval($pUid)>0) {
			$pRecord = tslib_pibase::pi_getRecord($this->personTable,intval($pUid));
			if($pRecord[$this->titleField] != ''){
				$name = $pRecord[$this->titleField].' ';
			}
			if($pRecord[$this->firstNameField] != ''){
				$name .=	$pRecord[$this->firstNameField].' ';
			}
			if($pRecord[$this->lastNameField] != ''){
				$name .=	$pRecord[$this->lastNameField];
			}
			if($pRecord[$this->titleAfterField] != ''){
				$name .=	', '.$pRecord[$this->titleAfterField];
			}
			$pageTitle = $this->pi_getLL('projects_of').$name;
			unset($pRecord);
		}
		if($listTerm['rows'] != ''){
			$list .= $this->cObj->substituteSubpart($listTerm['out'],'###rows###',$listTerm['rows']);
			// substitute header and other markers
			$markerArray['###span###'] = $this->colCount;
			$markerArray['###header###'] = $this->pi_getLL('title_terminated');
			$markerArray['###title###'] = $name;
			$list = $this->cObj->substituteMarkerArray($list,$markerArray);
		} else {
			$list .= '';
		}
		// insert rows
		$list = $this->cObj->substituteSubpart($this->listTP,'###rowSet###',$list);

		// add persons title to h1 and pagetitle
		$markerArray = array();
		$markerArray['###title###'] = $pageTitle;
		$list = $this->cObj->substituteMarkerArray($list,$markerArray);
		$GLOBALS['TSFE']->page['title'] = $pageTitle;

		return $list;
	}

	/**
	 * Checks which list view is supposed to show up and calls the appropriate
	 * function
	 *
	 * @return String
	 */
	function getCorrectListView() {

		switch($this->getTSFFvar('modeSelection')) {
			case 'category':
				return $this->listByCategory();
			break;
			case 'categoryMenu':
				return $this->getCategoryMenu();
			break;
			case 'listOfDetail':
				return $this->listOfDetailView();
			break;
			case 'alphabeticalList':
				return $this->listByAlphabet();
			break;
			case 'pm_running':
				$this->categoryField = "projectmanagement";
				//$this->categoryField = (FIND_IN_SET('.$w[uid].',projectmanagement) > 0)
				$this->categoryTable = $this->personTable;
				return $this->listByCategory();

			break;
			case 'pm_finished':
				$this->categoryField = "projectmanagement";
				$this->categoryTable = $this->personTable;
				return $this->listByCategory();

			break;
			default:
				return $this->listView();
			break;
		}
	}

	/**
	 * Renders a single cagegory
	 *
	 * @global array $TCA
	 * @param array $category Category recrod, by reference
	 * @return string
	 */
	function renderCategory(&$category) {
		global $TCA;

		if(isset($this->conf['catCol'])){
			$s['###list###'] = $this->listView(' AND (FIND_IN_SET('.intval($category['uid']).','.$this->conf['catCol'].') > 0)');
		} else {
			$s['###list###'] = $this->listView(' AND (FIND_IN_SET('.intval($category['uid']).',projectmanagement) > 0)');
		}

		if ($this->internal['res_count'] > 0){
			if($this->getTSFFvar('modeSelection') == "pm_running" || $this->getTSFFvar('modeSelection') == "pm_finished"){
				$m['###categoryLabel###'] = $this->getUserName($category);
			}else {
				$m['###categoryLabel###'] = $category[$TCA[$this->categoryTable]['ctrl']['label']];
			}

			$m['###categoryUid###'] = $category['uid'];

			return $this->cObj->substituteMarkerArray($s['###list###'],$m);
		} else {
			return '';
		}
	}

	/**
	 * Returns the name of a specific user with title
	 *
	 * @param 	array		user
	 * @return	string		users name with title
	 */
	function getUserName($user){
		if(!is_array($user)){
			$user = $this->pi_getRecord($this->tableNameUsers,intval($user));
		}
		// @todo: hardcoded table fields. Use defined variables instead
		return $user['title']." ".$user['firstname']." ".$user['lastname']." ".$user['title_after'];
	}

	/**
	 * Creates links which work like a "page selector", but using letters
	 *
	 * @todo: check if same page browser like x4epersdb => merge into x4pibase
	 *
	 * @param integer $step	Nummber of chars per step
	 * @return string HTML formatted page browser
	 */
	function alphabeticPageBrowser($step=4) {
		$t = $this->cObj->getSubpart($this->template,'###alphabeticPageBrowser###');
		$elT = $this->cObj->getSubpart($t,'###aPBElement###');
		$elActT = $this->cObj->getSubpart($t,'###aPBElementActive###');
			// remove active from main template
		$t = $this->cObj->substituteSubpart($t,'###aPBElementActive###','');

		$out = '';
		for ($i=65;$i<90;$i=$i+$step) {
			$upper = $i+$step-1;
			if ($upper > (90-1*$step)) {
				$upper = 90;
			}
			$m['###start###'] = chr($i);
			$m['###end###'] = chr($upper);
			$m['###linkStart###'] = str_replace('&nbsp;</a>','',$this->pi_linkTP_keepPIvars('&nbsp;',array('start'=>$i,'end'=>$upper,'sword'=>'')));
			$m['###linkEnd###'] = '</a>';
			if ($this->piVars['start'] == $i) {
				$out .= $this->cObj->substituteMarkerArray($elActT,$m);
			} else {
				$out .= $this->cObj->substituteMarkerArray($elT,$m);
			}
			if ($upper == 90) {
				$i = 90;
			}
		}
		return $this->cObj->substituteSubpart($t,'###aPBElement###',$out);
	}

	/**
	 * Functions lists all items, but ordered by category
	 *
	 * Requires member variable "categoryTable" to be set
	 *
	 * @param 	Integer		$step		Step to determin which result we want
	 * @return	String
	 */
	function listByCategory($step=-1) {
		global $TCA;

		if ($this->categoryTable == '') {
			return 'Category table not set';
		}

		$limit = '';

		if ($step>=0) {
			$limit = intval($step).',1';
		}

		$catsPID = $this->getTSFFvar('categoryPidList');
		if($catsPID == '') $catsPID = $this->getTSFFvar('pidList');

		$addWhere = '';
			// if category is selected, show only this category
		if (intval($this->piVars['category'])>0) {
			$addWhere .= ' AND '.$this->categoryTable.'.uid = '.intval($this->piVars['category']);
		}

		$start = intval($this->piVars['start']);
		$end = intval($this->piVars['end']);

		if ($start==0) {
			if($this->conf['browseFirstAlpha'] && $this->conf['browseLastAlpha']){
				$start = ord($this->conf['browseFirstAlpha']);
				$end = ord($this->conf['browseLastAlpha']);
				$this->piVars['start'] = $start;
				$this->piVars['end'] = $end;
			}else{
				$this->piVars['start'] = 65;
				$start=65;
				$end=68;
				$this->piVars['end'] = $end;
			}
		}


		if (t3lib_div::intInRange($start,64,86) && t3lib_div::intInRange($end,68,90)) {
			$addWhere .= ' AND SUBSTRING('.$this->conf['alphaSortField'].',1) >= "'.chr($start).'" AND SUBSTRING('.$this->conf['alphaSortField'].',1) <= "'.chr($end+1).'"';
		}

		if($this->getTSFFvar('modeSelection') == "pm_running" || $this->getTSFFvar('modeSelection') == "pm_finished"){
			$order = "name ASC";
		} else {
			$order = $TCA[$this->categoryTable]['ctrl']['sortby'];
		}

		$cats = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->categoryTable,$this->categoryTable.'.pid IN ('.$catsPID.')'.$this->cObj->enableFields($this->categoryTable).$addWhere,'',$order,$limit);

		if ($this->template == '') {
			$this->template = $this->cObj->fileResource($this->conf['listView.']['categoryViewTemplate']);
		}

		if ($this->template == '') {
			return 'listByCategory: no template found';
		}
		$sub['###pagebrowser###'] = $this->alphabeticPageBrowser(8);
		$sub['###alphabeticPageBrowser###'] = '';
		$sub['###header###'] = $this->conf['listByCategory.']['header'];
		$tmpl = $this->cObj->getSubpart($this->template,'###listView###');

		while($c = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($cats)) {
			if(intval($c['uid']) == intval($this->conf['projectCategory']) && intval($this->conf['hideProjectCategory']) == 1){
				$catRender = false;
			}

			if(!isset($catRender)){
				$this->currentCategory = $c;
				$sub['###listView###'] .= $this->renderCategory($c);
			}

		}

		unset($m,$s,$tmpl,$c);
		$GLOBALS['TYPO3_DB']->sql_free_result($cats);

		return $this->cObj->substituteMarkerArrayCached($this->template,array(),$sub);
	}

	/**
	 * Creates an instance of the pi3 plugin to render the projects contacts
	 *
	 * @return string
	 */
	function getContactInfo() {
		$persPi3 = t3lib_div::makeInstance('tx_'.$this->persExtKey.'_pi3');
		$project = $this->pi_getRecord($this->table,intval($this->piVars['showUid']));
		$persPi3->cObj = $this->cObj;
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_'.$this->persExtKey.'_pi3.'];
		$conf['templateFile'] = $this->conf['contact.']['templateFile'];
		$conf['templateSelection'] = 1;
		$conf['feUserUids'] = $project['contact'];
		return $persPi3->main('',$conf);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4euniprojectsgeneral/pi1/class.tx_x4euniprojectsgeneral_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4euniprojectsgeneral/pi1/class.tx_x4euniprojectsgeneral_pi1.php']);
}

?>