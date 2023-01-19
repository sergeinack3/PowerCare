<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Cim10\CFavoriCIM10;

CCanDo::checkRead();

$tag_id = CView::get('tag_id', 'ref class|CTag');
$reload = CView::get('reload', 'bool default|0');

CView::checkin();

$user = CUser::get();

// Recherche des codes favoris
$favori = new CFavoriCIM10();
$where = array();
$where["favoris_user"] = "= '$user->_id'";

$ljoin = array();
if ($tag_id) {
  $ljoin["tag_item"] = "tag_item.object_id = favoris_id AND tag_item.object_class = 'CFavoriCIM10'";
  $where["tag_item.tag_id"] = "= '$tag_id'";
}

/** @var CFavoriCIM10[] $favoris */
$favoris = $favori->loadList($where, "favoris_code", null, null, $ljoin);

$codes = array();
foreach ($favoris as $_favori) {
  $favoris_code = $_favori->favoris_code;

  $_favori->loadRefsTagItems();

  $code = CCodeCIM10::get($favoris_code);
  $code->_favoris_id = $_favori->favoris_id;
  $code->_ref_favori = $_favori;
  $code->occ = "0";

  $codes[$favoris_code] = $code;
}

// Chargement des favoris calculés, si pas de choix de tag
$listCimStat = array();

if (!$tag_id) {
  $ds = CSQLDataSource::get("std");
  $sql = "SELECT DP, count(DP) as nb_code
          FROM `sejour`
          WHERE sejour.praticien_id = '$user->_id'
          AND DP IS NOT NULL
          AND DP != ''
          GROUP BY DP
          ORDER BY count(DP) DESC
          LIMIT 10;";
  $cimStat = $ds->loadList($sql);

  foreach ($cimStat as $value) {
    $DP = $value["DP"];

    $code = CCodeCIM10::get($DP);
    $code->_favoris_id = "0";
    $code->occ = $value["nb_code"];

    $listCimStat[$DP] = $code;
  }
}

// Fusion des deux tableaux de favoris
$fusionCim = $listCimStat;
  
foreach ($codes as $keycode => $code) {
  if (!array_key_exists($keycode, $fusionCim)) {
    $fusionCim[$keycode] = $code;
    continue;
  }
}

$tag_tree = CFavoriCIM10::getTree($user->_id);
  
// Création du template
$smarty = new CSmartyDP();

$smarty->assign("cim10"    , new CCodeCIM10());
$smarty->assign("fusionCim", $fusionCim);
$smarty->assign("tag_tree" , $tag_tree);
$smarty->assign("tag_id"   , $tag_id);
$smarty->assign('reload'   , $reload);

$smarty->display("inc_favoris.tpl");
