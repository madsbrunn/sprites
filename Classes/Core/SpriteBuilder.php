<?php


define('TX_SPRITE_IMAGE_PATH_INDEX',1);
define('TX_SPRITE_REF_INDEX',2);
define('TX_SPRITE_DIRECTIVES_INDEX',3);

class Tx_Sprites_Core_SpriteBuilder{
	
	private $files = array();
	private $conf = array();
	private $sitepath = '';
	private $sprites = array();
	
	function __construct($files,$conf,$sitepath = ''){
		
		//$this->files = $files;
		
		
		foreach($files as $file){
			$this->files[] = array(
				'path' => $file 				
			);
		}
		
		
		$this->conf = $conf;
		$this->sitepath = $sitepath;
		
		if(TX_SPRITES_DEBUG){
			t3lib_div::devLog('SpriteBuilder constructor arguments','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,func_get_args());
		}
	}
	

	function buildSprites(){
		
		//$stdgfx = t3lib_div::makeInstance('t3lib_stdGraphic');
		//$stdgfx->init();
		
		
		//$im = imagecreatetruecolor(400,400);
		//list($red,$green,$blue) = $stdgfx->convertColor('#eeeeee');
		//$bgcolor = ImageColorAllocate($im, $red,$green,$blue);
		//ImageFilledRectangle($im, 0, 0, 400, 400, $bgcolor);
		
		//$sprite = t3lib_div::makeInstance('Tx_Sprites_Utility_Sprite');
		//$sprite->init();
		//$sprite->build();
		
		$ressources = array();
		
		
		foreach($this->files as $k => $file){
			
			
			$this->files[$k]['orig_content'] = t3lib_div::getURL($file['path']);
			
			$pattern = '/background-image\s*:\s*url\((.*)\)\s*;\s*\/\*\*\s+sprite-ref:\s*([a-z0-9]+);(.*)\*\//ime';	
			$replace = '$this->processRule("$0","$1","$2","$3","'.$file['path'].'")';
			$this->files[$k]['new_content'] = preg_replace($pattern,$replace,$this->files[$k]['orig_content']);
			
			
			/*
			if($backgroundimages = $this->extractAnnotatedBackgroundImageRules($content)){	
				if(TX_SPRITES_DEBUG){
					t3lib_div::devLog('Found '.count($backgroundimages).' annotated background images in file '.$file,'sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$backgroundimages);
				}
				
				foreach($backgroundimages as $data){
					$spriteref = $data[TX_SPRITE_REF_INDEX][0];
					if(!isset($this->conf['sprites'][$spriteref])){		
						t3lib_div::devLog("Sprite '$spriteref' not defined - skipping image",'sprites',t3lib_div::SYSLOG_SEVERITY_WARNING,$backgroundimage);
						continue;
					}
					$directives = $this->parseDirectives($data[TX_SPRITE_DIRECTIVES_INDEX][0]);
					$path = $this->getSiteRelPath($data[TX_SPRITE_IMAGE_PATH_INDEX][0],$file);
					
					if(!isset($this->sprites[$spriteref])){
						$this->sprites[$spriteref] = t3lib_div::makeInstance('Tx_Sprites_Utility_Sprite');
						$this->sprites[$spriteref]->init($spriteref,$this->conf['sprites'][$spriteref]);
					}
					$this->sprites[$spriteref]->addImage($path,$directives);
				}
			}*/
		}
		
		if(TX_SPRITES_DEBUG){
			t3lib_div::devLog('Extracted content from css-files and processed rules','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$this->files);
			ob_start();
			var_dump($this->sprites);
			$sprites = ob_get_contents();
			ob_end_clean();
			
			t3lib_div::devLog('Finished analyzing css files','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,array($sprites));
		}
		
		
		// build sprite images 
		foreach($this->sprites as $sprite){
			$sprite->make();		
		}
		
		// now replace placeholders in css files and write sprite'd css files
		foreach($this->files as $k => $file){
			foreach($this->sprites as $sprite){
				foreach($sprite->images as $md5 => $image){
					$this->files[$k]['new_content'] = str_replace($md5,$image['cssrules'],$this->files[$k]['new_content']);	
				}
			}
		}
		
		if(TX_SPRITES_DEBUG){
			t3lib_div::devLog('Wrote new css rules to files','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$this->files);			
		}
	}

	protected function extractAnnotatedBackgroundImageRules($content){
		$pattern = '/background-image\s*:\s*url\((.*)\)\s*;\s*\/\*\*\s+sprite-ref:\s*([a-z0-9]+);(.*)\*\//im';
		if(preg_match_all($pattern,$content,$matches,PREG_OFFSET_CAPTURE|PREG_SET_ORDER)){
			return $matches;
		}
	}	
	
	
	/**
	 * 	Converts relative file path to an absolute path
	 *	
	 *	handles the following relative paths
	 *
	 * 	image.jpg
	 *	img/image.jpg
	 *	../image.jpg
	 *	../img/images.jpg
	 *	/fileadmin/templates/img/image.jpg
	 *	http://www.example.com/path/image.jpg
	 *	
	 *
	 */
	 /*
	protected function getAbsPath($rel,$abs){

		$rel = trim($rel);
		$abs = trim($abs);
		
		$p = parse_url($rel);
		$basefolder = dirname($abs);

		if($p['scheme']) return $rel;
		
		if($relative{0} == '/') {
			
		}
          * 
	}*/
	
	
	
	public function getSiteRelPath($relative,$absolute){
		
		$p = parse_url($relative);
		if($p["scheme"])return $relative;
		extract(parse_url($absolute));
		$path = dirname($path); 
		if($relative{0} == '/') {
				$cparts = array_filter(explode("/", $relative));
		}
		else {
				$aparts = array_filter(explode("/", $path));
				$rparts = array_filter(explode("/", $relative));
				$cparts = array_merge($aparts, $rparts);
				foreach($cparts as $i => $part) {
						if($part == '.') {
								$cparts[$i] = null;
						}
						if($part == '..') {
								$cparts[$i - 1] = null;
								$cparts[$i] = null;
						}
				}
				$cparts = array_filter($cparts);
		}
		$path = implode("/", $cparts);
		$url = "";
		if($scheme) {
				$url = "$scheme://";
		}
		if($user) {
				$url .= "$user";
				if($pass) {
						$url .= ":$pass";
				}
				$url .= "@";
		}
		if($host) {
				$url .= "$host/";
		}
		$url .= $path;
		
		if($absolute{0} == '/') {
			$url = '/' . $url;
		}
		
		return $url;
	}
	
	protected function parseDirectives($str){
		//return preg_split('/.*:.*;/i',trim($str));	
		$directives = array();
		$tmp = t3lib_div::trimExplode(';',$str,1);
		foreach($tmp as $directive){
			list($k,$d) = explode(':',$directive,2);
			$directives[trim($k)]=trim($d);
		}
		return $directives;
	}	
	
	
	protected function addImageToSprite($imagepath,$spriteref,$directives){
		list($width,$height) = getimagesize('/'.$imagepath);
		$this->sprites[$spriteref]['images'][] = array(
			'file' => $imagepath,
			'width' => $width,
			'height' => $height,
			'directives' => $directives
		);
	}

        protected function makeImage(){

            $image = t3lib_div::makeInstance('t3lib_stdGraphic');
            $image->init();

        }
        
        
        function processRule($match,$image,$spriteref,$directives,$file){
		if(TX_SPRITES_DEBUG){
			t3lib_div::devLog('Process rule','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,func_get_args());					
		}
		
		if(!isset($this->conf['sprites'][$spriteref])){		
			t3lib_div::devLog("Sprite '$spriteref' not defined - skipping image",'sprites',t3lib_div::SYSLOG_SEVERITY_WARNING,func_get_args());
			continue;
		}
		$directives = $this->parseDirectives($directives);
		$path = $this->getSiteRelPath($image,$file);
		
		if(!isset($this->sprites[$spriteref])){
			$this->sprites[$spriteref] = t3lib_div::makeInstance('Tx_Sprites_Utility_Sprite');
			$this->sprites[$spriteref]->init($spriteref,$this->conf['sprites'][$spriteref]);
		}
		$key = $this->sprites[$spriteref]->registerImage($path,$directives,$match);
		
		return $key;
		
        }
}


?>
