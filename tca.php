<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_x4euniprojectsgeneral_list"] = Array (
	"ctrl" => $TCA["tx_x4euniprojectsgeneral_list"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,projecttitle,projectmanagement,externalprojectmanagement,personsinvolved,externalpersonsinvolved,start,end,financing,volume,link1,link2,link3,description,picture,finished"
	),
	"feInterface" => $TCA["tx_x4euniprojectsgeneral_list"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"projecttitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.projecttitle",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"contact" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.contact",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_x4epersdb_person",	
				"foreign_table_where" => "AND tx_x4epersdb_person.pid=###PAGE_TSCONFIG_ID### ORDER BY tx_x4epersdb_person.lastname",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
				//"itemsProcFunc" => "tx_x4euniprojectsgeneral_tx_x4euniprojectsgeneral_tca_proc->main",
			)
		),
		"projectmanagement" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.projectmanagement",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_x4epersdb_person",	
				"foreign_table_where" => "AND tx_x4epersdb_person.pid=###PAGE_TSCONFIG_ID### ORDER BY tx_x4epersdb_person.lastname",
				"size" => 8,	
				"minitems" => 0,
				"maxitems" => 100,
				//"itemsProcFunc" => "tx_x4euniprojectsgeneral_tx_x4euniprojectsgeneral_tca_proc->main",
			)
		),
		"externalprojectmanagement" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.externalprojectmanagement",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"personsinvolved" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.personsinvolved",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_x4epersdb_person",	
				"foreign_table_where" => "AND tx_x4epersdb_person.pid=###PAGE_TSCONFIG_ID### ORDER BY tx_x4epersdb_person.lastname",
				"size" => 8,	
				"minitems" => 0,
				"maxitems" => 100,
				//"itemsProcFunc" => "tx_x4euniprojectsgeneral_tx_x4euniprojectsgeneral_tca_proc->main",
			)
		),
		"externalpersonsinvolved" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.externalpersonsinvolved",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"start" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.start",		
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"end" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.end",		
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"financing" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.financing",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"collaboration" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.collaboration",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"volume" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.volume",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"link1" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.link1",		
			"config" => Array (
				"type" => "input",
				"size" => "15",
				"max" => "255",
				"checkbox" => "",
				"eval" => "trim",
				"wizards" => Array(
					"_PADDING" => 2,
					"link" => Array(
						"type" => "popup",
						"title" => "Link",
						"icon" => "link_popup.gif",
						"script" => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					)
				)
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"comment" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.comment",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"methodology" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.methodology",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"picture" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.picture",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_x4euniprojectsgeneral",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"finished" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.finished",		
			"config" => Array (
				"type" => "check",
			)
		),
		"category" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_list.category",		
			"config" => Array (
				"type" => "select",	
				"size" => "8",
				'foreign_table' => 'tx_x4euniprojectsgeneral_category',
				'foreign_table_where' => 'AND tx_x4euniprojectsgeneral_category.pid=###CURRENT_PID###',
				'minitems' => 0,
				'maxitems' => 999,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, projecttitle, category, contact, finished, projectmanagement, externalprojectmanagement, personsinvolved, externalpersonsinvolved, start, end, collaboration, financing, volume, link1, link2, link3, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[flag=rte_enabled|mode=ts];3-3-3, methodology;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[flag=rte_enabled|mode=ts];3-3-3,comment;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[flag=rte_enabled|mode=ts];3-3-3,picture")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime")
	)
);

$TCA["tx_x4euniprojectsgeneral_category"] = Array (
	"ctrl" => $TCA["tx_x4euniprojectsgeneral_category"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,cattitle"
	),
	"feInterface" => $TCA["tx_x4euniprojectsgeneral_category"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"cattitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4euniprojectsgeneral/locallang_db.php:tx_x4euniprojectsgeneral_category.cattitle",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, cattitle")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime")
	)
);

?>