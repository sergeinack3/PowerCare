<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Description
 */
class OxPivotMedecin extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_NOM         = 'nom';
    public const FIELD_PRENOM      = 'prenom';
    public const FIELD_SEXE        = 'sexe';
    public const FIELD_TITRE       = 'titre';
    public const FIELD_EMAIL       = 'email';
    public const FIELD_DISCIPLINES = 'disciplines';
    public const FIELD_TEL         = 'tel';
    public const FIELD_TEL_AUTRE   = 'tel_autre';
    public const FIELD_ADRESSE     = 'adresse';
    public const FIELD_CP          = 'cp';
    public const FIELD_VILLE       = 'ville';
    public const FIELD_RPPS        = 'rpps';
    public const FIELD_ADELI       = 'adeli';

    protected const FILE_NAME = GenericImport::MEDECIN;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID          => $this->buildFieldId('Identifiant unique du médecin'),
                self::FIELD_NOM         => $this->buildFieldNom(),
                self::FIELD_PRENOM      => $this->buildFieldPrenom(),
                self::FIELD_SEXE        => $this->buildFieldSexe(),
                self::FIELD_TITRE       => $this->buildFieldTitre(),
                self::FIELD_EMAIL       => $this->buildFieldEmail(),
                self::FIELD_DISCIPLINES => $this->buildFieldLongText(
                    self::FIELD_DISCIPLINES,
                    'Disciplines d\'exercice du médecin'
                ),
                self::FIELD_TEL         => $this->buildFieldTel(),
                self::FIELD_TEL_AUTRE   => $this->buildFieldTelAutre(),
                self::FIELD_ADRESSE     => $this->buildFieldLongText(
                    self::FIELD_ADRESSE,
                    'Libellé de l\'adresse du médecin'
                ),
                self::FIELD_CP          => $this->buildFieldCp(),
                self::FIELD_VILLE       => $this->buildFieldVille(),
                self::FIELD_RPPS        => $this->buildFieldRpps(),
                self::FIELD_ADELI       => $this->buildFieldAdeli(),
            ];
        }
    }

    private function buildFieldNom(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom du médecin',
            true
        );
    }

    private function buildFieldPrenom(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PRENOM,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Prénom du médecin'
        );
    }

    private function buildFieldEmail(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_EMAIL,
            255,
            FieldDescription::FIELD_TYPE_STRING . '. Droit contenir un arobase (@) et un point (.)',
            'Email du médecin'
        );
    }

    private function buildFieldTel(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TEL,
            14,
            FieldDescription::FIELD_TYPE_STRING
            . '. Doit avoir 10 chiffres qui peuvent être séparés par des espaces ( ), point (.) ou traits d\'union (-)',
            'Téléphone fixe du médecin'
        );
    }

    private function buildFieldTelAutre(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TEL_AUTRE,
            14,
            FieldDescription::FIELD_TYPE_STRING
            . '. Doit avoir 10 chiffres qui peuvent être séparés par des espaces ( ), point (.) ou traits d\'union (-)',
            'Autre téléphone du médecin'
        );
    }

    private function buildFieldCp(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CP,
            5,
            FieldDescription::FIELD_TYPE_INT,
            'Code postal du médecin'
        );
    }

    private function buildFieldVille(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_VILLE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Ville du médecin'
        );
    }

    private function buildFieldSexe(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SEXE,
            1,
            'm = masculin, f = féminin',
            'Sexe du médecin'
        );
    }

    private function buildFieldTitre(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TITRE,
            3,
            'm = monsieur, mme = madame, dr = docteur, pr = professeur',
            'Titre du médecin'
        );
    }

    private function buildFieldRpps(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_RPPS,
            11,
            FieldDescription::FIELD_TYPE_STRING,
            'Numéro RPPS du médecin'
        );
    }

    private function buildFieldAdeli(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_ADELI,
            9,
            FieldDescription::FIELD_TYPE_STRING,
            'Numéro ADELI du médecin'
        );
    }
}
