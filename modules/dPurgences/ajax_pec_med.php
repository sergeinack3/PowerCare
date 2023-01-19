<?php

/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkEdit();

$rpu_id = CView::get("rpu_id", "ref class|CRPU");

CView::checkin();

$rpu = new CRPU();
$rpu->load($rpu_id);
$rpu->loadRefConsult();

$user      = CMediusers::get();
$listPrats = $user->loadPraticiens(PERM_READ, CGroups::get()->service_urgences_id, null, true);

$smarty = new CSmartyDP();

$smarty->assign("rpu", $rpu);
$smarty->assign("ajax_pec", 1);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("callback", "Urgences.pecMed");

$smarty->display("inc_pec_praticien");
