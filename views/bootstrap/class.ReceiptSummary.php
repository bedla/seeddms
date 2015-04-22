<?php
/**
 * Implementation of ReceiptSummary view
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
 * Class which outputs the html page for ReceiptSummary view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_ReceiptSummary extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];

		$this->htmlStartPage(getMLText("my_documents"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("my_documents"), "my_documents");

		$this->contentHeading(getMLText("receipt_summary"));
		$this->contentContainerStart();

		// TODO: verificare scadenza

		// Get document list for the current user.
		$receiptStatus = $user->getReceiptStatus();

		// reverse order
		$receiptStatus["indstatus"]=array_reverse($receiptStatus["indstatus"],true);
		$receiptStatus["grpstatus"]=array_reverse($receiptStatus["grpstatus"],true);

		$printheader=true;
		$iRev = array();
		foreach ($receiptStatus["indstatus"] as $st) {
			$document = $dms->getDocument($st['documentID']);
			if($document)
				$version = $document->getContentByVersion($st['version']);
			$owner = $document->getOwner();
			$moduser = $dms->getUser($st['required']);

			if ($document && $version) {
			
				if ($printheader){
					print "<table class=\"table-condensed\">";
					print "<thead>\n<tr>\n";
					print "<th>".getMLText("name")."</th>\n";
					print "<th>".getMLText("owner")."</th>\n";
					print "<th>".getMLText("status")."</th>\n";
					print "<th>".getMLText("version")."</th>\n";
					print "<th>".getMLText("last_update")."</th>\n";
					print "<th>".getMLText("expires")."</th>\n";
					print "</tr>\n</thead>\n<tbody>\n";
					$printheader=false;
				}
			
				print "<tr>\n";
				print "<td><a href=\"out.DocumentVersionDetail.php?documentid=".$st["documentID"]."&version=".$st["version"]."\">".htmlspecialchars($document->getName())."</a></td>";
				print "<td>".htmlspecialchars($owner->getFullName())."</td>";
				print "<td>".getOverallStatusText($st["status"])."</td>";
				print "<td>".$st["version"]."</td>";
				print "<td>".$st["date"]." ". htmlspecialchars($moduser->getFullName()) ."</td>";
				print "<td>".(!$document->expires() ? "-":getReadableDate($document->getExpires()))."</td>";				
				print "</tr>\n";
			}
			if ($st["status"]!=-2) {
				$iRev[] = $st["documentID"];
			}
		}
		if (!$printheader) {
			echo "</tbody>\n</table>";
		} else {
			printMLText("no_docs_to_receipt");
		}

		$this->contentContainerEnd();
		$this->contentHeading(getMLText("group_receipt_summary"));
		$this->contentContainerStart();

		$printheader=true;
		foreach ($receiptStatus["grpstatus"] as $st) {
			$document = $dms->getDocument($st['documentID']);
			if($document)
				$version = $document->getContentByVersion($st['version']);
			$owner = $document->getOwner();
			$modgroup = $dms->getGroup($st['required']);

			if (!in_array($st["documentID"], $iRev) && $document && $version) {
			
				if ($printheader){
					print "<table class=\"folderView\">";
					print "<thead>\n<tr>\n";
					print "<th>".getMLText("name")."</th>\n";
					print "<th>".getMLText("owner")."</th>\n";
					print "<th>".getMLText("status")."</th>\n";
					print "<th>".getMLText("version")."</th>\n";
					print "<th>".getMLText("last_update")."</th>\n";
					print "<th>".getMLText("expires")."</th>\n";
					print "</tr>\n</thead>\n<tbody>\n";
					$printheader=false;
				}		
			
				print "<tr>\n";
				print "<td><a href=\"out.DocumentVersionDetail.php?documentid=".$st["documentID"]."&version=".$st["version"]."\">".htmlspecialchars($document->getName())."</a></td>";
				print "<td>".htmlspecialchars($owner->getFullName())."</td>";
				print "<td>".getOverallStatusText($st["status"])."</td>";
				print "<td>".$st["version"]."</td>";
				print "<td>".$st["date"]." ". htmlspecialchars($modgroup->getName()) ."</td>";
				print "<td>".(!$document->expires() ? "-":getReadableDate($document->getExpires()))."</td>";				
				print "</tr>\n";
			}
		}
		if (!$printheader) {
			echo "</tbody>\n</table>";
		}else{
			printMLText("empty_notify_list");
		}


		$this->contentContainerEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
