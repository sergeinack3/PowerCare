<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CModeleToPack;

CCanDo::check();

$pack_id        = CView::get("pack_id", "ref class|CPack");
$object_class   = CView::get("object_class", "str");
$object_id      = CView::get("object_id", "ref class|$object_class");
$ext_cabinet_id = CView::get("_ext_cabinet_id", "num");
$callback       = CView::get("callback", "str");

CView::checkin();

$modele_to_pack  = new CModeleToPack();
$modeles_to_pack = $modele_to_pack->loadAllModelesFor($pack_id);

$modeles = [];
foreach ($modeles_to_pack as $_modele_to_pack) {
    $modele                               = CCompteRendu::findOrFail($_modele_to_pack->modele_id);
    $modeles[$_modele_to_pack->modele_id] = $modele;
}

$smarty = new CSmartyDP();
$smarty->assign("modeles", $modeles);
$smarty->assign("modeles_to_pack", $modeles_to_pack);
$smarty->assign("pack_id", $pack_id);
$smarty->assign("object_id", $object_id);
$smarty->assign("object_class", $object_class);
$smarty->display("inc_choose_compte_rendu");
