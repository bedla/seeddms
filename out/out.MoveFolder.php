<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2006-2008 Malcolm Cowe
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.Language.php");
include("../inc/inc.Init.php");
include("../inc/inc.Extension.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.Authentication.php");

if (!isset($_GET["folderid"]) || !is_numeric($_GET["folderid"]) || intval($_GET["folderid"])<1) {
	UI::exitError(getMLText("folder_title", array("foldername" => getMLText("invalid_folder_id"))),getMLText("invalid_folder_id"));
}

$folder = $dms->getFolder($_GET["folderid"]);

if (!is_object($folder)) {
	UI::exitError(getMLText("folder_title", array("foldername" => getMLText("invalid_folder_id"))),getMLText("invalid_folder_id"));
}

if ($folder->getID() == $settings->_rootFolderID || !$folder->getParent()) {
	UI::exitError(getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))),getMLText("cannot_move_root"));
}

if ($folder->getAccessMode($user) < M_READWRITE) {
	UI::exitError(getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))),getMLText("access_denied"));
}

if(isset($_GET['targetid']) && $_GET['targetid']) {
	$target = $dms->getFolder($_GET["targetid"]);
	if (!is_object($target)) {
		UI::exitError(getMLText("folder_title", array("foldername" => $folder->getName())),getMLText("invalid_target_folder"));
	}

	if ($target->getAccessMode($user) < M_READWRITE) {
		UI::exitError(getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))),getMLText("access_denied"));
	}

	if($folder->isSubFolder($target)) {
		UI::exitError(getMLText("folder_title", array("foldername" => $folder->getName())),getMLText("invalid_target_folder"));
	}
} else {
	$target = null;
}

$tmp = explode('.', basename($_SERVER['SCRIPT_FILENAME']));
$view = UI::factory($theme, $tmp[1], array('dms'=>$dms, 'user'=>$user, 'folder'=>$folder, 'target'=>$target));
if($view) {
	$view->show();
	exit;
}

?>
