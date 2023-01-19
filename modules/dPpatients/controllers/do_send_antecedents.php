<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CDossierMedical;

CCanDo::checkEdit();

$antecedents_guid = CView::get("atcds_selected", "str");
$object_class     = CView::get("object_class", "enum list|CSejour|CPatient");
$object_id        = CView::get("object_id", "ref meta|object_class");
CView::checkin();

$counter = 0;

// Les antécédents a envoyés
foreach ($antecedents_guid as $_antecedent_guid) {
  $antecedent_enfant = new CAntecedent();
  /** @var CAntecedent $antecedent_parent */
  $antecedent_parent = CMbObject::loadFromGuid($_antecedent_guid);

  $antecedent_enfant->type               = 'fam';
  $antecedent_enfant->appareil           = $antecedent_parent->appareil;
  $antecedent_enfant->rques              = $antecedent_parent->rques;
  $antecedent_enfant->creation_date      = CMbDT::dateTime();
  $antecedent_enfant->owner_id           = CMediusers::get()->_id;
  $antecedent_enfant->origin             = "autre";
  $antecedent_enfant->origin_autre       = "Parent";
  $antecedent_enfant->dossier_medical_id = CDossierMedical::dossierMedicalId($object_id, $object_class);
  $antecedent_enfant->store();

  $counter++;
}

CAppUI::setMsg(CAppUI::tr("CAntecedent-msg-create") . " x $counter");
echo CAppUI::getMsg();
