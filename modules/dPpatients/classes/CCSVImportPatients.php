<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
class CCSVImportPatients extends CMbCSVObjectImport
{
    public const EXTERNAL_IDS_SEPARATOR = ',';

    public const EXTERNAL_ID_PARTS = '|';

    /** @var int */
    protected $group_id;

    /** @var array */
    protected $identito_fields = [];

    /** @var string */
    protected $secondary_operand = 'or';

    /** @var string */
    public static $found_action = 'replace_empty';

    /** @var bool[] */
    public static $options_interop = [
        'by_IPP'           => true,
        'generate_IPP'     => false,
        'disable_handlers' => false,
    ];

    /** @var bool[] */
    public static $options = [
        'no_create'     => false,
        'fail_on_empty' => false,
    ];

    /** @var bool[] */
    public static $identito_main = [
        "nom"             => true,
        "nom_jeune_fille" => true,
        "prenom"          => true,
        "sexe"            => false,
        "prenoms_autres"  => true,
    ];

    /** @var bool[] */
    public static $identito_secondary = [
        "tel"       => false,
        "matricule" => false,
    ];

    /** @var string[] */
    public static $options_found = [
        'nothing',
        'replace_empty',
        'replace_all',
    ];

    /** @var array */
    protected static $civilite = [
        'AR'   => null,
        'DR'   => 'dr',
        'ENFF' => 'enf',
        'ENFM' => 'enf',
        'ETB'  => null,
        'EURL' => null,
        'ME'   => 'mme',
        'MED'  => 'dr',
        'MEP'  => 'pr',
        'MLLE' => 'mlle',
        'MR'   => 'm',
        'MRD'  => 'dr',
        'MRP'  => 'pr',
        'PR'   => 'pr',
        'SA'   => null,
        'SARL' => null,
        'SOC'  => null,
    ];

    /**
     * @var string[]
     */
    protected static $sexe = [
        'H' => 'm',
        'M' => 'm',
        'F' => 'f',
        'm' => 'm',
        'f' => 'f',
        'u' => 'u',
    ];

    /**
     * CCSVImportPatients constructor.
     *
     * @param int    $start   Start the import at line $start
     * @param int    $step    Import $step lines
     * @param string $profile Profile tu use to read the CSV
     *
     * @throws Exception
     */
    public function __construct($start = 0, $step = 100, $profile = CCSVFile::PROFILE_EXCEL)
    {
        parent::__construct(CAppUI::conf("dPpatients imports pat_csv_path"), $start, $step, $profile);
    }

