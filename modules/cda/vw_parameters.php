<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\InteropResources\valueset\CXDSValueSet;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$group = CGroups::loadCurrent();

$idex_group_xds                 = $group->loadLastId400("xds_association_code");
$xds_value_set = new CXDSValueSet();
$xds_healthcareFacilityTypeCode = $xds_value_set->getHealthcareFacilityTypeCodeEntries();

$idex_group_dmp                 = $group->loadLastId400("cda_association_code");
$ans_value_set = new CANSValueSet();
$dmp_healthcareFacilityTypeCode = $ans_value_set->getHealthcareFacilityTypeCodeEntries();


$smarty = new CSmartyDP();
$smarty->assign("group", $group);
$smarty->assign("idex_group_xds", $idex_group_xds);
$smarty->assign("xds_healthcareFacilityTypeCode", $xds_healthcareFacilityTypeCode);
$smarty->assign("idex_group_dmp", $idex_group_dmp);
$smarty->assign("dmp_healthcareFacilityTypeCode", $dmp_healthcareFacilityTypeCode);
$smarty->display("vw_parameters.tpl");
