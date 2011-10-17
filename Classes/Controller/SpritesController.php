<?php

class Tx_Sprites_Controller_SpritesController extends Tx_Sprites_Controller_AbstractController{
	
	
	/**
	 * @var string Key of the extension this controller belongs to
	 */
	protected $extensionName = 'Sprites';

	/**
	 * @var t3lib_PageRenderer
	 */
	protected $pageRenderer;


	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		$this->pageRenderer->addInlineLanguageLabelFile('EXT:sprites/Resources/Private/Language/locallang.xml');			
	}

	/**
	 * @param string $path
	 *
	 */
	public function indexAction($path) {
		
		
		$fileArr = t3lib_div::getAllFilesAndFoldersInPath(array(),$path,'css');
		
		$files = array();
		
		foreach($fileArr as $k => $file){
			$files[] = array(
					'name' => basename($file),
					'dirname' => substr(dirname($file),strlen($path)).'/',
					'abspath' => $file,
					'siterelpath' => str_replace(PATH_site,'',$file),
					'relpath' => str_replace($path,'',$file),
					'size' => filesize($file),
					'lastchanged' => filemtime($file)
				);
		}
		
		$this->view->assign('files',$files);
		$this->view->assign('pathinfo',$GLOBALS['SOBE']->basicFF->getTotalFileInfo($path));
		$this->view->assign('parent',dirname($path).'/');
		$this->view->assign('isMount',$GLOBALS['SOBE']->basicFF->checkPathAgainstMounts(dirname($path).'/'));
		
	}
	
	
	/**
	 * @param string $path
	 */
	public function editAction($path){
		$registry = t3lib_div::makeInstance('t3lib_Registry');
		$config = $registry->get('tx_sprites','config');
		$this->view->assign('config',$config);
	}
	
	
	/**
	 * @param string $path
	 * @param string $config
	 */
	public function saveAction($path,$config){
		$registry = t3lib_div::makeInstance('t3lib_Registry');
		$registry->set('tx_sprites', 'config', $config);
		$this->flashMessages->add('Configuration was saved');		
		$this->forward('edit',NULL,NULL,array('path' => $path));
	}
	
	/**
	 * @param array $files
	 */
	public function generateAction($files){
	  
		set_time_limit(3000);
		
		$content = $this->view->render();
		list($start,$end) = explode('###CONTENT###',$content);
	  
		//render header
		ob_end_flush();
		echo $this->template->startpage($GLOBALS['LANG']->sL('LLL:EXT:sprites/Resources/Private/Language/locallang.xml:module.title'));
		echo $start;
		
		echo '
			<p id="progress-message">&nbsp;</p>
			<br />	
			<div style="width:100%; height:20px; border: 1px solid black;">
				<div id="progress-bar" style="float: left; width: 0%; height: 20px; background-color:green;">&nbsp;</div>
				<div id="transparent-bar" style="float: left; width: 100%; height: 20px; background-color:grey;">&nbsp;</div>
			</div>
		';		
		
		flush();
		
		
		
		//generate sprites
		$conf = $this->getConfiguration();
		$spriteBuilder = t3lib_div::makeInstance('Tx_Sprites_Utility_SpriteBuilder',$files,$conf);
		$spriteBuilder->addObserver($this);
		$spriteBuilder->buildSprites();
		
		
		

		//render footer		
		ob_end_flush();
		echo t3lib_BEfunc::getThumbNail('thumbs.php',PATH_site . 'fileadmin/sprites/vertical-sprite.gif','','300');
		echo $end;
		echo $this->template->endPage();
		flush;

		//we already rendered the content so return true
		return TRUE;
	}
	
	
	public function getConfiguration(){
		$conf = t3lib_div::makeInstance('t3lib_Registry')->get('tx_sprites','config');
		$tsparser = t3lib_div::makeInstance('t3lib_TSparser');
		$tsparser->parse($conf);
		return Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($tsparser->setup);
	}
	
	
	public function onRegisterImage(){
		
	}
	
	public function onAddImage($data){
		usleep(100000);
		ob_end_flush();
		echo ('
			<script type="text/javascript">
				document.getElementById("progress-bar").style.width = "' . $data['completed'] . '%";
				document.getElementById("transparent-bar").style.width = "' . (100 - $data['completed']) . '%";
				document.getElementById("progress-message").firstChild.data="' . $data['image']['file'].' added to sprite \''.$data['sprite-id'] . '\'";				
			</script>
		');		
		
		//echo '<p><i>'.$data['image']['file'].' added to sprite \''.$data['sprite-id'].'\' ('.$data['completed'].'% completed)</i></p>';
		flush();
	}
	
	public function onWriteSprite(){
		
	}
	
	function onProcessImage($image){
	  //usleep(500000);
	  ob_end_flush();
	  echo $image . '<br />';
	  flush();
	}

}




?>
