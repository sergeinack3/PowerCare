<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;

CCanDo::checkRead();

$object_guid = CValue::get("object_guid");

/** @var CCompteRendu $compte_rendu */
$compte_rendu = CMbObject::loadFromGuid($object_guid);
$compte_rendu->isAutoLock();
$compte_rendu->loadRefLocker()->loadRefFunction();


$smarty = new CSmartyDP();

$smarty->assign("compte_rendu", $compte_rendu);

$smarty->display("inc_show_locker");