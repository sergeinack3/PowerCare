<?php
/**
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Sip;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\CEAIObjectHandler;
use Ox\Interop\Eai\CGroupDomain;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CSipObjectHandler
 * SIP Event Handler
 */
class CSipObjectHandler extends CEAIObjectHandler
{
    /**
     * @var array
     */
    public static $handled = [
        "CPatient",
        "CCorrespondantPatient",
        "CIdSante400",
        "CPatientLink",
        "CAntecedent",
        'CCorrespondant',
        'CSourceIdentite',
    ];

    /**
     * @inheritdoc
     */
    public static function isHandled(CStoredObject $object)
    {
        return !$object->_ignore_eai_handlers && in_array($object->_class, self::$handled);
    }

    /**
     * @inheritdoc
     */
    public function onBeforeStore(CStoredObject $object)
    {
        if (!parent::onBeforeStore($object)) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function onAfterStore(CStoredObject $object)
    {
        if (!parent::onAfterStore($object)) {
            return;
        }

        // Si pas de tag patient
        if (!CAppUI::conf("eai use_domain") && !CAppUI::conf("dPpatients CPatient tag_ipp")) {
            throw new CMbException("no_tag_defined");
        }

        $this->sendFormatAction("onAfterStore", $object);
    }

    /**
     * @inheritdoc
     */
    public function onBeforeMerge(CStoredObject $object)
    {
        if (!parent::onBeforeMerge($object)) {
            return false;
        }

        if (!$object instanceof CPatient) {
            return false;
        }

        /** @var CPatient $patient */
        $patient = $object;

        if (!$patient_elimine_id = array_key_first($object->_merging)) {
            return false;
        }

        $patient_elimine = new CPatient();
        $patient_elimine->load($patient_elimine_id);
        if (!$patient_elimine->_id) {
            return false;
        }

        $object->_fusion = [];
        if (CAppUI::conf("eai use_domain")) {
            $domains = CDomain::getAllDomains(CGroupDomain::DOMAIN_TYPE_PATIENT);
            foreach ($domains as $_domain) {
                $patient->_IPP = null;
                $idexPatient   = CIdSante400::getMatchFor($patient, $_domain->tag);
                $patient1_ipp  = $idexPatient->id400;

                $patient_elimine->_IPP = null;
                $idexPatientElimine    = CIdSante400::getMatchFor($patient_elimine, $_domain->tag);
                $patient2_ipp          = $idexPatientElimine->id400;

                // Eviter de prendre des idex de tous les patients en cas de problème
                if (!$patient->_id || !$patient_elimine->_id) {
                    continue;
                }

                $idexs = array_merge(
                    CIdSante400::getMatches($patient->_class, $_domain->tag, null, $patient->_id),
                    CIdSante400::getMatches($patient_elimine->_class, $_domain->tag, null, $patient_elimine->_id)
                );

                $idexs_changed = [];
                if (count($idexs) > 1) {
                    foreach ($idexs as $_idex) {
                        // On continue pour ne pas mettre en trash l'IPP du patient que l'on garde
                        if ($_idex->id400 == $patient1_ipp) {
                            continue;
                        }

                        $_idex->tag = CAppUI::conf('dPpatients CPatient tag_ipp_trash') . $_domain->tag;
                        if (!$msg = $_idex->store()) {
                            if ($_idex->object_id == $patient_elimine->_id) {
                                $idexs_changed[$_idex->_id] = $_domain->tag;
                            }
                        }
                    }
                }

                foreach ($_domain->loadRefsGroupDomains() as $_group_domain) {
                    $object->_fusion[$_group_domain->group_id] = [
                        "patientElimine" => $patient_elimine,
                        "patient1_ipp"   => $patient1_ipp,
                        "patient2_ipp"   => $patient2_ipp,
                        "idexs_changed"  => $idexs_changed,
                    ];

                    if (CModule::getActive('appFineClient')) {
                        $idexPatientAppFine        = CIdSante400::getMatchFor(
                            $patient,
                            CAppFineClient::getObjectTagAppFine(
                                $_group_domain->group_id
                            )
                        );
                        $idexPatientElimineAppFine = CIdSante400::getMatchFor(
                            $patient_elimine,
                            CAppFineClient::getObjectTagAppFine(
                                $_group_domain->group_id
                            )
                        );

                        $object->_fusion[$_group_domain->group_id]['patient1_appFine'] = $idexPatientAppFine->id400;
                        $object->_fusion[$_group_domain->group_id]['patient2_appFine'] = $idexPatientElimineAppFine->id400;
                    }
                }
            }
        } else {
            foreach (CGroups::loadGroups() as $_group) {
                if (CMbArray::get($object->_fusion, $_group->_id)) {
                    continue;
                }

                /** @var CInteropSender $sender */
                $sender = $object->_eai_sender_guid ? CMbObject::loadFromGuid($object->_eai_sender_guid) : null;

                if ($sender && $sender->group_id == $_group->_id) {
                    continue;
                }
                $patient->_IPP = null;
                $patient->loadIPP($_group->_id);
                $patient1_ipp = $patient->_IPP;

                $patient_elimine->_IPP = null;
                $patient_elimine->loadIPP($_group->_id);
                $patient2_ipp = $patient_elimine->_IPP;

                // Passage en trash des IPP des patients
                $tap_IPP = CPatient::getTagIPP($_group->_id);
                if (!$tap_IPP) {
                    continue;
                }

                // Eviter de prendre des idex de tous les patients en cas de problème
                if (!$patient->_id || !$patient_elimine->_id) {
                    continue;
                }

                $idexPatient               = new CIdSante400();
                $idexPatient->tag          = $tap_IPP;
                $idexPatient->object_class = "CPatient";
                $idexPatient->object_id    = $patient->_id;
                $idexsPatient              = $idexPatient->loadMatchingList();

                $idexPatientElimine               = new CIdSante400();
                $idexPatientElimine->tag          = $tap_IPP;
                $idexPatientElimine->object_class = "CPatient";
                $idexPatientElimine->object_id    = $patient_elimine->_id;
                $idexsPatientElimine              = $idexPatientElimine->loadMatchingList();

                $idexs         = array_merge($idexsPatient, $idexsPatientElimine);
                $idexs_changed = [];
                if (count($idexs) > 1) {
                    foreach ($idexs as $_idex) {
                        // On continue pour ne pas mettre en trash l'IPP du patient que l'on garde
                        if ($_idex->id400 == $patient1_ipp) {
                            continue;
                        }

                        $old_tag = $_idex->tag;

                        $_idex->tag = CAppUI::conf('dPpatients CPatient tag_ipp_trash') . $tap_IPP;
                        if (!$msg = $_idex->store()) {
                            if ($_idex->object_id == $patient_elimine->_id) {
                                $idexs_changed[$_idex->_id] = $old_tag;
                            }
                        }
                    }
                }

                if (!$patient1_ipp && !$patient2_ipp) {
                    continue;
                }
            }

            $object->_fusion[$_group->_id] = [
                "patientElimine" => $patient_elimine,
                "patient1_ipp"   => $patient1_ipp,
                "patient2_ipp"   => $patient2_ipp,
                "idexs_changed"  => $idexs_changed,
            ];

            if (CModule::getActive('appFineClient')) {
                $idexPatientAppFine        = CIdSante400::getMatchFor(
                    $patient,
                    CAppFineClient::getObjectTagAppFine($_group->_id)
                );
                $idexPatientElimineAppFine = CIdSante400::getMatchFor(
                    $patient_elimine,
                    CAppFineClient::getObjectTagAppFine($_group->_id)
                );

                $object->_fusion[$_group->_id]['patient1_appFine'] = $idexPatientAppFine->id400;
                $object->_fusion[$_group->_id]['patient2_appFine'] = $idexPatientElimineAppFine->id400;
            }
        }

        $this->sendFormatAction("onBeforeMerge", $object);
    }

    /**
     * @inheritdoc
     */
    public function onAfterMerge(CStoredObject $object)
    {
        if (!parent::onAfterMerge($object)) {
            return;
        }

        if (!$object instanceof CPatient) {
            return false;
        }

        $this->sendFormatAction("onAfterMerge", $object);
    }

    /**
     * @inheritdoc
     */
    public function onBeforeDelete(CStoredObject $object)
    {
        if (!parent::onBeforeDelete($object)) {
            return;
        }

        $this->sendFormatAction("onBeforeDelete", $object);
    }

    /**
     * @inheritdoc
     */
    public function onAfterDelete(CStoredObject $object)
    {
        if (!parent::onAfterDelete($object)) {
            return;
        }

        $this->sendFormatAction("onAfterDelete", $object);
    }
}
