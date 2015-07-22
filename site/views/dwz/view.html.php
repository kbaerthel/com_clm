<?php
/**
 * @ Chess League Manager (CLM) Component 
 * @Copyright (C) 2008 Thomas Schwietert & Andreas Dorn. All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.fishpoke.de
 * @author Thomas Schwietert
 * @email fishpoke@fishpoke.de
 * @author Andreas Dorn
 * @email webmaster@sbbl.org
*/

jimport( 'joomla.application.component.view');

class CLMViewDWZ extends JView
{
	function display($tpl = null)
	{
		$model	  = &$this->getModel();
  		$liga     = $model->getCLMLiga();
		$this->assignRef('liga'  , $liga);

		$model = &$this->getModel();
		$zps = $model->getCLMzps();
		$this->assignRef('zps'  , $zps);

		$model	  = &$this->getModel();
		$vereinsliste     = $model->getCLMVereinsliste();
		$this->assignRef('vereinsliste'  , $vereinsliste);
		
		$model	  = &$this->getModel();
		$saisons     = $model->getCLMSaisons();
		$this->assignRef('saisons'  , $saisons);
		
		$document =& JFactory::getDocument();
		$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
		$document->addScript(JURI::base().'components/com_clm/javascript/updateTableHeaders.js');
		 
		/* Call the state object */
		$state =& $this->get( 'state' );
 
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order']     = $state->get( 'filter_order_dwz' );
		$lists['order_Dir'] = $state->get( 'filter_order_Dir_dwz' );
 
		$this->assignRef( 'lists', $lists );
		
		parent::display($tpl);
	}	
}
?>