<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultAnesth;

CCanDo::checkRead();

$dossier_anesth_id = CValue::get("dossier_anesth_id");

$consult_anesth = new CConsultAnesth();
$consult_anesth->load($dossier_anesth_id);
$consult = $consult_anesth->loadRefConsultation();

$smarty = new CSmartyDP();

$smarty->assign("consult"       , $consult);
$smarty->assign("consult_anesth", $consult_anesth);

$smarty->display("inc_consult_anesth/acc_examens_clinique.tpl");