<?php
/**
 * @ Chess League Manager (CLM) Component 
 * @Copyright (C) 2008-2014 Thomas Schwietert & Andreas Dorn. All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.chessleaguemanager.de
 * @author Fjodor Sch�fer
 * @email ich@vonfio.de
*/

defined('_JEXEC') or die('Restricted access');
//JHtml::_('behavior.tooltip', '.CLMTooltip', $params);
JHtml::_('behavior.tooltip', '.CLMTooltip');

$lid			= JRequest::getInt('liga','1'); 
$sid			= JRequest::getInt('saison','1');
$runde			= JRequest::getInt( 'runde', '1' );
$dg				= JRequest::getInt('dg','1');
$start			= JRequest::getVar('start','1');
$itemid			= JRequest::getInt('Itemid','1');
$termine		= $this->termine;
$schnellmenu	= $this->schnellmenu;

// Stylesheet laden
require_once(JPATH_COMPONENT.DS.'includes'.DS.'css_path.php');

// Browsertitelzeile setzen
$doc =JFactory::getDocument();
$daten['title'] = JText::_('TERMINE_HEAD');
$doc->setHeadData($daten);

?>
<div >
<div id="termine">
	<div class="componentheading"><?php echo JText::_('TERMINE_HEAD') ?>

		<?php
		// PDF-Links
		echo CLMContent::createPDFLink('termine', JText::_('TERMINE_SHORT_PRINT'), array('layout' => 'termine_short', 'saison' => $sid));
		echo CLMContent::createPDFLink('termine', JText::_('TERMINE_LONG_PRINT'), array('layout' => 'termine_long', 'saison' => $sid));
		?>	
	</div>

	<!-- Navigationsmenu -->
    <?php require_once(JPATH_COMPONENT.DS.'includes'.DS.'submenu.php'); ?>
    <br />
    
    <?php if (isset($termine[0]) AND $termine[0]->nr == 0) {	?>
    <div class="wrong"><?php echo JText::_('NO_ROUNDS') ?></div>
    <?php  } else { 
    
    $arrMonth = array(
        "January" => "Januar", "February" => "Februar", "March" => "M&auml;rz", "April" => "April", "May" => "Mai", "June" => "Juni", "July" => "Juli",  "August" => "August",  "September" => "September", "October" => "Oktober", "November" => "November", "December" => "Dezember",
        "01" => "Januar", "02" => "Februar", "03" => "M&auml;rz", "04" => "April", "05" => "Mai", "06" => "Juni", "07" => "Juli",  "08" => "August",  "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Dezember"
    );
            
    $arrWochentag = array( "Monday" => "Montag", "Tuesday" => "Dienstag", "Wednesday" => "Mittwoch", "Thursday" => "Donnerstag", "Friday" => "Freitag", "Saturday" => "Samstag", "Sunday" => "Sonntag", );
            
    ?>
    
    <table>
        <tr>
            <td align="left" colspan="3" class="clmbox"><ul>
            <?php // Schnellauswahlmenu
            for ($x = 0 ; $x < count ($schnellmenu); $x++) { 		
			$schnellmenu_arr[$x] = explode("-",$schnellmenu[$x]->datum);
            $schnellmenu_monat = mktime (0 , 0 , 0 , $schnellmenu_arr[$x][1]+1, 0 ,0);       
            if ( !isset($schnellmenu_arr[$x-1]) OR $schnellmenu_arr[$x][0] > $schnellmenu_arr[$x-1][0] ) { 
				$new_year = true;
				echo "</ul></li>\n<li><ul><li>\n".'<a href="index.php?option=com_clm&amp;view=termine&amp;saison='. $sid .'&amp;Itemid='. $itemid .'&amp;start='.$schnellmenu_arr[$x][0].'-01-01">'. $schnellmenu_arr[$x][0] ."</a>&nbsp;:&nbsp;</li>\n"; }
            else $new_year = false;    
            if ( !isset($schnellmenu_arr[$x-1]) OR  $schnellmenu_arr[$x][1] > $schnellmenu_arr[$x-1][1]  OR ($new_year)) { 
                echo '<li><a href="index.php?option=com_clm&amp;view=termine&amp;saison='. $sid .'&amp;Itemid='. $itemid .'&amp;start='.$schnellmenu_arr[$x][0].'-'.$schnellmenu_arr[$x][1].'-01">'. $arrMonth[date('F',$schnellmenu_monat)] ."</a></li>\n"; }
            		
            } ?>
            </ul></td>
        </tr>
        <?php // START : Terminschleife
		if ($start == '1') $date = date("Y-m-d");
		else $date = $start;

		for ($t = 0 ; $t < count ($termine); $t++) {
       
        if ( $date <= $termine[$t]->datum ) {
        
            // Veranstaltung verlinken
            if ($termine[$t]->source == 'termin') { 
				$linkname = "index.php?option=com_clm&amp;view=termine&amp;nr=". $termine[$t]->id ."&amp;layout=termine_detail"; 
			} elseif ($termine[$t]->source == 'liga') { 
				if ($termine[$t]->nr <= $termine[$t]->ligarunde) { $runde = $termine[$t]->nr; $dg = 1;}
				elseif ($termine[$t]->nr <= (2 * $termine[$t]->ligarunde)) { $runde = ($termine[$t]->nr - $termine[$t]->ligarunde); $dg = 2;}
				elseif ($termine[$t]->nr <= (3 * $termine[$t]->ligarunde)) { $runde = ($termine[$t]->nr - (2 * $termine[$t]->ligarunde)); $dg = 3;}
				else { $runde = ($termine[$t]->nr - (3 * $termine[$t]->ligarunde)); $dg = 4;}
				$linkname = "index.php?option=com_clm&amp;view=runde&amp;saison=". $termine[$t]->sid ."&amp;liga=".  $termine[$t]->typ_id ."&amp;runde=". $runde ."&amp;dg=". $dg; 
			} else {
				$linkname = "index.php?option=com_clm&amp;view=turnier_runde&amp;runde=". $termine[$t]->nr ."&amp;turnier=". $termine[$t]->typ_id; }
                
            // Veranstaltungsbereich / Ort verlinken
            if ($termine[$t]->source == 'termin') { 
				$linktyp = $termine[$t]->typ; 
			} elseif ($termine[$t]->source == 'liga') { 
				$linktyp = '<a href="index.php?option=com_clm&amp;view=rangliste&amp;saison='. $termine[$t]->sid .'&amp;liga='. $termine[$t]->typ_id;
				if ($itemid <>'') { $linktyp .= "&Itemid=". $itemid; }
				$linktyp .= '">'. $termine[$t]->typ .'</a>';
			} else { 
				$linktyp = '<a href="index.php?option=com_clm&amp;view=turnier_rangliste&amp;turnier='. $termine[$t]->typ_id;
				if ($itemid <>'') { $linktyp .= "&Itemid=". $itemid; }
				$linktyp .= '">'. $termine[$t]->typ .'</a>'; }
            
			
            // Datumsberechnungen
            $datum[$t] = strtotime($termine[$t]->datum);
            $datum_arr[$t] = explode("-",$termine[$t]->datum);
            $monatsausgabe = mktime (0 , 0 , 0 , $datum_arr[$t][1]+1, 0 ,0);
            
            // Monatsberechnungen
            if ( !isset($datum_arr[$t-1]) OR ( $datum_arr[$t][1] > $datum_arr[$t-1][1]) OR ( $datum_arr[$t][0] > $datum_arr[$t-1][0]) ) {
                echo '<tr><td colspan="3" class="noborder">&nbsp;</td></tr>';
                
                // Jahresberechnungen
                if ( !isset($datum_arr[$t-1]) OR $datum_arr[$t][0] > $datum_arr[$t-1][0] ) { 
                echo '<tr><th colspan="3"><a name="'. $datum_arr[$t][0] .'">'. $datum_arr[$t][0] .'</a></th></tr>'; }
                
                echo '<tr class="anfang"><td colspan="3"><a name="'. $datum_arr[$t][0] .'-'. $datum_arr[$t][1] .'">'. $arrMonth[date('F',$monatsausgabe)] . '</a></td></tr>';  
            } ?>
            <tr>
                <td width="150" align="right" class="date">
                <?php if (isset($datum[$t-1]) AND $datum[$t] == $datum[$t-1]) { echo ''; }
                else { echo $arrWochentag[date("l",$datum[$t])]. ",&nbsp;" . $datum_arr[$t][2].".".$datum_arr[$t][1].".".$datum_arr[$t][0]; }
				if (isset($datum[$t]) AND $datum[$t] != '0000-00-00') { if ($termine[$t]->starttime != '00:00:00') echo '&nbsp;&nbsp;&nbsp;'.substr($termine[$t]->starttime,0,5).'&nbsp;Uhr';} ?>
                </td>
                <td class="title"><a href="<?php echo $linkname; if ($itemid <>'') { echo "&Itemid=".$itemid; } ?>"><?php  echo $termine[$t]->name; ?></a></td>
                <td class="typ"><?php echo $linktyp; ?></td>
            </tr>
            
        <?php  }}  // ENDE : Terminschleife ?>
        
    </table>
    <?php  } ?>
    
    <br />
    <?php require_once(JPATH_COMPONENT.DS.'includes'.DS.'copy.php'); ?>
    <div class="clr"></div>
</div>
</div>
