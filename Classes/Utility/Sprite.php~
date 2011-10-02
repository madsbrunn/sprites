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
		
		$this->conf['layout'] = isset($this->conf['layout']) ? strtolower($this->conf['layout']) : 'vertical';
		
		if(!in_array($this->conf['layout'],array('vertical','horizontal'))) {
			$this->conf['layout'] = 'vertical';
			t3lib_div::devLog('Invalid sprite layout \''.$this->conf['layout'].'\'. Falling back to \'vertical\' layout','sprites',t3lib_div::SYSLOG_SEVERITY_WARNING);
		}
	}
	
	function make(){
		
		$this->setDimensions();
		$this->setWorkArea('');
		$this->sortImages();
		
		$this->im = imagecreatetruecolor($this->w,$this->h);
		imagealphablending( $this->im, false );
		imagesavealpha( $this->im, true );	
		
		imagefill($this->im, 0, 0, imagecolorallocatealpha($this->im, 0, 255, 255, 127));
		
		foreach($this->images as $key => $image){

			list($x,$y,$w,$h) = $this->workArea;
			
			$directives = $image['directives'];

			$h_pos = '0';
			$v_pos = '0';
			
			if($this->conf['layout'] == 'vertical'){
				
				$h_pos = 'left';
				$v_pos = '-'.$y.'px';
				
				
				//calculating  y - coordinate
				$y_pos = $y+$directives['sprite-margin-top'];

				if($directives['sprite-alignment'] == 'right'){				//right
					
					$h_pos = 'right';
					
					//x - coordinate if the image is aligned to the right edge of the sprite
					$x_pos = $this->w - $image['width'] - $directives['sprite-margin-right']; 
					
				} elseif($directives['sprite-alignment'] == 'left'){ 	//left
					
					//x - coordinate if aligned to the left edge of the sprite
					$x_pos = $directives['sprite-margin-left'];
					
				} else { 																							//repeat
					
					//calculating  x-coordinate
					$x_pos = $directives['sprite-margin-left'];
					
					//calculating number of times to repeat the image
					$tile = array(1,1);
					$_w = $this->w - $directives['sprite-margin-left'];
					$tile[0] = ceil($_w / $image['width']);
					$image['tile'] = implode(',',$tile);
					
				}
				
				
			} else {
				
				$h_pos = '-'.$x.'px';
				$v_pos = 'top';
				
				$x_pos = $x + $directives['sprite-margin-left'];
				if($directives['sprite-alignment'] == 'bottom'){ 			//bottom
					
					$v_pos = 'bottom';
					$y_pos = $this->h - $image['height'] - $directives['sprite-margin-bottom'];		
					
				} elseif($directives['sprite-alignment'] == 'top'){ 	//top
					
					$y_pos = $directives['sprite-margin-top'];		
					
				} else { 																							//repeat
					
					$y_pos = $directives['sprite-margin-top'];
					
					//number of times to repeat the image
					$tile = array(1,1);	
					$_h = $this->h - $directives['sprite-margin-top'];
					$tile[1] = ceil($_h / $image['height']);
					$image['tile'] = implode(',',$tile);
					
				}
			}
			
			
			
			//write new rule
			$match = stripslashes($this->images[$key]['match']);			
			
			if($image['type'] == 'background-image'){
				
				$pattern = '/([ \t]*)background-image\s*:.*url\(\s*(?:\'|")?(?:[\.\-\_\/a-zA-Z0-9]*)(?:\'|")?\s*\)(.*;?)\/\*\*\s*sprite-ref:\s*(?:[a-z0-9]+);?(?:.*)\*\//i';
				$replace =  "$1background-image: url(/".$this->conf['file'].")$2\n$1background-position: ".$h_pos." ".$v_pos.";";
				
				$this->images[$key]['cssrules'] = preg_replace($pattern,$replace,$match);
			
			} else {
				
				$pattern = '/';
				$pattern .= '([ \t]*)'; // matches white space at the beginning of the line
				$pattern .= 'background\s*:'; // matches the 'background' keyword + any white space + the colon
				$pattern .= '(.*)'; //matches anything between the colon and the 'url(...' part. Examples: 'transparent ', '#ffffff' (may be empty)
				$pattern .= 'url\(\s*(?:\'|")?(?:[\.\-\_\/a-zA-Z0-9]*)(?:\'|")?\s*\)'; //matches the background image. Examples: 'url(../hest/hest.gif)',  url("foobar.jpg") 
				$pattern .= '(\s*(?:no-repeat|repeat-x|repeat-y))?'; // matches any background-repeat rule. Either 'no-repeat', 'repeat-x', 'repeat-y' or nothing
				$pattern .= '(\s*(?:scroll|fixed|inherit))?';	//matches any background-attachment rule. Either 'scroll', 'fixed', 'inherit' or nothing
				$pattern .= '(?:\s*(?:(?:[0-9]*\s*(?:%|in|cm|mm|em|ex|pt|pc|px))|left|center|right|top|bottom)){0,2}'; //matches any background position rule. E.g. 'top left'
				$pattern .= '(.*;?)'; //matches anything between the above and a possible semi-kolon
				$pattern .= '\/\*\*\s*sprite-ref:\s*(?:[a-z0-9]+);?(?:.*)\*\//i';
				
				$replace = "$1background:$2url(/".$this->conf['file'].")$3$4 ".$h_pos." ".$v_pos."$5";

				$this->images[$key]['cssrules'] = preg_replace($pattern,$replace,$match);
			}
			
			
			$this->copyImageOntoImage($this->im,$image,array($x_pos,$y_pos,$w,$h));
			
			//calculate next offset
			if($this->conf['layout'] == 'horizontal'){
				$x += ($image['width'] + $directives['sprite-margin-left'] + $directives['sprite-margin-right']);
			} else { //vertical
				$y += ($image['height'] + $directives['sprite-margin-top'] + $directives['sprite-margin-bottom']);
			}
			$this->workArea = array($x,$y,$w,$h);
		}
		$this->output(PATH_site . $this->conf['file']);
	}


	function build(){
		$this->output(PATH_site . $this->conf['file']);
	}
	
	
	function addImage($file,$directives,$match,$type){

		if(!is_file($file)){
			t3lib_div::devLog('File "'.$file.'" did not exist','sprites',t3lib_div::SYSLOG_SEVERITY_WARNING);
			return;
		}
		
		$key = md5($match);
		
		//validate margins
		$directives['sprite-margin-left'] = intval($directives['sprite-margin-left']);
		$directives['sprite-margin-right'] = intval($directives['sprite-margin-right']);
		$directives['sprite-margin-top'] = intval($directives['sprite-margin-top']);
		$directives['sprite-margin-bottom'] = intval($directives['sprite-margin-bottom']);
		
		//validate sprite alignment
		$directives['sprite-alignment'] = isset($directives['sprite-alignment']) ? strtolower($directives['sprite-alignment']) : 'left';
		if($this->conf['layout'] == 'vertical'){ //allowed alignments for vertical layout are left,right and repeat
			
			if($directives['sprite-alignment'] && !in_array($directives['sprite-alignment'],array('left','right','repeat'))){
				t3lib_div::devLog('Invalid or missing sprite alignment \''.$directives['sprite-alignment'].'\' for sprite with vertical layout','sprites',t3lib_div::SYSLOG_SEVERITY_WARNING);				
			}
			$directives['sprite-alignment'] = in_array($directives['sprite-alignment'],array('left','right','repeat')) ? $directives['sprite-alignment'] :	'left';
			
		
		} else { //allowed alignments for horizontal layout
			$directives['sprite-alignment'] = in_array($directives['sprite-alignment'],array('top','bottom','repeat')) ?	$directives['sprite-alignment'] :	'top';
		}
		
		//@TODO implement centered sprite alignment
		
		if(!isset($this->images[$key])){
			
			$fileinfo = $this->getImageDimensions($file);
			$image = array(
				'width' => $fileinfo[0],
				'height' => $fileinfo[1],
				'ext' => $fileinfo[2],
				'file' => $fileinfo[3],
				'directives' => $directives,
				'match' => $match,
				'type' => $type
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
			$directives = $image['directives'];
			if($this->conf['layout'] == 'horizontal'){
				$this->w += $image['width'] + $directives['sprite-margin-left'] + $directives['sprite-margin-right'];
				if($image['height'] > $this->h) $this->h = $image['height'];
			} else {
				$this->h += $image['height'] + $directives['sprite-margin-top'] + $directives['sprite-margin-bottom'];
				if($image['width'] > $this->w)	$this->w = $image['width'];	
			}
		}
	}
	
	
	function sortImages(){
		
	}
	
	function compareImageSize(){
		
	}
	
	
	/**
	 * Copies two GDlib image pointers onto each other, using TypoScript configuration from $conf and the input $workArea definition.
	 *
	 * @param	pointer		GDlib image pointer, destination (bottom image)
	 * @param	pointer		GDlib image pointer, source (top image)
	 * @param	array		TypoScript array with the properties for the IMAGE GIFBUILDER object. Only used for the "tile" property value.
	 * @param	array		Work area
	 * @return	void		Works on the $im image pointer
	 * @access private
	 */
	function copyGifOntoGif(&$im, $cpImg, $conf, $workArea) {
		$cpW = imagesx($cpImg);
		$cpH = imagesy($cpImg);
		$tile = t3lib_div::intExplode(',', $conf['tile']);
		$tile[0] = t3lib_div::intInRange($tile[0], 1, 1000);
		$tile[1] = t3lib_div::intInRange($tile[1], 1, 1000);
		$cpOff = $this->objPosition($conf, $workArea, array($cpW * $tile[0], $cpH * $tile[1]));

		for ($xt = 0; $xt < $tile[0]; $xt++) {
			$Xstart = $cpOff[0] + $cpW * $xt;
			if ($Xstart + $cpW > $workArea[0]) { // if this image is inside of the workArea, then go on
					// X:
				if ($Xstart < $workArea[0]) {
					$cpImgCutX = $workArea[0] - $Xstart;
					$Xstart = $workArea[0];
				} else {
					$cpImgCutX = 0;
				}
				$w = $cpW - $cpImgCutX;
				if ($Xstart > $workArea[0] + $workArea[2] - $w) {
					$w = $workArea[0] + $workArea[2] - $Xstart;
				}
				if ($Xstart < $workArea[0] + $workArea[2]) { // if this image is inside of the workArea, then go on
						// Y:
					for ($yt = 0; $yt < $tile[1]; $yt++) {
						$Ystart = $cpOff[1] + $cpH * $yt;
						if ($Ystart + $cpH > $workArea[1]) { // if this image is inside of the workArea, then go on
							if ($Ystart < $workArea[1]) {
								$cpImgCutY = $workArea[1] - $Ystart;
								$Ystart = $workArea[1];
							} else {
								$cpImgCutY = 0;
							}
							$h = $cpH - $cpImgCutY;
							if ($Ystart > $workArea[1] + $workArea[3] - $h) {
								$h = $workArea[1] + $workArea[3] - $Ystart;
							}
							if ($Ystart < $workArea[1] + $workArea[3]) { // if this image is inside of the workArea, then go on
								// override - we are using imagecopy instead of imagecopyresized in order to preserve alpha transparency
								imagecopy($im,$cpImg,$Xstart,$Ystart,$cpImgCutX,$cpImgCutY,$w,$h);
							}
						}
					} // Y:
				}
			}
		}
	}
	
}



?>
