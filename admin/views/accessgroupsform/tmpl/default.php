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
defined('_JEXEC') or die('Restricted access');

	//BE-Parameter aufbereiten
	$be_paramsStringArray = explode("\n", $this->accessgroup->be_params);
	$this->accessgroup->be_params = array();
	foreach ($be_paramsStringArray as $value) {
		$ipos = strpos ($value, '=');
		if ($ipos !== false) {
			$this->accessgroup->be_params[substr($value,0,$ipos)] = substr($value,$ipos+1);
		}
	}	
?>

	<script language="javascript" type="text/javascript">

	<?php if (JVersion::isCompatible("1.6.0")) { ?>
		 Joomla.submitbutton = function (pressbutton) { 
	<?php } else { ?>
		 function submitbutton(pressbutton) {
	<?php } ?>		
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo JText::_( 'ACCESSGROUP_HINT_NAME_MISSING', true ); ?>" );
			 <?php foreach ($this->accessgroups as $agroup) { ?>		
			} else if (form.name.value == "<?php echo $agroup->name; ?>") {
				alert( "<?php echo JText::_( 'ACCESSGROUP_HINT_NAME_EXISTING', true ); ?>" );
			  <?php } ?>
			} else if (form.usertype.value == "") {
				alert( "<?php echo JText::_( 'ACCESSGROUP_HINT_GROUPTYPE_MISSING', true ); ?>" );
			 <?php foreach ($this->accessgroups as $agroup) { ?>		
			} else if (form.usertype.value == "<?php echo $agroup->usertype; ?>") {
				alert( "<?php echo JText::_( 'ACCESSGROUP_HINT_GROUPTYPE_EXISTING', true ); ?>" );
			  <?php } ?>
			} else if (form.user_clm.value == "") {
				alert( "<?php echo JText::_( 'ACCESSGROUP_HINT_USER_MISSING', true ); ?>" ); 
			 <?php foreach ($this->accessgroups as $agroup) { ?>		
			} else if (form.user_clm.value == "<?php echo $agroup->user_clm; ?>") {
				alert( "<?php echo JText::_( 'ACCESSGROUP_HINT_USER_EXISTING', true ); ?>" );
			  <?php } ?>
			} else if (form.kind.value == "CLM") {
				alert( "<?php echo JText::_( 'ACCESSGROUP_HINT_KIND_CLM', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		  
		</script>

<form action="index.php" method="post" name="adminForm" id="adminForm"> 
	<table class="admintable"> 
		<tr>
			<td width="50%" style="vertical-align: top;">
				<fieldset class="adminform"> 
					<legend><?php echo JText::_( 'ACCESSGROUP_BASICS' ); ?></legend> 
					<table class="admintable">
						<tr> 
							<td width="100" align="right" class="key"> 
								<label for="name"><?php echo JText::_( 'ACCESSGROUP_NAME' ); ?></label> 
							</td> 
							<td> 
								<input class="inputbox" type="text" name="name" 
								id="name" size="48" maxlength="255" 
								value="<?php echo $this->accessgroup->name;?>" /> 
							</td> 
						</tr> 
						<tr> 
							<td width="100" align="right" class="key"> 
								<label for="usertype">
								<span class="editlinktip hasTip">
								<?php echo JText::_( 'ACCESSGROUP_USERTYPE' ); ?></span></label> 
							</td> 
							<td> 
								<input class="inputbox" type="text" name="usertype" 
								id="usertype" size="15" maxlength="15" 
								value="<?php echo $this->accessgroup->usertype;?>" /> 
							</td> 
						</tr>
						<tr> 
							<td width="100" align="right" class="key"> 
								<label for="user_clm">
								<span class="editlinktip hasTip" title="<?php echo JText::_( 'ACCESSGROUP_USER_HINT' );?>">
								<?php echo JText::_( 'ACCESSGROUP_USER' ); ?></span></label> 
							</td> 
							<td> 
								<input class="inputbox" type="text" name="user_clm" 
								id="user_clm" size="4" maxlength="4" 
								value="<?php echo $this->accessgroup->user_clm;?>" /> 
							</td> 
						</tr>
						<tr> 
							<td width="100" align="right" class="key"> 
								<label for="kind">
								<span class="editlinktip hasTip" title="<?php echo JText::_( 'ACCESSGROUP_KIND_HINT' );?>">
								<?php echo JText::_( 'ACCESSGROUP_KIND' ); ?></span></label> 
							</td> 
							<td> 
								<input class="inputbox" type="text" name="kind" 
								id="kind" size="4" maxlength="4" 
								value="<?php echo $this->accessgroup->kind;?>" /> 
							</td> 
						</tr>
						<tr> 
							<td width="100" align="right" class="key"> 
								<label for="published"><?php echo JText::_( 'JPUBLISHED' ); ?></label> 
							</td> 
							<td><fieldset class="radio"> 
								<?php  echo $this->lists['published'];?> 
							</fieldset></td> 
						</tr>
						<?php if ($this->accessgroup->kind == 'CLM') { ?>
						<tr> 
							<td width="100" align="right" class="key" colspan="1"> 
								<label for="comment"><?php echo JText::_( 'ACCESSGROUP_COMMENT' ); ?></label> 
							</td> 
							<td width="100" valign="top" colspan="1">
								<textarea class="text" name="comment" id="comment" cols="35" rows="4"><?php echo JText::_( 'ACCESSGROUP_COMMENT_'.$this->accessgroup->usertype );?></textarea>
							</td>
						</tr>
						<?php } ?>
						
					</table> 
				</fieldset>

			</td>
			<td width="50%" style="vertical-align: top;">
				<fieldset class="adminform"> 
					<legend><?php echo JText::_( 'ACCESSGROUP_BE_DETAILS' ); ?></legend> 
					<table class="admintable">
						<?php  foreach ($this->accesspoints as $apoint) { 
								if ($apoint->area == 'BE') {
									$pname = $apoint->area.'_'.$apoint->accesstopic.'_'.$apoint->accesspoint;
									$tname = 'TAP_'.$pname;
									if (!isset($this->accessgroup->be_params[$pname])) $this->accessgroup->be_params[$pname] = 0;
						?> 
							<tr> 
								<td width="100" align="right" class="key"> 
									<label for="<?php echo $pname; ?>"><?php echo JText::_( $tname ); ?></label> 
									</td><td>
									<select name="be_params[<?php echo $pname; ?>]" id="be_params[<?php echo $pname; ?>]" value="<?php echo $this->accessgroup->be_params[$pname]; ?>" size="1">
									<option value="0" <?php if ($this->accessgroup->be_params[$pname] == 0) {echo 'selected="selected"';}  ?>><?php echo JText::_( 'ACCESS_VALUE_0' );?></option>
									<option value="1" <?php if ($this->accessgroup->be_params[$pname] == 1) {echo 'selected="selected"';}  ?>><?php echo JText::_( 'ACCESS_VALUE_1' );?></option>
									<?php if ($apoint->rule != "NY") {
											$access2 = 'ACCESS_VALUE_2'.substr($apoint->rule,2,1); ?>
									<option value="2" <?php if ($this->accessgroup->be_params[$pname] == 2) {echo 'selected="selected"';}  ?>><?php echo JText::_( $access2 );?></option>
									<?php } ?>		
									</select>
								</td>	
							</tr>
							<?php } } ?>
					</table> 
				</fieldset>
			</td>
		</tr>
    </table> 
	<div class="clr"></div> 
	<input type="hidden" name="option" value="com_clm" />
	<input type="hidden" name="view" value="accessgroupsform" />
	<input type="hidden" name="id" value="<?php echo $this->accessgroup->id; ?>" />
	<input type="hidden" name="ordering" value="<?php echo $this->accessgroup->ordering; ?>" />
	<input type="hidden" name="controller" value="accessgroupsform" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_( 'form.token' ); ?>

</form>
 