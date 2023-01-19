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
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$praticien_id = CView::getRefCheckEdit("praticien_id", "ref class|CMediusers");
CView::checkin();

$lieu     = new CLieuConsult();
$mediuser = (CMediusers::find($praticien_id)) ?: CMediusers::get();
$lieux    = (!CCanDo::admin() || $praticien_id) ? $mediuser->loadRefsLieuxConsult(false) : $lieu->loadGroupList();

$smarty = new CSmartyDP();
$smarty->assign("lieu", $lieu);
$smarty->assign("lieux", $lieux);
$smarty->display("inc_list_lieux.tpl");
