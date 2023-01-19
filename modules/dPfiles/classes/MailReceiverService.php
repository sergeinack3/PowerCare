<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\CompteRendu\CDestinataire;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * A service that get the CDestinaire objects from a given object
 */
class MailReceiverService
{
    public const ADDRESS_TYPE_MAIL = 'mail';
    public const ADDRESS_TYPE_APICRYPT = 'apicrypt';
    public const ADDRESS_TYPE_MSSANTE = 'mssante';

    public const RECEIVER_TYPE_MEDICAL = 'medical';
    public const RECEIVER_TYPE_CASUAL  = 'casual';
    public const RECEIVER_TYPE_ALL     = 'all';

    protected static array $classes = [
        'CConsultation',
        'CPatient',
        'CSejour',
        'CFactureCabinet',
    ];

    protected CMbObject $object;

    /**
     * @param CMbObject $object
     *
     * @throws CMbException
     */
    public function __construct(CMbObject $object)
    {
        /* In case of a CPrescription, get the linked object instead */
        if ($object instanceof CPrescription) {
            $object = $object->loadRefObject();
        } elseif ($object instanceof CEvenementPatient) {
            $object = $object->loadRefPatient();
        } elseif ($object instanceof COperation) {
            $object = $object->loadRefSejour();
        } elseif ($object instanceof CConsultAnesth) {
            $object = $object->loadRefConsultation();
        } elseif ($object instanceof CFactureCabinet) {
            $object = $object->loadRefPatient();
        }

        if (!in_array($object->_class, self::$classes)) {
            throw new CMbException('MailReceiverService-error-class_not_handled');
        }

        $this->object = $object;
    }

    /**
     * @return CMbObject
     */
    public function getObject(): CMbObject
    {
        return $this->object;
    }

    /**
     * Returns the list of receivers of the given address type.
     *
     * @return CDestinataire[][]
     */
    public function getReceivers(
        string $address_type = self::ADDRESS_TYPE_MAIL,
        string $receiver_type = self::RECEIVER_TYPE_ALL
    ): array {
        $receivers = [];

        switch ($this->object->_class) {
            case 'CPatient':
                $result = $this->getReceiversFromPatient(
                    $this->object,
                    $address_type,
                    $receiver_type
                );
                $receivers = $result;
                break;
            case 'CConsultation':
            case 'CSejour':
                /** @var CCodable $object */
                $object = $this->object;
                $object->loadRefPatient();
                $praticien = $object->loadRefPraticien();

                $result = $this->getReceiversFromPatient(
                    $object->_ref_patient,
                    $address_type,
                    $receiver_type,
                    $praticien->_id
                );

                $receivers = $result;

                $result = $this->getReceiversFromMediuser(
                    $object->_ref_praticien,
                    $address_type,
                    $receiver_type
                );

                if (count($result)) {
                    $receivers[$object->_ref_praticien->_class] = $result;
                }
                break;
            default:
        }

        return $receivers;
    }

    /**
     * @param CPatient $patient
     * @param string   $address_type
     * @param string   $receiver_type
     * @param int      $user_id
     * @return array
     */
    protected function getReceiversFromPatient(
        CPatient $patient,
        string $address_type,
        string $receiver_type = self::RECEIVER_TYPE_ALL,
        int $user_id = null
    ): array {
        $receivers = [];
        switch ($receiver_type) {
            case self::RECEIVER_TYPE_CASUAL:
                $result = $this->getCasualReceiversFromPatient($patient, $address_type);
                if (count($result)) {
                    $receivers[$patient->_class] = $result;
                }
                break;
            case self::RECEIVER_TYPE_MEDICAL:
                $result = $this->getMedicalReceiversFromPatient($patient, $address_type, $user_id);
                if (count($result)) {
                    $receivers['CMedecin'] = $result;
                }
                break;
            case self::RECEIVER_TYPE_ALL:
            default:
                $result = $this->getCasualReceiversFromPatient($patient, $address_type);
                if (count($result)) {
                    $receivers[$patient->_class] = $result;
                }
                $result = $this->getMedicalReceiversFromPatient($patient, $address_type, $user_id);
                if (count($result)) {
                    $receivers['CMedecin'] = $result;
                }
        }

        return $receivers;
    }

    /**
     * @param CPatient $patient
     * @param string   $address_type
     * @param int      $user_id
     *
     * @return array
     */
    protected function getMedicalReceiversFromPatient(CPatient $patient, string $address_type, int $user_id = null): array
    {
        $receivers = [];

        $patient->loadRefsCorrespondants();
        $patient->_ref_medecin_traitant->setExercicePlace($patient->loadRefMedecinTraitantExercicePlace());

        $receivers['traitant'] = CDestinataire::getFromCMedecin(
            $patient->_ref_medecin_traitant,
            'traitant',
            $address_type,
            $user_id
        );

        foreach ($patient->_ref_medecins_correspondants as $correspondant) {
            $correspondant->_ref_medecin->setExercicePlace($correspondant->loadRefMedecinExercicePlace());
            $receivers[$correspondant->_guid] = CDestinataire::getFromCMedecin(
                $correspondant->_ref_medecin,
                'correspondant',
                $address_type,
                $user_id
            );
        }

        return $receivers;
    }

    /**
     * @param CPatient $patient
     * @param string   $address_type
     *
     * @return array
     */
    protected function getCasualReceiversFromPatient(CPatient $patient, string $address_type): array
    {
        $receivers = [];

        if ($address_type === self::ADDRESS_TYPE_MAIL) {
            /* Add a receiver for the patient himself */
            if ($patient->email) {
                $receivers[] = CDestinataire::getFromPatient($patient);
            }

            $patient->loadRefsCorrespondantsPatient();
            foreach ($patient->_ref_correspondants_patient as $correspondant) {
                $receivers[$correspondant->_guid] = CDestinataire::getFromCorrespondantPatient($correspondant);
            }
        }

        return $receivers;
    }

    /**
     * @param CMediusers $user
     * @param string     $address_type
     * @param string     $receiver_type
     *
     * @return array
     */
    protected function getReceiversFromMediuser(
        CMediusers $user,
        string $address_type,
        string $receiver_type = self::RECEIVER_TYPE_ALL
    ): array {
        switch ($receiver_type) {
            case self::RECEIVER_TYPE_CASUAL:
                $receivers = [];
                break;
            case self::RECEIVER_TYPE_MEDICAL:
            case self::RECEIVER_TYPE_ALL:
            default:
                $receivers = [
                    CDestinataire::getFromMediuser($user, 'praticien', $address_type)
                ];
        }

        return $receivers;
    }
}