    /**
     * @inheritdoc
     */
    public function import()
    {
        $this->openFile();
        $this->setColumnNames();
        $this->setPointerToStart();

        // Load the current CGroups
        $this->group_id = CGroups::loadCurrent()->_id;

        // For each line
        while ($this->nb_treated_line < $this->step) {
            $this->nb_treated_line++;
            $this->current_line = $this->start + $this->nb_treated_line + 1;

            $patient = new CPatient();
            // Read and sanitize the line
            $_patient = $this->readAndSanitizeLine();

            // If no line end the import
            if (!$_patient) {
                CAppUI::stepAjax('CMbCSVObjectImport-end', UI_MSG_OK);

                return false;
            }

            // If import by IPP and there is not IPP in the file, ignore the line
            if (self::$options_interop['by_IPP'] && !$_patient['_IPP']) {
                $msg = CAppUI::tr("CCSVImportPatients-IPP.none", $this->current_line);

                CApp::log($msg, null, LoggerLevels::LEVEL_WARNING);
                CAppUI::setMsg($msg);

                $this->nb_errors++;
                $this->start++;
                continue;
            }

            $patient->_IPP = $_patient['_IPP'];

            // If import by IPP continue if the patient exists
            if (self::$options_interop['by_IPP']) {
                $patient = $this->getPatientByIpp($patient);

                // Patient found and no maj : continue
                if ($patient === null) {
                    $this->nb_errors++;
                    $this->start++;
                    continue;
                }
            }

            $create_doubloon = (bool)(self::$options_interop['by_IPP'] && $_patient['_IPP']);

            if (!$patient->_id && !$create_doubloon) {
                // Set the patient mandatory fields and
                $patient = $this->checkPatientExists($patient, $_patient);

                if (!$patient) {
                    $this->nb_errors++;
                    $this->start++;
                    continue;
                }

                if ($patient->_id) {
                    CAppUI::setMsg("CPatient-msg-found", UI_MSG_OK);
                }
            }

            // Do not create patient
            if (self::$options['no_create'] && !$patient->_id) {
                $this->nb_errors++;
                $this->start++;
                continue;
            }

            // Si le patient n'existe pas OU que l'on veut remplacer tous ses champs
            if (!$patient->_id || ($patient->_id && static::$found_action == 'replace_all')) {
                $patient = $this->createNewPatient($patient, $_patient);
                if ($patient === null) {
                    $this->nb_errors++;
                    $this->start++;
                    continue;
                }

                CAppUI::setMsg('CPatient-msg-create', UI_MSG_OK);
            } elseif ($patient->_id && static::$found_action == 'replace_empty') {
                // Si le patient existe mais qu'on ne veut remplacer que ses champs vides
                foreach ($_patient as $_field => $_value) {
                    if (trim($_value) && property_exists("CPatient", $_field) && $patient->$_field == '') {
                        $patient->$_field = trim($_value);
                    }
                }

                if ($msg = $patient->store()) {
                    CAppUI::setMsg($msg, UI_MSG_WARNING);
                    $this->nb_errors++;
                    $this->start++;
                    continue;
                }
            }

            if ($patient->_id) {
                if (self::$options_interop['by_IPP']) {
                    $ipp = $this->createIppTag($patient, $_patient);
                    if ($ipp === null) {
                        $this->nb_errors++;
                        $this->start++;
                        continue;
                    }

                    // Forcing ITI30 handler event
                    $patient->_no_synchro_eai = false;
                    $patient->rques           = rtrim($patient->rques, '.');
                    $patient->store();
                }

                if (isset($_patient['identifiants_externes']) && $_patient['identifiants_externes']) {
                    $this->importExternalIds($patient, $_patient['identifiants_externes']);
                }
            }
        }

        return true;
    }

    /**
     * Get a patient from it's IPP and update the fields if needed
     *
     * @param CPatient $patient Patient object
     *
     * @return CPatient
     */
    public function getPatientByIpp($patient)
    {
        $patient->loadFromIpp($this->group_id);

        if ($patient->_id) {
            CAppUI::setMsg("CPatient-msg-found", UI_MSG_OK);

            if (static::$found_action == 'nothing') {
                return null; // return null to do nothing
            }
        }

        return $patient;
    }

    /**
     * @param CPatient $patient Patient object
     * @param array    $line    The CSV line
     *
     * @return CPatient
     * @throws Exception
     */
    public function checkPatientExists($patient, $line)
    {
        $patient->nom             = ($line['nom']) ? $this->cleanString($line['nom']) : $this->cleanString(
            $line['nom_jeune_fille']
        );
        $patient->nom_jeune_fille = $this->cleanString($line['nom_jeune_fille']);

        if (!$patient->nom) {
            if ($patient->nom_jeune_fille) {
                $patient->nom = $patient->nom_jeune_fille;
            } else {
                $msg = (self::$options_interop['by_IPP'])
                    ? "IPP #{$line['_IPP']} : Pas de nom"
                    : "Ligne {$this->current_line} : Pas de nom";
                CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);

                CAppUI::setMsg($msg, UI_MSG_WARNING);

                return null;
            }
        }

