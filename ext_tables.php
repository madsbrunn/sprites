<?php
if (!defined('TYPO3_MODE')) {die ('Access denied.');}



if (TYPO3_MODE == 'BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		'name' => 'tx_sprites_cm1',
		'path' => PATH_txsprites.'class.tx_sprites_cm1.php'
	);
}



if (TYPO3_MODE == 'BE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) { 
	
	// add our own module dispatcher class name
	// this is nescessary in order to prevent the module from showing up in the module menu
	array_unshift($GLOBALS['TBE_MODULES']['_dispatcher'],'Tx_Sprites_Core_Bootstrap');

	// we are building the module configuration on our own since Tx_Extbase_Utility_Extension::registerModule
	// only knows how to handle modules which are supposed to show up in the module menu 	
	
	$configuration = array(
		'access' => 'user,group',
		'configureModuleFunction' => array('Tx_Extbase_Utility_Extension','configureModule'),
		'extensionName' => 'Sprites',
		'extRelPath' => PATH_txsprites_rel.'Classes/',
		'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Images/moduleicon.gif',
		'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xml',
		'name' => 'txspritesM1',
		'script' => 'mod.php?M=txspritesM1'
	);
	$GLOBALS['TBE_MODULES']['_configuration']['txspritesM1'] = $configuration;
	
	
	// registering controllers and actions
	$controllers = array(
		'Sprites' => array(
			'actions' => array('index','generate','save','edit')
		)
	);
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Sprites']['modules']['txspritesM1']['controllers'] = $controllers;
    
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_txspritesM1','EXT:sprites/Resources/Private/Language/locallang_csh.xml');
}





?>