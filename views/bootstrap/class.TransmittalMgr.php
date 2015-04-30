<?php
/**
 * Implementation of TransmittalMgr view
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
 * Class which outputs the html page for TransmittalMgr view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_TransmittalMgr extends SeedDMS_Bootstrap_Style {

	function showTransmittalForm($transmittal) { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
?>
	<form action="../op/op.TransmittalMgr.php" method="post" enctype="multipart/form-data" name="form<?php print $transmittal ? $transmittal->getID() : '0';?>" onsubmit="return checkForm('<?php print $transmittal ? $transmittal->getID() : '0';?>');">
<?php
		if($transmittal) {
			echo createHiddenFieldWithKey('edittransmittal');
?>
	<input type="hidden" name="transmittalid" value="<?php print $transmittal->getID();?>">
	<input type="hidden" name="action" value="edittransmittal">
<?php
		} else {
			echo createHiddenFieldWithKey('addtransmittal');
?>
	<input type="hidden" name="action" value="addtransmittal">
<?php
		}
?>
	<table class="table-condensed">
<?php
	if($transmittal) {
?>
		<tr>
			<td></td>
			<td><a class="standardText btn" href="../out/out.RemoveTransmittal.php?transmittalid=<?php print $transmittal->getID();?>"><i class="icon-remove"></i> <?php printMLText("rm_transmittal");?></a></td>
		</tr>
<?php
	}
?>
		<tr>
			<td><?php printMLText("transmittal_name");?>:</td>
			<td><input type="text" name="name" value="<?php print $transmittal ? htmlspecialchars($transmittal->getName()) : "";?>"></td>
		</tr>
		<tr>
			<td><?php printMLText("transmittal_comment");?>:</td>
			<td><input type="text" name="comment" value="<?php print $transmittal ? htmlspecialchars($transmittal->getComment()) : "";?>"></td>
		</tr>
		<tr>
			<td></td>
			<td><button type="submit" class="btn"><i class="icon-save"></i> <?php printMLText($transmittal ? "save" : "add_transmittal")?></button></td>
		</tr>
	</table>
	</form>
<?php
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$seltransmittal = $this->params['seltransmittal'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];

		$db = $dms->getDB();
		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth);

		$this->htmlStartPage(getMLText("my_transmittals"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("my_transmittals"), "my_documents");
		$this->contentHeading(getMLText("my_transmittals"));
?>
<div class="row-fluid">
<div class="span4">
<?php
		$this->contentContainerStart();

		$transmittals = $dms->getAllTransmittals($user);

		if ($transmittals){
			print "<table class=\"table table-condensed\">";
			print "<thead>\n<tr>\n";
			print "<th>".getMLText("name")."</th>\n";
			print "<th>".getMLText("comment")."</th>\n";
			print "<th></th>\n";
			print "</tr>\n</thead>\n<tbody>\n";
			foreach($transmittals as $transmittal) {
				print "<tr>\n";
				print "<td>".$transmittal->getName()."</td>";
				print "<td>".$transmittal->getComment()."</td>";
				print "<td>";
				print "<div class=\"list-action\">";
				print "<a href=\"../out/out.TransmittalMgr.php?transmittalid=".$transmittal->getID()."\" title=\"".getMLText("edit_transmittal_props")."\"><i class=\"icon-edit\"></i></a>";
				print "</div>";
				print "</td>";
				print "</tr>\n";
			}
			print "</tbody>\n</table>\n";
		}

		$this->contentContainerEnd();
?>
</div>
<div class="span8">
<?php
		$this->contentContainerStart();
		$this->showTransmittalForm($seltransmittal);
		$this->contentContainerEnd();
		$items = $seltransmittal->getItems();
		if($items) {
			print "<table class=\"table table-condensed\">";
			print "<thead>\n<tr>\n";
			print "<th>".getMLText("document")."</th>\n";
			print "<th>".getMLText("version")."</th>\n";
			print "<th></th>\n";
			print "</tr>\n</thead>\n<tbody>\n";
			foreach($items as $item) {
				print "<tr>";
				print "<td>";
				$content = $item->getContent();
				$document = $content->getDocument();
				print $content->getVersion();
				print "</td>";
				print "<td>";
				echo $this->documentListRow($document, $previewer, false, $content->getVersion());
				print $item->getDate();
				print "</td>";
				print "</tr>";
			}
			print "</tbody>\n</table>\n";
		}
?>
</div>
</div>
<?php
		$this->htmlEndPage();
	} /* }}} */
}
?>
