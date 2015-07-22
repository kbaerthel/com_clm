<?php
/**
 * @ Chess League Manager (CLM) Component 
 * @Copyright (C) 2008-2015 CLM Team.  All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.fishpoke.de
 * @author Thomas Schwietert
 * @email fishpoke@fishpoke.de
 * @author Andreas Dorn
 * @email webmaster@sbbl.org
*/
defined('_JEXEC') or die('Restricted access');

$swt = JRequest::getVar ('swt_file', '', 'default', 'string');
$sid = JRequest::getVar ('filter_saison', 0, 'default', 'int');

jimport( 'joomla.filesystem.file' );
$path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'swt' . DIRECTORY_SEPARATOR;

$mturnier = 1;
/* $liga_mannschaften = CLMSWT::readInt ($path.$swt, 602);
$modus = CLMSWT::readInt ($path.$swt, 596);
if (empty ($liga_mannschaften) || $modus != 1) { // keine Liga oder nicht vollrundig
	$mturnier = 1;
} */

$liga = JRequest::getVar('liga', 0, 'default', 'int');
?>

<script language="javascript" type="text/javascript">

	Joomla.submitbutton = function (pressbutton) { 
        var form = document.adminForm;
        if (pressbutton == 'cancel') {
            submitform( pressbutton );
            return;
        }
        // do field validation
		if ( getSelectedValue('adminForm','task') == 'update' ) {
			if ( getSelectedValue('adminForm','liga') == 0 ) {
				alert( "<?php echo JText::_( 'LEAGUE_HINT_00', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
        } else {
            submitform( pressbutton );
        }
    }
	
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >
	<table width="100%" class="admintable"> 
		<tr>
			<td width="50%" style="vertical-align: top;">
				<fieldset class="adminform"> 
					<legend><?php echo JText::_( 'SWT_LEAGUE_OVERWRITE_HINTS_TAB' ); ?></legend> 
					<?php echo JText::_( 'SWT_LEAGUE_OVERWRITE_HINTS_TEXT' ); ?>
				</fieldset>
			</td>
			<td width="50%" style="vertical-align: top;">
				<fieldset class="adminform"> 
					<legend><?php echo JText::_( 'SWT_LEAGUE_OVERWRITE_TAB' ); ?></legend> 
					<table width="100%">
						<tr>
							<td width="50%"><?php echo $this->lists['saisons'] ?></td>
							<td width="50%"><?php echo JText::_( 'SWT_LEAGUE_OVERWRITE_SEASONS_TEXT' ); ?></td>
						</tr>
						<tr>
							<td width="50%"><?php echo $this->lists['ligen'] ?></td>
							<td width="50%"><?php echo JText::_( 'SWT_LEAGUE_OVERWRITE_LEAGUE_TEXT' ); ?></td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform"> 
					<legend><?php echo JText::_( 'SWT_LEAGUE_MODE_TAB' ); ?></legend> 
					<table width="100%">
						<tr>
							<td width="50%">
								<select name="mturnier" id="mturnier" value="0" size="1">
									<option value="0" <?php if ($mturnier != 1) { echo 'selected="selected"'; } ?>><?php echo JText::_( 'SWT_MODE_LEAGUE' );?></option>
									<option value="1" <?php if ($mturnier == 1) { echo 'selected="selected"'; } ?>><?php echo JText::_( 'SWT_MODE_MTURN' );?></option>
								</select>
							</td>
							<td width="50%"><?php echo JText::_( 'SWT_MODE_TEXT' ); ?></td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>		
	</table>

	<input type="hidden" name="option" value="com_clm" />
	<input type="hidden" name="view" value="swtliga" />
	<input type="hidden" name="controller" value="swtliga" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="swt" value="<?php echo $swt; ?>" />
	<input type="hidden" name="swt_file" value="<?php echo $swt_file; ?>" />
	<input type="hidden" name="sid" value="<?php echo $sid; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>