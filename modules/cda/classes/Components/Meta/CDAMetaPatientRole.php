<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\CCdaTools;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_city;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_country;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_county;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_postalCode;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_family;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_given;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAPN;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Birthplace;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Guardian;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Patient;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_PatientRole;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Person;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Place;
use Ox\Interop\Eai\CItemReport;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPaysInsee;

class CDAMetaPatientRole extends CDAMeta
{
    /** @var CPatient */
    protected $patient;

    /**
     * CDAMetaPatientRole constructor.
     *
     * @param CCDAFactory $factory
     * @param CPatient    $patient
     */
    public function __construct(CCDAFactory $factory, CPatient $patient)
    {
        parent::__construct($factory);

        $this->patient = $patient;
        $this->content = new CCDAPOCD_MT000040_PatientRole();
    }

    /**
     * @return CCDAPOCD_MT000040_PatientRole
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDAPOCD_MT000040_PatientRole $patient_role */
        $patient_role = parent::build();

        // Pour CDA structuré et non structuré, on retourne une erreur pck on ne met pas d'autres identifiants et
        // par défaut, il en faut obligatoirement 1
        if (!$this->patient->getINSNIR()) {
            $this->factory->report->addData(
                CAppUI::tr('CReport-msg-Patient doesnt have INS NIR'),
                CItemReport::SEVERITY_ERROR
            );

            return $patient_role;
        }

        // ID
        $this->setId($patient_role);

        // Address
        $this->setAddress($patient_role);

        // Telecom
        $this->setTelecom($patient_role);

        // Patient
        $this->setPatient($patient_role);

