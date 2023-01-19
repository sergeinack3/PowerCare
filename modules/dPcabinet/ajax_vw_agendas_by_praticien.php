<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$praticien_id    = CView::getRefCheckRead("praticien_id", "ref class|CMediusers", true);
$plageconsult_id = CView::getRefCheckRead("plageconsult_id", "ref class|CPlageconsult");

CView::checkin();

// Plage de consultation selectionnée
$plage = new CPlageconsult();
$plage->load($plageconsult_id);

$praticien = new CMediusers();
$praticien->load($praticien_id);

$agendas_praticien = $praticien->_id ? $praticien->loadRefsAgendasPraticienByGroup(true) : [];

CStoredObject::massLoadFwdRef($agendas_praticien, "lieuconsult_id");

foreach ($agendas_praticien as $_agenda_praticien) {
  $_agenda_praticien->loadRefLieu();
}

$smarty = new CSmartyDP();

$smarty->assign("agendas_praticien", $agendas_praticien);
$smarty->assign("plage", $plage);

$smarty->display("inc_vw_agendas_by_praticien");
