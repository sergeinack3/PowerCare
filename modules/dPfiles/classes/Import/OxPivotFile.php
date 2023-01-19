<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Description
 */
class OxPivotFile extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_NOM          = 'nom';
    public const FIELD_DATE         = 'date';
    public const FIELD_TYPE         = 'type';
    public const FIELD_AUTEUR       = 'auteur';
    public const FIELD_CONSULTATION = 'consultation';
    public const FIELD_SEJOUR       = 'sejour';
    public const FIELD_PATIENT      = 'patient';
    public const FIELD_EVENEMENT    = 'evenement';
    public const FIELD_CATEGORIE    = 'categorie';
    public const FIELD_CONTENU      = 'contenu';
    public const FIELD_CHEMIN       = 'chemin';


    protected const FILE_NAME = GenericImport::FICHIER;


    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID           => $this->buildFieldId('Identifiant unique du fichier'),
                self::FIELD_NOM          => $this->buildFieldNom(),
                self::FIELD_DATE         => $this->buildFieldDatetime(),
                self::FIELD_TYPE         => $this->buildFieldType(),
                self::FIELD_AUTEUR       => $this->buildFieldExternalId(
                    self::FIELD_AUTEUR,
                    'Identifiant unique du médecin auteur du fichier',
                    true
                ),
                self::FIELD_CONSULTATION => $this->buildFieldExternalId(
                    self::FIELD_CONSULTATION,
                    'Identifiant unique de la consultation de rattachement du fichier',
                    true
                ),
                self::FIELD_SEJOUR => $this->buildFieldExternalId(
                    self::FIELD_SEJOUR,
                    'Identifiant unique du séjour de rattachement du fichier',
                    true
                ),
                self::FIELD_PATIENT      => $this->buildFieldExternalId(
                    self::FIELD_PATIENT,
                    'Identifiant unique du patient de rattachement du fichier',
                    true
                ),
                self::FIELD_EVENEMENT    => $this->buildFieldExternalId(
                    self::FIELD_EVENEMENT,
                    'Identifiant unique de l\'événement de rattachement du fichier',
                    true
                ),
                self::FIELD_CATEGORIE    => $this->buildFieldCategorie(),
                self::FIELD_CONTENU      => $this->buildFieldContent(),
                self::FIELD_CHEMIN       => $this->buildFieldPath(),
            ];
        }
    }

    public function getAdditionnalInfos(): array
    {
        return [
            'Un des champs "consultation_id", "patient_id", "evenement_id" ou "sejour_id" doit être rempli.',
            "Il est inutile de tous les remplir.\n",
            "Un des champs \"contenu\" ou \"chemin\" doit être rempli, il est inutile de remplir les deux.\n",
            'Si le champs chemin est rempli il faut également nous fournir un dossier contenant l\'ensemble des fichiers.',
        ];
    }

    private function buildFieldNom(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom du fichier',
            true
        );
    }

    private function buildFieldDatetime(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_DATE,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Date de création du fichier'
        );
    }

    private function buildFieldType(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TYPE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Type mime du fichier'
        );
    }

    private function buildFieldCategorie(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CATEGORIE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom de la catégorie du fichier. Doit être le nom d\'une catégorie existante'
        );
    }

    private function buildFieldContent(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CONTENU,
            0,
            FieldDescription::FIELD_TYPE_STRING,
            'Contenu du fichier',
            true
        );
    }

    private function buildFieldPath(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CHEMIN,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Chemin vers le fichier',
            true
        );
    }

}
