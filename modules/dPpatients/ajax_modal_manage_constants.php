<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

CCanDo::checkAdmin();
$releve_id = CView::get("releve_id", "ref class|CConstantReleve");
CView::checkin();

$releve = new CConstantReleve();
$releve->load($releve_id);
$where = array("active" => "= '1'");
$releve->loadAllValues($where);
$unused_specs = CConstantSpec::getSpecWhitout(CConstantSpec::getListSpecByCode(CConstantSpec::$ALL_SPECS), array($releve));

$smarty = new CSmartyDP();
$smarty->assign("releve", $releve);
$smarty->assign("unused_specs", $unused_specs);
$smarty->display("modal_manage_constantes.tpl");

