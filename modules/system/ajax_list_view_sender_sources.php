<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\ViewSender\CViewSenderSource;

CCanDo::checkRead();

// Chargement des senders sources
$sender_source = new CViewSenderSource();

/** @var CViewSenderSource[] $senders_source */
$senders_source = $sender_source->loadList(null, "name");
foreach ($senders_source as $_source) {
  $_source->loadRefGroup();
  $_source->loadRefSource();
  $_source->loadRefSenders();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("senders_source", $senders_source);
$smarty->display("inc_list_view_senders_source.tpl");
