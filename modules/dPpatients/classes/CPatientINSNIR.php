<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Com\Tecnick\Barcode\Barcode;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Mediboard\OpenData\CCommuneFrance;

/**
 * Description
 */
class CPatientINSNIR extends CMbObject
{
    public const OID_INS_NIR      = '1.2.250.1.213.1.4.8';
    public const OID_INS_NIR_TEST = '1.2.250.1.213.1.4.10';
    public const OID_INS_NIA      = '1.2.250.1.213.1.4.9';

    /**
     * @var integer Primary key
     */
    public $patient_ins_nir_id;

    public $created_datetime;
    public $last_update;
    public $patient_id;
    public $ins_nir;
    public $oid;
    public $is_nia;
    public $source_identite_id;
    public $ins_temporaire;
    public $name;
    public $firstname;
    public $birthdate;
    public $provider;

    public $_is_ins_nir = false;
    public $_is_ins_nia = false;

    /** @var string */
    public $_ins_type;

    /** @var CSourceIdentite */
    public $_ref_source_identite;

    /** @var CPatient */
    public $_ref_patient;

    public $datamatrix_ins;

    /**
     * Create or update Patient INS NIR
     *
     * @param int    $patient_id
     * @param string $name
     * @param string $first_name
     * @param string $birth_date
     * @param string $ins_nir
     * @param string $provider
     * @param string $oid
     * @param int    $source_identite_id
     *
     * @return string|null
     * @throws Exception
     */
    public static function createUpdate(
        $patient_id,
        $name,
        $first_name,
        $birth_date,
        $ins_nir,
        $provider,
        $oid = null,
        $is_nia = false,
        $source_identite_id = null,
        $ins_temporaire = false,
        $force_new = false
    ) {
        $patient_ins_nir             = new self();
        $patient_ins_nir->patient_id = $patient_id;

        if (!$force_new) {
            $patient_ins_nir->loadMatchingObject();
        }

        $patient_ins_nir->provider           = $provider;
        $patient_ins_nir->ins_nir            = $ins_nir;
        $patient_ins_nir->name               = $name;
        $patient_ins_nir->firstname          = $first_name;
        $patient_ins_nir->birthdate          = $birth_date;
        $patient_ins_nir->oid                = $oid;
        $patient_ins_nir->is_nia             = $is_nia;
        $patient_ins_nir->source_identite_id = $source_identite_id;
        $patient_ins_nir->ins_temporaire     = $ins_temporaire;

        if ($msg = $patient_ins_nir->store()) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
        }

        return $patient_ins_nir;
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        if (!$this->_id) {
            $this->created_datetime = $this->last_update = 'now';
        }

        if ($this->objectModified()) {
            $this->last_update = 'now';
        }

        $ins_temporaraire_modified = $this->fieldModified('ins_temporaire', '0');

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($ins_temporaraire_modified) {
            if ($msg = (new PatientStatus($this->loadRefPatient()))->updateStatus()) {
                return $msg;
            }
        }

