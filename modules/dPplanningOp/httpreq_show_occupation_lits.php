<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::read();

$type       = CView::get('type', 'enum list|' . implode('|', CSejour::$types));
$service_id = CView::get('service_id', 'ref class|CService');
$entree     = CView::get('entree', 'date');
$view       = CView::get('view', 'str default|old_dhe');

CView::checkin();

$group = CGroups::loadCurrent();
$group->loadConfigValues();

$service = new CService();
$service->load($service_id);

$sejour    = new CSejour();
$nb_sejour = 0;
if ($type && $entree) {
    $where = [
        "type"     => "= '$type'",
        "annule"   => "= '0'",
        "group_id" => "= '$group->_id'",
    ];

    $min = $entree  . ' 00:00:00';
    $max = $entree . ' 23:59:59';

    if ($type == "ambu") {
        $where[] = "entree BETWEEN '$min' AND '$max'";
    } else {
        $where[] = "entree <= '$max' AND sortie >= '$min'";
    }

    if ($service->_id) {
        $where['service_id'] = " = {$service->_id}";
    }

    $nb_sejour = $sejour->countList($where);
}

$occupation = -1;
$max        = 0;
if ($type == "ambu") {
    if ($service->_id && $service->max_ambu_per_day) {
        $max = $service->max_ambu_per_day;
    } elseif ($group->_configs["max_ambu"]) {
        $max = $group->_configs["max_ambu"];
    }
} elseif ($type == "comp") {
    if ($service->_id && $service->max_hospi_per_day) {
        $max = $service->max_hospi_per_day;
    } elseif ($group->_configs["max_comp"]) {
        $max = $group->_configs["max_comp"];
    }
}

if ($max) {
    $occupation = round($nb_sejour / $max * 100);
}

$pct = min($occupation, 100);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("occupation", $occupation);
$smarty->assign("pct", $pct);
$smarty->assign('view', $view);

$smarty->display("inc_show_occupation_lits");
