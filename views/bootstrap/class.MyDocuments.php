<?php
/**
 * Implementation of MyDocuments view
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
 * Class which outputs the html page for MyDocuments view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_MyDocuments extends SeedDMS_Bootstrap_Style {

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$showInProcess = $this->params['showinprocess'];
		$cachedir = $this->params['cachedir'];
		$workflowmode = $this->params['workflowmode'];
		$previewwidth = $this->params['previewWidthList'];

		$db = $dms->getDB();
		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth);

		$this->htmlStartPage(getMLText("my_documents"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("my_documents"), "my_documents");

		if ($showInProcess){

			if (!$db->createTemporaryTable("ttstatid") || !$db->createTemporaryTable("ttcontentid")) {
				$this->contentHeading(getMLText("warning"));
				$this->contentContainer(getMLText("internal_error_exit"));
				$this->htmlEndPage();
				exit;
			}

			if($workflowmode == 'traditional' || $workflowmode == 'traditional_only_approval') {
				
				// Get document list for the current user.
				$reviewStatus = $user->getReviewStatus();
				$approvalStatus = $user->getApprovalStatus();

				$resArr = $dms->getDocumentList('AppRevByMe', $user);
				if (is_bool($resArr) && !$resArr) {
					$this->contentHeading(getMLText("warning"));
					$this->contentContainer(getMLText("internal_error_exit"));
					$this->htmlEndPage();
					exit;
				}
				if($resArr) {
					/* Create an array to hold all of these results, and index the array 
					 * by document id. This makes it easier to retrieve document ID
					 * information later on and saves us having to repeatedly poll the
					 * database every time
					 * new document information is required.
					 */
					$docIdx = array();
					foreach ($resArr as $res) {
						
						/* verify expiry */
						if ( $res["expires"] && time()>$res["expires"]+24*60*60 ){
							if  ( $res["status"]==S_DRAFT_APP || $res["status"]==S_DRAFT_REV ){
								$res["status"]=S_EXPIRED;
							}
						}

						$docIdx[$res["id"]][$res["version"]] = $res;
					}

					// List the documents for which a review has been requested.
					if($workflowmode == 'traditional') {
						$this->contentHeading(getMLText("documents_to_review"));
						$this->contentContainerStart();
						$printheader=true;
						$iRev = array();
						$dList = array();
						foreach ($reviewStatus["indstatus"] as $st) {
						
							if ( $st["status"]==0 && isset($docIdx[$st["documentID"]][$st["version"]]) && !in_array($st["documentID"], $dList) ) {
								$dList[] = $st["documentID"];
								$document = $dms->getDocument($st["documentID"]);
							
								if ($printheader){
									print "<table class=\"table table-condensed\">";
									print "<thead>\n<tr>\n";
									print "<th></th>\n";
									print "<th>".getMLText("name")."</th>\n";
									print "<th>".getMLText("owner")."</th>\n";
									print "<th>".getMLText("version")."</th>\n";
									print "<th>".getMLText("last_update")."</th>\n";
									print "<th>".getMLText("expires")."</th>\n";
									print "</tr>\n</thead>\n<tbody>\n";
									$printheader=false;
								}
							
								print "<tr>\n";
								$latestContent = $document->getLatestContent();
								$previewer->createPreview($latestContent);
								print "<td><a href=\"../op/op.Download.php?documentid=".$st["documentID"]."&version=".$st["version"]."\">";
								if($previewer->hasPreview($latestContent)) {
									print "<img class=\"mimeicon\" width=\"".$previewwidth."\"src=\"../op/op.Preview.php?documentid=".$document->getID()."&version=".$latestContent->getVersion()."&width=".$previewwidth."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
								} else {
									print "<img class=\"mimeicon\" src=\"".$this->getMimeIcon($latestContent->getFileType())."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
								}
								print "</a></td>";
								print "<td><a href=\"out.ViewDocument.php?documentid=".$st["documentID"]."\">".htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["name"])."</a></td>";
								print "<td>".htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["ownerName"])."</td>";
								print "<td>".$st["version"]."</td>";
								print "<td>".$st["date"]." ". htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["statusName"]) ."</td>";
								print "<td".($docIdx[$st["documentID"]][$st["version"]]['status']!=S_EXPIRED?"":" class=\"warning\"").">".(!$docIdx[$st["documentID"]][$st["version"]]["expires"] ? "-":getReadableDate($docIdx[$st["documentID"]][$st["version"]]["expires"]))."</td>";				
								print "</tr>\n";
							}
						}
						foreach ($reviewStatus["grpstatus"] as $st) {
						
							if (!in_array($st["documentID"], $iRev) && $st["status"]==0 && isset($docIdx[$st["documentID"]][$st["version"]]) && !in_array($st["documentID"], $dList) && $docIdx[$st["documentID"]][$st["version"]]['owner'] != $user->getId()) {
								$dList[] = $st["documentID"];
								$document = $dms->getDocument($st["documentID"]);

								if ($printheader){
									print "<table class=\"table table-condensed\">";
									print "<thead>\n<tr>\n";
									print "<th></th>\n";
									print "<th>".getMLText("name")."</th>\n";
									print "<th>".getMLText("owner")."</th>\n";
									print "<th>".getMLText("version")."</th>\n";
									print "<th>".getMLText("last_update")."</th>\n";
									print "<th>".getMLText("expires")."</th>\n";
									print "</tr>\n</thead>\n<tbody>\n";
									$printheader=false;
								}

								print "<tr>\n";
								$latestContent = $document->getLatestContent();
								$previewer->createPreview($latestContent);
								print "<td><a href=\"../op/op.Download.php?documentid=".$st["documentID"]."&version=".$st["version"]."\">";
								if($previewer->hasPreview($latestContent)) {
									print "<img class=\"mimeicon\" width=\"".$previewwidth."\"src=\"../op/op.Preview.php?documentid=".$document->getID()."&version=".$latestContent->getVersion()."&width=".$previewwidth."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
								} else {
									print "<img class=\"mimeicon\" src=\"".$this->getMimeIcon($latestContent->getFileType())."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
								}
								print "</a></td>";
								print "<td><a href=\"out.ViewDocument.php?documentid=".$st["documentID"]."\">".htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["name"])."</a></td>";
								print "<td>".htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["ownerName"])."</td>";
								print "<td>".$st["version"]."</td>";
								print "<td>".$st["date"]." ". htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["statusName"])."</td>";
								print "<td".($docIdx[$st["documentID"]][$st["version"]]['status']!=S_EXPIRED?"":" class=\"warning\"").">".(!$docIdx[$st["documentID"]][$st["version"]]["expires"] ? "-":getReadableDate($docIdx[$st["documentID"]][$st["version"]]["expires"]))."</td>";				
								print "</tr>\n";
							}
						}
						if (!$printheader){
							echo "</tbody>\n</table>";
						}else{
							printMLText("no_docs_to_review");
						}
						$this->contentContainerEnd();
					}

					// List the documents for which an approval has been requested.
					$this->contentHeading(getMLText("documents_to_approve"));
					$this->contentContainerStart();
					$printheader=true;
					
					foreach ($approvalStatus["indstatus"] as $st) {
					
						if ( $st["status"]==0 && isset($docIdx[$st["documentID"]][$st["version"]])) {
							$document = $dms->getDocument($st["documentID"]);
						
							if ($printheader){
								print "<table class=\"table table-condensed\">";
								print "<thead>\n<tr>\n";
								print "<th></th>\n";
								print "<th>".getMLText("name")."</th>\n";
								print "<th>".getMLText("owner")."</th>\n";
								print "<th>".getMLText("version")."</th>\n";
								print "<th>".getMLText("last_update")."</th>\n";
								print "<th>".getMLText("expires")."</th>\n";
								print "</tr>\n</thead>\n<tbody>\n";
								$printheader=false;
							}

							print "<tr>\n";
							$latestContent = $document->getLatestContent();
							$previewer->createPreview($latestContent);
							print "<td><a href=\"../op/op.Download.php?documentid=".$st["documentID"]."&version=".$st["version"]."\">";
							if($previewer->hasPreview($latestContent)) {
								print "<img class=\"mimeicon\" width=\"".$previewwidth."\"src=\"../op/op.Preview.php?documentid=".$document->getID()."&version=".$latestContent->getVersion()."&width=".$previewwidth."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
							} else {
								print "<img class=\"mimeicon\" src=\"".$this->getMimeIcon($latestContent->getFileType())."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
							}
							print "</a></td>";
							print "<td><a href=\"out.ViewDocument.php?documentid=".$st["documentID"]."\">".htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["name"])."</a></td>";
							print "<td>".htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["ownerName"])."</td>";
							print "<td>".$st["version"]."</td>";
							print "<td>".$st["date"]." ". htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["statusName"])."</td>";
							print "<td".($docIdx[$st["documentID"]][$st["version"]]['status']!=S_EXPIRED?"":" class=\"warning\"").">".(!$docIdx[$st["documentID"]][$st["version"]]["expires"] ? "-":getReadableDate($docIdx[$st["documentID"]][$st["version"]]["expires"]))."</td>";					
							print "</tr>\n";
						}
					}
					foreach ($approvalStatus["grpstatus"] as $st) {
					
						if (!in_array($st["documentID"], $iRev) && $st["status"]==0 && isset($docIdx[$st["documentID"]][$st["version"]]) && $docIdx[$st["documentID"]][$st["version"]]['owner'] != $user->getId()) {
							$document = $dms->getDocument($st["documentID"]);
							if ($printheader){
								print "<table class=\"table table-condensed\">";
								print "<thead>\n<tr>\n";
								print "<th></th>\n";
								print "<th>".getMLText("name")."</th>\n";
								print "<th>".getMLText("owner")."</th>\n";
								print "<th>".getMLText("version")."</th>\n";
								print "<th>".getMLText("last_update")."</th>\n";
								print "<th>".getMLText("expires")."</th>\n";
								print "</tr>\n</thead>\n<tbody>\n";
								$printheader=false;
							}
							print "<tr>\n";
							$latestContent = $document->getLatestContent();
							$previewer->createPreview($latestContent);
							print "<td><a href=\"../op/op.Download.php?documentid=".$st["documentID"]."&version=".$st["version"]."\">";
							if($previewer->hasPreview($latestContent)) {
								print "<img class=\"mimeicon\" width=\"".$previewwidth."\"src=\"../op/op.Preview.php?documentid=".$document->getID()."&version=".$latestContent->getVersion()."&width=".$previewwidth."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
							} else {
								print "<img class=\"mimeicon\" src=\"".$this->getMimeIcon($latestContent->getFileType())."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
							}
							print "</a></td>";
							print "<td><a href=\"out.ViewDocument.php?documentid=".$st["documentID"]."\">".htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["name"])."</a></td>";
							print "<td>".htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["ownerName"])."</td>";
							print "<td>".$st["version"]."</td>";				
							print "<td>".$st["date"]." ". htmlspecialchars($docIdx[$st["documentID"]][$st["version"]]["statusName"])."</td>";
							print "<td".($docIdx[$st["documentID"]][$st["version"]]['status']!=S_EXPIRED?"":" class=\"warning\"").">".(!$docIdx[$st["documentID"]][$st["version"]]["expires"] ? "-":getReadableDate($docIdx[$st["documentID"]][$st["version"]]["expires"]))."</td>";				
							print "</tr>\n";
						}
					}
					if (!$printheader){
						echo "</tbody>\n</table>\n";
					 }else{
						printMLText("no_docs_to_approve");
					 }
					$this->contentContainerEnd();
				}
				else {
					if($workflowmode == 'traditional') {	
						$this->contentHeading(getMLText("documents_to_review"));
						$this->contentContainerStart();
						printMLText("no_review_needed");
						$this->contentContainerEnd();
					}
					$this->contentHeading(getMLText("documents_to_approve"));
					$this->contentContainerStart();
					printMLText("no_approval_needed");
					$this->contentContainerEnd();
				}

				/* Get list of documents owned by current user that are
				 * pending review or pending approval.
				 */
				$resArr = $dms->getDocumentList('AppRevOwner', $user);
				if (is_bool($resArr) && !$resArr) {
					$this->contentHeading(getMLText("warning"));
					$this->contentContainer(getMLText("internal_error_exit"));
					$this->htmlEndPage();
					exit;
				}

				$this->contentHeading(getMLText("documents_user_requiring_attention"));
				$this->contentContainerStart();
				if (count($resArr)>0) {

					print "<table class=\"table table-condensed\">";
					print "<thead>\n<tr>\n";
					print "<th></th>";
					print "<th>".getMLText("name")."</th>\n";
					print "<th>".getMLText("status")."</th>\n";
					print "<th>".getMLText("version")."</th>\n";
					print "<th>".getMLText("last_update")."</th>\n";
					print "<th>".getMLText("expires")."</th>\n";
					print "</tr>\n</thead>\n<tbody>\n";

					foreach ($resArr as $res) {
						$document = $dms->getDocument($res["documentID"]);
					
						// verify expiry
						if ( $res["expires"] && time()>$res["expires"]+24*60*60 ){
							if  ( $res["status"]==S_DRAFT_APP || $res["status"]==S_DRAFT_REV ){
								$res["status"]=S_EXPIRED;
							}
						}
					
						print "<tr>\n";
						$latestContent = $document->getLatestContent();
						$previewer->createPreview($latestContent);
						print "<td><a href=\"../op/op.Download.php?documentid=".$res["documentID"]."&version=".$res["version"]."\">";
						if($previewer->hasPreview($latestContent)) {
							print "<img class=\"mimeicon\" width=\"".$previewwidth."\"src=\"../op/op.Preview.php?documentid=".$document->getID()."&version=".$latestContent->getVersion()."&width=".$previewwidth."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
						} else {
							print "<img class=\"mimeicon\" src=\"".$this->getMimeIcon($latestContent->getFileType())."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
						}
						print "</a></td>";
						print "<td><a href=\"out.ViewDocument.php?documentid=".$res["documentID"]."\">" . htmlspecialchars($res["name"]) . "</a></td>\n";
						print "<td>".getOverallStatusText($res["status"])."</td>";
						print "<td>".$res["version"]."</td>";
						print "<td>".$res["statusDate"]." ".htmlspecialchars($res["statusName"])."</td>";
						print "<td>".(!$res["expires"] ? "-":getReadableDate($res["expires"]))."</td>";				
						print "</tr>\n";
					}		
					print "</tbody></table>";	
					
				}
				else printMLText("no_docs_to_look_at");
				
				$this->contentContainerEnd();
			}
			
			/* Get list of documents locked by current user */
			$resArr = $dms->getDocumentList('LockedByMe', $user);
			if (is_bool($resArr) && !$resArr) {
				$this->contentHeading(getMLText("warning"));
				$this->contentContainer(getMLText("internal_error_exit"));
				$this->htmlEndPage();
				exit;
			}

			$this->contentHeading(getMLText("documents_locked_by_you"));
			$this->contentContainerStart();
			if (count($resArr)>0) {

				print "<table class=\"table table-condensed\">";
				print "<thead>\n<tr>\n";
				print "<th></th>";
				print "<th>".getMLText("name")."</th>\n";
				print "<th>".getMLText("status")."</th>\n";
				print "<th>".getMLText("version")."</th>\n";
				print "<th>".getMLText("last_update")."</th>\n";
				print "<th>".getMLText("expires")."</th>\n";
				print "</tr>\n</thead>\n<tbody>\n";

				foreach ($resArr as $res) {
					$document = $dms->getDocument($res["documentID"]);
				
					// verify expiry
					if ( $res["expires"] && time()>$res["expires"]+24*60*60 ){
						if  ( $res["status"]==S_DRAFT_APP || $res["status"]==S_DRAFT_REV ){
							$res["status"]=S_EXPIRED;
						}
					}
				
					print "<tr>\n";
					$latestContent = $document->getLatestContent();
					$previewer->createPreview($latestContent);
					print "<td><a href=\"../op/op.Download.php?documentid=".$res["documentID"]."&version=".$res["version"]."\">";
					if($previewer->hasPreview($latestContent)) {
						print "<img class=\"mimeicon\" width=\"".$previewwidth."\"src=\"../op/op.Preview.php?documentid=".$document->getID()."&version=".$latestContent->getVersion()."&width=".$previewwidth."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
					} else {
						print "<img class=\"mimeicon\" src=\"".$this->getMimeIcon($latestContent->getFileType())."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
					}
					print "</a></td>";
					print "<td><a href=\"out.ViewDocument.php?documentid=".$res["documentID"]."\">" . htmlspecialchars($res["name"]) . "</a></td>\n";
					print "<td>".getOverallStatusText($res["status"])."</td>";
					print "<td>".$res["version"]."</td>";
					print "<td>".$res["statusDate"]." ".htmlspecialchars($res["statusName"])."</td>";
					print "<td>".(!$res["expires"] ? "-":getReadableDate($res["expires"]))."</td>";				
					print "</tr>\n";
				}		
				print "</tbody></table>";	
				
			}
			else printMLText("no_docs_locked");

			$this->contentContainerEnd();

			/* Get list of documents checked out by current user */
			$resArr = $dms->getDocumentList('CheckedOutByMe', $user);
			if (is_bool($resArr) && !$resArr) {
				$this->contentHeading(getMLText("warning"));
				$this->contentContainer(getMLText("internal_error_exit"));
				$this->htmlEndPage();
				exit;
			}

			$this->contentHeading(getMLText("documents_checked_out_by_you"));
			$this->contentContainerStart();
			if (count($resArr)>0) {

				print "<table class=\"table table-condensed\">";
				print "<thead>\n<tr>\n";
				print "<th></th>";
				print "<th>".getMLText("name")."</th>\n";
				print "<th>".getMLText("status")."</th>\n";
				print "<th>".getMLText("version")."</th>\n";
				print "<th>".getMLText("last_update")."</th>\n";
				print "<th>".getMLText("expires")."</th>\n";
				print "</tr>\n</thead>\n<tbody>\n";

				foreach ($resArr as $res) {
					$document = $dms->getDocument($res["documentID"]);
				
					// verify expiry
					if ( $res["expires"] && time()>$res["expires"]+24*60*60 ){
						if  ( $res["status"]==S_DRAFT_APP || $res["status"]==S_DRAFT_REV ){
							$res["status"]=S_EXPIRED;
						}
					}
				
					print "<tr>\n";
					$latestContent = $document->getLatestContent();
					$previewer->createPreview($latestContent);
					print "<td><a href=\"../op/op.Download.php?documentid=".$res["documentID"]."&version=".$res["version"]."\">";
					if($previewer->hasPreview($latestContent)) {
						print "<img class=\"mimeicon\" width=\"".$previewwidth."\"src=\"../op/op.Preview.php?documentid=".$document->getID()."&version=".$latestContent->getVersion()."&width=".$previewwidth."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
					} else {
						print "<img class=\"mimeicon\" src=\"".$this->getMimeIcon($latestContent->getFileType())."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
					}
					print "</a></td>";
					print "<td><a href=\"out.ViewDocument.php?documentid=".$res["documentID"]."\">" . htmlspecialchars($res["name"]) . "</a></td>\n";
					print "<td>".getOverallStatusText($res["status"])."</td>";
					print "<td>".$res["version"]."</td>";
					print "<td>".$res["statusDate"]." ".htmlspecialchars($res["statusName"])."</td>";
					print "<td>".(!$res["expires"] ? "-":getReadableDate($res["expires"]))."</td>";				
					print "</tr>\n";
				}		
				print "</tbody></table>";	
				
			}
			else printMLText("no_docs_checked_out");

			$this->contentContainerEnd();

		}
		else {

			/* Get list of documents owned by current user */
			$resArr = $dms->getDocumentList('MyDocs', $user, $orderby);
			if (is_bool($resArr) && !$resArr) {
				$this->contentHeading(getMLText("warning"));
				$this->contentContainer(getMLText("internal_error_exit"));
				$this->htmlEndPage();
				exit;
			}

			$this->contentHeading(getMLText("all_documents"));
			$this->contentContainerStart();

			if (count($resArr)>0) {

				print "<table class=\"table table-condensed\">";
				print "<thead>\n<tr>\n";
				print "<th></th>";
				print "<th><a href=\"../out/out.MyDocuments.php?orderby=n\">".getMLText("name")."</a></th>\n";
				print "<th><a href=\"../out/out.MyDocuments.php?orderby=s\">".getMLText("status")."</a></th>\n";
				print "<th>".getMLText("version")."</th>\n";
				print "<th><a href=\"../out/out.MyDocuments.php?orderby=u\">".getMLText("last_update")."</a></th>\n";
				print "<th><a href=\"../out/out.MyDocuments.php?orderby=e\">".getMLText("expires")."</a></th>\n";
				print "</tr>\n</thead>\n<tbody>\n";

				$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth);
				foreach ($resArr as $res) {
					$document = $dms->getDocument($res["documentID"]);
				
					// verify expiry
					if ( $res["expires"] && time()>$res["expires"]+24*60*60 ){
						if  ( $res["status"]==S_DRAFT_APP || $res["status"]==S_DRAFT_REV ){
							$res["status"]=S_EXPIRED;
						}
					}
				
					print "<tr>\n";
					$latestContent = $document->getLatestContent();
					$previewer->createPreview($latestContent);
					print "<td><a href=\"../op/op.Download.php?documentid=".$res["documentID"]."&version=".$res["version"]."\">";
					if($previewer->hasPreview($latestContent)) {
						print "<img class=\"mimeicon\" width=\"".$previewwidth."\"src=\"../op/op.Preview.php?documentid=".$document->getID()."&version=".$latestContent->getVersion()."&width=".$previewwidth."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
					} else {
						print "<img class=\"mimeicon\" src=\"".$this->getMimeIcon($latestContent->getFileType())."\" title=\"".htmlspecialchars($latestContent->getMimeType())."\">";
					}
					print "</a></td>";
					print "<td><a href=\"out.ViewDocument.php?documentid=".$res["documentID"]."\">" . htmlspecialchars($res["name"]) . "</a></td>\n";
					print "<td>".getOverallStatusText($res["status"])."</td>";
					print "<td>".$res["version"]."</td>";
					print "<td>".$res["statusDate"]." ". htmlspecialchars($res["statusName"])."</td>";
					//print "<td>".(!$res["expires"] ? getMLText("does_not_expire"):getReadableDate($res["expires"]))."</td>";				
					print "<td>".(!$res["expires"] ? "-":getReadableDate($res["expires"]))."</td>";				
					print "</tr>\n";
				}
				print "</tbody></table>";
			}
			else printMLText("empty_notify_list");
			
			$this->contentContainerEnd();
		}

		$this->htmlEndPage();
	} /* }}} */
}
?>
