<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=="BE"){
	include_once(t3lib_extMgm::extPath("x4euniprojectsgeneral")."class.tx_x4euniprojectsgeneral_tx_x4euniprojectsgeneral_tca_proc.php");
}

$TCA["tx_x4euniprojectsgeneral_list"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list",		
		"label" => "projecttitle",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_x4euniprojectsgeneral_list.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, projecttitle, projectmanagement, externalprojectmanagement, personsinvolved, externalpersonsinvolved, start, end, financing, volume, link1, link2, link3, description, picture, finished",
	)
);

$TCA["tx_x4euniprojectsgeneral_category"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_category",		
		"label" => "cattitle",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_x4euniprojectsgeneral_list.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, cattitle",
	)
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';


t3lib_extMgm::addPlugin(Array('LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','4eyes - Project database');


// flexform stuff
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:x4euniprojectsgeneral/pi1/flexform_ds_pi1.xml');
?>