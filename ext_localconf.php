<?php
if (!defined('TYPO3_MODE')) {die ('Access denied.');}

if (!defined ('PATH_txsprites')) {
    define('PATH_txsprites', t3lib_extMgm::extPath($_EXTKEY));
}

if (!defined ('PATH_txsprites_rel')) {
    define('PATH_txsprites_rel', t3lib_extMgm::extRelPath($_EXTKEY));
}

if (!defined ('PATH_txsprites_siteRel')) {
    define('PATH_txsprites_siteRel', t3lib_extMgm::siteRelPath($_EXTKEY));
}

if(TYPO3_MODE == 'BE'){
	$tmp_extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['sprites']);
	
	if(!defined('TX_SPRITES_DEBUG')){
		define('TX_SPRITES_DEBUG',$tmp_extConf['debugMode']);
	}
	
	if(!defined('TX_SPRITES_PNGCRUSH_PATH')){
		define('TX_SPRITES_PNGCRUSH_PATH',$tmp_extConf['pngcrush_path']);
	}
}


?>