        return null;
    }

    /**
     * @param $patient_id
     *
     * Vérifie les données d'un patient qui est supposé correspondre aux données retournées par la TD0.0
     *
     * @return bool|null
     * @throws Exception
     */
    public function compare($patient_id)
    {
        $patient = new CPatient();
        $patient->load($patient_id);
        if (!$patient) {
            return true;
        }

        return (CMbString::lower($this->name) != CMbString::lower($patient->_nom_naissance)
            || CMbString::lower($this->firstname) != CMbString::lower($patient->prenom)
            || $this->birthdate != $patient->naissance
            || $this->ins_nir != $patient->matricule);
    }

    /**
     * @inheritDoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'patient_ins_nir';
        $spec->key   = 'patient_ins_nir_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['patient_id']         = 'ref class|CPatient notNull back|patient_ins_nir';
        $props['created_datetime']   = 'dateTime notNull';
        $props['last_update']        = 'dateTime notNull';
        $props['ins_nir']            = 'str notNull';
        $props['oid']                = 'str';
        $props['is_nia']             = 'bool default|0';
        $props['source_identite_id'] = 'ref class|CSourceIdentite back|patient_ins_nir cascade';
        $props['ins_temporaire']     = 'bool';
        $props['name']               = 'str';
        $props['firstname']          = 'str';
        $props['birthdate']          = 'birthDate';
        $props['provider']           = 'str notNull';
        $props['_ins_type']          = 'str';

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        if ($this->oid === self::OID_INS_NIA) {
            $this->_is_ins_nia = true;
        }

        if ($this->oid === self::OID_INS_NIR) {
            $this->_is_ins_nir = true;
        }

        $this->_ins_type = $this->getINSType();
    }

    /**
     * Chargement de la source d'identité
     *
     * @return CSourceIdentite|CStoredObject
     */
    public function loadRefSourceIdentite(): CSourceIdentite
    {
        return $this->_ref_source_identite = $this->loadFwdRef('source_identite_id', true);
    }

    /**
     * @see parent::fillTemplate()
     */
    function fillTemplate(&$template, $champ = "Patient")
    {
        if (!$this->_ref_patient) {
            $this->loadRefPatient();
        }

        $template->addDatamatrixProperty(
            "$champ - " . CAppUI::tr("CPatientINSNIR_datamatrix_ins"), $this->_ref_patient->status == "QUAL" ?
            $this->createDatamatrix($this->createDataForDatamatrix()) : "", [
                "datamatrix" => ["title" => CAppUI::tr("CPatientINSNIR_datamatrix_unsigned")],
            ]
        );

        $template->addProperty(
            CAppUI::tr("CPatient") . " - " . CAppUI::tr('CPatientINSNIR-_ins_type'), $this->_ref_patient->status == "QUAL" ?
            $this->_ins_type : ""
        );
    }

    /**
     * Création du datamatrix INS
     *
     * @param string|null $data
     *
     * @return string|null
     */
    public function createDatamatrix(?string $data): ?string
    {
        if ($this->datamatrix_ins != null) {
            return $this->datamatrix_ins;
        }

        if (is_null($data)) {
            return null;
        }

        $datamatrix_ins = base64_encode(
            (new Barcode())->getBarcodeObj(
                'DATAMATRIX',
                $data,
                72,
                72
            )->getPngData()
        );

        return $this->datamatrix_ins = "data:image/png;base64," . $datamatrix_ins;
    }

    /**
     * Création des données du datamatrix INS
     *
     * @return array|string|null
     */
    public function createDataForDatamatrix()
    {
        if ($this->datamatrix_ins != null) {
            return $this->datamatrix_ins;
        }

        if (!$this->_ref_patient) {
            $this->loadRefPatient();
        }

        $patient = $this->_ref_patient;

        if (!$this->oid || !$patient->sexe || CAppUI::conf("ref_pays") != 1) {
            return null;
        }

        $datamatrix = "IS010000000000000000000000";

        //Matricule INS
        $datamatrix .= "S1" . strtoupper($this->ins_nir);

        //OID
        $datamatrix .= "S2" . $this->oid;
        if (strlen($this->oid) < 20) {
            $datamatrix .= chr(29);
        }

        //Liste des prénoms de naissance
        $datamatrix .= "S3" . strtoupper($patient->prenoms);
        if (strlen($patient->prenoms) < 100) {
            $datamatrix .= chr(29);
        }

        //Nom de naissance
        $datamatrix .= "S4" . strtoupper($patient->nom_jeune_fille);
        if (strlen($patient->nom_jeune_fille) < 100) {
            $datamatrix .= chr(29);
        }

        //Sexe
        $datamatrix .= "S5" . strtoupper($patient->sexe);

        //Date de naissance
        $date_naissance = $patient->naissance;
        $datamatrix     .= "S6" . date("d-m-Y", strtotime($date_naissance));

        //code lieu naissance
        if ($patient->pays_naissance_insee == 250 && $patient->commune_naissance_insee) {
            $datamatrix .= "S7" . strtoupper($patient->commune_naissance_insee);
        } elseif ($patient->pays_naissance_insee) {
            $pays       = CPaysInsee::getPaysByNumerique($patient->pays_naissance_insee);
            $datamatrix .= "S7" . strtoupper($pays->code_insee);
        }

        return $datamatrix;
    }

    /**
     * Chargement du patient
     *
     * @return CPatient|CStoredObject
     */
    public function loadRefPatient(): CPatient
    {
        return $this->_ref_patient = $this->loadFwdRef('patient_id', true);
    }

    /**
     * Recherche d'un patient depuis un datamatrix INS
     *
     * @param string $ins
     *
     * @return array
     */
    public function readDatamatrixINS(string $ins): array
    {
        $data = [];

        //Matricule INS
        $s1 = strpos($ins, "S1");
        //OID
        $s2 = strpos($ins, "S2");
        //Liste des prénoms de naissance
        $s3 = strpos($ins, "S3");
        //Nom de naissance
        $s4 = strpos($ins, "S4");
        //Sexe
        $s5 = strpos($ins, "S5");
        //Date de naissance
        $s6 = strpos($ins, "S6");
        //code lieu naissance
        $s7 = strpos($ins, "S7");
        $s  = [
            "s1" => $s1,
            "s2" => $s2,
            "s3" => $s3,
            "s4" => $s4,
            "s5" => $s5,
            "s6" => $s6,
        ];
        if ($s7) {
            $s["s7"] = $s7;
        }
        asort($s);

        foreach ($s as $key => $_s) {
            if (in_array($key, ["s2", "s3", "s4"])) {
                $key_next = "";
                $flag     = false;
                foreach ($s as $key2 => $value) {
                    if ($flag) {
                        $key_next = $key2;
                        break;
                    }
                    if ($key2 == $key) {
                        $flag = true;
                    }
                }

                if ($key_next == "") {
                    $end = strlen($ins);
                } else {
                    $end = $s[$key_next];
                }
            }

            switch ($key) {
                case "s1":
                    $data["matricule"] = substr($ins, $_s + 2, 15);
                    break;
                case "s2":
                    $data["oid"] = str_replace("<GS>", "", substr($ins, $_s + 2, ($end - ($_s + 2))));
                    break;
                case "s3":
                    $data["prenoms"] = str_replace("<GS>", "", substr($ins, $_s + 2, ($end - ($_s + 2))));
                    break;
                case "s4":
                    $data["nom"] = str_replace("<GS>", "", substr($ins, $_s + 2, ($end - ($_s + 2))));
                    break;
                case "s5":
                    $data["sexe"] = substr($ins, $_s + 2, 1);
                    break;
                case "s6":
                    $data["date_naissance"] = date("Y-m-d", strtotime(substr($ins, $_s + 2, 10)));
                    $data["jour"]           = date("d", strtotime(substr($ins, $_s + 2, 10)));
                    $data["mois"]           = date("m", strtotime(substr($ins, $_s + 2, 10)));
                    $data["annee"]          = date("Y", strtotime(substr($ins, $_s + 2, 10)));
                    break;
                case "s7":
                    $code_insee = substr($ins, $_s + 1, 6);
                    $index      = strpos($code_insee, "99");
                    if ($index == 1) {
                        $pays                             = (new CPaysInsee())->loadByInsee(substr($code_insee, 1));
                        $data["pays_naissance_insee"]     = $pays->numerique;
                        $data["code_insee"]               = $pays->code_insee;
                        $data["nom_pays_naissance_insee"] = $pays->nom_fr;
                    } else {
                        $commune                         = (new CCommuneFrance())->loadByInsee(
                            substr($code_insee, 1)
                        );
                        $data["commune_naissance_insee"] = $commune->INSEE;

                        $data["lieu_naissance"]           = $commune->commune;
                        $data["cp_naissance"]             = $commune->code_postal;
                        $data["pays_naissance_insee"]     = CPaysInsee::getPaysNumByNomFR("France");
                        $data["nom_pays_naissance_insee"] = "France";
                    }
                    break;
                default:
                    break;
            }
        }

        return $data;
    }

    /**
     * Get the nature of the INS identification
     *
     * @return string
     */
    protected function getINSType(): string
    {
        return CAppUI::tr("CPatientINSNIR-_ins_type." . ($this->_is_ins_nir ? "nir" : "nia"));
    }
}
