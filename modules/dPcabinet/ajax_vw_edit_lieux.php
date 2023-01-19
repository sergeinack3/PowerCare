<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CLieuConsult;

CCanDo::checkEdit();

$lieu_id = CView::get("lieu_id", "ref class|CLieuConsult");
$prat_id = CView::get("prat_id", "ref class|CMediusers");

CView::checkin();

$lieu = new CLieuConsult();
$lieu->load($lieu_id);

if (!$lieu->_id) {
  $lieu->_prat_id = $prat_id;
}

$smarty = new CSmartyDP();

$smarty->assign("lieu", $lieu);

$smarty->display("inc_edit_lieu_consult.tpl");