        return $patient_role;
    }

    /**
     * @param CCDAPOCD_MT000040_PatientRole $patient_role
     */
    protected function setId(CCDAPOCD_MT000040_PatientRole $patient_role): void
    {
        if (!$this->patient->getINSNIR()) {
            return;
        }

        $ii = new CCDAII();
        $ii->setRoot(CAppUI::conf("dmp NIR_OID"));
        $ii->setExtension($this->patient->getINSNIR());
        $patient_role->appendId($ii);
    }

    /**
     * @param CCDAPOCD_MT000040_PatientRole $patient_role
     */
    protected function setAddress(CCDAPOCD_MT000040_PatientRole $patient_role): void
    {
        $address = (new CDAMetaAddress($this->factory, $this->patient))->build();
        $patient_role->appendAddr($address);
    }

    /**
     * @param CCDAPOCD_MT000040_PatientRole $patient_role
     */
    protected function setTelecom(CCDAPOCD_MT000040_PatientRole $patient_role): void
    {
        $telecoms_data = CDAMetaTelecom::filterTelecoms(
            $this->patient,
            [
                CDAMetaTelecom::TYPE_TELECOM_EMAIL,
                CDAMetaTelecom::TYPE_TELECOM_MOBILE,
                CDAMetaTelecom::TYPE_TELECOM_TEL,
            ]
        ) ?: [CDAMetaTelecom::TYPE_TELECOM_TEL];

        foreach ($telecoms_data as $type) {
            $telecom = (new CDAMetaTelecom($this->factory, $this->patient, $type))->build();
            $patient_role->appendTelecom($telecom);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_PatientRole $patient_role
     */
    protected function setPatient(CCDAPOCD_MT000040_PatientRole $patient_role): void
    {
        $patientCDA = new CCDAPOCD_MT000040_Patient();

        // Name
        $this->setNamePatient($patientCDA);

        // Administrative Gender Code
        $this->setAdministrativeGenderCode($patientCDA);

        // BirthTime
        $this->setBirthTime($patientCDA);

        // Guardian
        $this->setGuardian($patientCDA);

        // BirthPlace
        $this->setBirthPlace($patientCDA);

        // Marital status
        $status = CCdaTools::getMaritalStatus($this->factory->patient->situation_famille);
        $patientCDA->setMaritalStatusCode($status);

        $patient_role->setPatient($patientCDA);
    }

    /**
     * @param CCDAPOCD_MT000040_Patient $patientCDA
     */
    protected function setNamePatient(CCDAPOCD_MT000040_Patient $patientCDA): void
    {
        $patient = $this->patient;

        $pn = new CCDAPN();

        // Family Nom d'usage
        $enxp = new CCDA_en_family();
        $enxp->setData($patient->nom);
        $enxp->setQualifier(['SP']);
        $pn->append("family", $enxp);

        if ($patient->_p_maiden_name && $patient->prenom) {
            // Given Nom de jeune fille
            $enxp2 = new CCDA_en_family();
            $enxp2->setQualifier(["BR"]);
            $enxp2->setData($patient->_p_maiden_name);
            $pn->append("family", $enxp2);

            // Given Premier prénom
            $enxp = new CCDA_en_given();
            $enxp->setData($patient->prenom);
            $enxp->setQualifier(['BR']);
            $pn->append("given", $enxp);
        }

        if ($patient->nom && $patient->prenom_usuel) {
            // Family Nom utilisé (RNIV)
            $enxp = new CCDA_en_family();
            $enxp->setData($patient->nom);
            $enxp->setQualifier(['CL']);
            $pn->append("family", $enxp);

            // Given Prénom utilisé
            $enxp = new CCDA_en_given();
            $enxp->setData($patient->prenom_usuel);
            $enxp->setQualifier(['CL']);
            $pn->append("given", $enxp);
        }

        // append Name
        $patientCDA->appendName($pn);
    }

    /**
     * @param CCDAPOCD_MT000040_Patient $patientCDA
     * @param CCorrespondantPatient     $representant_legal
     */
    protected function setNameLegalRepresentant(
        CCDAPOCD_MT000040_Person $person, CCorrespondantPatient $representant_legal
    ): void
    {
        $pn = new CCDAPN();

        // Family
        $enxp = new CCDA_en_family();
        $enxp->setData($representant_legal->_p_last_name);
        $enxp->setQualifier(["SP"]);
        $pn->append("family", $enxp);

        // Given
        $enxp = new CCDA_en_given();
        $enxp->setData($representant_legal->_p_first_name);
        $pn->append("given", $enxp);

        // append Name
        $person->appendName($pn);
    }

    /**
     * @param CCDAPOCD_MT000040_Patient $patientCDA
     */
    protected function setAdministrativeGenderCode(CCDAPOCD_MT000040_Patient $patientCDA): void
    {
        $gender = CCdaTools::getAdministrativeGenderCode($this->patient->sexe);

        $patientCDA->setAdministrativeGenderCode($gender);
    }

    /**
     * @param CCDAPOCD_MT000040_Patient $patientCDA
     */
    protected function setBirthTime(CCDAPOCD_MT000040_Patient $patientCDA): void
    {
        $ts = new CCDATS();
        $ts->setValue($this->patient->_p_birth_date);
        if (!$this->patient->_p_birth_date) {
            $ts->setNullFlavor("NASK");
        }
        $patientCDA->setBirthTime($ts);
    }

    /**
     * @param CCDAPOCD_MT000040_Patient $patientCDA
     */
    protected function setGuardian(CCDAPOCD_MT000040_Patient $patientCDA): void
    {
        $patient = $this->patient;

        $representant_legal = new CCorrespondantPatient();
        $where = [];
        $ds = $patient->getDS();
        $where['relation']   = $ds->prepare("= ?", 'representant_legal');
        $where['patient_id'] = $ds->prepare("= ?", $patient->_id);
        $where[] = " 'date_fin' IS NULL OR 'date_fin' > '". CMbDT::date(). "'";

        $representants_legaux = $representant_legal->loadList($where, 'date_fin DESC');

        if (!$representants_legaux) {
            return;
        }

        $representant_legal = reset($representants_legaux);

        // Guardian
        $guardian = new CCDAPOCD_MT000040_Guardian();

        // Address
        $address = (new CDAMetaAddress($this->factory, $representant_legal))->build();
        $guardian->appendAddr($address);

        // Telecom
        $telecoms = array_filter(
            [
                CDAMetaTelecom::TYPE_TELECOM_EMAIL  => $representant_legal->_p_email,
                CDAMetaTelecom::TYPE_TELECOM_MOBILE => $representant_legal->_p_mobile_phone_number,
                CDAMetaTelecom::TYPE_TELECOM_TEL    => $representant_legal->_p_phone_number,
            ]
        );
        foreach ($telecoms ?: [CDAMetaTelecom::TYPE_TELECOM_TEL => null] as $type => $value) {
            $telecom = (new CDAMetaTelecom($this->factory, $representant_legal, $type))->build();

            $guardian->appendTelecom($telecom);
        }

        // GuardianPerson
        $person = new CCDAPOCD_MT000040_Person();
        $this->setNameLegalRepresentant($person, $representant_legal);
        $guardian->setGuardianPerson($person);

        $patientCDA->appendGuardian($guardian);
    }

    /**
     * @param CCDAPOCD_MT000040_Patient $patientCDA
     */
    protected function setMaritalStatus(CCDAPOCD_MT000040_Patient $patientCDA): void
    {
        $status = CCdaTools::getMaritalStatus($this->patient->situation_famille);
        $patientCDA->setMaritalStatusCode($status);
    }

    /**
     * @param CCDAPOCD_MT000040_Patient $patientCDA
     */
    protected function setBirthPlace(CCDAPOCD_MT000040_Patient $patientCDA): void
    {
        $patient         = $this->patient;
        $birthPlace      = $patient->lieu_naissance;
        $birthPostalCode = $patient->cp_naissance;

        if (!$birthPlace && !$birthPostalCode && $this->factory::TYPE !== CCDAFactory::TYPE_ZEPRA) {
            return;
        }

        // Place
        $place = new CCDAPOCD_MT000040_Place();

        $pays_naissance_insee = CPaysInsee::getPaysByNumerique($patient->pays_naissance_insee);

        // address
        $ad   = new CCDAAD();
        $adxp = new CCDA_adxp_city();
        $adxp->setData($birthPlace);
        $ad->append("city", $adxp);
        $adxp = new CCDA_adxp_postalCode();
        $adxp_county = new CCDA_adxp_county();
        if ($this->factory::TYPE === CCDAFactory::TYPE_ZEPRA) {
            // Par défaut on met "00000" car on a pas la base INSEE pour avoir le code Pays
            $adxp->setData($pays_naissance_insee->alpha_3 == "FRA" ? $birthPostalCode : "00000");
        } else {
            $adxp_county->setData($birthPostalCode);
            $adxp->setData($birthPostalCode);
        }
        $ad->append("postalCode", $adxp);
        $ad->append("county", $adxp_county);

        // Pour Sisra, ajout du pays de naissance
        if ($this->factory::TYPE === CCDAFactory::TYPE_ZEPRA) {
            $adxp = new CCDA_adxp_country();
            $adxp->setData($pays_naissance_insee->nom_fr);
            $ad->append("country", $adxp);
        }
        $place->setAddr($ad);

        // Birth place
        $birthplace = new CCDAPOCD_MT000040_Birthplace();
        $birthplace->setPlace($place);

        // set birth place
        $patientCDA->setBirthplace($birthplace);
    }
}
