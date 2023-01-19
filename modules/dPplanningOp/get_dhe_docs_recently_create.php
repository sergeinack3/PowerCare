<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkRead();

$rpps           = CView::get("rpps", "str");
$cabinet_id     = CView::get("cabinet_id", "num");
$context_guid   = CView::get("context_guid", "str");
$ext_patient_id = CView::get("ext_patient_id", "str");

CView::checkin();

//Recherche du praticien
$mediuser        = new CMediusers();
$mediuser->actif = "1";
$mediuser->rpps  = $rpps;
$mediuser->loadMatchingObjectEsc();

//Recherche du patient
$idexs = CIdSante400::getMatches("CPatient", "ext_patient_id-$cabinet_id", null);

if (!$mediuser->_id || !count($idexs)) {
  $json = ["context_guid" => null, "cabinet_id" => $cabinet_id];
  CApp::json($json);
}

$json = [
  "context_guid" => $context_guid,
  "docitems"     => []
];

/** @var CSejour|COperation $context */
$context = CMbObject::loadFromGuid($context_guid);

switch ($context->_class) {
  default:
  case "CSejour":
    $group = $context->loadRefEtablissement();
    break;
  case "COperation";
    $group = $context->loadRefSejour()->loadRefEtablissement();
}

foreach ($context->loadRefsDocItems() as $_docitem) {
  $_json = [
    "docitem_guid" => $_docitem->_guid,
    "author"       => $_docitem->loadRefAuthor()->_view,
    "group"        => $group->_view
  ];

  switch ($_docitem->_class) {
    default:
    case "CCompteRendu":
      $_json["name"]          = $_docitem->nom;
      $_json["creation_date"] = $_docitem->creation_date;
      break;

    case "CFile":
      $_json["name"]          = $_docitem->file_name;
      $_json["creation_date"] = $_docitem->file_date;
  }

  $json["docitems"][] = $_json;
}

foreach ($context->loadRefsForms() as $_form) {
  $json["docitems"][] = [
    "docitem_guid"  => $_form->_guid,
    "author"        => $_form->loadRefOwner()->_view,
    "group"         => $group->_view,
    "name"          => $_form->loadRefExClass()->name,
    "creation_date" => $_form->datetime_create
  ];
}

CApp::json($json);
