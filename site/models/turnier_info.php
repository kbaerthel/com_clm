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

defined('_JEXEC') or die();

jimport('joomla.application.component.model');
jimport( 'joomla.html.parameter' );

class CLMModelTurnier_Info extends JModel {
	
	
	function __construct() {
		
		parent::__construct();

		$this->turnierid = JRequest::getInt('turnier', 0);

		$this->_getTurnierData();

		$this->_getTurnierPlayers();
		
		if ($this->turnier->rnd == 1) { // bereits ausgelost?
			$this->_getTurnierMatches();
		}

	}
	
	
	
	function _getTurnierData() {
	
		$query = "SELECT t.*, CHAR_LENGTH(t.invitationText) AS invitationLength, s.name AS saisonname, u.name AS tlname"
			." FROM #__clm_turniere AS t"
			." LEFT JOIN #__clm_saison AS s ON s.id = t.sid"
			." LEFT JOIN #__clm_user AS u ON jid = t.tl"
			// ." LEFT JOIN #__clm_dwz_vereine AS v ON v.ZPS = t.vereinZPS"
			." WHERE t.id = ".$this->turnierid
			;
		$this->_db->setQuery( $query );
		$this->turnier = $this->_db->loadObject();

		// Ausrichter
		if (strlen($this->turnier->vereinZPS) == 5) {
			$query = 'SELECT Vereinname as hostname'
					. ' FROM dwz_vereine'
					. ' WHERE ZPS = "'.$this->turnier->vereinZPS.'"'
					;
			$this->_db->setQuery( $query );
			$this->turnier->organame = $this->_db->loadResult();
		} elseif  (strlen($this->turnier->vereinZPS) == 3) {
			$query = 'SELECT Verbandname as hostname'
					. ' FROM dwz_verbaende'
					. ' WHERE Verband = "'.$this->turnier->vereinZPS.'"'
				;
			$this->_db->setQuery( $query );
			$this->turnier->organame = $this->_db->loadResult();
		} else  {
			$this->turnier->organame = "";
		}


		// turniernamen anpassen?
		$turParams = new JParameter($this->turnier->params);
		$addCatToName = $turParams->get('addCatToName', 0);
		if ($addCatToName != 0 AND ($this->turnier->catidAlltime > 0 OR $this->turnier->catidEdition > 0)) {
			$this->turnier->name = CLMText::addCatToName($addCatToName, $this->turnier->name, $this->turnier->catidAlltime, $this->turnier->catidEdition);
		}

	}
	
	
	function _getTurnierPlayers() {
		
		$query = "SELECT twz"
			." FROM #__clm_turniere_tlnr"
			." WHERE turnier = ".$this->turnierid
			;
		$this->_db->setQuery( $query );
		$this->players = $this->_db->loadObjectList();
	
		$this->turnier->playersIn = count($this->players);
		
		// TWZ-Schnitt
		$this->turnier->playersTWZ = 0;
		$this->turnier->TWZSum = 0;
		
		foreach ($this->players as $value) {
			if ($value->twz > 0) {
				$this->turnier->playersTWZ++;
				$this->turnier->TWZSum += $value->twz;
			}
		}
		if ($this->turnier->playersTWZ == 0) $this->turnier->TWZAverage = 0;      //klkl
		else $this->turnier->TWZAverage = round($this->turnier->TWZSum/$this->turnier->playersTWZ, 0);
	
	}
	
	function _getTurnierMatches() {
	
		$query = "SELECT *"
			." FROM #__clm_turniere_rnd_spl"
			." WHERE turnier = ".$this->turnierid." AND heim = '1'"
			;
		$this->_db->setQuery( $query );
		$this->matches = $this->_db->loadObjectList();

		// MatchCount
		$this->matchStats['count'] = count($this->matches);
		$this->matchStats['played'] = 0;
		$this->matchStats['winsW'] = 0;
		$this->matchStats['remis'] = 0;
		$this->matchStats['winsB'] = 0;
		$this->matchStats['bye'] = 0; // Freilos
		$this->matchStats['default'] = 0; // kampflos

		// alle Matches durchgehen
		foreach ($this->matches as $value) {
			// gespielt
			if ($value->ergebnis == 8) { // Freilos
				$this->matchStats['bye']++;
			} elseif ($value->ergebnis >= 3) {
				$this->matchStats['default']++;
			} elseif ($value->ergebnis != NULL) {
				$this->matchStats['played']++;
				if ($value->ergebnis == 0) {
					$this->matchStats['winsB']++;
				} elseif ($value->ergebnis == 1) {
					$this->matchStats['winsW']++;
				} elseif ($value->ergebnis == 2) {
					$this->matchStats['remis']++;
				}
			}
		}
		
		if ($this->matchStats['played'] > 0) {
			$this->matchStats['percW'] = round($this->matchStats['winsW']/($this->matchStats['played']/100), 2);
			$this->matchStats['percR'] = round($this->matchStats['remis']/($this->matchStats['played']/100), 2);
			$this->matchStats['percB'] = round($this->matchStats['winsB']/($this->matchStats['played']/100), 2);
		}
		


	}

}
?>