        if ($line["naissance"]) {
            $line["naissance"]  = preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '\\3-\\2-\\1', $line["naissance"]);
            $patient->naissance = $line["naissance"];
        }

        $patient->repair();

        if (!$patient->naissance) {
            $msg = (self::$options_interop['by_IPP'])
                ? "IPP #{$line['_IPP']} : Date de naissance invalide ({$line['naissance']})"
                : "Ligne {$this->current_line} : Date de naissance invalide ({$line['naissance']})";

            CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);
            CAppUI::setMsg($msg, UI_MSG_WARNING);

            return null;
        }

        $patient->prenom = $this->cleanString($patient->prenom);

        $this->loadMatchingPatientCustom($patient, $line);

        return $patient;
    }

    /**
     * @param CPatient $patient Patient
     * @param array    $line    Line from the CSV
     *
     * @return void
     * @throws Exception
     */
    protected function loadMatchingPatientCustom($patient, $line)
    {
        $ds    = $patient->getDS();
        $where = [];

        if (CAppUI::isCabinet()) {
            $function_id          = CMediusers::get()->function_id;
            $where["function_id"] = "= '$function_id'";
        } elseif (CAppUI::isGroup()) {
            $group_id          = CMediusers::get()->loadRefFunction()->group_id;
            $where["group_id"] = "= '$group_id'";
        }

        $where["patient_id"] = " != '$patient->_id'";

        // if no birthdate, sql request too strong
        // Use the naissance field from patient for birthdate
        if (!$line["naissance"]) {
            return null;
        }

        $where['naissance'] = $ds->prepare('= ?', $line["naissance"]);

        if (static::$identito_main['nom'] && static::$identito_main['nom_jeune_fille']) {
            $whereOr = [];
            if ($line['nom']) {
                $whereOr[] = "nom " . $ds->prepareLikeName($line['nom']);
                $whereOr[] = "nom_jeune_fille " . $ds->prepareLikeName($line['nom']);
            }

            if ($line['nom_jeune_fille']) {
                $whereOr[] = "nom " . $ds->prepareLikeName($line['nom_jeune_fille']);
                $whereOr[] = "nom_jeune_fille " . $ds->prepareLikeName($line['nom_jeune_fille']);
            }

            if ($whereOr) {
                $where[] = implode(" OR ", $whereOr);
            }
        } elseif (static::$identito_main['nom']) {
            $where['nom'] = $ds->prepareLikeName($line['nom']);
        } elseif (static::$identito_main['nom_jeune_fille']) {
            $where['nom_jeune_fille'] = $ds->prepareLikeName($line['nom_jeune_fille']);
        }


        if (static::$identito_main['prenom']) {
            $where['prenom'] = $ds->prepareLikeName($line['prenom']);
        }

        if (static::$identito_main['sexe']) {
            $where['sexe'] = $ds->prepare('= ?', $line['sexe']);
        }

        if (static::$identito_main['prenoms_autres']) {
            $prepare_like_prenoms = [];
            if ($line['prenom_2']) {
                $prepare_like_prenoms[] = '`prenoms` ' . $ds->prepareLikeName($line['prenom_2']);
            }

            if ($line['prenom_3']) {
                $prepare_like_prenoms[] = '`prenoms` ' . $ds->prepareLikeName($line['prenom_3']);
            }

            if ($line['prenom_4']) {
                $prepare_like_prenoms[] = '`prenoms` ' . $ds->prepareLikeName($line['prenom_4']);
            }

            if (count($prepare_like_prenoms)) {
                $where['prenoms'] = implode(' OR ', $prepare_like_prenoms);
            }
        }

        $secondary_conditions = [];
        if (static::$identito_secondary['tel']) {
            $whereOr = [];
            if ($line['tel']) {
                $line['tel'] = preg_replace("/[^0-9]/", "", $line['tel']);
                if (strlen($line['tel']) === 9) {
                    $line['tel'] = "0" . $line['tel'];
                }

                $whereOr[] = "tel " . $ds->prepare('= ?', $line['tel']);
                $whereOr[] = "tel2 " . $ds->prepare('= ?', $line['tel']);
            }

            if ($line['tel2']) {
                $line['tel2'] = preg_replace("/[^0-9]/", "", $line['tel2']);
                if (strlen($line['tel2']) === 9) {
                    $line['tel2'] = "0" . $line['tel2'];
                }

                $whereOr[] = "tel " . $ds->prepare('= ?', $line['tel2']);
                $whereOr[] = "tel2 " . $ds->prepare('= ?', $line['tel2']);
            }

            if ($whereOr) {
                $secondary_conditions[] = implode(" OR ", $whereOr);
            }
        }

        if (static::$identito_secondary['matricule']) {
            $secondary_conditions[] = "matricule " . $ds->prepare('= ?', $line['matricule']);
        }

        if (static::$options['fail_on_empty'] && !$line['tel'] && !$line['tel2'] && !$line['matricule']) {
            return;
        }

        if ($secondary_conditions) {
            if ($this->secondary_operand == 'or') {
                $where[] = implode(" OR ", $secondary_conditions);
            } else {
                $where = array_merge($where, $secondary_conditions);
            }
        }

        $patient->loadObject($where);
    }

    /**
     * Create a new patient with the hash from CSV
     *
     * @param CPatient $patient Patient object
     * @param array    $line    The CSV line
     *
     * @return CPatient
     * @throws Exception
     */
    public function createNewPatient($patient, $line)
    {
        $patient->bind($line, false);
        $patient->nom             = $this->cleanString($patient->nom);
        $patient->prenom          = $this->cleanString($patient->prenom);
        $patient->nom_jeune_fille = $this->cleanString($patient->nom_jeune_fille);
        $patient->naissance       = preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '\\3-\\2-\\1', $patient->naissance);

        $patient->tel = preg_replace("/[^0-9]/", "", $patient->tel);
        if (strlen($patient->tel) === 9) {
            $patient->tel = "0$patient->tel";
        }

        $patient->tel_autre = preg_replace("/[^0-9]/", "", $patient->tel_autre);
        if (strlen($patient->tel_autre) === 9) {
            $patient->tel_autre = "0$patient->tel_autre";
        }

        $patient->sexe        = ($patient->sexe) ? self::$sexe[trim($patient->sexe)] : 'u';
        $patient->assure_sexe = ($patient->assure_sexe) ? self::$sexe[trim($patient->assure_sexe)] : 'u';

        $patient->repair();

        if (
            !isset($line['civilite']) || !$line['civilite'] || !isset(self::$civilite[$line['civilite']])
            || !self::$civilite[$line['civilite']]
        ) {
            // After $patient->repair() because of 'guess' is not in spec
            $patient->civilite = 'guess';
        } else {
            $patient->civilite = self::$civilite[$line['civilite']];
        }

        if (
            !isset($line['addure_civilite']) || !$line['assure_civilite']
            || !isset(self::$civilite[$line['assure_civilite']]) || !self::$civilite[$line['assure_civilite']]
        ) {
            // After $patient->repair() because of 'guess' is not in spec
            $patient->civilite = 'guess';
        } else {
            $patient->assure_civilite = self::$civilite[$line['assure_civilite']];
        }

        if (self::$options_interop['generate_IPP']) {
            $patient->_IPP = null;
        } else {
            // Ne pas générer de nouvel IPP (interne Mediboard)
            $patient->_generate_IPP = false;

            if (self::$options_interop['by_IPP']) {
                // Because of ITI30 handler which forces IPP generation
                $patient->_no_synchro_eai = true;

                $patient->rques = ($patient->rques === null) ? '.' : $patient->rques . '.';
            }
        }

        $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_IMPORT;

        if ($msg = $patient->store()) {
            $msg = (self::$options_interop['by_IPP'])
                ? "IPP #{$line['_IPP']} : $msg"
                : "Ligne {$this->current_line} : $msg";
            CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);

            CAppUI::setMsg($msg, UI_MSG_WARNING);

            return null;
        } else {
            CAppUI::stepAjax("CPatient-msg-create", UI_MSG_OK);
        }

        return $patient;
    }

    /**
     * Put an IPP on the patient
     *
     * @param CPatient $patient Patient object
     * @param array    $line    The CSV line
     *
     * @return CIdSante400
     * @throws Exception
     */
    public function createIppTag($patient, $line)
    {
        $ipp = CIdSante400::getMatch($patient->_class, CPatient::getTagIPP($this->group_id), null, $patient->_id);

        if ($ipp->_id && $ipp->id400 != $patient->_IPP) {
            $msg = "IPP #{$line['_IPP']} : Ce patient possède déjà un IPP ({$ipp->id400})";
            CApp::log($msg, null, LoggerLevels::LEVEL_WARNING);
            CAppUI::setMsg($msg, UI_MSG_WARNING);

            return null;
        }

        if (!$ipp->_id) {
            $ipp->id400 = $line['_IPP'];

            if ($msg = $ipp->store()) {
                CApp::log("IPP #{$line['_IPP']} : $msg", LoggerLevels::LEVEL_WARNING);

                CAppUI::setMsg("IPP #{$line['_IPP']} : $msg", UI_MSG_WARNING);

                return null;
            }
        }

        return $ipp;
    }

    /**
     * @param bool   $by_IPP        Import par IPP
     * @param bool   $generate_IPP  Generate a new IPP
     * @param string $patient_found Action when a patient is found
     * @param bool   $no_create     Do not create new patients
     * @param bool   $fail_on_empty Do not match if a parameter is missing
     *
     * @return void
     */
    public function setOptions(
        $by_IPP = true,
        $generate_IPP = false,
        $patient_found = "replace_empty",
        $no_create = false,
        $fail_on_empty = false
    ) {
        static::$options_interop["by_IPP"]       = $by_IPP;
        static::$options_interop["generate_IPP"] = $generate_IPP;

        static::$found_action = $patient_found;

        static::$options['no_create']     = $no_create;
        static::$options['fail_on_empty'] = $fail_on_empty;
    }

    /**
     * @param bool   $nom               Use last_name for identito
     * @param bool   $prenom            Use first_name for identito
     * @param bool   $naissance         Use birthdate for identito
     * @param bool   $sexe              Use sex for identito
     * @param bool   $prenoms_autres    Use all the last_names for identito
     * @param bool   $tel               Use tel for identito
     * @param bool   $matricule         Use matricule for identito
     * @param string $secondary_operand Operand for the secondary identito criteras
     *
     * @return void
     */
    public function setIdentito(
        $nom = false,
        $prenom = false,
        $naissance = false,
        $sexe = false,
        $prenoms_autres = false,
        $tel = false,
        $matricule = false,
        $secondary_operand = 'or'
    ) {
        static::$identito_main['nom']            = $nom;
        static::$identito_main['prenom']         = $prenom;
        static::$identito_main['naissance']      = $naissance;
        static::$identito_main['sexe']           = $sexe;
        static::$identito_main['prenoms_autres'] = $prenoms_autres;
        static::$identito_secondary['tel']       = $tel;
        static::$identito_secondary['matricule'] = $matricule;

        $this->secondary_operand = $secondary_operand;
    }

    /**
     * @param string $str String to clean
     *
     * @return null|string|string[]
     */
    public function cleanString($str)
    {
        $str = preg_replace("/\s*-+\s*/", '-', $str);
        $str = trim(preg_replace("/\s*'+\s*/", "'", $str));

        return $str;
    }

    private function importExternalIds(CPatient $patient, string $ext_ids): void
    {
        foreach (explode(self::EXTERNAL_IDS_SEPARATOR, $ext_ids) as $ext_id) {
            $ext_id_parts = explode(self::EXTERNAL_ID_PARTS, $ext_id);
            $id400        = $ext_id_parts[0];
            $tag          = $ext_id_parts[1] ?? null;

            $idx               = new CIdSante400();
            $idx->id400        = $id400;
            $idx->tag          = ($tag === CPatient::getTagIPP($this->group_id)) ? 'trash_' . $tag : $tag;
            $idx->object_class = $patient->_class;
            $idx->object_id     = $patient->_id;
            $idx->loadMatchingObjectEsc();

            $new = !$idx->_id;

            if ($msg = $idx->store()) {
                CAppUI::setMsg($msg, UI_MSG_WARNING);
            } else {
                CAppUI::setMsg('CIdSante400-msg-' . ($new ? 'create' : 'found'));
            }
        }
    }
}
