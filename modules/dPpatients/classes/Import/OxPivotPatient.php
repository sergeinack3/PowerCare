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
use Ox\Import\GenericImport\OxLaboPivotObject;

/**
 * Description
 */
class OxPivotPatient extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_NOM                   = 'nom';
    public const FIELD_PRENOM                = 'prenom';
    public const FIELD_DECES                 = 'deces';
    public const FIELD_DATE_NAISSANCE        = 'date_naissance';
    public const FIELD_NOM_NAISSANCE         = 'nom_naissance';
    public const FIELD_CP_NAISSANCE          = 'cp_naissance';
    public const FIELD_LIEU_NAISSANCE        = 'lieu_naissance';
    public const FIELD_PROFESSION            = 'profession';
    public const FIELD_EMAIL                 = 'email';
    public const FIELD_TEL                   = 'tel';
    public const FIELD_TEL2                  = 'tel2';
    public const FIELD_TEL_AUTRE             = 'tel_autre';
    public const FIELD_ADRESSE               = 'adresse';
    public const FIELD_CP                    = 'cp';
    public const FIELD_VILLE                 = 'ville';
    public const FIELD_PAYS                  = 'pays';
    public const FIELD_MATRICULE             = 'matricule';
    public const FIELD_SEXE                  = 'sexe';
    public const FIELD_CIVILITE              = 'civilite';
    public const FIELD_MARITAL_STATUS        = 'situation_famille';
    public const FIELD_PROFESSIONAL_ACTIVITY = 'activite_pro';
    public const FIELD_REMARQUES             = 'remarques';
    public const FIELD_MEDECIN_TRAITANT      = 'medecin_traitant';
    public const FIELD_ALD                   = 'ald';
    public const FIELD_IPP                   = 'ipp';
    public const FIELD_NOM_ASSURE            = 'nom_assure';
    public const FIELD_PRENOM_ASSURE         = 'prenom_assure';
    public const FIELD_NOM_NAISSANCE_ASSURE  = 'nom_naissance_assure';
    public const FIELD_SEXE_ASSURE           = 'sexe_assure';
    public const FIELD_CIVILITE_ASSURE       = 'civilite_assure';
    public const FIELD_NAISSANCE_ASSURE      = 'naissance_assure';
    public const FIELD_ADRESSE_ASSURE        = 'adresse_assure';
    public const FIELD_VILLE_ASSURE          = 'ville_assure';
    public const FIELD_CP_ASSURE             = 'cp_assure';
    public const FIELD_PAYS_ASSURE           = 'pays_assure';
    public const FIELD_TEL_ASSURE            = 'tel_assure';
    public const FIELD_MATRICULE_ASSURE      = 'matricule_assure';

    protected const FILE_NAME = GenericImport::PATIENT;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID                    => $this->buildFieldId('Identifiant unique du patient'),
                self::FIELD_NOM                   => $this->buildFieldNom(),
                self::FIELD_PRENOM                => $this->buildFieldPrenom(),
                self::FIELD_DECES                 => $this->buildFieldDeces(),
                self::FIELD_DATE_NAISSANCE        => $this->buildFieldNaissance(),
                self::FIELD_NOM_NAISSANCE         => $this->buildFieldNomNaissance(),
                self::FIELD_CP_NAISSANCE          => $this->buildFieldCpNaissance(),
                self::FIELD_LIEU_NAISSANCE        => $this->buildFieldLieuNaissance(),
                self::FIELD_PROFESSION            => $this->buildFieldProfession(),
                self::FIELD_EMAIL                 => $this->buildFieldEmail(),
                self::FIELD_TEL                   => $this->buildFieldTel(),
                self::FIELD_TEL2                  => $this->buildFieldTel2(),
                self::FIELD_TEL_AUTRE             => $this->buildFieldTelAutre(),
                self::FIELD_ADRESSE               => $this->buildFieldLongText(
                    self::FIELD_ADRESSE,
                    'Libellé de l\'adresse du patient'
                ),
                self::FIELD_CP                    => $this->buildFieldCp(),
                self::FIELD_VILLE                 => $this->buildFieldVille(),
                self::FIELD_PAYS                  => $this->buildFieldPays(),
                self::FIELD_MATRICULE             => $this->buildFieldMatricule(),
                self::FIELD_SEXE                  => $this->buildFieldSexe(),
                self::FIELD_CIVILITE              => $this->buildFieldCivilite(),
                self::FIELD_MARITAL_STATUS        => $this->buildFieldMaritalStatus(),
                self::FIELD_PROFESSIONAL_ACTIVITY => $this->buildFieldProfessionalActivity(),
                self::FIELD_REMARQUES             => $this->buildFieldLongText(
                    self::FIELD_REMARQUES,
                    'Remarques sur le patient'
                ),
                self::FIELD_MEDECIN_TRAITANT      => $this->buildFieldExternalId(
                    self::FIELD_MEDECIN_TRAITANT,
                    'Identifiant unique du médecin traitant'
                ),
                self::FIELD_ALD                   => $this->buildFieldAld(),
                self::FIELD_IPP                   => $this->buildFieldIpp(),
                self::FIELD_NOM_ASSURE            => $this->buildFieldNomAssure(),
                self::FIELD_PRENOM_ASSURE         => $this->buildFieldPrenomAssure(),
                self::FIELD_NOM_NAISSANCE_ASSURE  => $this->buildFieldNomNaissanceAssure(),
                self::FIELD_SEXE_ASSURE           => $this->buildFieldSexeAssure(),
                self::FIELD_CIVILITE_ASSURE       => $this->buildFieldCiviliteAssure(),
                self::FIELD_NAISSANCE_ASSURE      => $this->buildFieldNaissanceAssure(),
                self::FIELD_ADRESSE_ASSURE        => $this->buildFieldLongText(
                    self::FIELD_ADRESSE_ASSURE,
                    'Adresse de l\'assuré'
                ),
                self::FIELD_VILLE_ASSURE          => $this->buildFieldVilleAssure(),
                self::FIELD_CP_ASSURE             => $this->buildFieldCpAssure(),
                self::FIELD_PAYS_ASSURE           => $this->buildFieldPaysAssure(),
                self::FIELD_TEL_ASSURE            => $this->buildFieldTelAssure(),
                self::FIELD_MATRICULE_ASSURE      => $this->buildFieldMatriculeAssure(),
            ];
        }
    }

    public function getAdditionnalInfos(): array
    {
        return [
            'Le "nom" ou le "nom de naissance" doit être obligatoirement renseigné.',
            'Il est possible de renseigner les deux s\'ils sont différents.',
        ];
    }

    private function buildFieldNom(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom du patient',
            true
        );
    }

    private function buildFieldPrenom(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PRENOM,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Prénom du patient',
            true
        );
    }

    private function buildFieldDeces(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_DECES,
            10,
            FieldDescription::FIELD_TYPE_DATE,
            'Date de décès du patient'
        );
    }

    private function buildFieldNaissance(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_ID,
            10,
            FieldDescription::FIELD_TYPE_DATE,
            'Date de naissance du patient',
            true
        );
    }

    private function buildFieldNomNaissance(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM_NAISSANCE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom de naissance du patient',
            true
        );
    }

    private function buildFieldCpNaissance(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CP_NAISSANCE,
            5,
            FieldDescription::FIELD_TYPE_INT,
            'Code postal de naissance du patient'
        );
    }

    private function buildFieldLieuNaissance(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_LIEU_NAISSANCE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Lieu de naissance du patient'
        );
    }

    private function buildFieldProfession(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PROFESSION,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Profession du patient'
        );
    }

    private function buildFieldEmail(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_EMAIL,
            255,
            FieldDescription::FIELD_TYPE_STRING . '. Droit contenir un arobase (@) et un point (.)',
            'Email du patient'
        );
    }

    private function buildFieldTel(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TEL,
            14,
            FieldDescription::FIELD_TYPE_STRING
            . '. Doit avoir 10 chiffres qui peuvent être séparés par des espaces ( ), point (.) ou traits d\'union (-)',
            'Téléphone fixe du patient'
        );
    }

    private function buildFieldTel2(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TEL2,
            14,
            FieldDescription::FIELD_TYPE_STRING
            . '. Doit avoir 10 chiffres qui peuvent être séparés par des espaces ( ), point (.) ou traits d\'union (-)',
            'Téléphone portable du patient'
        );
    }

    private function buildFieldTelAutre(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TEL_AUTRE,
            14,
            FieldDescription::FIELD_TYPE_STRING
            . '. Doit avoir 10 chiffres qui peuvent être séparés par des espaces ( ), point (.) ou traits d\'union (-)',
            'Autre téléphone du patient'
        );
    }

    private function buildFieldCp(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CP,
            5,
            FieldDescription::FIELD_TYPE_INT,
            'Code postal du patient'
        );
    }

    private function buildFieldVille(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_VILLE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Ville du patient'
        );
    }

    private function buildFieldPays(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PAYS,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom du pays du patient'
        );
    }

    private function buildFieldMatricule(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MATRICULE,
            15,
            FieldDescription::FIELD_TYPE_STRING . '. Numéro de sécurité sociale sans espace avec la clé.',
            'Numéro de sécurité sociale du patient'
        );
    }

    private function buildFieldSexe(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SEXE,
            1,
            'm = masculin, f = féminin',
            'Sexe du patient'
        );
    }

    private function buildFieldCivilite(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CIVILITE,
            2,
            'm = monsieur, mme = madame, mlle, mademoiselle, enf = enfant, dr = docteur, pr = professeur, me = maître',
            'Civilité du patient'
        );
    }

    private function buildFieldAld(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_ALD,
            1,
            'booleen (0 ou 1)',
            'Affection Longue Durée'
        );
    }

    private function buildFieldIpp(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_IPP,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'IPP du patient'
        );
    }


    private function buildFieldNomAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM_ASSURE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom de l\'assuré'
        );
    }

    private function buildFieldPrenomAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PRENOM_ASSURE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Prénom de l\'assuré'
        );
    }

    private function buildFieldNomNaissanceAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM_NAISSANCE_ASSURE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom de naissance de l\'assuré'
        );
    }

    private function buildFieldSexeAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SEXE_ASSURE,
            1,
            'm = masculin, f = féminin',
            'Sexe de l\'assuré'
        );
    }

    private function buildFieldCiviliteAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CIVILITE_ASSURE,
            2,
            'm = monsieur, mme = madame, mlle, mademoiselle, enf = enfant, dr = docteur, pr = professeur, me = maître',
            'Civilité de l\'assuré'
        );
    }

    private function buildFieldNaissanceAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM_NAISSANCE_ASSURE,
            10,
            FieldDescription::FIELD_TYPE_DATE,
            'Date de naissance de l\'assuré',
        );
    }

    private function buildFieldVilleAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_VILLE_ASSURE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Ville de l\'assuré'
        );
    }

    private function buildFieldCpAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CP_ASSURE,
            5,
            FieldDescription::FIELD_TYPE_INT,
            'Code postal de l\'assuré'
        );
    }

    private function buildFieldPaysAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PAYS_ASSURE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom du pays de l\'assuré'
        );
    }

    private function buildFieldTelAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TEL_ASSURE,
            14,
            FieldDescription::FIELD_TYPE_STRING
            . '. Doit avoir 10 chiffres qui peuvent être séparés par des espaces ( ), point (.) ou traits d\'union (-)',
            'Téléphone de l\'assuré'
        );
    }

    private function buildFieldMatriculeAssure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MATRICULE_ASSURE,
            15,
            FieldDescription::FIELD_TYPE_STRING . '. Numéro de sécurité sociale sans espace avec la clé.',
            'Numéro de sécurité sociale de l\'assuré'
        );
    }

    private function buildFieldMaritalStatus(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_MARITAL_STATUS,
            1,
            'S = Célibataire, M = Marié(e), G = Concubin(e), P = Pacsé(e), ' .
            'D = Divorcé(e), W = Veuf / Veuve, A = Séparé(e)',
            'Situation familliale actuelle'
        );
    }

    private function buildFieldProfessionalActivity(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PROFESSIONAL_ACTIVITY,
            2,
            '(vide) = Non renseigné | a = Actif | c = Chômeur | f = Au foyer | cp = Congé parental, ' .
            'e = Elève, étudiant ou en formation | i = Inactif | r = Retraité',
            'Activité professionnelle exercée par le patient'
        );
    }
}
