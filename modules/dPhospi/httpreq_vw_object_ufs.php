<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

// Récupération des paramètres
$object_guid = CView::get("object_guid", "str");
CView::checkin();

$object = CMbObject::loadFromGuid($object_guid);

$ufs = array();
if ($object instanceof CSejour || $object instanceof CAffectation) {
  $object->loadRefUfs();
  $ufs[$object->uf_hebergement_id] = $object->_ref_uf_hebergement;
  $ufs[$object->uf_soins_id]       = $object->_ref_uf_soins;
  $ufs[$object->uf_medicale_id]    = $object->_ref_uf_medicale;
}
else {
  /* @var CService|CChambre|CLit|CMediusers|CFunctions|CProtocole $object */
  $affectations_uf = $object->loadBackRefs("ufs");
  foreach ($affectations_uf as $_affect_uf) {
    /* @var CAffectationUniteFonctionnelle $_affect_uf */
    $uf            = $_affect_uf->loadRefUniteFonctionnelle();
    $ufs[$uf->_id] = $uf;
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("ufs", $ufs);

$smarty->display("httpreq_vw_object_ufs.tpl");
