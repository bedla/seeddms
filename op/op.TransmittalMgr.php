<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010 Matteo Lucarelli
//    Copyright (C) 2010-2012 Uwe Steinmann
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
include("../inc/inc.LogInit.php");
include("../inc/inc.Utils.php");
include("../inc/inc.Language.php");
include("../inc/inc.Init.php");
include("../inc/inc.Extension.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.Authentication.php");
include("../inc/inc.ClassPasswordStrength.php");

if ($user->isGuest()) {
	UI::exitError(getMLText("my_transmittals"),getMLText("access_denied"));
}

if (isset($_POST["action"])) $action=$_POST["action"];
else $action=NULL;

// add new transmittal ---------------------------------------------------
if ($action == "addtransmittal") {
	
	/* Check if the form data comes for a trusted request */
	if(!checkFormKey('addtransmittal')) {
		UI::exitError(getMLText("my_transmittals"),getMLText("invalid_request_token"));
	}

	$name    = $_POST["name"];
	$comment = $_POST["comment"];

	$newTransmittal = $dms->addTransmittal($name, $comment, $user);
	if ($newTransmittal) {
	}
	else UI::exitError(getMLText("my_transmittals"),getMLText("access_denied"));
	
	$transmittalid=$newTransmittal->getID();
	
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_add_transmittal')));

	add_log_line(".php&action=addtransmittal&name=".$name);
}

// delete user ------------------------------------------------------------
else if ($action == "removetransmittal") {

	/* Check if the form data comes for a trusted request */
	if(!checkFormKey('removetransmittal')) {
		UI::exitError(getMLText("my_transmittals"),getMLText("invalid_request_token"));
	}

	if (isset($_POST["transmittalid"])) {
		$transmittalid = $_POST["transmittalid"];
	}

	if (!isset($transmittalid) || !is_numeric($transmittalid) || intval($transmittalid)<1) {
		UI::exitError(getMLText("my_transmittals"),getMLText("invalid_transmittal_id"));
	}

	$transmittalToRemove = $dms->getUser($transmittalid);
	if (!is_object($transmittalToRemove)) {
		UI::exitError(getMLText("my_transmittals"),getMLText("invalid_transmittal_id"));
	}

	add_log_line(".php&action=removetransmittal&transmittalid=".$transmittalid);
	
	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_rm_transmittal')));
	$transmittalid=-1;
}

// modify transmittal ----------------------------------------------------
else if ($action == "edittransmittal") {

	/* Check if the form data comes for a trusted request */
	if(!checkFormKey('edittransmittal')) {
		UI::exitError(getMLText("my_transmittals"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["transmittalid"]) || !is_numeric($_POST["transmittalid"]) || intval($_POST["transmittalid"])<1) {
		UI::exitError(getMLText("my_transmittals"),getMLText("invalid_transmittal"));
	}
	
	$transmittalid=$_POST["transmittalid"];
	$editedTransmittal = $dms->getTransmittal($transmittalid);
	
	if (!is_object($editedTransmittal)) {
		UI::exitError(getMLText("my_transmittals"),getMLText("invalid_transmittal"));
	}

	$name = $_POST["name"];
	$comment = $_POST["comment"];
	
	if ($editedTransmittal->getName() != $name)
		$editedTransmittal->setName($name);
	if ($editedTransmittal->getComment() != $comment)
		$editedTransmittal->setComment($comment);

	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_edit_transmittal')));
	add_log_line(".php&action=edittransmittal&transmittalid=".$transmittalid);
}
else UI::exitError(getMLText("my_transmittals"),getMLText("unknown_command"));

header("Location:../out/out.TransmittalMgr.php?transmittalid=".$transmittalid);

?>

