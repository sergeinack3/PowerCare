<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$group = CGroups::loadCurrent();
$cabinet_id = $group->service_urgences_id;

$consult = new CConsultation;
$consult->motif = CAppUI::tr("CConsultation.reconvoc_immediate");
$consult->_datetime = "now";

$praticiens = CConsultation::loadPraticiens(PERM_READ, $cabinet_id);

$smarty = new CSmartyDP;
$smarty->assign("praticiens", $praticiens);
$smarty->assign("consult"   , $consult);
$smarty->display("inc_create_reconvoc.tpl");
