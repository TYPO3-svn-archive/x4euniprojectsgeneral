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

require_once('typo3conf/ext/x4euniprojects/pi1/class.tx_x4euniprojects_pi1.php');

class tx_x4euniprojectsgeneral_pi1 extends tx_x4euniprojects_pi1 {
	var $prefixId = 'tx_x4euniprojectsgeneral_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.x4euniprojectsgeneral_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'x4euniprojectsgeneral';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $persExtKey = 'x4epersdb';
	var $personTable = 'tx_x4epersdb_person';
	var $firstNameField = 'firstname';
	var $lastNameField = 'lastname';
	var $titleField = 'title';
	var $titleAfterField = 'title_after';
	var $table = 'tx_x4euniprojectsgeneral_list';
	var $tableName = 'tx_x4euniprojectsgeneral_list';
	var $categoryTable = 'tx_x4euniprojectsgeneral_category';

	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4euniprojectsgeneral/pi1/class.tx_x4euniprojectsgeneral_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4euniprojectsgeneral/pi1/class.tx_x4euniprojectsgeneral_pi1.php']);
}

?>