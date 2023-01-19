<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Soins\CObjectifSoin;
use Ox\Mediboard\Soins\CObjectifSoinCible;

$object_id    = CView::get("object_id", "num");
$object_class = CView::get("object_class", "enum list|CPrescriptionLineElement|CPrescriptionLineMedicament|" .
  "CPrescriptionLineComment|CCategoryPrescription|CAdministration|CPrescriptionLineMix");
$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

// Chargement des objectifs ouverts
$objectif_soin            = new CObjectifSoin();
$objectif_soin->sejour_id = $sejour_id;
$objectif_soin->statut    = "ouvert";
$objectifs_list           = $objectif_soin->loadMatchingList("libelle ASC");

// Chargement des objectifs liés à la cible
$objectif_cible                   = new CObjectifSoinCible();
$where["object_class"]            = " = '$object_class'";
$where["object_id"]               = " = '$object_id'";
$where["objectif_soin.sejour_id"] = " = '$sejour_id'";
$ljoin["objectif_soin"]           = "objectif_soin.objectif_soin_id = objectif_soin_cible.objectif_soin_id";
$objectifs_cible                  = $objectif_cible->loadList($where, "libelle ASC", null, null, $ljoin);

$objectifs = array();
foreach ($objectifs_cible as $_objectif_cible) {
  $_objectif_cible->loadRefObjectifSoin();
  $objectifs[$_objectif_cible->_id] = $_objectif_cible->_ref_objectif_soin;
}

$object = "";
if ($object_class && $object_id) {
  $object = new $object_class;
  $object->load($object_id);
}

$smarty = new CSmartyDP;
$smarty->assign("objectifs", $objectifs);
$smarty->assign("objectifs_list", $objectifs_list);
$smarty->assign("object", $object);
$smarty->display("inc_list_objectifs");
