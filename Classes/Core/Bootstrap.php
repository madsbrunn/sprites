<?php


class Tx_Sprites_Core_Bootstrap extends Tx_Extbase_Core_Bootstrap{
	
	 /**
	  * This method forwards the call to run(). This method is invoked by the mod.php
	  * function of TYPO3.
	  *
	  * @param string $moduleSignature
	  * @return boolean TRUE, if the request request could be dispatched
	  * @see run()
	  **/
	public function callModule($moduleSignature) {
		if($moduleSignature !== 'txspritesM1') return FALSE;
		
		if (!isset($GLOBALS['TBE_MODULES']['_configuration'][$moduleSignature])) {
			return FALSE;
		}
		$moduleConfiguration = $GLOBALS['TBE_MODULES']['_configuration'][$moduleSignature];

		//@TODO: check for access to the folder

		// BACK_PATH is the path from the typo3/ directory from within the
		// directory containing the controller file. We are using mod.php dispatcher
		// and thus we are already within typo3/ because we call typo3/mod.php
		$GLOBALS['BACK_PATH'] = '';

		$configuration = array(
			'extensionName' => $moduleConfiguration['extensionName'],
			'pluginName' => $moduleSignature
		);
		$content = $this->run('', $configuration);

		print $content;
		return TRUE;
	}
}


?>
