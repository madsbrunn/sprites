<?php

class Tx_Sprites_Utility_Optimizer{

	
	/**
	 * @param string $file
	 */
	public function optimize($file){
		if(!is_file($file)) return;
		$pathinfo = pathinfo($file);
		if($pathinfo['extension'] == 'png'){
			self::optimizePng($file);			
		}
	}
	
	public function optimizePng($file){
		t3lib_exec::addPaths('TX_SPRITES_PNGCRUSH_PATH');
		if($cmd = t3lib_exec::getCommand('pngcrush')){
			$tmpname = t3lib_div::tempnam('tx_sprites');
			@t3lib_utility_Command::exec($cmd . ' '.$file.' '.$tmpname);
			@rename($tmpname,$file);
			t3lib_div::unlink_tempfile($tmpname);
		}
	}
}


?>
