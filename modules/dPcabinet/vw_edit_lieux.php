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
use Ox\Mediboard\Cabinet\CAgendaPraticien;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$mediuser = CMediusers::get();
$praticien_id  = CView::get("praticien_id", "ref class|CUser");

CView::checkin();

// Liste des chirurgiens
$mediusers     = new CMediusers();
$listPraticien = $mediusers->loadProfessionnelDeSanteByPref(PERM_EDIT);
$assoc         = new CAgendaPraticien();

$smarty = new CSmartyDP();
$smarty->assign("praticien_id", $praticien_id);
$smarty->assign("listPraticien", $listPraticien);
$smarty->assign("assoc", $assoc);
$smarty->display("vw_edit_lieux");
