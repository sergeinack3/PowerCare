<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CDepistageGrossesse;

CCanDo::checkEdit();

$depistage_id = CView::get("depistage_id", "ref class|CDepistageGrossesse");
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
CView::checkin();

$depistage = new CDepistageGrossesse();
if (!$depistage->load($depistage_id)) {
  $depistage->grossesse_id = $grossesse_id;
}

$depistage->updateFormFields();
$depistage->loadBackRefs("depistages_customs");

$smarty = new CSmartyDP();
$smarty->assign("depistage", $depistage);
$smarty->display("edit_depistage.tpl");

