<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
class CCSVImportSejours extends CMbCSVObjectImport {
  protected $group_id;
  protected $prefix;
  protected $codes;
  protected $codes_entree;
  protected $codes_sortie;

  public static $options = array(
    'by_NDA' => true,
    'maj'    => false,
  );

  /**
   * CCSVImportSejours constructor.
   *
   * @inheritdoc
   */
  function __construct($start = 0, $step = 100, $profile = CCSVFile::PROFILE_EXCEL) {
    parent::__construct(CAppUI::conf("dPpatients imports sejour_csv_path"), $start, $step, $profile);
  }

  /**
   * @inheritdoc
   */
  function import() {
    $this->openFile();
    $this->setColumnNames();
    $this->setPointerToStart();

    $this->group_id = CGroups::loadCurrent()->_id;

    $this->codes        = $this->getCodesModeTraitement();
    $this->codes_entree = $this->getCodesEntree();
    $this->codes_sortie = $this->getCodesSortie();

    while ($this->nb_treated_line < $this->step) {
      $this->nb_treated_line++;
      $this->current_line = $this->start + $this->step + 1;

      $_sejour = $this->readAndSanitizeLine();

      if (!$_sejour) {
        CAppUI::stepAjax('CMbCSVObjectImport-end', UI_MSG_OK);
        return false;
      }

      if (self::$options['by_NDA'] && (!isset($_sejour['_NDA']) || !$_sejour['_NDA'])) {
        $msg = "Ligne #{$this->current_line} : Aucun NDA";
        CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);
        CAppUI::setMsg($msg, UI_MSG_WARNING);

        $this->nb_errors++;
        $this->start++;
        continue;
      }

      $this->prefix = (self::$options['by_NDA']) ? "NDA #{$_sejour['_NDA']}" : "Ligne #{$this->current_line}";

      $sejour = new CSejour();

      if (self::$options['by_NDA']) {
        $sejour->loadFromNDA($_sejour['_NDA']);

        if ($sejour->_id && !self::$options['maj']) {
          $msg = "NDA #{$_sejour['_NDA']} : Un séjour avec ce NDA existe déjà";
          CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);
          CAppUI::setMsg($msg, UI_MSG_WARNING);

          $this->nb_errors++;
          $this->start++;
          continue;
        }
        elseif ($sejour->_id) {
          CAppUI::setMsg('CSejour-msg-found', UI_MSG_OK);
        }
      }

      $_sejour['sortie_reelle'] = $_sejour['sortie_reelle'] ?? null;
      $_sejour['sortie_prevue'] = $_sejour['sortie_prevue'] ?? null;
      $_sejour['entree_reelle'] = $_sejour['entree_reelle'] ?? null;
      $_sejour['entree_prevue'] = $_sejour['entree_prevue'] ?? null;

      $sortie     = ($_sejour['sortie_reelle'] || $_sejour['sortie_prevue']);
      $has_sortie = $this->checkSejourIO($_sejour['entree_reelle'], $_sejour['entree_prevue'], $sortie);
      if ($has_sortie === null) {
        $this->nb_errors++;
        $this->start++;
        continue;
      }

      if ($has_sortie === false) {
        $_sejour['sortie_prevue'] = ($_sejour['entree_reelle']) ?: $_sejour['entree_prevue'];
      }

      $default_mediuser = (isset($_sejour['default_mediuser'])) ? $_sejour['default_mediuser'] : null;
      $mediuser         = $this->getMediuserFromCSV($_sejour['rpps'], $_sejour['adeli'], $default_mediuser);
      if (!$mediuser || !$mediuser->_id) {
        $msg = "$this->prefix : Praticien non retrouvé (ADELI : {$_sejour['adeli']}, RPPS : {$_sejour['rpps']})";
        CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);
        CAppUI::setMsg($msg, UI_MSG_WARNING);

