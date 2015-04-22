<?php
/**
 * Implementation of SetRevisers view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2015 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for SetRevisers view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2015 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_SetRevisers extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$document = $this->params['document'];
		$content = $this->params['version'];

		$overallStatus = $content->getStatus();

		$this->htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true, $document), "view_document", $document);
		$this->contentHeading(getMLText("change_assignments"));

		// Retrieve a list of all users and groups that have revision privileges.
		$docAccess = $document->getReadAccessList();

		// Retrieve list of currently assigned revisers, along with
		// their latest status.
		$revisionStatus = $content->getRevisionStatus();
		$startdate = '2015-05-23';

		// Index the revision results for easy cross-reference with the reviser list.
		$revisionIndex = array("i"=>array(), "g"=>array());
		foreach ($revisionStatus as $i=>$rs) {
			if ($rs["type"]==0) {
				$revisionIndex["i"][$rs["required"]] = array("status"=>$rs["status"], "idx"=>$i);
			} elseif ($rs["type"]==1) {
				$revisionIndex["g"][$rs["required"]] = array("status"=>$rs["status"], "idx"=>$i);
			}
		}
?>

<?php $this->contentContainerStart(); ?>

<form action="../op/op.SetRevisers.php" method="post" name="form1">

<?php $this->contentSubHeading(getMLText("update_revisers"));?>

  <span class="input-append date" style="display: inline;" id="revisionstartdate" data-date="<?php echo date('d-m-Y'); ?>" data-date-format="dd-mm-yyyy" data-date-language="<?php echo str_replace('_', '-', $this->params['session']->getLanguage()); ?>">
    <input class="span4" size="16" name="startdate" type="text" value="<?php if($startdate) echo $startdate; else echo date('d-m-Y'); ?>">
    <span class="add-on"><i class="icon-calendar"></i></span>
  </span>

  <div class="cbSelectTitle"><?php printMLText("individuals")?>:</div>
  <select class="chzn-select span9" name="indRevisers[]" multiple="multiple" data-placeholder="<?php printMLText('select_ind_revisers'); ?>" data-no_results_text="<?php printMLText('unknown_owner'); ?>">
<?php

		foreach ($docAccess["users"] as $usr) {
			if (isset($revisionIndex["i"][$usr->getID()])) {

				switch ($revisionIndex["i"][$usr->getID()]["status"]) {
					case 0:
						print "<option value='". $usr->getID() ."' selected='selected'>".htmlspecialchars($usr->getLogin() . " - ". $usr->getFullName())."</option>";
						break;
					case -2:
						print "<option value='". $usr->getID() ."'>".htmlspecialchars($usr->getLogin() . " - ". $usr->getFullName())."</option>";
						break;
					default:
						print "<option value='". $usr->getID() ."' disabled='disabled'>".htmlspecialchars($usr->getLogin() . " - ". $usr->getFullName())."</option>";
						break;
				}
			} else {
				print "<option value='". $usr->getID() ."'>". htmlspecialchars($usr->getLogin() . " - ". $usr->getFullName())."</option>";
			}
		}
?>
  </select>

  <div class="cbSelectTitle"><?php printMLText("groups")?>:</div>
  <select class="chzn-select span9" name="grpRevisers[]" multiple="multiple" data-placeholder="<?php printMLText('select_grp_revisers'); ?>" data-no_results_text="<?php printMLText('unknown_group'); ?>">
<?php
		foreach ($docAccess["groups"] as $group) {
			if (isset($revisionIndex["g"][$group->getID()])) {
				switch ($revisionIndex["g"][$group->getID()]["status"]) {
					case 0:
						print "<option value='". $group->getID() ."' selected='selected'>".htmlspecialchars($group->getName())."</option>";
						break;
					case -2:
						print "<option value='". $group->getID() ."'>".htmlspecialchars($group->getName())."</option>";
						break;
					default:
						print "<option id='recGrp".$group->getID()."' type='checkbox' name='grpRevisers[]' value='". $group->getID() ."' disabled='disabled'>".htmlspecialchars($group->getName())."</option>";
						break;
				}
			} else {
				print "<option value='". $group->getID() ."'>".htmlspecialchars($group->getName())."</option>";
			}
		}
?>
  </select>

<p>
<input type='hidden' name='documentid' value='<?php echo $document->getID() ?>'/>
<input type='hidden' name='version' value='<?php echo $content->getVersion() ?>'/>
<input type="submit" class="btn" value="<?php printMLText("update");?>">
</p>
</form>
<?php
		$this->contentContainerEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
