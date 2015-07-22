<?php
/**
 * @ Chess League Manager (CLM) Component 
 * @Copyright (C) 2008-2014 Thomas Schwietert & Andreas Dorn. All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.chessleaguemanager.de
 * @author Thomas Schwietert
 * @email fishpoke@fishpoke.de
 * @author Andreas Dorn
 * @email webmaster@sbbl.org
*/

defined('_JEXEC') or die('Restricted access'); 

JRequest::checkToken() or die( 'Invalid Token' );
$mainframe	= JFactory::getApplication();
// Variablen holen
$option 	= JRequest::getInt('option','1');
$sid 		= JRequest::getInt('saison','1');
$lid 		= JRequest::getInt('lid',9898);
$zps 		= JRequest::getVar('zps');
$man 		= JRequest::getInt('man');
$stamm		= JRequest::getInt('stamm');
$ersatz		= JRequest::getInt('ersatz');
$man_name 	= JRequest::getInt('man_name');
$liga_lokal	= JRequest::getVar('lokal');
$liga_mf 	= JRequest::getVar('mf');
 
$user 		=& JFactory::getUser();
$meldung 	= $user->get('id');
$clmuser 	= $this->clmuser;
//$access		= $this->access;

// Prüfen ob Datensatz schon vorhanden ist
	$db			= JFactory::getDBO();
	$query	= "SELECT id, liste "
		." FROM #__clm_mannschaften "
		." WHERE sid = $sid AND zps = '$zps' "
		." AND liga = $lid AND man_nr = $man AND published = 1 "
		;
	$db->setQuery( $query );
	$test=$db->loadObjectList();
	
if (!isset($test[0]) AND $lid == 9898) {
	$link = 'index.php?option=com_clm&view=info';
	$msg = JText::_( 'Login-Modul sollte mind. Version 1.1.2 sein' );
	$mainframe->redirect( $link, $msg );
 			}

if ($test[0]->id < 1) {
	$link = 'index.php?option=com_clm&view=info';
	$msg = JText::_( 'CLUB_LIST_TEAM_DISABLED' );
	$mainframe->redirect( $link, $msg );
 			}
$abgabe		= $this->abgabe;
	$today = date("Y-m-d"); 
	//Liga-Parameter aufbereiten
	$paramsStringArray = explode("\n", $abgabe[0]->params);
	$abgabe[0]->params = array();
	foreach ($paramsStringArray as $value) {
		$ipos = strpos ($value, '=');
		if ($ipos !==false) {
			$key = substr($value,0,$ipos);
			if (substr($key,0,2) == "\'") $key = substr($key,2,strlen($key)-4);
			if (substr($key,0,1) == "'") $key = substr($key,1,strlen($key)-2);
			$abgabe[0]->params[$key] = substr($value,$ipos+1);
			}
	}	
	if (!isset($abgabe[0]->params['deadline_roster']))  {   //Standardbelegung
		$abgabe[0]->params['deadline_roster'] = '0000-00-00'; }

if ($abgabe[0]->liste > 0 AND $abgabe[0]->params['deadline_roster'] == '0000-00-00') {
	$msg = JText::_( 'CLUB_LIST_ALREADY_EXIST' );
	$link = "index.php?option=com_clm&view=info";
	$mainframe->redirect( $link, $msg );
 			}
if ($abgabe[0]->liste > 0 AND $abgabe[0]->params['deadline_roster'] < $today) {
	$msg = JText::_( 'CLUB_LIST_TOO_LATE' );
	$link = "index.php?option=com_clm&view=info";
	$mainframe->redirect( $link, $msg );
}
	$link 	= 'index.php';
	$db 	=& JFactory::getDBO();

// Datum und Uhrzeit für Meldung
	$date =& JFactory::getDate();
	$now = $date->toMySQL();

// Datensätze in Meldelistentabelle schreiben
	$query	= "UPDATE #__clm_mannschaften"
		." SET liste = ".$meldung
		." , mf = $liga_mf"
		." , lokal = '$liga_lokal'"
		." , datum = '$now'"
		." WHERE sid = ".$sid
		." AND man_nr = ".$man
		." AND zps = '$zps'"
		;
	$db->setQuery($query);
	$db->query();

