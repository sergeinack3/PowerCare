<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Description
 */
class OxPivotActeCCAM extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_CONSULTATION        = 'consultation';
    public const FIELD_EXECUTANT           = 'executant';
    public const FIELD_CODE_ACTE           = 'code_acte';
    public const FIELD_DATE_EXECUTION      = 'date_execution';
    public const FIELD_CODE_ACTIVITE       = 'code_activite';
    public const FIELD_CODE_PHASE          = 'code_phase';
    public const FIELD_MODIFICATEURS       = 'modificateurs';
    public const FIELD_MONTANT_BASE        = 'montant_base';
    public const FIELD_MONTANT_DEPASSEMENT = 'montant_depassement';

    protected const FILE_NAME = GenericImport::ACTE_CCAM;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID                  => $this->buildFieldId('Identifiant unique de l\'acte CCAM'),
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
                self::FIELD_DATE_EXECUTION      => $this->builldFieldExecution(),
                self::FIELD_CODE_ACTIVITE       => $this->buildFieldCodeActivite(),
                self::FIELD_CODE_PHASE          => $this->buildFieldCodePhase(),
                self::FIELD_MODIFICATEURS       => $this->buildFieldModificateurs(),
                self::FIELD_MONTANT_BASE        => $this->buildFieldMontantBase(),
                self::FIELD_MONTANT_DEPASSEMENT => $this->buildFieldMontantDepassement(),
            ];
        }
    }

    private function buildFieldCode(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CODE_ACTE,
            7,
            FieldDescription::FIELD_TYPE_STRING,
            'Code de l\'acte CCAM',
            true
        );
    }

    private function builldFieldExecution(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_DATE_EXECUTION,
            18,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Code de l\'acte CCAM',
            true
        );
    }

    private function buildFieldCodeActivite(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CODE_ACTIVITE,
            4,
            FieldDescription::FIELD_TYPE_INT,
            'Code activité l\'acte CCAM',
            true
        );
    }

    private function buildFieldCodePhase(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CODE_PHASE,
            4,
            FieldDescription::FIELD_TYPE_INT,
            'Code de la phase l\'acte CCAM',
            true
        );
    }

    private function buildFieldModificateurs(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MODIFICATEURS,
            4,
            FieldDescription::FIELD_TYPE_STRING,
            'Modificateurs de l\'acte CCAM'
        );
    }

    private function buildFieldMontantDepassement(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MONTANT_DEPASSEMENT,
            12,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Montant de dépassement l\'acte CCAM'
        );
    }

    private function buildFieldMontantBase(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MONTANT_BASE,
            12,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Montant de base l\'acte CCAM'
        );
    }
}
