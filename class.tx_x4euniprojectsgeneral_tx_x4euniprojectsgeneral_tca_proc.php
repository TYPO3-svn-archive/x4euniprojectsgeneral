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
 *
 * @author	Andi Keller <andi-at-4eyes.ch>
 */


class tx_x4euniprojectsgeneral_tx_x4euniprojectsgeneral_tca_proc {
	function main(&$params,&$pObj=0){
		switch($pid) {
                        // @todo: hardcoded uid!
			case '8079':
				$this->getPersons($params);
			break;
			default:
				foreach ($params['items'] as $k => $value){
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('firstname,lsatname','tx_x4epersdb_person','1 and uid='.$value[1]);
					$params['items'][$k][0] = $result[0]['lastname'].' '.$result[0]['firstname'];
				}
			 break;
		}
	}
	
	function getPersons(&$params) {
		$params['items'] = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, lastname, firstname','tx_x4epersdb_person','hidden=0 AND deleted = 0 AND pid = 7998','','lastname');
		while($p = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$person[0] = $p['lastname'].' '.$p['firstname'];
			$person[1] = $p['uid'];
			$params['items'][] = $person;
		}
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4euniprojectsgeneral/class.tx_x4euniprojectsgeneral_tx_x4euniprojectsgeneral_tca_proc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4euniprojectsgeneral/class.tx_x4euniprojectsgeneral_tx_x4euniprojectsgeneral_tca_proc.php']);
}

?>