        $this->nb_errors++;
        $this->start++;
        continue;
      }

      $patient = $this->getPatientFromIpp($_sejour['_IPP']);
      if (!$patient || !$patient->_id) {
        $msg = "$this->prefix : Patient non retrouvé (IPP : {$patient->_IPP})";
        CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);
        CAppUI::setMsg($msg, UI_MSG_WARNING);

        $this->nb_errors++;
        $this->start++;
        continue;
      }

      $sejour->bind($_sejour);

      if (!$sejour->_id) {
        $sejour->_NDA = null;
      }

      $sejour->patient_id   = $patient->_id;
      $sejour->praticien_id = $mediuser->_id;
      $sejour->group_id     = $this->group_id;

      $sejour      = $this->setModeTraitement($sejour, $_sejour['MDT']);
      $mode_entree = isset($_sejour['MDE']) ? $_sejour['MDE'] : '';
      $mode_sortie = isset($_sejour['MDS']) ? $_sejour['MDS'] : '';
      $sejour      = $this->setModesIO($sejour, $mode_entree, $mode_sortie);

      $sejour = $this->setEntranceDate($sejour, $_sejour['entree_reelle'], $_sejour['entree_prevue']);
      $sejour = $this->setExitDate($sejour, $_sejour['sortie_reelle'], $_sejour['sortie_prevue']);

      if (!$has_sortie) {
        $sejour = $this->setSejourExit($sejour);
      }

      $sejour->sortie = ($sejour->sortie_reelle) ?: $sejour->sortie_prevue;

      $sejour->repair();
      $sejour->updatePlainFields();

      if (!$sejour->entree || !$sejour->sortie) {
        $msg =
          "$this->prefix : Dates de séjour invalides (entrée : {$_sejour['entree_reelle']}, sortie : {$_sejour['sortie_reelle']})";
        CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);
        CAppUI::setMsg($msg, UI_MSG_WARNING);

        $this->nb_errors++;
        $this->start++;
        continue;
      }

      if (!$sejour->type && (CMbDT::date($sejour->entree) == CMbDT::date($sejour->sortie))) {
        $sejour->type = 'ambu';
      }

      $sejour->loadMatchingObjectEsc();

      $collides = $sejour->getCollisions();
      if ($collides && !self::$options['maj']) {
        $sejour = reset($collides);
      }
      else {
        // Ne pas générer un nouveau NDA (interne Mediboard)
        $sejour->_generate_NDA = false;

        if ($msg = $sejour->store()) {
          CApp::log("$this->prefix : $msg", null, LoggerLevels::LEVEL_DEBUG);
          CAppUI::setMsg("$this->prefix : $msg", UI_MSG_WARNING);

          $this->nb_errors++;
          $this->start++;
          continue;
        }
        else {
          ($sejour->_NDA) ? CAppUI::setMsg('CSejour-msg-modify', UI_MSG_OK) : CAppUI::setMsg('CSejour-msg-create', UI_MSG_OK);
        }
      }

      if (self::$options['maj'] && $sejour->_NDA) {
        $this->start++;
        continue;
      }

      if ($_sejour['_NDA']) {
        $nda = $this->createNDA($sejour, $_sejour['_NDA']);
        if ($nda === null) {
          $this->nb_errors++;
          $this->start++;
          continue;
        }
      }
    }

    return true;
  }

  /**
   * @param string $ipp Patient IPP
   *
   * @return CPatient
   * @throws \Exception
   */
  function getPatientFromIpp($ipp) {
    if (!$ipp) {
      CApp::log("$this->prefix : Aucun IPP", null, LoggerLevels::LEVEL_DEBUG);
      CAppUI::setMsg("$this->prefix : Aucun IPP", UI_MSG_WARNING);
    }

    $cache = new Cache('CCSVImportSejours.getPatientFromIpp', $ipp, Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    $patient       = new CPatient();
    $patient->_IPP = $ipp;
    $patient->loadFromIPP($this->group_id);

    return $cache->put($patient);
  }

  /**
   * @param string $entree_reelle Date for the real start
   * @param string $entree_prevue Date for the start
   * @param bool   $sortie        Does the sejour have an exit
   *
   * @return bool|null
   * @throws \Exception
   */
  function checkSejourIO($entree_reelle, $entree_prevue, $sortie) {
    if (!$entree_reelle && !$entree_prevue) {
      CApp::log("$this->prefix : Entrée prévue/réelle manquante", null, LoggerLevels::LEVEL_DEBUG);
      CAppUI::setMsg("$this->prefix : Entrée prévue/réelle manquante", UI_MSG_WARNING);

      return null;
    }

    return $sortie;
  }

  /**
   * Load a Mediuser from his RPPS or ADELI
   *
   * @param string $rpps             User's RPPS
   * @param string $adeli            User's ADELI
   * @param int    $default_mediuser Default mediuser ID to use for the CSejour
   *
   * @return CMediusers
   * @throws \Exception
   */
  function getMediuserFromCSV($rpps, $adeli, $default_mediuser = null) {
    $cache = new Cache('CCSVImportSejours.getMediuserFromCSV', [$rpps, $adeli, $default_mediuser], Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

      $mediuser = null;
      if ($rpps) {
          $mediuser = CMediusers::loadFromRPPS($rpps);
      }

      if (!($mediuser && $mediuser->_id) && $adeli) {
          $mediuser = CMediusers::loadFromAdeli($adeli);
      }

      if ((!$mediuser || !$mediuser->_id) && $default_mediuser) {
        $mediuser = new CMediusers();
        $mediuser->load($default_mediuser);

      }

    return $cache->put(($mediuser && $mediuser->_id) ? $mediuser : null);
  }

  /**
   * @param CSejour $sejour         Sejour object
   * @param string  $_entree_reelle Real entrance date
   * @param string  $_entree_prevue Estimated entrance date
   *
   * @return mixed
   */
  function setEntranceDate($sejour, $_entree_reelle, $_entree_prevue) {
    $entree_reelle = preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '\\3-\\2-\\1', $_entree_reelle);
    $time          = CMbDT::time($entree_reelle);

    if ($_entree_prevue) {
      $entree_prevue = preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '\\3-\\2-\\1', $_entree_prevue);
      $time_prevue   = CMbDT::time($entree_prevue);

      $sejour->entree_prevue = ($time_prevue) ? CMbDT::date($entree_prevue) . " $time_prevue" : "$entree_prevue 09:00:00";
    }
    else {
      $sejour->entree_prevue = ($time) ? CMbDT::date($entree_reelle) . " $time" : "$entree_reelle 09:00:00";
    }

    if ($entree_reelle) {
      $sejour->entree_reelle = ($time) ? CMbDT::date($entree_reelle) . " $time" : "$entree_reelle 09:00:00";
    }

    $sejour->entree = ($sejour->entree_reelle) ?: $sejour->entree_prevue;

    return $sejour;
  }

  /**
   * @param CSejour $sejour         Sejour object
   * @param string  $_sortie_reelle Real exit date
   * @param string  $_sortie_prevue Estimated exit date
   *
   * @return mixed
   */
  function setExitDate($sejour, $_sortie_reelle, $_sortie_prevue) {
    $sortie_reelle = preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '\\3-\\2-\\1', $_sortie_reelle);
    $time_sortie   = CMbDT::time($sortie_reelle);

    if ($_sortie_prevue) {
      $sortie_prevue = preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '\\3-\\2-\\1', $_sortie_prevue);
      $time_prevue   = CMbDT::time($sortie_prevue);

      $sejour->sortie_prevue = ($time_prevue) ? CMbDT::date($sortie_prevue) . " $time_prevue" : "$sortie_prevue 09:00:00";
    }
    else {
      $sejour->sortie_prevue = ($time_sortie) ? CMbDT::date($sortie_reelle) . " $time_sortie" : "$sortie_reelle 09:00:00";
    }

    if ($sortie_reelle) {
      $sejour->sortie_reelle = ($time_sortie) ? CMbDT::date($sortie_reelle) . " $time_sortie" : "$sortie_reelle 09:00:00";
    }

    return $sejour;
  }

  /**
   * @param CSejour $sejour Sejour object
   *
   * @return CSejour
   */
  function setSejourExit($sejour) {
    $add_datetime = CAppUI::gconf("dPplanningOp CSejour sortie_prevue " . $sejour->type);
    switch ($add_datetime) {
      case "1/4":
        $add_datetime = "00:15:00";
        break;
      case "1/2":
        $add_datetime = "00:30:00";
        break;
      default:
        $add_datetime = $add_datetime . ":00:00";
    }
    $sejour->sortie_prevue = CMbDT::addDateTime($add_datetime, $sejour->entree);

    return $sejour;
  }

  /**
   * @param CSejour $sejour The Sejour object
   * @param string  $_nda   Sejour's NDA
   *
   * @return CIdSante400
   * @throws \Exception
   */
  function createNDA($sejour, $_nda) {
    $nda = CIdSante400::getMatch($sejour->_class, CSejour::getTagNDA($this->group_id), null, $sejour->_id);
    if ($nda->_id && $nda->id400 != $_nda) {
      $msg = "$this->prefix : Ce séjour possède déjà un NDA ({$nda->id400})";
      CApp::log($msg, null, LoggerLevels::LEVEL_DEBUG);
      CAppUI::setMsg($msg, UI_MSG_WARNING);

      return null;
    }

    if (!$nda->_id) {
      $nda->id400 = $_nda;

      if ($msg = $nda->store()) {
        CApp::log("$this->prefix : $msg", null, LoggerLevels::LEVEL_DEBUG);
        CAppUI::setMsg("$this->prefix : $msg", UI_MSG_WARNING);

        return null;
      }
      else {
        CAppUI::setMsg('CIdSante400-msg-create', UI_MSG_OK);
      }
    }

    return $nda;
  }

  /**
   * @inheritdoc
   */
  function sanitizeLine($line) {
    if (isset($line['entree_prevue'])) {
      $line['entree_prevue'] = (preg_match('/0000-00-00/', $line['entree_prevue'])) ? null : $line['entree_prevue'];
    }

    if (isset($line['entree_reelle'])) {
      $line['entree_reelle'] = (preg_match('/0000-00-00/', $line['entree_reelle'])) ? null : $line['entree_reelle'];
    }

    if (isset($line['sortie_prevue'])) {
      $line['sortie_prevue'] = (preg_match('/0000-00-00/', $line['sortie_prevue'])) ? null : $line['sortie_prevue'];
    }

    if (isset($line['sortie_reelle'])) {
      $line['sortie_reelle'] = (preg_match('/0000-00-00/', $line['sortie_reelle'])) ? null : $line['sortie_reelle'];
    }

    return $line;
  }

  /**
   * @return array
   */
  function getCodesModeTraitement() {
    $mode_traitement           = new CChargePriceIndicator();
    $mode_traitement->group_id = $this->group_id;
    $mode_traitement->actif    = 1;

    /** @var CChargePriceIndicator[] $modes_traitement */
    $modes_traitement = $mode_traitement->loadMatchingList();

    $codes = array();
    foreach ($modes_traitement as $_mode) {
      $codes[$_mode->code] = array(
        'id'       => $_mode->_id,
        'type'     => $_mode->type,
        'type_pec' => $_mode->type_pec,
      );
    }

    return $codes;
  }

  /**
   * @param CSejour $sejour Sejour to update
   * @param string  $mdt    Sejour's MDT
   *
   * @return mixed
   */
  function setModeTraitement($sejour, $mdt) {
    if (!$mdt || !isset($this->codes[$mdt])) {
      if (!$sejour->type) {
        $sejour->type = 'comp';
      }
    }
    else {
      $sejour->charge_id = $this->codes[$mdt]['id'];
      $sejour->type      = $this->codes[$mdt]['type'];
      $sejour->type_pec  = $this->codes[$mdt]['type_pec'];
    }

    return $sejour;
  }

  /**
   * @return array
   */
  function getCodesEntree() {
    $mode_entree           = new CModeEntreeSejour();
    $mode_entree->group_id = $this->group_id;
    $mode_entree->actif    = 1;

    /** @var CModeEntreeSejour[] $modes_entree */
    $modes_entree = $mode_entree->loadMatchingList();

    $codes_entrees = array();
    foreach ($modes_entree as $_mode) {
      $codes_entrees[$_mode->code] = array('id' => $_mode->_id, 'mode' => $_mode->mode);
    }

    return $codes_entrees;
  }

  /**
   * @param CSejour $sejour Sejour object
   * @param string  $mde    Sejour's MDE
   * @param string  $mds    Sejour's MDS
   *
   * @return CSejour
   */
  function setModesIO($sejour, $mde, $mds) {
    if ($mde && isset($this->codes_entree[$mde])) {
      $sejour->mode_entree_id = $this->codes_entree[$mde]['id'];
      $sejour->mode_entree    = $this->codes_entree[$mde]['mode'];
    }

    if ($mds && isset($this->codes_sortie[$mds])) {
      $sejour->mode_sortie_id = $this->codes_sortie[$mds]['id'];
      $sejour->mode_sortie    = $this->codes_sortie[$mds]['mode'];
    }

    return $sejour;
  }

  /**
   * @return array
   */
  function getCodesSortie() {
    $mode_sortie           = new CModeSortieSejour();
    $mode_sortie->group_id = $this->group_id;
    $mode_sortie->actif    = 1;

    /** @var CModeSortieSejour[] $modes_sortie */
    $modes_sortie = $mode_sortie->loadMatchingList();

    $codes_sorties = array();
    foreach ($modes_sortie as $_mode) {
      $codes_sorties[$_mode->code] = array('id' => $_mode->_id, 'mode' => $_mode->mode);
    }

    return $codes_sorties;
  }

  /**
   * @param bool $maj    Update retrieved sejours
   * @param bool $by_NDA Import by NDA
   *
   * @return void
   */
  function setOptions($maj, $by_NDA) {
    self::$options['by_NDA'] = $by_NDA;
    self::$options['maj']    = $maj;
  }
}
