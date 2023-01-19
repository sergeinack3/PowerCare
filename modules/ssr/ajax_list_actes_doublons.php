<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkAdmin();

$debut   = CView::get("_debut", "dateTime notNull");
$fin     = CView::get("_fin", "dateTime notNull");
$current = CView::get("current", "num default|0");
$dry_run = CView::get("dry_run", "bool");

CView::checkin();

$where = array(
  "debut" => "BETWEEN '$debut' AND '$fin'"
);

$ljoin = array(
  "acte_csarr" => "acte_csarr.evenement_ssr_id = evenement_ssr.evenement_ssr_id"
);

$request = new CRequest();
$request->addSelect("DISTINCT evenement_ssr.evenement_ssr_id");
$request->addTable("evenement_ssr");
$request->addWhere($where);
$request->addLJoin($ljoin);
$request->addGroup("evenement_ssr.evenement_ssr_id, acte_csarr.code");
$request->addHaving("COUNT(acte_csarr.code) > 1");

$ds             = CSQLDataSource::get("std");
$evenements_ids = $ds->loadColumn($request->makeSelect());

$where = array(
  "evenement_ssr.evenement_ssr_id" => CSQLDataSource::prepareIn($evenements_ids)
);

$evenement  = new CEvenementSSR();
$evenements = $evenement->loadList($where, null, "$current,20");

CStoredObject::massLoadFwdRef($evenements, "sejour_id");
CStoredObject::massLoadBackRefs($evenements, "actes_csarr");
/** @var CEvenementSSR $_evenement */
foreach ($evenements as $_evenement) {
  $_evenement->loadRefSejour();
  $_evenement->loadRefsActesCsARR();

  if (!$dry_run) {
    $actes_by_code = array();
    $actes_deleted = 0;
    foreach ($_evenement->_ref_actes_csarr as &$_acte) {
      $actes_by_code[$_acte->code][] = $_acte;
    }

    foreach ($actes_by_code as $code => $_actes) {
      if (count($_actes) == 1) {
        continue;
      }
      // On retire le premier acte car il faut le conserver
      array_shift($_actes);
      foreach ($_actes as $_acte) {
        $actes_deleted++;
        $_acte->delete();
      }
    }

    $_evenement->_actes_deleted = $actes_deleted;
  }
}

$smarty = new CSmartyDP();

$smarty->assign("current", $current);
$smarty->assign("evenements", $evenements);
$smarty->assign("total", count($evenements_ids));
$smarty->assign("dry_run", $dry_run);

$smarty->display("inc_list_actes_doublons");