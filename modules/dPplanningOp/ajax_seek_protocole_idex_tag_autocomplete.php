<?php
/**
 * @package Mediboard\planningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//Autcomplete : recherche de tags
//Le principe est de remonter une liste d'identifiants aléatoires, correspondant chacun à un tag

use Ox\Core\CApp;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Sante400\CIdSante400;

$object_class = CView::get("object_class", "str notNull");
$field        = CView::get("field", "str");
$view_field   = CView::get("view_field", "str default|{$field}");
$input_field  = CView::get("input_field", "str default|{$view_field}");
$show_view    = CView::get("show_view", "bool default|1");
$keywords     = CView::get("{$input_field}", "str");
$limit        = CView::get('limit', "num default|30");

CView::checkin();
CView::enableSlave();

/** @var CIdSante400 $object */
$object = new CIdSante400();

$where                 = array();
$where["object_class"] = " = '$object_class'";

$candidates = $object->loadList($where, null, 30);

$tags = array();
foreach ($candidates as $candidate) {
  if (!in_array($candidate->tag, $tags)) {
    $tags[] = $candidate->tag;
  }
}

if ($keywords !== "") {
  $tags = array_values(array_filter($tags, function ($v) use ($keywords) {

    if(strpos($v, $keywords) !== false){
      return true;
    }

    return false;
  }));


}


$smarty = new CSmartyDP();
$smarty->assign('tags', $tags);
$smarty->assign("input", "");
$smarty->display('inc_field_seek_protocole_idex_tag_autocomplete');