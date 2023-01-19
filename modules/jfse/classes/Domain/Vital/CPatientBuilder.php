<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use Ox\Mediboard\Patients\CPatient;

class CPatientBuilder
{
    /** @var CPatient */
    private $patient;

    /** @var bool */
    private $from_adri;

    public function __construct(CPatient $patient = null, bool $from_adri = false)
    {
        $this->patient               = $patient ?? new CPatient();
        $this->patient->_bind_vitale = true;
        $this->from_adri             = $from_adri;
    }

    public function updateInsuredBeneficiary(VitalCard $card, Beneficiary $beneficiary): void
    {
        $insured = $card->getInsured();
        if (!$this->from_adri) {
            $this->patient->assure_matricule    = $card->getFullNir();
            $this->patient->matricule           = $this->patient->assure_matricule;
            $this->patient->code_gestion        = $insured->getManagingCode();
            $this->patient->fin_validite_vitale = $card->getExpirationDate() ?
                $card->getExpirationDate()->format('Y-m-d') : '';
            $this->patient->regime_sante        = $insured->getRegimeLabel();
        }

        $insured_beneficiary                   = $card->getFirstBeneficiary();
        $this->patient->assure_nom_jeune_fille = $insured_beneficiary->getPatient()->getBirthName()
            ?: $insured->getLastName();
        $this->patient->assure_nom             = $insured->getLastName();
        $this->patient->assure_prenom          = $insured->getFirstName();
        $this->patient->assure_matricule       = $insured->getNir() . $insured->getNirKey();
        $this->patient->assure_sexe            = $this->patient->assure_matricule[0] === '2' ? 'f' : 'm';
        $this->patient->assure_nom_jeune_fille = $insured_beneficiary->getPatient()->getBirthName();
        $this->patient->assure_naissance       = $insured_beneficiary->getPatient()->getBirthDate();
        $this->patient->assure_rang_naissance  = $insured_beneficiary->getPatient()->getBirthRank();
        $this->patient->assure_adresse         = $insured_beneficiary->getPatient()->getAddress();
        $this->patient->assure_cp              = $insured_beneficiary->getPatient()->getZipCode();
        $this->patient->assure_ville           = $insured_beneficiary->getPatient()->getCity();

        if ($beneficiary->getCertifiedNir()) {
            $this->patient->matricule = $beneficiary->getFullCertifiedNir();
        } else {
            $this->patient->matricule = $this->patient->assure_matricule;
        }

        if ($beneficiary === $insured_beneficiary || $beneficiary->getCertifiedNir()) {
            $this->patient->sexe = $this->patient->matricule[0] === '2' ? 'f' : 'm';
        }

        $this->patient->code_regime       = str_pad($insured->getRegimeCode(), '0', 2);
        $this->patient->centre_gest       = $insured->getManagingCenter();
        $this->patient->caisse_gest       = $insured->getManagingFund();
        $this->patient->code_gestion      = $insured->getManagingCode();
        $this->patient->regime_sante      = $insured->getRegimeLabel();
        $this->patient->qual_beneficiaire = str_pad($beneficiary->getQuality(), 2, '0', STR_PAD_LEFT);

        $rights = $beneficiary->getLastAmoRight();
        if ($rights) {
            $this->patient->deb_amo = $rights->getBeginDate()->format('Y-m-d');
            $this->patient->fin_amo = $rights->getEndDate()->format('Y-m-d');
        }

        $coverage           = $beneficiary->getCurrentCoverage();
        $this->patient->ald = '0';
        if ($coverage) {
            $this->patient->ald = (bool)$coverage->getAldCode() ? '1' : '0';
        }

        $this->patient->is_smg = '0';
        if (
            $insured->getRegimeCode() === "08"
            && $insured->getManagingFund() === "835"
            && $insured->getManagingCenter() === "0300"
        ) {
            $this->patient->is_smg = '1';
        }

        $this->patient->acs      = $beneficiary->getAcs();
        $this->patient->acs_type = $beneficiary->getAcsType();

        // TODO: DEAL WITH INSC
        //        if ($beneficiary->getInscNumber() && $beneficiary->getInscKey() && $patient->_id) {
        //            $ins             = new CINSPatient();
        //            $ins->provider   = 'pyxvital'; // TODO: jfse ?
        //            $ins->patient_id = $patient->_id;
        //            $ins->loadMatchingObjectEsc();
        //
        //            if ($ins->_id) {
        //                $ins->ins  = $beneficiary->getInscNumber() . $beneficiary->getInscKey();
        //                $ins->date = CMbDT::dateTime();
        //                $ins->type = 'C';
        //                $ins->store();
        //            }
        //        }

        $patientJfse = $beneficiary->getPatient();

        if ($beneficiary->getCertifiedNir()) {
            $this->patient->_vitale_nir_certifie = $beneficiary->getFullCertifiedNir();
        }
        $this->patient->_bind_vitale         = 'jfse';
        $this->patient->_vitale_firstname    = $patientJfse->getFirstName();
        $this->patient->_vitale_lastname     = $patientJfse->getLastName();
        $this->patient->_vitale_birthdate    = $patientJfse->getBirthDate();
        $this->patient->_vitale_birthrank    = $patientJfse->getBirthRank();
        $this->patient->_vitale_quality      = $beneficiary->getQuality();
        $this->patient->_vitale_nir          = $card->getFullNir();
        $this->patient->_vitale_code_regime  = $insured->getRegimeCode();
        $this->patient->_vitale_code_gestion = $insured->getManagingCode();
        $this->patient->_vitale_code_caisse  = $insured->getManagingFund();
        $this->patient->_vitale_code_centre  = $insured->getManagingCenter();

        $this->patient->c2s = '0';
        if ($beneficiary->getHealthInsurance() && $beneficiary->getHealthInsurance()->getIsC2S()) {
            $this->patient->c2s = '1';
        }

        if ($beneficiary->getCurrentCoverage()) {
            $this->patient->regime_am = $beneficiary->getCurrentCoverage()->getAlsaceMozelleFlag();
        }
    }

    public function updateIdentity(Patient $patient_jfse): void
    {
        if (self::isFieldEmpty($this->patient->nom_jeune_fille)) {
            $this->patient->nom_jeune_fille = $patient_jfse->getBirthName() ?: $patient_jfse->getLastName();
        }

        if (self::isFieldEmpty($this->patient->nom)) {
            $this->patient->nom = $patient_jfse->getLastName();
        }

        if (self::isFieldEmpty($this->patient->prenom)) {
            $this->patient->prenom = $patient_jfse->getFirstName();
        }

        if (self::isFieldEmpty($this->patient->naissance)) {
            $this->patient->naissance = $patient_jfse->getBirthDate();
        }

        if (self::isFieldEmpty($this->patient->rang_naissance)) {
            $this->patient->rang_naissance = $patient_jfse->getBirthRank();
        }

        if (self::isFieldEmpty($this->patient->adresse)) {
            $this->patient->adresse = $patient_jfse->getAddress();
        }

        if (self::isFieldEmpty($this->patient->cp)) {
            $this->patient->cp = $patient_jfse->getZipCode();
        }

        if (self::isFieldEmpty($this->patient->ville)) {
            $this->patient->ville = $patient_jfse->getCity();
        }
    }

    public function getPatient(): CPatient
    {
        return $this->patient;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private static function isFieldEmpty($value): bool
    {
        return is_null($value) || $value === '';
    }
}
