<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CPatientExportHm implements IShortNameAutoloadable {
  public $patients_csv = array();
  public $date_time_format = '%d%m%Y%H%M'; // = ddmmyyyyhh24mi
  public $sanitize_fields = array("\r", ";", "\n");

  static public $columns = array(
    'TypeLigne'            => 3,            // Type de ligne, toujours PAT
    '_IPP'                 => 15,                 // IPP du patient : string (15)
    'IPPAFusionner'        => 15,        // IPP à fusionner : string (15)
    'nom'                  => 60,                  // Nom usuel : string (60)
    'prenom'               => 60,               // Prénom : string (60)
    'sexe'                 => 1,                 // Sexe : enum (F, H, I)
    'civilite'             => 40,             // Civilite : enum (AR,DR,ENFF,ENFM,ETS,EURL,ME,MED,MEP,MLLE,MR,MRD,MRP,PR,SA,SARL,SOC)
    'nom_jeune_fille'      => 60,      // Nom de naissance : string (60)
    'naissance'            => 12,            // Date de naissance : format ddmmyyyhh24mi
    'rang_naissance'       => 1,        // Rang de naissance : int (1-9)
    'adresse'              => 0,               // Adresse du patient, voir formatAdresse()
    'email'                => 80,                // Email du patient : string (80)
    'situation_famille'    => 40,    // Situation matrimoniale : enum (CELIBATAIRE, DIVORCE, MARIE, SEPARE, VEUVAGE, VIE_MARITALE)
    'deces'                => 12,                // Date de décès : format ddmmyyyhh24mi
    'PoidsNaissance'       => 4,        // Poids de naissance du patient :
    'NumeroINSEE'          => 15,          // Numéro INSEE du patient : string (15)
    'NombreEnfants'        => 2,         // Nombre d'enfants : int (2)
    'NumeroArchive'        => 25,        // Laisser vide
    'rques'                => 255,               // Commentaires sur le patient : string (255)
    'NumeroINS'            => 0,             // Numéro INS, description dans le fichier de description de l'export
    'IDConfidentielNom'    => 60,    // Nom de rempalcement pour un patient confidentiel : string (60)
    'IDConfidentielPrenom' => 60, // Prénom de remplacement pour un patient confidentiel : string (60)
    'DateNaissanceAMO'     => 12,     // Date de naissance de la carte vitale : format ddmmyyyhh24mi
  );

  static public $corresp_sexe = array(
    'm' => 'H',
    'f' => 'F',
  );

  static public $corresp_civilite = array(
    'm'    => 'MR',
    'mme'  => 'ME',
    'mlle' => 'MLLE',
    'enff' => 'ENFF',
    'enfm' => 'ENFM',
    'enf'  => 'ENFM', // Si pas de sexe pour le patient
    'dr'   => 'DR',
    'pr'   => 'PR',
    'me'   => '', // Maitre
    'vve'  => '', // Veuve
  );

  static public $corresp_situation_famille = array(
    'S' => 'CELIBATAIRE',
    'M' => 'MARIE',
    'G' => 'VIE_MARITALE',
    'P' => 'VIE_MARITALE',
    'D' => 'DIVORCE',
    'W' => 'VEUVAGE',
    'A' => 'SEPARE',
  );

  /**
   * @param int $start Start export at
   * @param int $count Number of patients to export
   *
   * @return int
   */
  public function doExport($start, $count) {
    $patients = $this->loadPatients($start, $count);

    $nb_no_ipp = 0;
    foreach ($patients as $_pat) {
      if (!$this->isPatientValide($_pat)) {
        $nb_no_ipp++;
        continue;
      }
      $this->parsePatient($_pat);
    }

    return count($patients) - $nb_no_ipp;
  }

  /**
   * @param CPatient $patient Patient to check
   *
   * @return bool
   */
  function isPatientValide($patient) {
    if (!$patient->_IPP) {
      return false;
    }

    if (preg_match('/\D/', $patient->_IPP)) {
      return false;
    }

    if (trim($patient->nom) === 'NON VALIDE') {
      return false;
    }

    return true;
  }

  /**
   * Load the first $count patients starting at $start
   *
   * @param int $start Start at
   * @param int $count Number of results
   *
   * @return array CPatient[]
   */
  public function loadPatients($start, $count) {
    $patient = new CPatient();

    $patients_ids = $patient->loadIds(null, 'patient_id ASC', "$start,$count");
    $patients     = $patient->loadAll($patients_ids);
    CPatient::massLoadIPP($patients);

    return $patients;
  }

  /**
   * @param CPatient $patient Patient to export
   *
   * @return void
   */
  public function parsePatient($patient) {
    $patient_csv = array();

    foreach (self::$columns as $_col => $_size) {
      switch ($_col) {
        case 'TypeLigne':
          $patient_csv[$_col] = 'PAT'; // Toujours mettre à PAT pour un export patients
          break;
        case '_IPP':
        case 'nom':
        case 'prenom':
          $patient_csv[$_col] = (strlen($patient->$_col) < $_size) ? $patient->$_col : substr($patient->$_col, 0, $_size);
          break;
        case 'sexe':
          $patient_csv[$_col] = ($patient->sexe) ? self::$corresp_sexe[$patient->sexe] : 'I';
          break;
        case 'nom_jeune_fille':
          $patient_csv[$_col] = ($patient->nom_jeune_fille) ? $patient->nom_jeune_fille : $patient->nom;
          break;
        case 'email':
          $patient_csv[$_col] = (preg_match('/^[-a-z0-9\._]+@[-a-z0-9\.]+\.[a-z]{2,4}$/i', $patient->email)) ? $patient->email : '';
          break;
        case 'civilite':
          if ($patient->civilite == 'enf' && $patient->sexe == 'm') {
            $patient_csv[$_col] = self::$corresp_civilite['enfm'];
            break;
          }

          if ($patient->civilite == 'enf' && $patient->sexe == 'f') {
            $patient_csv[$_col] = self::$corresp_civilite['enff'];
            break;
          }

          $patient_csv[$_col] = ($patient->civilite) ? self::$corresp_civilite[$patient->civilite] : '';
          break;
        case 'naissance':
          $patient_csv[$_col] = $this->formatDateTime($patient->naissance);
          break;
        case 'adresse':
          $patient_csv[$_col] = $this->formatAdresse($patient);
          break;
        case 'situation_famille':
          $patient_csv[$_col] = ($patient->situation_famille) ? self::$corresp_situation_famille[$patient->situation_famille] : '';
          break;
        case 'deces':
          $patient_csv[$_col] = ($patient->deces) ? $this->formatDateTime($patient->deces) : '';
          break;
        case 'rang_naissance':
          $patient_csv[$_col] = ($patient->rang_naissance) ?: 1;
          break;
        case 'PoidsNaissance':
        case 'NumeroINSEE':
        case 'NombreEnfants':
        case 'NumeroINS':
        case 'IDConfidentielNom':
        case 'IDConfidentielPrenom':
        case 'DateNaissanceAMO':
        case 'IPPAFusionner':
        case 'rques':
        case 'NumeroArchive': // Toujours laisser vide
        default:
          $patient_csv[$_col] = '';
      }
    }

    $this->patients_csv[$patient->_id] = $patient_csv;
  }

  /**
   * @param string $dateTime Date time to format
   *
   * @return string
   */
  public function formatDateTime($dateTime) {
    return CMbDT::format($dateTime, $this->date_time_format);
  }

  /**
   * @param CPatient $patient Patient to get adresse from
   *
   * @return string
   */
  public function formatAdresse($patient) {
    return sprintf(
      '%s~%s~%s~%s~%s~%s~%s~%s', //  cp, ville, INSEE pays, tel, tel_autre
      'ADR_PERSO', // Type : ADR_PERSO|ADR_PROF|ADR_NAISSANCE
      ($patient->adresse) ?: '', // Adresse ligne 1
      '', // Adresse ligne 2
      '', // Adresse ligne 3
      ($patient->cp) ?: '', // cp
      ($patient->ville) ?: '', // ville
      ($patient->pays_insee) ?: '', // Pays INSEE
      ($patient->tel) ?: '', // tel
      ($patient->tel_autre) ?: '' // tel_autre
    );
  }

  /**
   * @return array
   */
  public function getPatientsCsv() {
    return $this->patients_csv;
  }

  /**
   * Create the first line of the file
   *
   * @return array
   */
  public function createHeader() {
    $group  = CGroups::loadCurrent();
    $header = array(
      'H',
      ($group->finess) ?: 'finess',
      $group->text,
    );

    return $header;
  }

  /**
   * Write a line to the export file
   *
   * @param array    $infos Infos to write to file
   * @param resource $fp    File handle
   *
   * @return void
   */
  public function writeLine($infos, $fp) {
    $line = $this->sanitizeLine($infos);
    $line = implode(";", $line) . "\r";
    fwrite($fp, $line);
  }

  /**
   * Sanitize the fields to removes chars from $this->sanitize_fields
   *
   * @param array|string $line Strings to sanitize
   *
   * @return array|mixed
   */
  public function sanitizeLine($line) {
    if (is_array($line)) {
      array_walk(
        $line, function (&$value) {
        $value = str_replace($this->sanitize_fields, '', $value);
      }
      );
    }
    else {
      $line = str_replace($this->sanitize_fields, '', $line);
    }

    return $line;
  }
}
