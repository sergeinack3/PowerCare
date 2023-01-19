<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;

CCanDo::checkAdmin();

$mode_class = CView::get("mode_class", "str");
$mode_id    = CView::get("mode_id", "ref class|$mode_class");

CView::checkin();

if (!in_array($mode_class, array("CModeEntreeSejour", "CModeSortieSejour"))) {
  throw new CMbException("Invalid class: '$mode_class'");
}

/** @var CModeEntreeSejour|CModeSortieSejour $mode */
$mode = new $mode_class;
$mode->load($mode_id);

$mode->loadRefsNotes();
if ($mode instanceof CModeSortieSejour) {
  $mode->loadRefEtabExterne();
}
if (!$mode->_id) {
  $mode->group_id = CGroups::loadCurrent()->_id;
}

$smarty = new CSmartyDP();
$smarty->assign("mode", $mode);
$smarty->display("inc_edit_mode_entree_sortie");
