<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_x4euniprojectsgeneral_list=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_x4euniprojectsgeneral_pi1 = < plugin.tx_x4euniprojectsgeneral_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_x4euniprojectsgeneral_pi1.php','_pi1','list_type',1);


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_x4euniprojectsgeneral_list = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_x4euniprojectsgeneral_list.CMD = singleView
',43);


// scheduler task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_x4euniprojectsgeneral_import'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Project OAI import',
    'description'      => 'Import from OAI API',
    'additionalFields' => 'tx_x4euniprojectsgeneral_import_additionalfieldprovider'
);


?>