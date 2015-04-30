<?php
/**
 * Implementation of ReceiptDocument view
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
 * Class which outputs the html page for ReceiptDocument view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_ReceiptDocument extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$document = $this->params['document'];
		$content = $this->params['version'];

		$receipts = $content->getReceiptStatus();
		foreach($receipts as $receipt) {
			if($receipt['receiptID'] == $_GET['receiptid']) {
				$receiptStatus = $receipt;
				break;
			}
		}

		$this->htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true, $document), "view_document", $document);
		$this->contentHeading(getMLText("submit_receipt"));
?>
<script language="JavaScript">
function checkIndForm()
{
	msg = new Array();
	if (document.form1.receiptStatus.value == "") msg.push("<?php printMLText("js_no_receipt_status");?>");
	if (document.form1.comment.value == "") msg.push("<?php printMLText("js_no_comment");?>");
	if (msg != "") {
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
function checkGrpForm()
{
	msg = "";
	if (document.form1.receiptGroup.value == "") msg += "<?php printMLText("js_no_receipt_group");?>\n";
	if (document.form1.receiptStatus.value == "") msg += "<?php printMLText("js_no_receipt_status");?>\n";
	if (document.form1.comment.value == "") msg += "<?php printMLText("js_no_comment");?>\n";
	if (msg != "")
	{
		alert(msg);
		return false;
	}
	else
		return true;
}
</script>

<?php
		$this->contentContainerStart();

		// Display the Receipt form.
		if ($receiptStatus['type'] == 0) {
			if($receiptStatus["status"]!=0) {

				print "<table class=\"folderView\"><thead><tr>";
				print "<th>".getMLText("status")."</th>";
				print "<th>".getMLText("comment")."</th>";
				print "<th>".getMLText("last_update")."</th>";
				print "</tr></thead><tbody><tr>";
				print "<td>";
				printReceiptStatusText($receiptStatus["status"]);
				print "</td>";
				print "<td>".htmlspecialchars($receiptStatus["comment"])."</td>";
				$indUser = $dms->getUser($receiptStatus["userID"]);
				print "<td>".$receiptStatus["date"]." - ". htmlspecialchars($indUser->getFullname()) ."</td>";
				print "</tr></tbody></table><br>";
			}
?>
	<form method="post" action="../op/op.ReceiptDocument.php" name="form1" onsubmit="return checkIndForm();">
	<?php echo createHiddenFieldWithKey('receiptdocument'); ?>
	<table class="table-condensed">
		<tr>
			<td><?php printMLText("comment")?>:</td>
			<td><textarea name="comment" cols="80" rows="4"></textarea></td>
		</tr>
		<tr>
			<td><?php printMLText("receipt_status")?></td>
			<td>
				<select name="receiptStatus">
<?php if($receiptStatus['status'] != 1) { ?>
					<option value='1'><?php printMLText("status_receipted")?></option>
<?php } ?>
<?php if($receiptStatus['status'] != -1) { ?>
					<option value='-1'><?php printMLText("rejected")?></option>
<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' class="btn" name='indReceipt' value='<?php printMLText("submit_receipt")?>'/></td>
		</tr>
	</table>
	<input type='hidden' name='receiptType' value='ind'/>
	<input type='hidden' name='documentid' value='<?php echo $document->getID() ?>'/>
	<input type='hidden' name='version' value='<?php echo $content->getVersion() ?>'/>
	</form>
<?php
		}
		else if ($receiptStatus['type'] == 1) {

			if($receiptStatus["status"]!=0) {

				print "<table class=\"folderView\"><thead><tr>";
				print "<th>".getMLText("status")."</th>";
				print "<th>".getMLText("comment")."</th>";
				print "<th>".getMLText("last_update")."</th>";
				print "</tr></thead><tbody><tr>";
				print "<td>";
				printReceiptStatusText($receiptStatus["status"]);
				print "</td>";
				print "<td>".htmlspecialchars($receiptStatus["comment"])."</td>";
				$indUser = $dms->getUser($receiptStatus["userID"]);
				print "<td>".$receiptStatus["date"]." - ". htmlspecialchars($indUser->getFullname()) ."</td>";
				print "</tr></tbody></table><br>\n";
			}

?>
	<form method="post" action="../op/op.ReceiptDocument.php" name="form1" onsubmit="return checkGrpForm();">
	<?php echo createHiddenFieldWithKey('receiptdocument'); ?>
	<table class="table-condensed">
		<tr>
			<td><?php printMLText("comment")?>:</td>
			<td><textarea name="comment" cols="80" rows="4"></textarea></td>
		</tr>
		<tr>
			<td><?php printMLText("receipt_status")?>:</td>
			<td>
				<select name="receiptStatus">
<?php if($receiptStatus['status'] != 1) { ?>
					<option value='1'><?php printMLText("status_receipted")?></option>
<?php } ?>
<?php if($receiptStatus['status'] != -1) { ?>
					<option value='-1'><?php printMLText("rejected")?></option>
<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' class="btn" name='groupReceipt' value='<?php printMLText("submit_receipt")?>'/></td>
		</tr>
	</table>
	<input type='hidden' name='receiptType' value='grp'/>
	<input type='hidden' name='receiptGroup' value='<?php echo $receiptStatus['required']; ?>'/>
	<input type='hidden' name='documentid' value='<?php echo $document->getID() ?>'/>
	<input type='hidden' name='version' value='<?php echo $content->getVersion() ?>'/>
	</form>
<?php
		}
		$this->contentContainerEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
