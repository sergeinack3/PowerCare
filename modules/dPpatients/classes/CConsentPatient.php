<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Consentement patient
 */
class CConsentPatient extends CMbObject
{
    public const FIELD_CONSENT = [
        CRGPDConsent::TAG_TERRESANTE      => 'terresante',
        CRGPDConsent::TAG_DMP             => 'dmp',
        CRGPDConsent::TAG_MSSANTE_PATIENT => 'mssante_patient',
        CRGPDConsent::TAG_MSSANTE_PRO     => 'mssante_pro',
    ];

    /** @var int Primary key */
    public $consent_patient_id;

    /** @var string */
    public $tag;

    /** @var string */
    public $status;

    /** @var string */
    public $acceptance_datetime;

    /** @var string */
    public $refusal_datetime;

    /** @var int */
    public $object_id;

    /** @var string */
    public $object_class;

    /** @var int */
    public $group_id;

    /**
     * Enregistrement du consentement patient
     *
     * @param CPatient $patient
     * @param int      $tag
     *
     * @return string|null
     */
    public static function storeConsent(CPatient $patient, int $tag = null): ?string
    {
        if (!isset($patient->{'_consent_' . static::FIELD_CONSENT[$tag]})) {
            return null;
        }

        switch ($tag) {
            default:
            case CRGPDConsent::TAG_TERRESANTE:
                if (
                    !CModule::getActive("terreSante")
                    || !CAppUI::gconf("terreSante CConsentPatient patient_consents")
                ) {
                    return null;
                }
                break;
            case CRGPDConsent::TAG_DMP:
                if (!CMOdule::getActive('dmp')) {
                    return null;
                }
                break;

            case CRGPDConsent::TAG_MSSANTE_PATIENT:
            case CRGPDConsent::TAG_MSSANTE_PRO:
                if (!CModule::getActive('mssante')) {
                    return null;
                }
                break;
        }

        $consent               = new static();
        $consent->object_class = $patient->_class;
        $consent->object_id    = $patient->_id;
        $consent->group_id     = CGroups::loadCurrent()->_id;
        $consent->tag          = $tag;

        $consent->loadMatchingObject();

        if ($patient->{'_consent_' . static::FIELD_CONSENT[$tag]}) {
            if (!$consent->acceptance_datetime) {
                $consent->acceptance_datetime = "current";
                $consent->status              = CRGPDConsent::STATUS_ACCEPTED;
                $consent->refusal_datetime    = "";
            }
        } elseif (!$consent->refusal_datetime) {
            $consent->refusal_datetime    = "current";
            $consent->status              = CRGPDConsent::STATUS_REFUSED;
            $consent->acceptance_datetime = "";
        }

        return $consent->store();
    }

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "consent_patient";
        $spec->key   = "consent_patient_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                        = parent::getProps();
        $props["tag"]                 = "num";
        $props["status"]              = "enum list|5|6";
        $props["acceptance_datetime"] = "dateTime";
        $props["refusal_datetime"]    = "dateTime";
        $props["object_id"]           = "ref notNull class|CPatient meta|object_class back|patient_consents cascade";
        $props["object_class"]        = "enum list|CPatient notNull";
        $props["group_id"]            = "ref class|CGroups back|patient_consents";

        return $props;
    }
}
