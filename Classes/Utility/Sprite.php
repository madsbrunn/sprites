<?php

class Tx_Sprites_Utility_Sprite extends t3lib_stdGraphic{
	
	var $id;
	var $im = '';
	var $w = 0;
	var $h = 0;
	var $images = Array();
	var $workArea = Array();
	
	function init($id,$conf){
		parent::init();
		$this->id = $id;
		$this->conf = $conf;
	}
	
	function make(){
		
		$this->setDimensions();
		$this->setWorkArea('');		
		$this->sortImages();
		
		$this->im = imagecreatetruecolor($this->w,$this->h);
		
		foreach($this->images as $key => $image){
			$this->copyImageOntoImage($this->im,$image,$this->workArea);
			
			list($x,$y,$w,$h) = $this->workArea;
			
			$this->images[$key]['cssrules'] = "background-image:url('".$this->conf['file']."');\n";
			$this->images[$key]['cssrules'] .= "background-position: left -".$y."px";
			
			if(strtolower($this->conf['layout']) == 'horizontal'){
				$x += $image['width'];				
			} else {
				$y += $image['height'];	
			}
			$this->workArea = array($x,$y,$w,$h);
			
		}
		
		$this->output(PATH_site . $this->conf['file']);
	}
	


	function build(){
		
		//$this->setWorkArea('0,0');
		//$this->im = imagecreatetruecolor($this->w,$this->h);
		//list($red,$green,$blue) = $this->convertColor('#eeeeee');
		//$bgcolor = ImageColorAllocate($this->im, $red,$green,$blue);
		//ImageFilledRectangle($this->im, 0, 0, $this->w, $this->h, $bgcolor);
		
		//$img2 = $this->imageCreateFromFile(PATH_site . 'fileadmin/templates/main/images/logo.png');
		//$this->copyGifOntoGif($this->im, $img2, array('tile'=>'1,1'), array(20,20));
		//imagedestroy($img2);
		
		$this->output(PATH_site . $this->conf['file']);
	}
	
	
	/**
	 * Adds an image to the sprite
	 */
	function addImage($file,$directives){
		
		if(!is_file($file)){
			t3lib_div::devLog('File "'.$file.'" did not exist','sprites',t3lib_div::SYSLOG_SEVERITY_WARNING);
			return;
		}
		
		$fileinfo = $this->getImageDimensions($file);
		$image = array(
			'width' => $fileinfo[0],
			'height' => $fileinfo[1],
			'ext' => $fileinfo[2],
			'file' => $fileinfo[3],
			'directives' => $directives
		);
		
		$this->images[] = $image;
		
		if(TX_SPRITES_DEBUG){
			t3lib_div::devLog('Added image to sprite "'.$this->id.'"','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$image);
		}		
		
	}
	
	
	function registerImage($file,$directives,$match){

		if(!is_file($file)){
			t3lib_div::devLog('File "'.$file.'" did not exist','sprites',t3lib_div::SYSLOG_SEVERITY_WARNING);
			return;
		}
		
		$key = md5($match);
		
		if(!isset($this->images[$key])){
			$fileinfo = $this->getImageDimensions($file);
			$image = array(
				'width' => $fileinfo[0],
				'height' => $fileinfo[1],
				'ext' => $fileinfo[2],
				'file' => $fileinfo[3],
				'directives' => $directives
			);
			
			$this->images[$key] = $image;
			
			if(TX_SPRITES_DEBUG){
				t3lib_div::devLog('Added image to sprite "'.$this->id.'"','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$image);
			}
			
			
		} else {
			
			if(TX_SPRITES_DEBUG){
				t3lib_div::devLog('Image "'.$file.'" has already been registered','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$image);
			}	
			
		}
		
		return $key;
	}
	
	
	
	function setDimensions(){
		foreach($this->images as $image){
			if(strtolower($this->conf['layout']) == 'horizontal'){
				$this->w += $image['width'];
				if($image['height'] > $this->h){
					$this->h = $image['height'];	
				}
			} else {
				$this->h += $image['height'];
				if($image['width'] > $this->w){
					$this->w = $image['width'];	
				}
			}
		}
	}
	
	function sortImages(){
		
	}
	
	function compareImageSize(){
		
	}
}



?>
