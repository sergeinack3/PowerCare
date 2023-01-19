<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CLigneActivitesRHS;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkRead();

$rhs_id     = CView::post("rhs_id", "ref class|CRHS");
$part       = CView::post("part", "str");
$_nb_weeks  = CView::post("_nb_weeks", "num");
$_lines_rhs = CView::post("_lines_rhs", "str");

CView::checkin();

$rhs = new CRHS();
$rhs->load($rhs_id);

switch ($part) {
  case "dependances":
    $rhs->loadRefDependances();
    break;
  case "diagnostics":

    break;
  case "activites":
    break;
  default:
}

$date_min = $rhs->date_monday;
$date_max = CMbDT::date("+$_nb_weeks WEEKS", $date_min);

$rhss = CRHS::getAllRHSsFor($rhs->loadRefSejour(), $date_min);

foreach ($rhss as $_rhs) {
  if ($_rhs->date_monday <= $date_min || $_rhs->date_monday > $date_max) {
    continue;
  }

  if (!$_rhs->_id) {
    $msg = $_rhs->store();

    CAppUI::setMsg($msg ?: "CRHS-msg-create", $msg ? UI_MSG_ERROR : UI_MSG_OK);

    if ($msg) {
      continue;
    }
  }

  switch ($part) {
    case "dependances":
      $dependances = $_rhs->loadRefDependances();

      $new_dep = !$dependances->_id;

      $dependances->cloneFrom($rhs->_ref_dependances);
      $dependances->_id    = "";
      $dependances->rhs_id = $_rhs->_id;

      $msg = $dependances->store();
      CAppUI::setMsg($msg ?: "CDependancesRHS-msg-" . ($new_dep ? "create" : "modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);
      break;
    case "diagnostics":
      foreach (array("FPP", "MMP", "AE") as $_diag) {
        $_rhs->$_diag = $rhs->$_diag;
      }

      foreach (array("DAS", "DAD") as $_diags) {
        if (!$rhs->$_diags) {
          continue;
        }

        foreach (explode("|", $rhs->$_diags) as $_diag) {
          if (!strpos($_diag, $_rhs->$_diags)) {
            if ($_rhs->$_diags) {
              $_rhs->$_diags .= "|";
            }
            $_rhs->$_diags .= $_diag;
          }
        }
      }

      $msg = $_rhs->store();
      CAppUI::setMsg($msg ?: "CRHS-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);

      break;
    case "activites":
      foreach ($rhs->loadBackRefs("lines") as $_line_rhs) {
        if (!isset($_lines_rhs[$_line_rhs->_id])) {
          continue;
        }

        $line_rhs = new CLigneActivitesRHS();
        $line_rhs->cloneFrom($_line_rhs);
        $line_rhs->rhs_id = $_rhs->_id;

        $msg = $line_rhs->store();
        CAppUI::setMsg($msg ?: "CLigneActivitesRHS-msg-create", $msg ? UI_MSG_ERROR : UI_MSG_OK);
      }

      break;
    default:
  }
}

echo CAppUI::getMsg();