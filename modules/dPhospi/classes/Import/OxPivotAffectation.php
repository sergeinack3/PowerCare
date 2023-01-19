<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Pivot for affectation import
 */
class OxPivotAffectation extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_SEJOUR_ID   = 'sejour';
    public const FIELD_NOM_SERVICE = 'nom_service';
    public const FIELD_NOM_LIT     = 'nom_lit';
    public const FIELD_ENTREE      = 'entree';
    public const FIELD_SORTIE      = 'sortie';
    public const FIELD_REMARQUES   = 'remarques';
    public const FIELD_EFFECTUE    = 'effectue';
    public const FIELD_MODE_ENTREE = 'mode_entree';
    public const FIELD_MODE_SORTIE = 'mode_sortie';
    public const FIELD_CODE_UF     = 'code_unite_fonctionnelle';

    protected const FILE_NAME = GenericImport::AFFECTATION;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID          => $this->buildFieldId('Identifiant unique de l\'affectation'),
                self::FIELD_SEJOUR_ID   => $this->buildFieldSejourId(),
                self::FIELD_NOM_SERVICE => $this->buildFieldNomService(),
                self::FIELD_NOM_LIT     => $this->buildFieldNomLit(),
                self::FIELD_ENTREE      => $this->buildFieldEntree(),
                self::FIELD_SORTIE      => $this->buildFieldSortie(),
                self::FIELD_REMARQUES   => $this->buildFieldLongText(
                    self::FIELD_REMARQUES,
                    'Remarques sur l\'affectation'
                ),
                self::FIELD_EFFECTUE    => $this->buildFieldEffectue(),
                self::FIELD_MODE_ENTREE => $this->buildFieldModeEntree(),
                self::FIELD_MODE_SORTIE => $this->buildFieldModeSortie(),
                self::FIELD_CODE_UF     => $this->buildFieldCodeUf(),
            ];
        }
    }

    private function buildFieldSejourId(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SEJOUR_ID,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Identifiant unique du séjour',
            true
        );
    }

    private function buildFieldNomService(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM_SERVICE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom du service',
            true
        );
    }

    private function buildFieldNomLit(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM_LIT,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom du lit',
        );
    }

    private function buildFieldEntree(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_ENTREE,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Date d\'entrée',
            true
        );
    }

    private function buildFieldSortie(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SORTIE,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Date de sortie',
            true
        );
    }

    private function buildFieldEffectue(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_EFFECTUE,
            1,
            '0 = non, 1 = oui',
            'Effectuée'
        );
    }

    private function buildFieldModeEntree(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MODE_ENTREE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Code du mode d\'entrée',
        );
    }

    private function buildFieldModeSortie(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MODE_SORTIE,
            255,
            '0 = Transfert après acte, 
            4 = Fugue ou sortie contre avis médical, 
            5 = Sortie à l\'essai, 
            6 = Mutation (même hopital), 
            7 = Transfert, 
            8 = Départ vers le domicile ou assimilé, 
            9 = Décès',
            'Code du mode de sortie'
        );
    }

    private function buildFieldCodeUf(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CODE_UF,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Code de l\'unité fonctionnelle',
        );
    }
}
