<?php

class Tx_Sprites_Controller_AbstractController extends Tx_Extbase_MVC_Controller_ActionController{

	
	protected function initializeView($view){
		
		//echo '<pre>';
		//print_r($this->request->getArguments());
		//echo '</pre>';
		//die();
		
		$this->view->assign('arguments',$this->request->getArguments());
	}
	
	

	/**
	 * Processes a general request. The result can be returned by altering the given response.
	 *
	 * @param Tx_Extbase_MVC_RequestInterface $request The request object
	 * @param Tx_Extbase_MVC_ResponseInterface $response The response, modified by this handler
	 * @throws Tx_Extbase_MVC_Exception_UnsupportedRequestType if the controller doesn't support the current request type
	 * @return void
	 */
	public function processRequest(Tx_Extbase_MVC_RequestInterface $request, Tx_Extbase_MVC_ResponseInterface $response) {
			global $FILEMOUNTS,$TYPO3_CONF_VARS;
		
			$this->template = t3lib_div::makeInstance('template');
			$this->pageRenderer = $this->template->getPageRenderer();
	
			$GLOBALS['SOBE'] = new stdClass();
			$GLOBALS['SOBE']->doc = $this->template;
			$GLOBALS['SOBE']->basicFF = t3lib_div::makeInstance('t3lib_basicFileFunctions');
			$GLOBALS['SOBE']->basicFF->init($FILEMOUNTS,$TYPO3_CONF_VARS['BE']['fileExtensions']);				
	
			parent::processRequest($request, $response);
	
			$pageHeader = $this->template->startpage(
					$GLOBALS['LANG']->sL('LLL:EXT:sprites/Resources/Private/Language/locallang.xml:module.title')
			);
			$pageEnd = $this->template->endPage();
	
			$response->setContent($pageHeader . $response->getContent() . $pageEnd);
	}   
	
}

?>
