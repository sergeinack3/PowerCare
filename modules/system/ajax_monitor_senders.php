<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\ViewSender\CViewSender;

CCanDo::checkRead();

// Chargement des senders
$sender  = new CViewSender();
$sender->active = "1";

/** @var CViewSender[] $senders */
$senders = $sender->loadMatchingList("name");

// Détails des senders
foreach ($senders as $_sender) {
  $senders_source = $_sender->loadRefSendersSource();
  $_sender->getLastAge();

  foreach ($senders_source as $_sender_source) {
    $_sender_source->loadRefSender();
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("senders", $senders);
$smarty->display("inc_monitor_senders.tpl");
