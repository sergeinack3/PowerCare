<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkRead();

$rhs_id = CView::get("rhs_id", "ref class|CRHS");
$part   = CView::get("part", "str");

CView::checkin();

$rhs = new CRHS();
$rhs->load($rhs_id);

$sejour = $rhs->loadRefSejour();

$rhs->_nb_weeks = count(CRHS::getAllMondays($rhs->date_monday, $sejour->sortie));

$code_das = array();
$code_dad = array();

switch ($part) {
  case "dependances":
    $rhs->loadRefDependances()->loadRefBilanRHS();
    break;
  case "diagnostics":
    $rhs->loadRefDiagnostics();

    // Diagnostics DAS et DAD
    $code_das = array();
    $code_dad = array();

    $code_das_rhs = explode("|", $rhs->DAS);
    $code_dad_rhs = explode("|", $rhs->DAD);

    foreach ($code_das_rhs as $_code_das) {
      $code_das[] = CCodeCIM10::get($_code_das);
    }

    foreach ($code_dad_rhs as $_code_dad) {
      $code_dad[] = CCodeCIM10::get($_code_dad);
    }
    break;
  case "activites":
    $rhs->buildTotaux();
    break;
  default:
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("rhs", $rhs);
$smarty->assign("part", $part);
$smarty->assign("code_das", $code_das);
$smarty->assign("code_dad", $code_dad);

$smarty->display("inc_duplicate_rhs");
