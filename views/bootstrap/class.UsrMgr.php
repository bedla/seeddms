<?php
/**
 * Implementation of UsrMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for UsrMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_UsrMgr extends SeedDMS_Bootstrap_Style {

	function showUserForm($currUser) { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$seluser = $this->params['seluser'];
		$groups = $this->params['allgroups'];
		$passwordstrength = $this->params['passwordstrength'];
		$passwordexpiration = $this->params['passwordexpiration'];
		$httproot = $this->params['httproot'];
		$enableuserimage = $this->params['enableuserimage'];
		$undeluserids = $this->params['undeluserids'];
		$workflowmode = $this->params['workflowmode'];
		$quota = $this->params['quota'];
?>
	<form action="../op/op.UsrMgr.php" method="post" enctype="multipart/form-data" name="form<?php print $currUser ? $currUser->getID() : '0';?>" onsubmit="return checkForm('<?php print $currUser ? $currUser->getID() : '0';?>');">
<?php
		if($currUser) {
			echo createHiddenFieldWithKey('edituser');
?>
	<input type="hidden" name="userid" value="<?php print $currUser->getID();?>">
	<input type="hidden" name="action" value="edituser">
<?php
		} else {
			echo createHiddenFieldWithKey('adduser');
?>
	<input type="hidden" name="action" value="adduser">
<?php
		}
?>
	<table class="table-condensed">
<?php
	if($currUser && !in_array($currUser->getID(), $undeluserids)) {
?>
		<tr>
			<td></td>
			<td><a class="standardText btn" href="../out/out.RemoveUser.php?userid=<?php print $currUser->getID();?>"><i class="icon-remove"></i> <?php printMLText("rm_user");?></a></td>
		</tr>
<?php
	}
?>
		<tr>
			<td><?php printMLText("user_login");?>:</td>
			<td><input type="text" name="login" value="<?php print $currUser ? htmlspecialchars($currUser->getLogin()) : "";?>"></td>
		</tr>
		<tr>
			<td><?php printMLText("password");?>:</td>
			<td><input type="password" class="pwd" rel="strengthbar<?php echo $currUser ? $currUser->getID() : "0"; ?>" name="pwd"></td>
		</tr>
<?php
		if($passwordstrength > 0) {
?>
		<tr>
			<td><?php printMLText("password_strength");?>:</td>
			<td>
				<div id="strengthbar<?php echo $currUser ? $currUser->getID() : "0"; ?>" class="progress" style="width: 220px; height: 30px; margin-bottom: 8px;"><div class="bar bar-danger" style="width: 0%;"></div></div>
			</td>
		</tr>
<?php
		}
?>
		<tr>
			<td><?php printMLText("confirm_pwd");?>:</td>
			<td><input type="Password" name="pwdconf"></td>
		</tr>
<?php
	if($passwordexpiration > 0) {
?>
		<tr>
			<td><?php printMLText("password_expiration");?>:</td>
			<td><select name="pwdexpiration"><?php if($currUser) { ?><option value=""><?php printMLText("keep");?></option><?php } ?><option value="<?php echo date('Y-m-d H:i:s'); ?>"><?php printMLText("now");?></option><option value="<?php echo date('Y-m-d H:i:s', time()+$passwordexpiration*86400); ?>"><?php printMLText("according_settings");?></option><option value="0000-00-00"><?php printMLText("never");?></option></select> <?php echo $currUser ? $currUser->getPwdExpiration() : ""; ?></td>
		</tr>
<?php
	}
?>
		<tr>
			<td><?php printMLText("user_name");?>:</td>
			<td><input type="text" name="name" value="<?php print $currUser ? htmlspecialchars($currUser->getFullName()) : "";?>"></td>
		</tr>
		<tr>
			<td><?php printMLText("email");?>:</td>
			<td><input type="text" name="email" value="<?php print $currUser ? htmlspecialchars($currUser->getEmail()) : "";?>"></td>
		</tr>
		<tr>
			<td><?php printMLText("comment");?>:</td>
			<td><textarea name="comment" rows="4" cols="50"><?php print $currUser ? htmlspecialchars($currUser->getComment()) : "";?></textarea></td>
		</tr>
		<tr>
			<td><?php printMLText("role");?>:</td>
			<td><select name="role"><option value="<?php echo SeedDMS_Core_User::role_user ?>"><?php printMLText("role_user"); ?></option><option value="<?php echo SeedDMS_Core_User::role_admin ?>" <?php if($currUser && $currUser->getRole() == SeedDMS_Core_User::role_admin) echo "selected"; ?>><?php printMLText("role_admin"); ?></option><option value="<?php echo SeedDMS_Core_User::role_guest ?>" <?php if($currUser && $currUser->getRole() == SeedDMS_Core_User::role_guest) echo "selected"; ?>><?php printMLText("role_guest"); ?></option></select></td>
		</tr>
		<tr>
			<td><?php printMLText("groups");?>:</td>
			<td><select class="chzn-select" multiple="multiple" name="groups[]" data-placeholder="<?php printMLText('select_groups'); ?>">
<?php
		foreach($groups as $group) {
			echo '<option value="'.$group->getID().'"'.($currUser && $group->isMember($currUser) ? ' selected' : '').'>'.$group->getName().'</option>';
		}
?>
			</select></td>
		</tr>
		<tr>
			<td><?php printMLText("home_folder")?>:</td>
			<td><?php $this->printFolderChooser("form".($currUser ? $currUser->getId() : '0'), M_READ, -1, $currUser ? $dms->getFolder($currUser->getHomeFolder()) : 0, 'homefolder');?></td>
		</tr>
		<tr>
			<td><?php printMLText("quota");?>:</td>
			<td><input type="text" name="quota" value="<?php echo $currUser ? $currUser->getQuota() : ""; ?>">
<?php
	if($quota > 0)
		echo $this->warningMsg(getMLText('current_quota', array('quota'=>SeedDMS_Core_File::format_filesize($quota))));
	else
		echo $this->warningMsg(getMLText('quota_is_disabled'));
?>
			</td>
		</tr>
		<tr>
			<td><?php printMLText("is_hidden");?>:</td>
			<td><input type="checkbox" name="ishidden" value="1"<?php print ($currUser && $currUser->isHidden() ? " checked='checked'" : "");?>></td>
		</tr>
		<tr>
			<td><?php printMLText("is_disabled");?>:</td>
			<td><input type="checkbox" name="isdisabled" value="1"<?php print ($currUser && $currUser->isDisabled() ? " checked='checked'" : "");?>></td>
		</tr>

<?php if ($enableuserimage){ ?>

<?php if ($currUser) { ?>
		<tr>
			<td><?php printMLText("user_image");?>:</td>
			<td>
<?php
					if ($currUser->hasImage())
						print "<img src=\"".$httproot . "out/out.UserImage.php?userid=".$currUser->getId()."\">";
					else
						printMLText("no_user_image");
?>
			</td>
		</tr>
		<tr>
			<td><?php printMLText("new_user_image");?>:</td>
			<td>
<?php
	$this->printFileChooser('userfile', false, "image/jpeg");
?>
			</td>
		</tr>
<?php } else { ?>
		<tr>
			<td><?php printMLText("user_image");?>:</td>
			<td>
<?php
	$this->printFileChooser('userfile', false, 'image/jpeg');
?>
			</td>
		</tr>
<?php } ?>

<?php
		}
		if($workflowmode == "traditional" || $workflowmode == 'traditional_only_approval') {
		if($workflowmode == "traditional") {
?>

		<tr>
			<td colspan="2"><?php printMLText("reviewers");?>:</td>
		</tr>
		<tr>
			<td>
				<div class="cbSelectTitle"><?php printMLText("individuals");?>:</div>
			</td>
			<td>
        <select class="chzn-select" name="usrReviewers[]" multiple="multiple">
<?php

				if($currUser)
					$res=$currUser->getMandatoryReviewers();
				else
					$res = array();

				foreach ($users as $usr) {

					if ($usr->isGuest() || ($currUser && $usr->getID() == $currUser->getID()))
						continue;

					$checked=false;
					foreach ($res as $r) if ($r['reviewerUserID']==$usr->getID()) $checked=true;

					print "<option value=\"".$usr->getID()."\" ".($checked?"selected='selected' ":"").">". htmlspecialchars($usr->getLogin()." - ".$usr->getFullName())."</option>";
				}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<div class="cbSelectTitle"><?php printMLText("groups");?>:</div>
			</td>
			<td>
        <select class="chzn-select" name="grpReviewers[]" multiple="multiple" data-placeholder="<?php printMLText('select_groups'); ?>">
<?php
				foreach ($groups as $grp) {

					$checked=false;
					foreach ($res as $r) if ($r['reviewerGroupID']==$grp->getID()) $checked=true;

					print "<option value=\"".$grp->getID()."\" ".($checked?"selected='selected' ":"").">". htmlspecialchars($grp->getName())."</option>";
				}
?>
				</select>
			</td>
		</tr>
<?php
				}
?>
		<tr>
			<td colspan="2"><?php printMLText("approvers");?>:</td>
		</tr>
		<tr>
			<td>
				<div class="cbSelectTitle"><?php printMLText("individuals");?>:</div>
			</td>
			<td>
        <select class="chzn-select" name="usrApprovers[]" multiple="multiple" data-placeholder="<?php printMLText('select_users'); ?>">
<?php
				if($currUser)
					$res=$currUser->getMandatoryApprovers();
				else
					$res = array();
				foreach ($users as $usr) {
					if ($usr->isGuest() || ($currUser && $usr->getID() == $currUser->getID()))
						continue;

					$checked=false;
					foreach ($res as $r) if ($r['approverUserID']==$usr->getID()) $checked=true;

					print "<option value=\"".$usr->getID()."\" ".($checked?"selected='selected' ":"").">". htmlspecialchars($usr->getLogin()." - ".$usr->getFullName())."</option>";
				}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<div class="cbSelectTitle"><?php printMLText("groups");?>:</div>
			</td>
			<td>
        <select class="chzn-select" name="grpApprovers[]" multiple="multiple" data-placeholder="<?php printMLText('select_groups'); ?>">
<?php
				foreach ($groups as $grp) {

					$checked=false;
					foreach ($res as $r) if ($r['approverGroupID']==$grp->getID()) $checked=true;

					print "<option value=\"".$grp->getID()."\" ".($checked?"selected='selected' ":"").">". htmlspecialchars($grp->getName())."</option>";
				}
?>
				</select>
			</td>
		</tr>
<?php
		} else {
			$workflows = $dms->getAllWorkflows();
			if($workflows) {
?>
		<tr>
			<td>
				<div class="cbSelectTitle"><?php printMLText("workflow");?>:</div>
			</td>
			<td>
        <select name="workflow" data-placeholder="<?php printMLText('select_workflow'); ?>">
<?php
				print "<option value=\"\">"."</option>";
				foreach ($workflows as $workflow) {
					print "<option value=\"".$workflow->getID()."\"";
					if($currUser && $currUser->getMandatoryWorkflow() && $currUser->getMandatoryWorkflow()->getID() == $workflow->getID())
						echo " selected=\"selected\"";
					print ">". htmlspecialchars($workflow->getName())."</option>";
				}
?>
				</select>
			</td>
		</tr>
<?php
			}
		}
?>
		<tr>
			<td></td>
			<td><button type="submit" class="btn"><i class="icon-save"></i> <?php printMLText($currUser ? "save" : "add_user")?></button></td>
		</tr>
	</table>
	</form>
<?php
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$seluser = $this->params['seluser'];
		$users = $this->params['allusers'];
		$groups = $this->params['allgroups'];
		$passwordstrength = $this->params['passwordstrength'];
		$passwordexpiration = $this->params['passwordexpiration'];
		$httproot = $this->params['httproot'];
		$enableuserimage = $this->params['enableuserimage'];
		$undeluserids = $this->params['undeluserids'];
		$workflowmode = $this->params['workflowmode'];
		$quota = $this->params['quota'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");

?>
<script language="JavaScript">

function checkForm(num)
{
	msg = new Array();
	eval("var formObj = document.form" + num + ";");

	if (formObj.login.value == "") msg.push("<?php printMLText("js_no_login");?>");
	if ((num == '0') && (formObj.pwd.value == "")) msg.push("<?php printMLText("js_no_pwd");?>");
	if ((formObj.pwd.value != formObj.pwdconf.value)&&(formObj.pwd.value != "" )&&(formObj.pwd.value != "" )) msg.push("<?php printMLText("js_pwd_not_conf");?>");
	if (formObj.name.value == "") msg.push("<?php printMLText("js_no_name");?>");
	if (formObj.email.value == "") msg.push("<?php printMLText("js_no_email");?>");
	//if (formObj.comment.value == "") msg.push("<?php printMLText("js_no_comment");?>");
	if (msg != "")
	{
  	noty({
  		text: msg.join('<br />'),
  		type: 'error',
      dismissQueue: true,
  		layout: 'topRight',
  		theme: 'defaultTheme',
			_timeout: 1500,
  	});
		return false;
	}
	else
		return true;
}


obj = -1;
function showUser(selectObj) {
	if (obj != -1)
		obj.style.display = "none";

	id = selectObj.options[selectObj.selectedIndex].value;
	if (id == -1)
		return;

	obj = document.getElementById("keywords" + id);
	obj.style.display = "";
}
</script>
<?php
		$this->contentHeading(getMLText("user_management"));
?>

<div class="row-fluid">
<div class="span4">
<div class="well">
<?php echo getMLText("selection")?>:
<select class="chzn-select" onchange="showUser(this)" id="selector" class="span9">
<option value="-1"><?php echo getMLText("choose_user")?>
<option value="0"><?php echo getMLText("add_user")?>
<?php
		$selected=0;
		$count=2;
		foreach ($users as $currUser) {
			if ($seluser && $currUser->getID()==$seluser->getID()) $selected=$count;
			print "<option value=\"".$currUser->getID()."\">" . htmlspecialchars($currUser->getLogin() . " - ". $currUser->getFullName());
			$count++;
		}
?>
</select>
</div>
</div>

<div class="span8">
	<div class="well">
		<div id="keywords0" style="display : none;">
		<?php $this->showUserForm(false); ?>
		</div>

<?php
		foreach ($users as $currUser) {
			print "<div id=\"keywords".$currUser->getID()."\" style=\"display : none;\">";
			$this->showUserForm($currUser);
			print "</div>\n";
		}
?>
	</div>
	</div>
</div>

<script language="JavaScript">

sel = document.getElementById("selector");
sel.selectedIndex=<?php print $selected ?>;
showUser(sel);

</script>


<?php
		$this->htmlEndPage();
	} /* }}} */
}
?>
