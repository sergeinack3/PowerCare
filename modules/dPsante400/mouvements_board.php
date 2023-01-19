<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Sante400\CMouvFactory;

CCanDo::checkAdmin();

$marked = array("0", "1");
$types  = CMouvFactory::getTypes();
$report = array();
foreach ($types as $_type) {
  // Oldest mouvement
  $mouv = CMouvFactory::create($_type);
  $mouv->loadOldest();
  $report[$_type]["triggers"]["oldest"] = $mouv;

  // Latest mouvement
  $mouv = CMouvFactory::create($_type);
  $mouv->loadLatest();
  $report[$_type]["triggers"]["latest"] = $mouv;

  // Marked and unmarked counts
  foreach ($marked as $_marked) {
    $count                                          = $mouv->count($_marked);
    $report[$_type]["triggers"]["marked"][$_marked] = $count;
  }

  // Available
  $report[$_type]["triggers"]["available"] = $mouv->countAvailable();

  // Marks
  $report[$_type]["marks"]["oldest"]    = $mouv->loadOldestMark();
  $report[$_type]["marks"]["latest"]    = $mouv->loadLatestMark();
  $report[$_type]["marks"]["all"]       = $mouv->countAllMarks();
  $report[$_type]["marks"]["purgeable"] = $mouv->countOlderMarks($report[$_type]["triggers"]["oldest"]->rec);

  // Obsolete marks in error
  // Should not be used wrt to purgeable marks
  $count                               = $mouv->count(true, $report[$_type]["triggers"]["oldest"]->rec);
  $report[$_type]["marks"]["obsolete"] = $count;
}

$smarty = new CSmartyDP;
$smarty->assign("report", $report);
$smarty->display("mouvement_board.tpl");