// alte Meldeliste löschen bei Bedarf
	if ($abgabe[0]->liste > 0) {
		$query	= "DELETE FROM #__clm_meldeliste_spieler"
		." WHERE sid = ".$sid
		." AND lid = ".$lid
		." AND mnr = ".$man
		." AND ( zps = '$zps' or FIND_IN_SET(zps,'".$abgabe[0]->sg_zps."') != 0 ) "
		;
	$db->setQuery($query);
	$db->query();
	}
	
// neue Meldeliste schreiben
for ($y=1; $y< (1+$stamm+$ersatz) ; $y++){ 
	$stm		= JRequest::getInt( 'name'.$y);
	$dwz		= JRequest::getInt( 'dwz'.$y);
	$mgl		= JRequest::getInt( 'hidden_mglnr'.$y);
	$hidden_zps		= JRequest::getVar( 'hidden_zps'.$y);

	$query	= "INSERT INTO #__clm_meldeliste_spieler "
		." ( `sid`, `lid`, `mnr`, `snr`, `mgl_nr`, `zps`, `ordering`) "
		." VALUES ('$sid','$lid','$man','$y','$mgl','$hidden_zps','0') "
		;
	$db->setQuery($query);
	$db->query();
	}

// Log
	$date 		=& JFactory::getDate();
	$now 		= $date->toMySQL();
	$user 		=& JFactory::getUser();
	$jid_aktion 	=  ($user->get('id'));
	$aktion 	= "Meldeliste FE";

	$query	= "INSERT INTO #__clm_log "
		." ( `aktion`, `jid_aktion`, `sid` , `lid` ,`zps`,`man`, `datum`) "
		." VALUES ('$aktion','$jid_aktion','$sid','$lid','$zps','$man','$now') "
		;
	$db->setQuery($query);
	$db->query();

// Mails verschicken ?
	$query	= "SELECT l.*, u.email as sl_email, u.name as sl_name FROM #__clm_liga as l "
		." LEFT JOIN #__clm_user as u ON u.jid = l.sl AND u.sid = l.sid "  
		." WHERE l.sid = ".$sid
		." AND l.id = ".$lid
		;
	$db->setQuery($query);
	$liga = $db->loadObjectList();
	//echo "<br>liga: "; var_dump($liga);
	//echo "<br>query: ".$query; 
	//echo "<br>error: ".mysql_errno() . ": " . mysql_error(). "\n";
	//die('<br> abfrage');

