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
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkRead();

$rpu_id      = CView::get("rpu_id", "ref class|CRPU");
$submit_ajax = CView::get("submit_ajax", "str");

CView::checkin();

// Chargement du rpu
$rpu = new CRPU();
$rpu->load($rpu_id);

$rpu->loadRefIOA();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("rpu", $rpu);
$smarty->assign("submit_ajax", $submit_ajax);
$smarty->display("inc_vw_rpu_pec_ioa");