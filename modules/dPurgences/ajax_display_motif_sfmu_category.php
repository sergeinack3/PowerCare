<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Urgences\CMotifSFMU;

$categorie = CView::get("categorie", "str");

CView::checkin();

$list_motif_sfmu = array();

if ($categorie) {
  $motif_sfmu      = new CMotifSFMU();
  $list_motif_sfmu = $motif_sfmu->loadList(array("categorie" => "= '$categorie'"), null, null, "motif_sfmu_id");
}

$smarty = new CSmartyDP();
$smarty->assign("list_motif_sfmu", $list_motif_sfmu);
$smarty->display("inc_display_motif_sfmu_category");