if ( $liga[0]->mail > 0 ) {
	// Konfigurationsparameter auslesen
	$config = &JComponentHelper::getParams( 'com_clm' );
	// Zur Abwärtskompatibilität mit CLM <= 1.0.3 werden alte Daten aus Language-Datei als Default eingelesen
	$from = $config->get('email_from', JText::_('RESULT_DATA_MAIL'));
	$fromname = $config->get('email_fromname', JText::_('RESULT_DATA_FROM'));
	$bcc	= $config->get('email_bcc', $config->get('bcc'));
	$bcc_mail	= $config->get('bcc');
	$sl_mail	= $config->get('sl_mail',0);
	
// nur wegen sehr leistungsschwachen Providern
	$query	= " SET SQL_BIG_SELECTS=1";
	$db->setQuery($query);
	$db->query();

// Daten für Email sammeln
// Melder
	$query	= "SELECT a.* FROM #__clm_user as a "
		." WHERE a.sid =".$sid
		."   AND a.jid =".$jid_aktion
		;
	$db->setQuery($query);
	$melder = $db->loadObjectList();
// Saison
	$query	= "SELECT a.* FROM #__clm_saison as a "
		." WHERE a.id =".$sid
		;
	$db->setQuery($query);
	$saison = $db->loadObjectList();
// Mannschaft
	$query	= "SELECT a.*, u.email as mf_email, u.name as mf_name, Vereinname FROM #__clm_mannschaften as a "
		." LEFT JOIN #__clm_user as u ON u.jid = a.mf AND u.sid = a.sid "  
		." LEFT JOIN #__clm_dwz_vereine as v ON (v.sid = a.sid AND v.ZPS = a.zps) "
		." WHERE a.sid =".$sid
		." AND a.liga =".$lid
		." AND a.man_nr = ".$man
		." AND a.zps = '".$zps."'"
		//." AND u.jid > 0 "
		;
	$db->setQuery($query);
	$mannschaft = $db->loadObjectList();

// Meldeliste
	$query	= "SELECT a.*, p.DWZ as pDWZ, Spielername, Vereinname FROM #__clm_meldeliste_spieler as a"
		." LEFT JOIN #__clm_dwz_spieler as p ON (p.sid = a.sid AND p.ZPS = a.zps AND p.Mgl_Nr = a.mgl_nr) "
		." LEFT JOIN #__clm_dwz_vereine as v ON (v.sid = a.sid AND v.ZPS = a.zps) "
		." WHERE a.sid = ".$sid
		." AND a.lid = ".$lid
		." AND a.status = 0 "
		." AND a.mnr = ".$mannschaft[0]->man_nr
		." AND ( a.zps = '".$mannschaft[0]->zps."' OR FIND_IN_SET(a.zps,'".$mannschaft[0]->sg_zps."') )"
		." ORDER BY a.snr ASC "
		;
	$db->setQuery($query);
	$meldeliste=$db->loadObjectList();
	
// Mailbody HTML Header
	$body_html_header = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
			<title>Online Mannschaftsmeldung</title>
			</head>
			<body>';
	$body_html_footer = '
			</body>
			</html>';	
// Mailbody HTML SMeldeliste
	$body_html =	'
		<table width="700" border="0" cellspacing="0" cellpadding="3" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px;">
		<tr>
			<td bgcolor="#F2F2F2" style="border-bottom: solid 1px #000000; border-top: solid 1px #000000; padding: 3px;" colspan="6"><div align="center" style="font-size: 12px;"><strong>Online Mannschaftsmeldung vom ' .JHTML::_('date', date("Y-m-d"), JText::_('DATE_FORMAT_CLM_F')). '</strong></div></td>
		</tr>
		<tr>
			<td width="120">&nbsp;</td>
			<td>&nbsp;</td>
			<td width="5">&nbsp;</td>
			<td width="5">&nbsp;</td>
			<td width="80">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="120" style="border-bottom: solid 1px #999999;"><strong>Liga:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$liga[0]->name. '&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="80" style="border-bottom: solid 1px #999999;"><strong>Saison:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$saison[0]->name. '&nbsp;</td>
		</tr>
		<tr>
			<td width="120" style="border-bottom: solid 1px #999999;"><strong>Staffelleiter:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$liga[0]->sl_name. '&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="80" style="border-bottom: solid 1px #999999;"><strong>email:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$liga[0]->sl_email. '&nbsp;</td>
		</tr>
		<tr>
			<td width="120" style="border-bottom: solid 1px #999999;"><strong>Mannschaft:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$mannschaft[0]->name. '&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="80" style="border-bottom: solid 1px #999999;"><strong>Verein:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$mannschaft[0]->Vereinname. '&nbsp;</td>
		</tr>
		<tr>
			<td width="120" style="border-bottom: solid 1px #999999;"><strong>Mannschaftsleiter:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$mannschaft[0]->mf_name. '&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="80" style="border-bottom: solid 1px #999999;"><strong>email:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$mannschaft[0]->mf_email. '&nbsp;</td>
		</tr>
		<tr>
			<td width="120" style="border-bottom: solid 1px #999999;"><strong>Spiellokal:</strong></td>
			<td style="border-bottom: solid 1px #999999;">' .$mannschaft[0]->lokal. '&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="5" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td width="80" style="border-bottom: solid 1px #999999;">&nbsp;</td>
			<td style="border-bottom: solid 1px #999999;">&nbsp;</td>
		</tr>
		<tr>
			<td width="120">&nbsp;</td>
			<td>&nbsp;</td>
			<td width="5">&nbsp;</td>
			<td width="5">&nbsp;</td>
			<td width="80">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		</table>
		
		<table width="700" border="0" cellspacing="0" cellpadding="3" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px;">
		<tr>
			<td width="50" bgcolor="#F2F2F2" style="border-bottom: solid 1px #000000; border-top: solid 1px #000000; padding: 3px;"><div align="center" style="font-size: 12px;"><strong>Nr</strong></div></td>
			<td width="210" bgcolor="#F2F2F2" style="border-bottom: solid 1px #000000; border-top: solid 1px #000000; padding: 3px;"><div align="center" style="font-size: 12px;"><strong>Name </strong></div></td>
			<td width="75" bgcolor="#F2F2F2" style="border-bottom: solid 1px #000000; border-top: solid 1px #000000; padding: 3px;"><div align="center" style="font-size: 12px;"><strong>DWZ </strong></div></td>
			<td width="75" bgcolor="#F2F2F2" style="border-bottom: solid 1px #000000; border-top: solid 1px #000000; padding: 3px;"><div align="center" style="font-size: 12px;"><strong>Mgl.Nr.</strong></div></td>
			<td width="210" bgcolor="#F2F2F2" style="border-bottom: solid 1px #000000; border-top: solid 1px #000000; padding: 3px;"><div align="center" style="font-size: 12px;"><strong>Verein</strong></div></td>
		</tr>
	';
	foreach ($meldeliste as $meldepos) {
	  if ($meldepos->mgl_nr > 0) {
  	  $body_html .=   '
		<tr>
			<td width="50" style="border-bottom: solid 1px #999999;"><div align="center"><strong>'.$meldepos->snr.'</strong></div></td>
			<td width="210" style="border-bottom: solid 1px #999999;"><div align="center">' .$meldepos->Spielername. '&nbsp;</div></td>
			<td width="75" style="border-bottom: solid 1px #999999;"><div align="center">' .$meldepos->pDWZ. '&nbsp;</div></td>
			<td width="75" style="border-bottom: solid 1px #999999;"><div align="center">' .str_pad($meldepos->mgl_nr,3,"0",STR_PAD_LEFT). '&nbsp;</div></td>
			<td width="210" style="border-bottom: solid 1px #999999;"><div align="center">' .$meldepos->Vereinname. '&nbsp;</div></td>
		</tr>
	  ';
	} }
	$body_html .= 	  '
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="80" valign="top"><strong>Melder:</strong></td>
			<td>' .$melder[0]->name. '&nbsp;</td>
		</tr>
	
		</table>
	';

	$subject = $fromname.': '.JTEXT::_('CLUB_LIST_SUBJECT').' '.$liga[0]->name.': '.$mannschaft[0]->name;

	$body_name = JText::_('RESULT_NAME').$melder[0]->name.",";
	$cc = '';
	$countmail = 0;

	// Textparameter setzen
	if ($abgabe[0]->liste > 0) $erstmeldung = 0;	// Erstmeldung nein
	else $erstmeldung = 1;  						// Erstmeldung ja
	if ($abgabe[0]->params['deadline_roster'] < $today) { $korr_moeglich = 0; 	// Korrektur möglich im FE nein
														$deadline_roster = ''; }
	else { $korr_moeglich = 1; 			// Korrektur möglich im FE ja
		$deadline_roster = JHTML::_('date', $abgabe[0]->params['deadline_roster'], JText::_('DATE_FORMAT_CLM_F')); }
	
	// Mail Melder
	if (isset($melder[0]->email) AND $melder[0]->email > '') {
		$body_html_md = '
		<table width="700" border="0" cellspacing="0" cellpadding="3" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;">
		<tr>
		  <td>'.JText::_('CLUB_LIST_MAIL_MD1').'</td>
  		</tr>
		<tr>
		  <td>';
		if ($erstmeldung == 1)  $body_html_md .= JText::_('CLUB_LIST_MAIL_MD2'); 
		else $body_html_md .= JText::_('CLUB_LIST_MAIL_MD2A');
		$body_html_md .= '</td>
		</tr>
		<tr>';
		if ($korr_moeglich == 1) { 
			if ($erstmeldung == 1)  $body_html_md .= '<td>'.JText::_('CLUB_LIST_MAIL_MD3').$deadline_roster.'</td></tr><tr>'; 
							  else $body_html_md .= '<td>'.JText::_('CLUB_LIST_MAIL_MD3A').$deadline_roster.'</td></tr><tr>'; }
		$body_html_md .= '</tr>
		</table>
		';
		$body_name = JText::_('RESULT_NAME').$melder[0]->name.",";
		$body = $body_html_header.$body_name.$body_html_md.$body_html.$body_html_footer;
		$recipient = $melder[0]->email;
		JUtility::sendMail ($from,$fromname,$recipient,$subject,$body,1,$cc,$bcc);
		$countmail++;
	}
	// Mail Mannschaftsleiter
	if (isset($mannschaft[0]->mf_email) AND $mannschaft[0]->mf_email > '') {
		$body_html_mf = '
		<table width="700" border="0" cellspacing="0" cellpadding="3" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;">
		<tr>
		  <td>';
		if ($erstmeldung == 1)  $body_html_mf .= JText::_('CLUB_LIST_MAIL_MF1'); 
		else $body_html_mf .= JText::_('CLUB_LIST_MAIL_MF1A');
		$body_html_mf .= '</td>
  		</tr>
		<tr>
		  <td>'.JText::_('CLUB_LIST_MAIL_MF2').'</td>
		</tr>
		<tr>';
		if ($korr_moeglich == 1) { 
			if ($erstmeldung == 1)  $body_html_mf .= '<td>'.JText::_('CLUB_LIST_MAIL_MF3').$deadline_roster.'</td></tr><tr>'; 
							  else $body_html_mf .= '<td>'.JText::_('CLUB_LIST_MAIL_MF3A').$deadline_roster.'</td></tr><tr>'; }
		$body_html_mf .= '</tr>
		</table>
		';
		$body_name = JText::_('RESULT_NAME').$mannschaft[0]->mf_name.",";
		$body = $body_html_header.$body_name.$body_html_mf.$body_html.$body_html_footer;
		$recipient = $mannschaft[0]->mf_email;
		JUtility::sendMail ($from,$fromname,$recipient,$subject,$body,1,$cc,$bcc);
		$countmail++;
	}
	// Mail Staffelleiter
	if ($sl_mail == 1 AND isset($liga[0]->sl_email) AND $liga[0]->sl_email > '') {
		$body_html_sl = '
		<table width="700" border="0" cellspacing="0" cellpadding="3" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;">
		<tr>';
			if ($erstmeldung == 1)  $body_html_sl .= '<td>'.JText::_('CLUB_LIST_MAIL_SL1').'</td>'; 
							  else $body_html_sl .= '<td>'.JText::_('CLUB_LIST_MAIL_SL1A').'</td>'; 
		$body_html_sl .= '</tr>
		<tr>
		  <td>'.JText::_('CLUB_LIST_MAIL_SL2').'</td>
		</tr>
		<tr>
		  <td>'.JText::_('CLUB_LIST_MAIL_SL3').'</td>
		</tr>
		<tr> </tr>
		</table>
		';
		$body_name = JText::_('RESULT_NAME').$liga[0]->sl_name.",";
		$body = $body_html_header.$body_name.$body_html_sl.$body_html.$body_html_footer;
		$recipient = $liga[0]->sl_email;
		JUtility::sendMail ($from,$fromname,$recipient,$subject,$body,1,$cc,$bcc);
		$countmail++;
	}
	if ($bcc_mail != "") {
	// Mail Admin 
		$body_html_ad = '
		<table width="700" border="0" cellspacing="0" cellpadding="3" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;">
		<tr>';
			if ($erstmeldung == 1)  $body_html_ad .= '<td>'.JText::_('CLUB_LIST_MAIL_AD1').'</td>'; 
							  else $body_html_ad .= '<td>'.JText::_('CLUB_LIST_MAIL_AD1A').'</td>'; 
		$body_html_ad .= '</tr>
		<tr>
		  <td>'.JText::_('CLUB_LIST_MAIL_AD2').'</td>
		</tr>
		<tr> </tr>
		</table>
		';
		$body_name = JText::_('RESULT_NAME').$bcc_name.",";
		$body = $body_html_header.$body_name.$body_html_ad.$body_html.$body_html_footer;
		$recipient = $bcc_mail;
		JUtility::sendMail ($from,$fromname,$recipient,$subject,$body,1);
		$countmail++;
	}

}
$msg = JText::_( 'CLUB_LIST_SEND_OK' );
if ($countmail > 0) $msg .= JText::_( 'CLUB_LIST_SEND_MAIL' );
$mainframe->redirect( $link, $msg );
?>