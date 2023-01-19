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
use Ox\Mediboard\Cabinet\CAgendaPraticien;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$lieu_id = CView::get("lieu_id", 'ref class|CLieuConsult');

CView::checkin();

$lieu = new CLieuConsult();
$lieu->load($lieu_id);

$assoc     = new CAgendaPraticien();
$assocList = $lieu->loadRefsAgendasPrat();

$mediusers     = new CMediusers();
$listPraticien = $mediusers->loadProfessionnelDeSanteByPref(PERM_EDIT);

foreach ($assocList as $_assoc) {
  $prat = $_assoc->loadRefPraticien();
  unset($listPraticien[$prat->_id]);
}


$smarty = new CSmartyDP();

$smarty->assign("lieu", $lieu);
$smarty->assign("assoc", $assoc);
$smarty->assign("assocList", $assocList);
$smarty->assign("listPraticien", $listPraticien);

$smarty->display("inc_agendas_lieu_consult");