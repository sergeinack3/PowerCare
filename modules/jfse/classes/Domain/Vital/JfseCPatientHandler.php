<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use Ox\Core\CStoredObject;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Patients\CPatient;

class JfseCPatientHandler extends ObjectHandler
{
    public function onAfterStore(CStoredObject $object): bool
    {
        if (!$this->isHandled($object) || !$object->_id) {
            return false;
        }

        if ($object instanceof CPatient) {
            if (
                !($object->_vitale_nir && $object->_vitale_birthdate && $object->_vitale_birthrank)
                || $object->_vitale_quality === null
            ) {
                return false;
            }

            $link             = new CJfsePatient();
            $link->patient_id = $object->_id;
            $link->nir        = $object->_vitale_nir;
            $link->loadMatchingObjectEsc();

            $link_is_child             = new CJfsePatient();
            $link_is_child->patient_id = $object->_id;
            $link_is_child->quality    = Beneficiary::CHILD_QUALITY;
            $link_is_child->loadMatchingObjectEsc();

            // If new patient, add a new link between the patient and the vital card.
            // If the patient already existed, update the link unless it's a child in which case, add a link.
            // Children can be linked to several vital cards (e.g. both parents)
            if ($object->_ref_current_log && $object->_ref_current_log->type === 'store') {
                if ($object->qual_beneficiaire === Beneficiary::CHILD_QUALITY && $link->nir !== $object->_vitale_nir) {
                    $link->_id = null;
                }
            } elseif (
                $object->qual_beneficiaire === str_pad(Beneficiary::INSURED_QUALITY, 2, '0')
                && $link_is_child->_id
            ) {
                // If the patient exists and has his personal vital card, links to parent vital cards must be deleted
                $link_delete             = new CJfsePatient();
                $link_delete->patient_id = $object->_id;
                $link_delete->quality    = Beneficiary::CHILD_QUALITY;

                foreach ($link_delete->loadMatchingListEsc() as $_link) {
                    $_link->delete();
                }
            }

            if (strlen($object->_vitale_nir_certifie) == 15) {
                $link->certified_nir = $object->_vitale_nir_certifie;
            }
            $link->birth_date          = $object->_vitale_birthdate;
            $link->birth_rank          = $object->_vitale_birthrank;
            $link->quality             = $object->_vitale_quality;
            $link->last_name           = $object->_vitale_lastname;
            $link->first_name          = $object->_vitale_firstname;
            $link->amo_regime_code     = $object->_vitale_code_regime;
            $link->amo_managing_fund   = $object->_vitale_code_caisse;
            $link->amo_managing_center = $object->_vitale_code_centre;
            $link->amo_managing_code   = $object->_vitale_code_gestion;

            $link->store();
        }

        return true;
    }

    public static function isHandled(CStoredObject $object): bool
    {
        if (!CModule::getActive("jfse")) {
            return false;
        }

        return (bool)($object instanceof CPatient);
    }
}
