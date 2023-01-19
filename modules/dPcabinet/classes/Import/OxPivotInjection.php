<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;

/**
 * Pivot for injection import
 */
class OxPivotInjection extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_PATIENT           = 'patient';
    public const FIELD_PRACTITIONER_NAME = 'practitioner_name';
    public const FIELD_INJECTION_DATE    = 'injection_date';
    public const FIELD_BATCH             = 'batch';
    public const FIELD_SPECIALITY        = 'speciality';
    public const FIELD_REMARQUES         = 'remarques';
    public const FIELD_CIP_PRODUCT       = 'cip_product';
    public const FIELD_EXPIRATION_DATE   = 'expiration_date';
    public const FIELD_RECALL_AGE        = 'recall_age';
    public const FIELD_TYPE_VACCIN       = 'type_vaccin';

    protected const FILE_NAME = GenericImport::INJECTION;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID                => $this->buildFieldId('Identifiant unique de l\'injection'),
                self::FIELD_PATIENT           => $this->buildFieldExternalId(
                    self::FIELD_PATIENT,
                    'Identifiant unique du patient',
                    true
                ),
                self::FIELD_PRACTITIONER_NAME => $this->buildFieldPractitionerName(),
                self::FIELD_INJECTION_DATE    => $this->buildFieldInjectionDate(),
                self::FIELD_BATCH             => $this->buildFieldBatch(),
                self::FIELD_SPECIALITY        => $this->buildFieldSpeciality(),
                self::FIELD_REMARQUES         => $this->buildFieldRemarques(),
                self::FIELD_CIP_PRODUCT       => $this->buildFieldCipProduct(),
                self::FIELD_EXPIRATION_DATE   => $this->buildFieldExpirationDate(),
                self::FIELD_RECALL_AGE        => $this->buildFieldRecallAge(),
                self::FIELD_TYPE_VACCIN       => $this->buildFieldTypeVaccin(),
            ];
        }
    }

    private function buildFieldPractitionerName(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PRACTITIONER_NAME,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom du professionnel de santé'
        );
    }

    private function buildFieldInjectionDate(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_INJECTION_DATE,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Date et heure de l\'injection du vaccin',
            true
        );
    }

    private function buildFieldBatch(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_BATCH,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Lot du vaccin');
    }

    private function buildFieldSpeciality(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SPECIALITY,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Produit du vaccin',
            true
        );
    }

    private function buildFieldRemarques(): FieldDescription
    {
        return $this->buildFieldLongText(
            self::FIELD_REMARQUES,
            'Remarques lors de l\'injection'
        );
    }

    private function buildFieldCipProduct(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CIP_PRODUCT,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Code CIP du produit du vaccin'
        );
    }

    private function buildFieldExpirationDate(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_EXPIRATION_DATE,
            20,
            FieldDescription::FIELD_TYPE_DATE,
            'Date d\'expiration'
        );
    }

    private function buildFieldRecallAge(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_RECALL_AGE,
            11,
            FieldDescription::FIELD_TYPE_INT,
            'Age du rappel (en mois)'
        );
    }

    private function buildFieldTypeVaccin(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TYPE_VACCIN,
            80,
            implode(", ", CVaccination::TYPES_VACCINATIONS),
            'Type de vaccin (valeur par défaut Autre)'
        );
    }
}
