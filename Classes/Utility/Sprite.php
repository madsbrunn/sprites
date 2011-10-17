<?php

//@TODO: improve caching of sprite image by using md5 or date in image name


class Tx_Sprites_Utility_Sprite extends t3lib_stdGraphic{
	
	protected $id;	//the id of the sprite
	protected $im = ''; //internal pointer to the image resource
	protected $w = 0;	//the width of the sprite image
	protected $h = 0;	//the height of the sprite image
	public $images = Array();	//images added to the sprite
	public $workArea = Array(); 
	protected $extension = '';	//extension of the sprite image (gif,jpg,png)
	protected $colors = 0;	//maximum number of colors in image
	protected $isTrueColor = FALSE;	//is this a truecolor
	protected $imageTypes = array();	//all registered image types
	protected $directiveAliases = array(
		'sprite-margin-left' => array('margin-left'),
		'sprite-margin-right' => array('margin-right'),
		'sprite-margin-bottom' => array('margin-bottom'),
		'sprite-margin-top' => array('margin-top'),
		'sprite-alignment' => array('alignment','align')
	);
	
	/**
	 * @param  string  $id: the sprite id
	 * @param  array  $conf: configuration for the sprite
	 * @return  void
	 * @access  public
	 */
	public function init($id,$conf){
		parent::init();
		$this->id = $id;
		$this->conf = $conf;
		
		$this->conf['layout'] = isset($this->conf['layout']) ? strtolower($this->conf['layout']) : 'vertical';
		
		$this->conf['matte-color'] = $this->conf['matte-color'] ? $this->conf['matte-color'] : '#ffffff';
		
		if(!in_array($this->conf['layout'],array('vertical','horizontal'))) {
			$this->conf['layout'] = 'vertical';
			t3lib_div::devLog('Invalid sprite layout \''.$this->conf['layout'].'\'. Falling back to \'vertical\' layout','sprites',t3lib_div::SYSLOG_SEVERITY_WARNING);
		}
		$pathparts = pathinfo($this->conf['file']);
		$this->extension = $pathparts['extension'];
		
	}
	
	
	public function preMakeSprite(){
		$this->setDimensions();
		$this->setWorkArea('');
		$this->sortImages();
		
		$this->im = imagecreatetruecolor($this->w,$this->h);
		imagealphablending( $this->im, false );
		imagesavealpha( $this->im, true );	
		
		imagefill($this->im, 0, 0, imagecolorallocatealpha($this->im, 255, 255, 255, 127));					
		
	}
	
	public function postMakeSprite(){}
	
	
	/**
	 * Compiles the actual sprite image
	 *
	 * @return  void
	 * @access  public
	 */
	public function make(){
		
		$this->setDimensions();
		$this->setWorkArea('');
		$this->sortImages();
		
		$this->im = imagecreatetruecolor($this->w,$this->h);
		imagealphablending( $this->im, false );
		imagesavealpha( $this->im, true );

	  //imagefill($this->im, 0, 0, imagecolorallocatealpha($this->im, 255, 255, 255, 127));
		
		//if($this->extension == 'png'){
		//imagefill($this->im, 0, 0, imagecolorallocatealpha($this->im, 0, 255, 255, 127));		
		imagefill($this->im, 0, 0, imagecolorallocatealpha($this->im, 255, 255, 255, 127));
		//} elseif($this->extension == 'gif'){
		//  imagefill($this->im, 0, 0, imagecolorallocatealpha($this->im, 0, 0, 0,127));
		//}

		//if($
		//if($this->extension == 'png'){
		//} else {
		//	imagefill($this->im, 0, 0, imagecolorallocate($this->im, 127, 127, 127));
		//}
		
		
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
				
				$pattern = '/([ \t]*)background\s*:(.*)url\(\s*(?:\'|")?(?:[\.\-\_\/a-zA-Z0-9]*)(?:\'|")?\s*\)(\s*(?:no-repeat|repeat-x|repeat-y)\s*)?(\s*(?:scroll|fixed|inherit)\s*)?(?:\s*(?:left|center|right|top|bottom|(?:-?[0-9]*(?:\.[0-9]*)?\s*(?:%|in|cm|mm|em|ex|pt|pc|px)?))\s*){0,2}(.*;?)\/\*\*\s*sprite-ref:\s*(?:[a-z0-9]+);?(?:.*)\*\//i';
				$replace = "$1background: $2 url(/".$this->conf['file'].")$3$4".$h_pos." ".$v_pos."$5";
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
		
		//if((!$this->truecolor) && $this->colors){
		//  $this->makeEffect($this->im, array('value' => 'colors=' . $this->colors));
		//}
		//ob_end_flush();
		//echo imagecolorstotal($this->im).'<br />';
		//flush();
		
		
		//imagetruecolortopalette($this->im,TRUE,255);
		
		
		$this->output(PATH_site . $this->conf['file']);
	}


