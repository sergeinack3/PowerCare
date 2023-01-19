<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CSurvEchoGrossesse;

CCanDo::checkEdit();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
$print        = CView::get("print", "bool default|0");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefGroup();
$grossesse->getDateAccouchement();
$grossesse->loadRefsNaissances();

$count_children    = 0;
$list_children     = array();
$list_children_new = array();
$test              = 0;
$list_cat = array(
  "lcc"             => array("button" => true, "text" => "mm", "class" => ""),
  "cn"              => array("button" => true, "text" => "mm", "class" => ""),
  "bip"             => array("button" => true, "text" => "mm", "class" => ""),
  "pc"              => array("button" => true, "text" => "mm", "class" => ""),
  "pa"              => array("button" => true, "text" => "mm", "class" => ""),
  "lf"              => array("button" => true, "text" => "mm", "class" => ""),
  "poids_foetal"    => array("button" => true, "text" => "g", "class" => ""),
  "dfo"             => array("button" => false, "text" => "mm", "class" => ""),
  "remarques"       => array("button" => false, "text" => "", "class" => "text"),
  "avis_dan"        => array("button" => false, "text" => "", "class" => ""),
  "pos_placentaire" => array("button" => false, "text" => "", "class" => "text"),
);

/** @var CSurvEchoGrossesse[] $echographies */
$echographies   = $grossesse->loadBackRefs("echographies", "date ASC");
$count_children = count($grossesse->loadBackRefs("echographies", "date ASC", null, "num_enfant"));

foreach ($echographies as $echographie) {
  $echographie->getSA();

  if ($grossesse->multiple) {
    $list_children[$echographie->num_enfant][$echographie->_id] = $echographie;

    foreach ($list_cat as $key_cat => $_cat) {
      $value_cat = $echographie->$key_cat;

      if ($key_cat == 'avis_dan') {
        $value_cat = $value_cat ? CAppUI::tr('Yes') : CAppUI::tr('No') ;
      }

      $list_cat[$key_cat]["datas"][$echographie->_id] = $value_cat;
    }

    $list_children_new[$echographie->date]["sa"]                       = $echographie->_sa;
    $list_children_new[$echographie->date]["type_echo"]                = $echographie->type_echo;
    $list_children_new[$echographie->date]["counter"]                  = $count_children;
    $list_children_new[$echographie->date]["echos"][$echographie->_id] = $echographie;
  }
}

foreach ($list_children_new as $date => $data_echos) {
    // On recompte les échos en cas de transition grossesse simple -> grossesse multiple
    $list_children_new[$date]["counter"] = count($list_children_new[$date]["echos"]);
}

$patient = $grossesse->loadRefParturiente();

$smarty = new CSmartyDP();
$smarty->assign("grossesse"        , $grossesse);
$smarty->assign("count_children"   , $count_children);
$smarty->assign("surv_echo"        , $echographies);
$smarty->assign("list_children"    , $list_children);
$smarty->assign("list_children_new", $list_children_new);
$smarty->assign("print"            , $print);
$smarty->assign("list_cat"         , $list_cat);
$smarty->display("dossier_mater_echographies");

