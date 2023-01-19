<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Exception;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\SpecMatch;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * External patient spec builder
 */
class PatientSpecBuilder
{
    use SpecBuilderTrait;

    private const NAISSANCE_MIN = '1850-01-01';
    private const CONF_ADRESSE  = 'dPpatients CPatient addr_patient_mandatory';
    private const CONF_CP       = 'dPpatients CPatient cp_patient_mandatory';
    private const CONF_TEL      = 'dPpatients CPatient tel_patient_mandatory';

    private const FIELD_ID                   = 'external_id';
    private const FIELD_NOM                  = 'nom';
    private const FIELD_PRENOM               = 'prenom';
    private const FIELD_DECES           = 'deces';
    private const FIELD_NAISSANCE            = 'naissance';
    private const FIELD_CP_NAISSANCE         = 'cp_naissance';
    private const FIELD_LIEU_NAISSANCE       = 'lieu_naissance';
    private const FIELD_NJF                  = 'nom_jeune_fille';
    private const FIELD_PROFESSION           = 'profession';
    private const FIELD_EMAIL                = 'email';
    private const FIELD_TEL                  = 'tel';
    private const FIELD_TEL2                 = 'tel2';
    private const FIELD_TEL_AUTRE            = 'tel_autre';
    private const FIELD_ADRESSE              = 'adresse';
    private const FIELD_CP                   = 'cp';
    private const FIELD_VILLE                = 'ville';
    private const FIELD_PAYS                 = 'pays';
    private const FIELD_MATRICULE            = 'matricule';
    private const FIELD_SEXE                 = 'sexe';
    private const FIELD_CIVILITE             = 'civilite';
    private const FIELD_MARITAL_STATUS       = 'situation_famille';
    private const FIELD_PROFESSIONAL_ACTIVITY = 'activite_pro';
    private const FIELD_MEDECIN_TRAITANT     = 'medecin_traitant';
    private const FIELD_ALD                  = 'ald';
    private const FIELD_IPP                  = 'ipp';
    public const  FIELD_NOM_ASSURE           = 'nom_assure';
    public const  FIELD_PRENOM_ASSURE        = 'prenom_assure';
    public const  FIELD_NOM_NAISSANCE_ASSURE = 'nom_naissance_assure';
    public const  FIELD_SEXE_ASSURE          = 'sexe_assure';
    public const  FIELD_CIVILITE_ASSURE      = 'civilite_assure';
    public const  FIELD_NAISSANCE_ASSURE     = 'naissance_assure';
    public const  FIELD_ADRESSE_ASSURE       = 'adresse_assure';
    public const  FIELD_VILLE_ASSURE         = 'ville_assure';
    public const  FIELD_CP_ASSURE            = 'cp_assure';
    public const  FIELD_PAYS_ASSURE          = 'pays_assure';
    public const  FIELD_TEL_ASSURE           = 'tel_assure';
    public const  FIELD_MATRICULE_ASSURE     = 'matricule_assure';

    private const SEXE_F = 'f';
    private const SEXE_M = 'm';