	public function addImage($key){
		
		$image = $this->images[$key];

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
			
			$pattern = '/([ \t]*)background\s*:(.*)url\(\s*(?:\'|")?(?:[\.\-\_\/a-zA-Z0-9]*)(?:\'|")?\s*\)(\s*(?:no-repeat|repeat-x|repeat-y)\s*)?(\s*(?:scroll|fixed|inherit)\s*)?(?:\s*(?:left|center|right|top|bottom|(?:-?[0-9]*(?:\.[0-9]*)?\s*(?:%|in|cm|mm|em|ex|pt|pc|px)?))\s*){0,2}(.*;?)\/\*\*\s*sprite-ref:\s*(?:[a-z0-9]+);?(?:.*)\*\//i';
			$replace = "$1background: $2 url(/".$this->conf['file'].")$3$4".$h_pos." ".$v_pos."$5";
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


	public function write(){
		$this->output(PATH_site . $this->conf['file']);		
	}
	
	
	/**
	 * Adds an image to the sprite and returns an md5 hash which is used later to insert a new css rule in the css file
	 *
	 * @param  string  $file: the absolute path to the file that should be inserted in the sprite
	 * @param  array  $directives: instructions for how to position the image on the sprite
	 * @param  string  $match: the complete match of the css-rule containing the image
	 * @param  string  $type: The type of css-rule (background or background-image)
	 * @return  string  an md5 hash
	 * @access  public
	 */
	public function registerImage($file,$directives,$match,$type){

		if(!is_file($file)){
			t3lib_div::devLog('File "'.$file.'" did not exist','sprites',t3lib_div::SYSLOG_SEVERITY_WARNING);
			return;
		}
		
		$key = md5($match);
		
		//validate margins
		$directives['sprite-margin-left'] = intval($this->getDirective('sprite-margin-left',$directives,0));
		$directives['sprite-margin-right'] = intval($this->getDirective('sprite-margin-right',$directives,0));
		$directives['sprite-margin-top'] = intval($this->getDirective('sprite-margin-top',$directives,0));
		$directives['sprite-margin-bottom'] = intval($this->getDirective('sprite-margin-bottom',$directives,0));
		
		//validate sprite alignment
		$alignment = $this->getDirective('sprite-alignment',$directives);
		$directives['sprite-alignment'] = $alignment ? strtolower($alignment) : 'left';
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
			
			$this->imageTypes[$fileinfo['2']] = $fileinfo['2'];

			$img = $this->imageCreateFromFile($fileinfo[3]);
			
			//number of colors in image
			$colorsInImage = imagecolorstotal($img);
			
			//is this a truecolor image
			$isTrueColor = imageistruecolor($img);
			imagedestroy($img);
			
			$this->colors = $this->colors < $colorsInImage ? $colorsInImage : $this->colors;
			$this->isTrueColor = $isTrueColor;
			
			$image = array(
				'width' => $fileinfo[0],
				'height' => $fileinfo[1],
				'ext' => $fileinfo[2],
				'file' => $fileinfo[3],
				'directives' => $directives,
				'match' => $match,
				'type' => $type,
				'colors' => $colorsInImage,
				'truecolor' => $isTrueColor
			);
			
			$this->images[$key] = $image;
			
			if(TX_SPRITES_DEBUG){t3lib_div::devLog('Added image to sprite "'.$this->id.'"','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$image);}
			
		} else {
			
			if(TX_SPRITES_DEBUG){t3lib_div::devLog('Image "'.$file.'" has already been registered','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$image);}	
		}
		
		return $key;
	}

	/**
	 * Returns a named directive or one of it's alias's
	 * 
	 * @param	string
	 * @param	array
	 */
	private function getDirective($key,$directives,$defval = null){
		
		if(isset($directives[$key])) return $directives[$key];
		
		if(isset($this->directiveAliases[$key])){
			foreach($this->directiveAliases[$key] as $altkey){
				if(isset($directives[$altkey])) return $directives[$altkey];				
			}			
		}
		
		if(!is_null($defval)) return $defval;
	}

	
	/**
	 * Returns the absolute path of the sprite image
	 *
	 * @return  string  the absolute path of the sprite image
	 * @access  public
	 */
	public function getAbsFileName(){
		return PATH_site . $this->conf['file'];		
	}
	
	
	
	/**
	 * Calculates the dimensions of the sprite image
	 *
	 * @return  void
	 * @access  private
	 */
	protected function setDimensions(){
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
	
	
	protected function sortImages(){
		
	}
	
	protected function compareImageSize(){
		
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
	public function copyGifOntoGif(&$im, $cpImg, $conf, $workArea) {
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
								//$this->imagecopyresized($im, $cpImg, $Xstart, $Ystart, $cpImgCutX, $cpImgCutY, $w, $h, $w, $h);

							}
						}
					} // Y:
				}
			}
		}
	}
	
	/**
	 * Implements the "IMAGE" GIFBUILDER object, when the "mask" property is false (using only $conf['file'])
	 *
	 * @param	pointer		GDlib image pointer
	 * @param	array		TypoScript array with configuration for the GIFBUILDER object.
	 * @param	array		The current working area coordinates.
	 * @return	void
	 * @see tslib_gifBuilder::make(), maskImageOntoImage()
	 */
	public function copyImageOntoImage(&$im, $conf, $workArea) {
		if ($conf['file']) {
			if (!t3lib_div::inList($this->gdlibExtensions, $conf['BBOX'][2])) {
				$conf['BBOX'] = $this->imageMagickConvert($conf['BBOX'][3], $this->gifExtension, '', '', '', '', '');
				$conf['file'] = $conf['BBOX'][3];
			}
			$cpImg = $this->imageCreateFromFile($conf['file']);
			
			$this->copyGifOntoGif($im, $cpImg, $conf, $workArea);
			imageDestroy($cpImg);
		}
	}
	
	public function getImageKeys(){
		return array_keys($this->images);
	}
	
	public function getImage($key){
		return $this->images[$key];
	}
	
	public function getImageCount(){
		return count($this->images);
	}
	
	public function getId(){
		return $this->id;		
	}
	
}



?>
