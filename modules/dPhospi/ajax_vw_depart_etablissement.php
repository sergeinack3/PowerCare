<?php

/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

$affectation_id = CView::get("affectation_id", "ref class|CAffectation");
$from_placement = CView::get("from_placement", "bool");

CView::checkin();

// Chargement de l'affectation pr�c�dent la permission
$affectation = new CAffectation();
$affectation->load($affectation_id);
$affectation->loadRefLit();
$affectation->loadRefService();

// Chargement du s�jour
$sejour = $affectation->loadRefSejour();
$sejour->loadRefPatient();

// Liste des services
$service = new CService();

$where = [
    "cancelled" => "= '0'",
    "externe"   => "= '1'",
];

$services = $service->loadGroupList($where);

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("services", $services);
$smarty->assign("affectation", $affectation);
$smarty->assign("from_placement", $from_placement);

$smarty->display("inc_vw_depart_etablissement");
