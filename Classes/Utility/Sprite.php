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
		
		$reg = array();
		preg_match('/([^\.]*)$/', $this->conf['file'], $reg);
		$ext = strtolower($reg[0]);

		if ($ext !='jpg') {
			
			imagealphablending($this->im,false);
			imagesavealpha($this->im,true);
			$transparent = imagecolorallocatealpha($this->im,255, 255, 255, 127);
			imagecolortransparent($this->im,$transparent);
			imagefilledrectangle($this->im, 0, 0, $this->w, $this->h, $transparent);
			
		} else {
			
			$transparent = imagecolorallocate($this->im,255, 255, 255);	// white
			imagefilledrectangle($this->im, 0, 0, $this->w, $this->h, $transparent);
			
		}		
		
		
		//list($red,$green,$blue) = $this->convertColor('#ffffff');
		//$bgcolor = imagecolorallocate($this->im, $red, $green, $blue);
		//imagefilledrectangle($this->im, 0, 0, $this->w, $this->h, $bgcolor);		
		
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
		$this->output(PATH_site . $this->conf['file']);
	}
	
	
	function addImage($file,$directives,$match){

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
