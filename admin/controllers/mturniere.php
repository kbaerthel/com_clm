<?php

/**
 * @ Chess League Manager (CLM) Component 
 * @Copyright (C) 2008-2015 CLM Team.  All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.chessleaguemanager.de
 * @author Thomas Schwietert
 * @email fishpoke@fishpoke.de
 * @author Andreas Dorn
 * @email webmaster@sbbl.org
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class CLMControllerMTurniere extends JControllerLegacy
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );
	}

	function display($cachable = false, $urlparams = array())
	{
	$mainframe	= JFactory::getApplication();

	$db 		=JFactory::getDBO();
	$user 		=JFactory::getUser();
	$cid 		= JRequest::getVar( 'cid', array(0), '', 'array' );
	$option 	= JRequest::getCmd( 'option' );
	$section 	= JRequest::getVar( 'section' );
	$row 		=JTable::getInstance( 'ligen', 'TableCLM' );
	JArrayHelper::toInteger($cid, array(0));
	
	// load the row from the db table
	$row->load( $cid[0] );

	$clmAccess = clm_core::$access;

	if($clmAccess->access('BE_teamtournament_edit_detail') === false) {
		$msg = JText::_( 'Kein Zugriff: ').JText::_( 'MTURN_STAFFEL_TOTAL' ) ;
		$mainframe->redirect( 'index.php?option='. $option.'&section='.$section, $msg ,"message");
		}
	if ($cid[0]==0) {
		if($clmAccess->access('BE_teamtournament_create') === false) {
		JError::raiseWarning( 500, JText::_( 'LIGEN_ADMIN' ));
		$mainframe->redirect( 'index.php?option='. $option.'&section='.$section, $msg ,"message");
				}
		// Neue ID
		$row->published	= 0;
	} else { 
	// Prüfen ob User Berechtigung zum editieren hat
	$saison		=JTable::getInstance( 'saisons', 'TableCLM' );
	$saison->load( $row->sid );
	// illegaler Einbruchversuch über URL !
	// evtl. mitschneiden !?!
		if ($saison->archiv == "1" AND $clmAccess->access('BE_teamtournament_edit_detail') === false) {
			JError::raiseWarning( 500, JText::_( 'MTURN_ARCHIV' ));
			$mainframe->redirect( 'index.php?option='. $option.'&section='.$section, $msg,"message" );
		}
		// Keine SL oder Admin
		$clmAccess->accesspoint = 'BE_teamtournament_edit_detail';
		if($clmAccess->access('BE_teamtournament_edit_detail') === false) {
			JError::raiseWarning( 500, JText::_( 'MTURN_STAFFEL_TOTAL' ) );
			$mainframe->redirect( 'index.php?option='. $option.'&section='.$section, $msg ,"message");
		}
		if($row->sl !== clm_core::$access->getJid() AND $clmAccess->access('BE_teamtournament_edit_detail') !== true) {
		JError::raiseWarning( 500, JText::_( 'MTURN_STAFFEL' ) );
		$mainframe->redirect( 'index.php?option='. $option.'&section='.$section, $msg,"message" );
				}
	// do stuff for existing records
		$row->checkout( $user->get('id') );
	}

	// Listen
	// Heimrecht vertauscht
	$lists['heim']	= JHtml::_('select.booleanlist',  'heim', 'class="inputbox"', $row->heim );
	// Published
	$lists['published']	= JHtml::_('select.booleanlist',  'published', 'class="inputbox"', $row->published );
	// Anzeige Mannschaftsaufstellung
	$lists['anzeige_ma']	= JHTML::_('select.booleanlist',  'anzeige_ma', 'class="inputbox"', $row->anzeige_ma );
	// automat. Mail
	$lists['mail']	= JHtml::_('select.booleanlist',  'mail', 'class="inputbox"', $row->mail );
	// Staffelleitermail als BCC
	$lists['sl_mail']	= JHtml::_('select.booleanlist',  'sl_mail', 'class="inputbox"', $row->sl_mail );
	// Ordering für Rangliste
	$lists['order']	= JHtml::_('select.booleanlist',  'order', 'class="inputbox"', $row->order );

	$userlist = $clmAccess->userlist('BE_teamtournament_edit_result','>0');
	
	if($userlist === false) {
		echo "<br>cl: "; var_dump($userlist); die('clcl'); }
	$sllist[]	= JHtml::_('select.option',  '0', JText::_( 'MTURN_TL' ), 'jid', 'name' );
	$sllist		= array_merge( $sllist, $userlist );
	$lists['sl']	= JHtml::_('select.genericlist',   $sllist, 'sl', 'class="inputbox" size="1"', 'jid', 'name', $row->sl );
	// Saisonliste
	$sql = "SELECT id as sid, name FROM #__clm_saison WHERE archiv = 0";
	$db->setQuery($sql);
	if (!$db->query()){ $this->setRedirect( 'index.php?option='.$option.'&section='.$section );
		return JError::raiseWarning( 500, $db->getErrorMsg() ); }
	$saisonlist[]	= JHtml::_('select.option',  '0', JText::_( 'LIGEN_SAISON' ), 'sid', 'name' );
	$saisonlist	= array_merge( $saisonlist, $db->loadObjectList() );
	$lists['saison']= JHtml::_('select.genericlist',   $saisonlist, 'sid', 'class="inputbox" size="1"','sid', 'name', $row->sid );
	// Rangliste
	$query = " SELECT id, Gruppe FROM #__clm_rangliste_name ";
	$db->setQuery($query);
	if (!$db->query()){ $this->setRedirect( 'index.php?option='.$option.'&section='.$section );
		return JError::raiseWarning( 500, $db->getErrorMsg() ); }
	$glist[]	= JHtml::_('select.option',  '0', JText::_( 'LIGEN_ML' ), 'id', 'Gruppe' );
	$glist		= array_merge( $glist, $db->loadObjectList() );
	$lists['gruppe']= JHtml::_('select.genericlist',   $glist, 'rang', 'class="inputbox" size="1"', 'id', 'Gruppe', $row->rang );

	require_once(JPATH_COMPONENT.DS.'views'.DS.'mturniere.php');
	CLMViewMTurniere::mturnier( $row, $lists, $option, ($cid[0]==0 ? true : false) );
	}
	
function apply() {
	$this->saveIt(true);		
}

function save() {
	$this->saveIt(false);
}


function saveIt($apply=false)
	{
	$mainframe	= JFactory::getApplication();

	// Check for request forgeries
	JRequest::checkToken() or die( 'Invalid Token' );

	$option		= JRequest::getCmd('option');
	$section	= JRequest::getVar('section');
	$db 		= JFactory::getDBO();
	$task 		= JRequest::getVar( 'task');
	$row 		= JTable::getInstance( 'ligen', 'TableCLM' );
	$msg		= JRequest::getVar( 'id');
	$sid_alt	= JRequest::getVar( 'sid_alt');
	$sid		= JRequest::getVar( 'sid');
	 
	if (!$row->bind(JRequest::get('post'))) {
		JError::raiseError(500, $row->getError() );
	}
	//Liga-Parameter zusammenfassen
	$row->params['anz_sgp'] = JRequest::getVar('anz_sgp');
	$paramsStringArray = array();
	foreach ($row->params as $key => $value) {
		//$paramsStringArray[] = $key.'='.intval($value);
		if (substr($key,0,2) == "\'") $key = substr($key,2,strlen($key)-4);
		if (substr($key,0,1) == "'") $key = substr($key,1,strlen($key)-2);
		$paramsStringArray[] = $key.'='.$value;
	}
	$row->params = implode("\n", $paramsStringArray);
	
	// pre-save checks
	if (!$row->check()) { JError::raiseError(500, $row->getError() ); }

	$teil	= $row->teil;

	// if new item, order last in appropriate group
	$aktion = JText::_( 'LIGEN_AKTION_LEAGUE_EDIT' );
	$neu_id = 0;
	$ungerade_id = 0;
	if (!$row->id) {
	$neu_id = 1;
	$aktion = JText::_( 'LIGEN_AKTION_NEW_LEAGUE' );
		$where = "sid = " . (int) $row->sid;
		$row->ordering = $row->getNextOrder( $where );
	
	// Bei ungerader Anzahl Mannschaften Teilnehmerzahl um 1 erhöhen
	if (($row->runden_modus != 4) AND ($row->runden_modus != 5)) { // vollrundig, Schweizer System
	if (($row->teil)%2 != 0) {
		$ungerade_id	= 1;
		$row->teil	= $row->teil+1;
		$tln		= $row->teil;
	JError::raiseWarning(500, JText::_( 'LIGEN_MANNSCH', true ) );
		}	}	
	if ($row->runden_modus == 4) {
		$ko_id = 0;
		$tln_ko	= $row->teil;
		while ($row->teil < pow(2,$row->runden)) { $ko_id++; $row->teil = $row->teil+1;}
		if ($ko_id > 0)  JError::raiseWarning(500, JText::_( 'MTURN_MANNSCH_KO', true ) ); 
			}	
	if ($row->runden_modus == 5) {
		$ko_id = 0;
		$tln_ko	= $row->teil;
		while ($row->teil < pow(2,$row->runden-1)) { $ko_id++; $row->teil = $row->teil+1;}
		if ($ko_id > 0)  JError::raiseWarning(500, JText::_( 'MTURN_MANNSCH_KO', true ) ); 
		}
 
	}
	$row->liga_mt	= 1; //mtmt 0 = liga  1 = mannschaftsturnier
	// save the changes
	if (!$row->store()) {
		JError::raiseError(500, $row->getError() );
		}
	$liga_man	= $row->id;
	$liga_rnd	= $row->runden;
	$liga_dg	= $row->durchgang;
	$publish	= $row->published;

	// Wenn sid gewechselt wurde, alle Daten in neue Saison verschieben
	if ($sid_alt != $sid AND $sid_alt != "") {
	JError::raiseNotice( 6000,  JText::_( 'LIGEN_SAISON_AEND' ));
	$query = " UPDATE #__clm_mannschaften "
		." SET sid = ".$sid
		." WHERE liga = ".$liga_man
		." AND sid = ".$sid_alt
		;
	$db->setQuery($query);
	$db->query();

	$query = " UPDATE #__clm_meldeliste_spieler "
		." SET sid = ".$sid
		." WHERE lid = ".$liga_man
		." AND sid = ".$sid_alt
		;
	$db->setQuery($query);
	$db->query();

	$query = " UPDATE #__clm_rnd_man "
		." SET sid = ".$sid
		." WHERE lid = ".$liga_man
		." AND sid = ".$sid_alt
		;
	$db->setQuery($query);
	$db->query();

	$query = " UPDATE #__clm_rnd_spl "
		." SET sid = ".$sid
		." WHERE lid = ".$liga_man
		." AND sid = ".$sid_alt
		;
	$db->setQuery($query);
	$db->query();

	$query = " UPDATE #__clm_runden_termine "
		." SET sid = ".$sid
		." WHERE liga = ".$liga_man
		." AND sid = ".$sid_alt
		;
	$db->setQuery($query);
	$db->query();
	}
	
	// Bei ungerader Anzahl Mannschaften "spielfrei" hinzufügen
	if (($row->runden_modus != 4) AND ($row->runden_modus != 5)) { // vollrundig, Schweizer System
	if ($ungerade_id == "1") {

	$query = " INSERT INTO #__clm_mannschaften "
		." ( `sid`,`name`,`liga`,`zps`,`liste`,`edit_liste`,`man_nr`,`tln_nr`,`mf`) "
		." VALUES ('$sid','spielfrei','$liga_man','0','0','62','0','$tln','0') "
		;
	$db->setQuery($query);
	$db->query();
	JError::raiseNotice( 6000,  JText::_( 'LIGEN_MANNSCH_1' ));
		}}
	// Bei KO-System  x Mannschaften "spielfrei" hinzufügen, wenn nötig
	if (($row->runden_modus == 4) OR ($row->runden_modus == 5)) { // KO System
	for($x=1; $x< 1+$ko_id; $x++) {
	$tln_ko++;
	$query = " INSERT INTO #__clm_mannschaften "
		." ( `sid`,`name`,`liga`,`zps`,`liste`,`edit_liste`,`man_nr`,`tln_nr`,`mf`) "
		." VALUES ('$sid','spielfrei','$liga_man','0','0','62','0','$tln_ko','0') "
		;
	$db->setQuery($query);
	$db->query();
		}
	if ($ko_id > 0) JError::raiseNotice( 6000,  $ko_id.JText::_( 'MTURN_MANNSCH_KO_1' ));
		}
	
	// Mannschaftsrunden anlegen
	if ($neu_id == "1") {
		clm_core::$api->db_tournament_genRounds($liga_man,true); 
	// Mannschaften anlegen
	for($x=1; $x< 1+$teil; $x++) {
	$man_name = JText::_( 'LIGEN_STD_TEAM' )." ".$x;
	if ($x < 10) $man_nr = $liga_man.'0'.$x; else $man_nr = $liga_man.$x;
	$query = " INSERT INTO #__clm_mannschaften "
		." (`sid`,`name`,`liga`,`zps`,`liste`,`edit_liste`,`man_nr`,`tln_nr`,`mf`,`published`) "
		." VALUES ('$sid','$man_name','$liga_man','1','0','0','$man_nr','$x','0','$publish') "
		;
	$db->setQuery($query);
	$db->query();
				}

	}

	clm_core::$api->db_tournament_ranking($liga_man,true); 

	//require_once(JPATH_COMPONENT.DS.'controllers'.DS.'ergebnisse.php');
	//CLMControllerErgebnisse::calculateRanking($sid,$liga_man);
	
	if($apply) {
			$msg = JText::_( 'LIGEN_AENDERN' );
			$link = 'index.php?option='.$option.'&section='.$section.'&cid[]='. $row->id ;
	} else {
			$msg = JText::_( 'LIGEN_LIGA' );
			$link = 'index.php?option='.$option.'&view=view_tournament_group&liga=0';
	}
	
	// Log schreiben
	$clmLog = new CLMLog();
	$clmLog->aktion = $aktion;
	$clmLog->params = array('sid' => $row->sid, 'lid' => $row->id);
	$clmLog->write();
	

	$mainframe->redirect( $link, $msg );
	}


	function cancel()
	{
	$mainframe	= JFactory::getApplication();
	// Check for request forgeries
	JRequest::checkToken() or die( 'Invalid Token' );
	
	$option		= JRequest::getCmd('option');
	$section	= JRequest::getVar('section');
	$id		= JRequest::getVar('id');	
	$row 		=JTable::getInstance( 'ligen', 'TableCLM' );

	$msg = JText::_( 'LIGEN_AKTION');
	$mainframe->redirect( 'index.php?option='.$option.'&view=view_tournament_group&liga=0', $msg , "message");
	}
}
