<?php


define('TX_SPRITE_IMAGE_PATH_INDEX',1);
define('TX_SPRITE_REF_INDEX',2);
define('TX_SPRITE_DIRECTIVES_INDEX',3);

class Tx_Sprites_Core_SpriteBuilder{
	
	private $files = array();
	private $conf = array();
	private $sprites = array();
	
	function __construct($files,$conf){
		
		foreach($files as $file){
			$pparts = pathinfo($file);
			$new_path = $pparts['dirname'].'/'.$pparts['filename'] . ($this->conf['css-file-suffix'] ? $this->conf['css-file-suffix'] : '-sprite') . '.'.$pparts['extension'];
			
			$this->files[] = array(
				'orig_path' => $file,
				'new_path' => $new_path
			);
		}
		
		$this->conf = $conf;
		
		if(TX_SPRITES_DEBUG){
			t3lib_div::devLog('SpriteBuilder constructor arguments','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,func_get_args());
		}
	}
	

	function buildSprites(){
		
		//extract rules and process them
		foreach($this->files as $k => $file){
			$this->files[$k]['orig_content'] = t3lib_div::getURL($file['orig_path']);
			$pattern = '/[ \t]*(background-image|background)\s*:.*url\(\s*(?:\'|")?([\.\-\_\/a-zA-Z0-9]*)(?:\'|")?\s*\).*;?\/\*\*\s*sprite-ref:\s*([a-z0-9]+);?(.*)\*\//ie';
			$replace = '$this->processRule("$0","$1","$2","$3","$4","'.$file['orig_path'].'")';
			$this->files[$k]['new_content'] = preg_replace($pattern,$replace,$this->files[$k]['orig_content']);
		}
		
		// build sprite images 
		foreach($this->sprites as $sprite){
			$sprite->make();		
		}
		
		// now replace placeholders in css content
		foreach($this->files as $k => $file){
			foreach($this->sprites as $sprite){
				foreach($sprite->images as $md5 => $image){
					$this->files[$k]['new_content'] = str_replace($md5,$image['cssrules'],$this->files[$k]['new_content']);	
				}
			}
		}
		
		if(TX_SPRITES_DEBUG){
			t3lib_div::devLog('Wrote new css rules to content','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$this->files);			
		}
		
		//now write new css files
		foreach($this->files as $k => $file){
			if(sha1($file['orig_content']) == sha1($file['new_content'])){
				t3lib_div::devLog('File "'.$file['orig_path'].'" was not changed so no reason to write','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$file);				
			}
			t3lib_div::writeFile($file['new_path'],$file['new_content']);						
		}
		
	}

	public function getRealPath($relative,$absolute){
	
		if(strstr($relative,'://')){
			//don't handle non-local images
			return false;			
		}
		
		if($relative{0} == '/'){ 
			//prepend site's path
			$relative = PATH_site . substr($relative,1);			   
		}
	
		$cwd = getcwd();
		$absdir = dirname($absolute);
		chdir($absdir);
		$realpath = realpath($relative);
		chdir($cwd);
		
		if(TX_SPRITES_DEBUG){
			$debuginfo = array();
			$debuginfo['relative_url'] = $relative;
			$debuginfo['absolute_url'] = $absolute;
			$debuginfo['resolved_url'] = $realpath;
			t3lib_div::devLog('Resolved URL','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,$debuginfo);
		}
		return $realpath;
	}	
	
	
	protected function parseDirectives($str){
		$directives = array();
		$tmp = t3lib_div::trimExplode(';',$str,1);
		foreach($tmp as $directive){
			list($k,$d) = explode(':',$directive,2);
			$directives[trim($k)]=trim($d);
		}
		return $directives;
	}	
	
	
	function processRule($match,$type,$image,$spriteref,$directives,$file){
		
		if(TX_SPRITES_DEBUG){
			t3lib_div::devLog('Process rule','sprites',t3lib_div::SYSLOG_SEVERITY_INFO,func_get_args());					
		}		
		
		if(!in_array($type,array('background','background-image'))){
			t3lib_div::devLog("Invalid argument - must be either 'background' or 'background-image' - skipping rule",'sprites',t3lib_div::SYSLOG_SEVERITY_WARNING,func_get_args());
			return $match;			
		}
		
		if(!isset($this->conf['sprites'][$spriteref])){		
			t3lib_div::devLog("Sprite '$spriteref' not defined - skipping image",'sprites',t3lib_div::SYSLOG_SEVERITY_WARNING,func_get_args());
			return $match;
		}		

		$path = $this->getRealPath($image,$file);		
		if(!$path){
			t3lib_div::devLog("Couldn't find image '$image' - skipping",'sprites',t3lib_div::SYSLOG_SEVERITY_WARNING,func_get_args());
			return $match;			
		}	
		
		$directives = $this->parseDirectives($directives);		
		
		if(!isset($this->sprites[$spriteref])){
			$this->sprites[$spriteref] = t3lib_div::makeInstance('Tx_Sprites_Utility_Sprite');
			$this->sprites[$spriteref]->init($spriteref,$this->conf['sprites'][$spriteref]);
		}

		$key = $this->sprites[$spriteref]->addImage($path,$directives,$match,$type);
		
		return $key;
	}
	
}


?>