    /**
     * @return SpecificationInterface|null
     * @throws Exception
     */
    public function build(): ?SpecificationInterface
    {
        $specs_to_add = [];

        $specs_to_add[] = $this->buildSpec(self::FIELD_ID);
        $specs_to_add[] = $this->buildSpec(self::FIELD_NOM);
        $specs_to_add[] = $this->buildSpec(self::FIELD_PRENOM);
        $specs_to_add[] = $this->buildSpec(self::FIELD_NAISSANCE);


        if ($spec = $this->buildSpec(self::FIELD_DECES)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_CP_NAISSANCE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_LIEU_NAISSANCE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_PROFESSION)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_EMAIL)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_TEL)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_TEL2)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_TEL_AUTRE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_ADRESSE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_CP)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_VILLE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_PAYS)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_MATRICULE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_SEXE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_CIVILITE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_MARITAL_STATUS)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_PROFESSIONAL_ACTIVITY)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_MEDECIN_TRAITANT)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_ALD)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_IPP)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_NOM_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_PRENOM_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_NOM_NAISSANCE_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_SEXE_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_CIVILITE_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_NAISSANCE_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_ADRESSE_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_VILLE_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_CP_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_PAYS_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_TEL_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_MATRICULE_ASSURE)) {
            $specs_to_add[] = $spec;
        }

        // Todo: Handle null (currently not allowed in Composite specification)
        return new AndX(...$specs_to_add);
    }

    /**
     * Build spec depending on $spec_name
     *
     * @param string $spec_name
     *
     * @return SpecificationInterface|null
     * @throws Exception
     */
    private function buildSpec(string $spec_name): ?SpecificationInterface
    {
        switch ($spec_name) {
            case self::FIELD_ID:
                return $this->getNotNullSpec($spec_name);

            case self::FIELD_NOM:
            case self::FIELD_NJF:
            case self::FIELD_PRENOM:
                return $this->getNameSpec($spec_name, true);

            case self::FIELD_NOM_ASSURE:
            case self::FIELD_NOM_NAISSANCE_ASSURE:
            case self::FIELD_PRENOM_ASSURE:
                return $this->getNameSpec($spec_name);

            case self::FIELD_NAISSANCE:
                return $this->getNaissanceSpec($spec_name, true);

            case self::FIELD_DECES:
            case self::FIELD_NAISSANCE_ASSURE:
                return $this->getNaissanceSpec($spec_name);

            case self::FIELD_PROFESSION:
                return $this->getProfessionSpec($spec_name);

            case self::FIELD_EMAIL:
                return $this->getEmailSpec(self::FIELD_EMAIL);

            case self::FIELD_TEL:
            case self::FIELD_TEL2:
            case self::FIELD_TEL_AUTRE:
                return $this->getTelSpec($spec_name);

            case self::FIELD_TEL_ASSURE:
                return $this->getTelAssureSpec($spec_name);

            case self::FIELD_ADRESSE:
                return $this->getAdresseSpec($spec_name);

            case self::FIELD_CP_NAISSANCE:
            case self::FIELD_CP:
                return $this->getCpSpec($spec_name);

            case self::FIELD_CP_ASSURE:
                return $this->getCpAssureSpec($spec_name);

            case self::FIELD_PAYS:
                return $this->getPaysSpec($spec_name);

            case self::FIELD_PAYS_ASSURE:
                return $this->getPaysAssureSpec($spec_name);

            case self::FIELD_VILLE:
                return $this->getVilleSpec($spec_name);

            case self::FIELD_LIEU_NAISSANCE:
            case self::FIELD_VILLE_ASSURE:
                return $this->getVilleAssureSpec($spec_name);

            case self::FIELD_MATRICULE:
            case self::FIELD_MATRICULE_ASSURE:
                return $this->getMatriculeSpec($spec_name);

            case self::FIELD_CIVILITE:
            case self::FIELD_CIVILITE_ASSURE:
                return $this->getCiviliteSpec($spec_name);
            case self::FIELD_MARITAL_STATUS:
                return $this->getSituationFamilleSpec($spec_name);
            case self::FIELD_PROFESSIONAL_ACTIVITY:
                return $this->getActiviteProSpec($spec_name);
            case self::FIELD_ALD:
                return $this->getAldSpec();

            case self::FIELD_SEXE_ASSURE:
            case self::FIELD_SEXE:
                return $this->getSexeSpec($spec_name);

            default:
                return null;
        }
    }

    private function getAldSpec(): OrX
    {
        return new OrX(
            Enum::is(self::FIELD_ALD, ['0', '1']),
            IsNull::is(self::FIELD_ALD)
        );
    }

    /**
     * @param string $field_name
     * @param bool   $conf
     *
     * @return SpecificationInterface
     */
    private function getCpAssureSpec(string $field_name): SpecificationInterface
    {
        return MaxLength::is($field_name, 5);
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface|null
     */
    public function getPaysAssureSpec(string $field_name): ?SpecificationInterface
    {
        return MaxLength::is($field_name, 80);
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface|null
     */
    public function getVilleAssureSpec(string $field_name): ?SpecificationInterface
    {
        return MaxLength::is($field_name, 80);
    }

    /**
     * @param string $field_name
     * @param bool   $conf
     *
     * @return SpecificationInterface
     */
    public function getTelAssureSpec(string $field_name): SpecificationInterface
    {
        $tel_spec = SpecMatch::is($field_name, '/^\d?(\d{2}[\s\.\-]?){5}$/');

        return new OrX(isNull::is($field_name), $tel_spec);
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    private function getSituationFamilleSpec(string $field_name): SpecificationInterface
    {
        return Enum::is($field_name, ['', 'S', 'M', 'G', 'P', 'D', 'W', 'A']);
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    private function getActiviteProSpec(string $field_name): SpecificationInterface
    {
        return Enum::is($field_name, ['', 'a', 'c', 'f', 'cp', 'e', 'i', 'r']);
    }
}
