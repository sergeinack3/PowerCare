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
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CSurvEchoGrossesse;

CCanDo::checkEdit();
$echographie_id = CView::get("echographie_id", "ref class|CSurvEchoGrossesse");
$grossesse_id   = CView::get("grossesse_id", "ref class|CGrossesse");
$date           = CView::get("date", "date");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$patient = $grossesse->loadRefParturiente();

$echographie = new CSurvEchoGrossesse();
if (!$echographie->load($echographie_id)) {
  $echographie->grossesse_id = $grossesse_id;
}

$echographie->loadRefEchoChildren($date);

$foetus = array();

$i = 1;

if (!$echographie->_id) {
  for ($i = 1; $i <= $grossesse->nb_foetus; $i++) {
    $echo               = new CSurvEchoGrossesse();
    $echo->num_enfant   = $i;
    $echo->grossesse_id = $grossesse_id;
    $foetus[$i]         = $echo;
  }
}
else {
  foreach ($echographie->_ref_echo_children as $_echo_child) {
    $foetus[$i] = $_echo_child;

    $i++;
  }
}

$smarty = new CSmartyDP();
$smarty->assign("grossesse"  , $grossesse);
$smarty->assign("patient"    , $patient);
$smarty->assign("echographie", $echographie);
$smarty->assign("foetus"     , $foetus);
$smarty->display("edit_echographie");

