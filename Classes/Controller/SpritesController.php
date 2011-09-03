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
	 */
	public function indexAction(string $path) {
		
		
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
	public function editAction(string $path){
		$registry = t3lib_div::makeInstance('t3lib_Registry');
		$config = $registry->get('tx_sprites','config');
		$this->view->assign('config',$config);
	}
	
	
	/**
	 * @param string $path
	 * @param string $config
	 */
	public function saveAction(string $path, string $config){
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
		$conf = $this->getConfiguration();
		$spriteBuilder = t3lib_div::makeInstance('Tx_Sprites_Core_SpriteBuilder',$files,$conf);
		$spriteBuilder->buildSprites();
	}
	
	
	public function getConfiguration(){
		$conf = t3lib_div::makeInstance('t3lib_Registry')->get('tx_sprites','config');
		$tsparser = t3lib_div::makeInstance('t3lib_TSparser');
		$tsparser->parse($conf);
		//die(t3lib_div::view_array(Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($tsparser->setup)));
		return Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($tsparser->setup);
	}
	

	

}




?>
