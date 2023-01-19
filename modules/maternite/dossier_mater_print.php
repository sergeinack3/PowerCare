<?php
/**
 * @package Mediboard\Maternité
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkRead();

$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");

CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefDossierPerinat()->loadEtatDossier();
$grossesse->loadLastSejour();

$sejour_id    = null;
$operation_id = null;

if ($grossesse->_ref_last_sejour) {
  $sejour_id = $grossesse->_ref_last_sejour->_id;
  if (count($grossesse->_ref_last_sejour->loadRefsOperations())) {
    $operation_id = $grossesse->_ref_last_sejour->_ref_last_operation->_id;
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("operation_id", $operation_id);

$smarty->display("dossier_mater_print.tpl");