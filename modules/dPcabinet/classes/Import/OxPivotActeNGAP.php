<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Description
 */
class OxPivotActeNGAP extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_CONSULTATION        = 'consultation';
    public const FIELD_EXECUTANT           = 'executant';
    public const FIELD_CODE_ACTE           = 'code_acte';
    public const FIELD_DATE_EXECUTION      = 'date_execution';
    public const FIELD_QUANTITE            = 'quantite';
    public const FIELD_COEFFICIENT         = 'coefficient';
    public const FIELD_MONTANT_BASE        = 'montant_base';
    public const FIELD_MONTANT_DEPASSEMENT = 'montant_depassement';
    public const FIELD_NUMERO_DENT         = 'numero_dent';

    protected const FILE_NAME = GenericImport::ACTE_NGAP;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID                  => $this->buildFieldId('Identifiant unique de l\'acte NGAP'),
                self::FIELD_CONSULTATION        => $this->buildFieldExternalId(
                    self::FIELD_CONSULTATION,
                    'Consultation de rattachement de l\'acte',
                    true
                ),
                self::FIELD_EXECUTANT           => $this->buildFieldExternalId(
                    self::FIELD_EXECUTANT,
                    'Praticien responsable de l\'exécution de l\'acte',
                    true
                ),
                self::FIELD_CODE_ACTE           => $this->buildFieldCode(),
                self::FIELD_DATE_EXECUTION      => $this->buildFieldExecution(),
                self::FIELD_QUANTITE            => $this->buildFieldQuantite(),
                self::FIELD_COEFFICIENT         => $this->buildFieldCoeff(),
                self::FIELD_MONTANT_BASE        => $this->buildFieldMontantBase(),
                self::FIELD_MONTANT_DEPASSEMENT => $this->buildFieldMontantDepassement(),
                self::FIELD_NUMERO_DENT         => $this->buildFieldNumeroDent(),
            ];
        }
    }

    private function buildFieldCode(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CODE_ACTE,
            5,
            FieldDescription::FIELD_TYPE_STRING,
            'Code de l\'acte NGAP',
            true
        );
    }

    private function buildFieldExecution(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_DATE_EXECUTION,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Date d\'exécution',
            true
        );
    }

    private function buildFieldQuantite(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_QUANTITE,
            11,
            FieldDescription::FIELD_TYPE_INT,
            'Nombre d\'occurences de l\'acte NGAP',
            true
        );
    }

    private function buildFieldCoeff(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_COEFFICIENT,
            12,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Coefficient l\'acte NGAP',
            true
        );
    }

    private function buildFieldNumeroDent(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NUMERO_DENT,
            4,
            FieldDescription::FIELD_TYPE_INT,
            'Numéro de la dent de l\'acte NGAP'
        );
    }

    private function buildFieldMontantDepassement(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MONTANT_DEPASSEMENT,
            12,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Montant de dépassement l\'acte NGAP'
        );
    }

    private function buildFieldMontantBase(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MONTANT_BASE,
            12,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Montant de base l\'acte NGAP'
        );
    }
}
