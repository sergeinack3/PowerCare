<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Pivot for sejour import
 */
class OxPivotSejour extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_TYPE            = 'type';
    public const FIELD_ENTREE_PREVUE   = 'entree_prevue';
    public const FIELD_ENTREE_REELLE   = 'entree_reelle';
    public const FIELD_SORTIE_PREVUE   = 'sortie_prevue';
    public const FIELD_SORTIE_REELLE   = 'sortie_reelle';
    public const FIELD_LIBELLE         = 'libelle';
    public const FIELD_PATIENT         = 'patient';
    public const FIELD_PRATICIEN       = 'praticien';
    public const FIELD_PRESTATION      = 'prestation';
    public const FIELD_NDA             = 'nda';
    public const FIELD_MODE_TRAITEMENT = 'mode_traitement';
    public const FIELD_MODE_ENTREE     = 'mode_entree';
    public const FIELD_MODE_SORTIE     = 'mode_sortie';

    protected const FILE_NAME = GenericImport::SEJOUR;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID              => $this->buildFieldId('Identifiant unique du séjour'),
                self::FIELD_TYPE            => $this->buildFieldType(),
                self::FIELD_ENTREE_PREVUE   => $this->buildFieldEntreePrevue(),
                self::FIELD_ENTREE_REELLE   => $this->buildFieldEntreeReelle(),
                self::FIELD_SORTIE_PREVUE   => $this->buildFieldSortiePrevue(),
                self::FIELD_SORTIE_REELLE   => $this->buildFieldSortieReelle(),
                self::FIELD_LIBELLE         => $this->buildFieldLibelle(),
                self::FIELD_PATIENT         => $this->buildFieldPatient(),
                self::FIELD_PRATICIEN       => $this->buildFieldPraticien(),
                self::FIELD_PRESTATION      => $this->buildFieldPrestation(),
                self::FIELD_NDA             => $this->buildFieldNda(),
                self::FIELD_MODE_TRAITEMENT => $this->buildFieldModeTraitement(),
                self::FIELD_MODE_ENTREE     => $this->buildFieldModeEntree(),
                self::FIELD_MODE_SORTIE     => $this->buildFieldModeSortie(),
            ];
        }
    }

    private function buildFieldType(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TYPE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Type',
        );
    }

    private function buildFieldEntreePrevue(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_ENTREE_PREVUE,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Entrée prévue',
            true,
        );
    }

    private function buildFieldEntreeReelle(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_ENTREE_REELLE,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Entrée réelle',
        );
    }

    private function buildFieldSortiePrevue(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SORTIE_PREVUE,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Sortie prévue',
            true
        );
    }

    private function buildFieldSortieReelle(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SORTIE_REELLE,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Sortie réelle',
        );
    }

    private function buildFieldLibelle(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_LIBELLE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Libellé',
            true,
        );
    }

    private function buildFieldPatient(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PATIENT,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Identifiant unique du patient',
            true,
        );
    }

    private function buildFieldPraticien(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PRATICIEN,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Identifiant unique du praticien',
            true,
        );
    }

    private function buildFieldPrestation(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PRESTATION,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Code de la prestation',
        );
    }

    private function buildFieldNda(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NDA,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'NDA du patient'
        );
    }

    private function buildFieldModeTraitement(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MODE_TRAITEMENT,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Code du mode de traitement du patient',
        );
    }

    private function buildFieldModeEntree(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MODE_ENTREE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Code du mode d\'entrée du patient',
        );
    }

    private function buildFieldModeSortie(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MODE_SORTIE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Code du mode de sortie du patient',
        );
    }
}
