<?php

/*
 *  LMS version 1.11-git
 *
 *  (C) Copyright 2001-2015 LMS Developers
 *
 *  Please, see the doc/AUTHORS for more information about authors!
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 *  USA.
 *
 *  $Id$
 */

function module_main()
{
    global $DB,$LMS,$SESSION,$SMARTY;

  if(isset($_GET['confirm_old']))
    {
       $DB->Execute('UPDATE nodes SET warning=0 WHERE ownerid = ?', array($SESSION->id));
    }
    elseif($DB->GetOne('SELECT MAX(warning) FROM vnodes WHERE ownerid = ?', array($SESSION->id)))
    {
       $warning = $LMS->GetCustomerMessage($SESSION->id);
       $SMARTY->assign('warning', $warning);
    }


	if (isset($_GET['confirm'])) {
		$confirm = $_GET['confirm'];
		$DB->Execute('UPDATE messageitems SET status = ?, lastdate = ?NOW? WHERE id = ?',
			array($confirm, MSG_DELIVERED));
		header('Location: ?m=notices');
	} else {
		$notice = $DB->GetAll('SELECT m.subject, m.cdate, m.body, m.type, mi.id, mi.messageid, mi.destination, mi.status, mi.lastdate
			FROM customers c
			JOIN messageitems mi ON mi.customerid = c.id
			JOIN messages m ON m.id = mi.messageid
			WHERE m.type in (?, ?) AND c.id = ?
			ORDER BY mi.status asc, m.cdate desc',
			array(MSG_USERPANEL, MSG_USERPANEL_URGENT, $SESSION->id));
		$SMARTY->assign('notice', $notice);
	}

	if (isset($_GET['confirm_urgent'])) {
		$confirm_urgent = $_GET['confirm_urgent'];
		$DB->Execute('UPDATE messageitems SET status = ?, lastdate = ?NOW? WHERE id = ?',
			array(MSG_DELIVERED, $confirm_urgent));
		header('Location: ?m=notices');
	}
	$SMARTY->display('module:notices.html');
}

function setNoticeRead($noticeid)
{
	global $DB;
	$result = new xajaxResponse();
	$DB->Execute('UPDATE messageitems SET lastreaddate = ?NOW? WHERE id = ?', array($noticeid));
	return $result;
}

$LMS->InitXajax();
$LMS->RegisterXajaxFunction('setNoticeRead');
$SMARTY->assign('xajax', $LMS->RunXajax());

?>
