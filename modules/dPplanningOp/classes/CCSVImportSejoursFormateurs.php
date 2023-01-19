<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Description
 */
class CCSVImportSejoursFormateurs extends CMbCSVObjectImport {
  public $types = array();
  public $group_id;

  /**
   * CCSVImportSejoursFormateurs constructor.
   *
   * @param string $file_path Path to the import file
   */
  function __construct($file_path) {
    parent::__construct($file_path, 0, 0, CCSVFile::PROFILE_OPENOFFICE);

    foreach (CSejour::$types as $type) {
      $this->types[CAppUI::tr("CSejour.type.$type")] = $type;
    }
    $this->types["Hospitalisation complète"] = "comp";
  }

  /**
   * @inheritdoc
   */
  function import() {
    $this->openFile();
    $this->setColumnNames();

    $this->group_id = CGroups::loadCurrent()->_id;

    while ($line = $this->readAndSanitizeLine()) {
      $this->current_line++;

      if (!$line['NOM']) {
        CAppUI::setMsg("Ligne $this->current_line : Le patient n'a pas de nom.", UI_MSG_WARNING);
        $this->nb_errors++;
        continue;
      }

      $mediuser = $this->getMediusers($line['PRATICIEN']);
      if (!$mediuser) {
        CAppUI::setMsg("Ligne $this->current_line : Praticien non retrouvé.", UI_MSG_WARNING);
        $this->nb_errors++;
        continue;
      }

      $patient = $this->createPatient($line['NOM'], $line['PRENOM'], $line['DN'], $line['SEXE']);
      if ($patient === null) {
        $this->nb_errors++;
        continue;
      }

      if ($line["GROSSESSE EN COURS"] == "OUI") {
        $grossesse = $this->createGrossesse($patient->_id, $line["DATE D'ENTREE PREVUE"], $line["DDR"]);
        if ($grossesse === null) {
          $this->nb_errors++;
          continue;
        }
      }

      $protocole = null;
      if ($line['PROTOCOLE DHE (existant)']) {
        $protocole = $this->getProtocole($mediuser->_id, $line['PROTOCOLE DHE (existant)']);
        if ($protocole === null) {
          $this->nb_errors++;
          continue;
        }
      }

      $service = $this->getService($line['SERVICE']);
      $chambre = null;
      $lit = null;
      if ($service && $service->_id) {
        $chambre = $this->getChambre($service->_id, $line['CHAMBRE']);
        if ($chambre && $chambre->_id) {
          $lit = $this->getLit($chambre->_id, $line['LIT']);
        }
      }

      $sejour = $this->createSejour(
        $patient, $mediuser, $line["DATE D'ENTREE PREVUE"], $line["DATE D'ENTREE REELLE"], $line["DATE DE SORTIE PREVUE"],
        $line["HEURE D'ENTREE PREVUE"], $line["HEURE D'ENTREE REELLE"], $line["TYPE D'HOSPITALISATION"], $line["TYPE M-C-O"],
        $line["MOTIF"], $service, $protocole
      );

      if ($sejour === null) {
        $this->nb_errors++;
        continue;
      }

      if (!$service || !$service->_id) {
        $group = CGroups::loadCurrent();
        $group->loadRefsServices();

        if ($group->_ref_services) {
          $service = reset($group->_ref_services);
        }
        else {
          CAppUI::setMsg('CGroups-back-services.empty', UI_MSG_WARNING);
          $this->nb_errors++;
          continue;
        }
      }

      if (!$chambre || !$chambre->_id) {
        $service->loadRefsChambres();
        $chambre = reset($service->_ref_chambres);
      }

      if (!$lit || !$lit->_id) {
        $chambre->loadRefsLits();
        $lit = reset($chambre->_ref_lits);
      }

      $this->createAffectation($sejour, $lit, $service);

      $cote = $line['COTE'];
      if ($protocole && $protocole->cote) {
        $cote = $protocole->cote;
      }

      $operation = null;
      if ($cote) {
        $operation = $this->createOperation(
          $sejour, CMbString::lower($cote), $line["DUREE INTERVENTION"], $line["DATE INTERVENTION"],
          $line["HEURE INTERVENTION"], $protocole
        );
      }
      else {
        CAppUI::setMsg('CCSVImportSejoursFormateurs-msg-Cote is null', UI_MSG_WARNING);
      }

      if ($line['PROTOCOLE PRESCRIPTION']) {
        $protocole_presc = new CPrescription();
        $protocole_presc->libelle = $line['PROTOCOLE PRESCRIPTION'];
        $protocole_presc->object_class = 'CSejour';
        $protocole_presc->type = 'sejour';
        $protocole_presc->loadMatchingObjectEsc();

        if ($protocole_presc && $protocole_presc->_id) {
          $this->createPrescription($sejour, $line['PROTOCOLE PRESCRIPTION'], $protocole_presc, $operation);
        }

      }
    }

    return $this->nb_errors;
  }

  /**
   * @param string $username User's username
   *
   * @return CMediusers
   */
  function getMediusers($username) {
    $cache = new Cache('CCSVImportSejoursFormateurs.getMediusers', $username, Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    $user                = new CUser();
    $user->user_username = $username;
    $user->loadMatchingObjectEsc();

    return $cache->put($user->loadRefMediuser());
  }

  /**
   * @param string $nom       Patient last name
   * @param string $prenom    Patient first name
   * @param string $naissance Patient birth date
   * @param string $sexe      Patient sexe
   *
   * @return CPatient
   */
  function createPatient($nom, $prenom, $naissance, $sexe) {
    $patient = new CPatient();
    $patient->nom = trim($nom);
    $patient->prenom = trim($prenom);
    $patient->naissance = trim($naissance);
    $patient->sexe = CMbString::lower(trim($sexe));

    $patient->loadMatchingPatient();

    if (!$patient->_id) {
      $patient->civilite = "guess";
      if ($msg = $patient->store()) {
        CAppUI::setMsg("Ligne $this->current_line : $msg", UI_MSG_WARNING);
        return null;
      }
      CAppUI::setMsg("CPatient-msg-create", UI_MSG_OK);
    }
    else {
      CAppUI::setMsg("CPatient-msg-Object found", UI_MSG_OK);
    }

    return $patient;
  }

  /**
   * @param int    $patient_id            Patient id
   * @param string $terme_prevu           Date of birth planned
   * @param string $date_dernieres_regles Date
   *
   * @return CGrossesse|null
   */
  function createGrossesse($patient_id, $terme_prevu, $date_dernieres_regles) {
    $grossesse = new CGrossesse();
    $grossesse->parturiente_id = $patient_id;
    $grossesse->terme_prevu = $terme_prevu;
    $grossesse->date_dernieres_regles = $date_dernieres_regles;
    $grossesse->group_id = $this->group_id;

    $grossesse->loadMatchingObjectEsc();
    if ($grossesse || $grossesse->_id) {
      CAppUI::setMsg("CGrossesse-msg-Object found", UI_MSG_OK);
      return $grossesse;
    }

    if ($msg = $grossesse->store()) {
      CAppUI::setMsg("Ligne $this->current_line : $msg", UI_MSG_WARNING);
      return null;
    }

    return $grossesse;
  }

  /**
   * @param int    $user_id Praticien ID
   * @param string $libelle Libelle
   *
   * @return CProtocole
   */
  function getProtocole($user_id, $libelle) {
    $cache = new Cache('CCSVImportSejoursFormateurs.getProtocole', [$user_id, $libelle], Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    $protocole = new CProtocole();
    $protocole->libelle = $libelle;
    $protocole->chir_id = $user_id;

    $protocole->loadMatchingObjectEsc();

    if (!$protocole->_id) {
      $protocole->chir_id = null;
      $protocole->loadMatchingObjectEsc();

      if (!$protocole->_id) {
        CAppUI::setMsg("Ligne $this->current_line : Protocole non retrouvé.", UI_MSG_WARNING);
        return null;
      }
    }

    return $cache->put($protocole);
  }

  /**
   * @param string $nom Service name
   *
   * @return CService
   */
  function getService($nom) {
    $cache = new Cache('CCSVImportSejoursFormateurs.getService', $nom, Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    if ($nom) {
      $service = new CService();
      $service->nom = $nom;
      $service->loadMatchingObjectEsc();

      if ($service->_id) {
        return $cache->put($service);
      }
    }

    if (CAppUI::conf("dPplanningOp CSejour service_id_notNull") == 1) {
      $group = CGroups::loadCurrent();
      $services = $group->loadRefsServices();
      return $cache->put(reset($services));
    }

    return $cache->put(null);
  }

  /**
   * @param int    $service_id Service ID
   * @param string $nom        CChambre name
   *
   * @return mixed
   */
  function getChambre($service_id, $nom) {
    $cache = new Cache('CCSVImportSejoursFormateurs.getChambre', [$service_id, $nom], Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    if (!$nom) {
      return $cache->put(null);
    }

    $chambre = new CChambre();
    $chambre->nom = $nom;
    $chambre->service_id = $service_id;
    $chambre->loadMatchingObjectEsc();

    return $cache->put($chambre);
  }

  /**
   * @param int    $chambre_id CChambre Id
   * @param string $nom        CLit name
   *
   * @return mixed
   */
  function getLit($chambre_id, $nom) {
    $cache = new Cache('CCSVImportSejoursFormateurs.getLit', [$chambre_id, $nom], Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    if (!$nom) {
      return $cache->put(null);
    }

    $lit = new CLit();
    $lit->nom = $nom;
    $lit->chambre_id = $chambre_id;
    $lit->loadMatchingObjectEsc();

    return $cache->put($lit);
  }

  /**
   * @param CPatient   $patient             Patient
   * @param CMediusers $mediuser            Praticien
   * @param string     $date_entree_prevue  Date of entrance planned
   * @param string     $date_entree_reelle  Real date of entrance
   * @param string     $date_sortie_prevue  Exit date planned
   * @param string     $heure_entree_prevue Entrance time planned
   * @param string     $heure_entree_reelle Entrance real time
   * @param string     $type_hospi          Type
   * @param string     $type_pec            PEC
   * @param string     $libelle             Libelle
   * @param CService   $service             Service for the CSejour
   * @param CProtocole $protocole           Protocole to use
   *
   * @return CSejour
   */
  function createSejour(
      $patient, $mediuser, $date_entree_prevue, $date_entree_reelle, $date_sortie_prevue, $heure_entree_prevue, $heure_entree_reelle,
      $type_hospi, $type_pec, $libelle, $service = null, $protocole = null
  ) {
    $sejour = new CSejour();
    $sejour->patient_id = $patient->_id;
    $sejour->praticien_id = $mediuser->_id;
    $sejour->group_id = $this->group_id;

    $sejour->entree_prevue = ($heure_entree_prevue) ? CMbDT::dateTime("$date_entree_prevue $heure_entree_prevue") :
      CMbDT::dateTime($date_entree_prevue);
    $sejour->entree_reelle = ($heure_entree_reelle) ? CMbDT::dateTime("$date_entree_reelle $heure_entree_reelle") :
      CMbDT::dateTime($date_entree_reelle);
    $sejour->sortie_prevue = CMbDT::dateTime("$date_sortie_prevue 19:00:00");

    $sejour->repair();
    $sejour->loadMatchingObjectEsc();

    if ($sejour && $sejour->_id) {
      CAppUI::setMsg("CSejour-msg-Object found", UI_MSG_OK);
      return $sejour;
    }

    $sejour->type = (isset($this->types[$type_hospi])) ? $this->types[$type_hospi] : "comp";
    $sejour->type_pec = $type_pec;
    $sejour->libelle = $libelle;
    $sejour->service_id = $service->_id;

    if ($protocole && $protocole->_id) {
      $sejour = $this->addProtocoleToSejour($sejour, $protocole);
    }

    $sejour->updatePlainFields();

    if ($msg = $sejour->store()) {
      CAppUI::setMsg("Ligne $this->current_line : $msg", UI_MSG_WARNING);
      return null;
    }
    CAppUI::setMsg("CSejour-msg-create", UI_MSG_OK);

    return $sejour;
  }

  /**
   * @param CSejour    $sejour    Sejour
   * @param CProtocole $protocole Protocole
   *
   * @return CSejour
   */
  function addProtocoleToSejour($sejour, $protocole) {
    if ($protocole->duree_heure_hospi !== null) {
      $sejour->_duree_prevue = $protocole->duree_heure_hospi;
    }

    if ($protocole->type_pec !== null) {
      $sejour->type_pec = $protocole->type_pec;
    }

    if ($protocole->facturable !== null) {
      $sejour->facturable = $protocole->facturable;
    }

    if ($protocole->rques_sejour !== null) {
      $sejour->rques = $protocole->rques_sejour;
    }

    if ($protocole->libelle_sejour !== null) {
      $sejour->libelle = $protocole->libelle_sejour;
    }
    if ($protocole->DP !== null) {
      $sejour->DP = $protocole->DP;
    }

    return $sejour;
  }

  /**
   * @param CSejour    $sejour             CSejour of the operation
   * @param string     $cote               Cote for the operation
   * @param int        $duree_intervention Duration of the operation
   * @param string     $date_intervention  Date of the operation
   * @param string     $heure_intervention Time of the operation
   * @param CProtocole $protocole          Protocole to use
   *
   * @return COperation
   */
  function createOperation($sejour, $cote, $duree_intervention, $date_intervention, $heure_intervention, $protocole) {
    if (!$sejour || !$sejour->_id) {
      return null;
    }

    $operation = new COperation();
    $operation->date = CMbDT::date($date_intervention);
    $operation->sejour_id = $sejour->_id;
    $operation->chir_id = $sejour->praticien_id;

    $operation->loadMatchingObjectEsc();

    if ($operation && $operation->_id) {
      CAppUI::setMsg("COperation-msg-Object found", UI_MSG_OK);
      return $operation;
    }

    $operation->time_operation = CMbDT::time($heure_intervention);
    $operation->cote = $cote;
    $operation->temp_operation = $duree_intervention;

    if ($protocole && $protocole->_id) {
      $operation->codes_ccam = $protocole->codes_ccam;
      $operation->libelle = $protocole->libelle;
      $operation->presence_preop = $protocole->presence_preop;
      $operation->presence_postop = $protocole->presence_postop;
      $operation->duree_bio_nettoyage = $protocole->duree_bio_nettoyage;
      $operation->cote = ($operation->cote) ?: $protocole->cote;
      $operation->type_anesth = $protocole->type_anesth;
      $operation->_time_op = $protocole->_time_op;
      $operation->materiel = $protocole->materiel;
      $operation->exam_extempo = $protocole->exam_extempo;
      $operation->duree_uscpo = $protocole->duree_uscpo;
      $operation->duree_preop = $protocole->duree_preop;
      $operation->rques = $protocole->rques_operation;
    }

    if ($msg = $operation->store()) {
      CAppUI::setMsg("Line $this->current_line : $msg", UI_MSG_WARNING);
      return null;
    }

    CAppUI::setMsg("COperation-msg-create", UI_MSG_OK);
    return $operation;
  }

  /**
   * @param CSejour       $sejour    CSejour of the prescription
   * @param string        $libelle   Libelle of the prescription
   * @param CPrescription $protocole Protocole to use
   * @param COperation    $operation COperation
   *
   * @return void
   */
  function createPrescription($sejour, $libelle, $protocole, $operation) {
    if (!$sejour || !$sejour->_id) {
      return;
    }

    $prescription = new CPrescription();
    $prescription->object_class = 'CSejour';
    $prescription->object_id = $sejour->_id;
    $prescription->type = 'sejour';
    $prescription->loadMatchingObjectEsc();

    if (!$prescription->_id) {
      $prescription->libelle = $libelle;
      if ($msg = $prescription->store()) {
        CAppUI::setMsg("Ligne $this->current_line : $msg", UI_MSG_WARNING);
        return;
      }

      CAppUI::setMsg("CPrescription-msg-create", UI_MSG_OK);
    }
    else {
      CAppUI::setMsg("CPrescription-msg-Object found", UI_MSG_OK);
    }

    $this->applyProtocoleToPrescription($prescription, $protocole, $operation);

  }

  /**
   * @param CPrescription $prescription Prescription
   * @param CPrescription $protocole    Protocole
   * @param COperation    $operation    Operation
   *
   * @return void
   */
  function applyProtocoleToPrescription($prescription, $protocole, $operation) {
    if (!$protocole || !$protocole->_id || !$operation || !$operation->_id) {
      CAppUI::setMsg("Ligne $this->current_line : Protocole ou intervention non existant", UI_MSG_WARNING);
      return;
    }

    $operation->loadRefPraticien();
    $prescription->_active = true;
    $prescription->applyPackOrProtocole(
      "prot-$protocole->_id", $operation->_ref_praticien->_id, CMbDT::date(), null, $operation->_id, null
    );

    $prescription->loadRefsLinesMed();
    foreach ($prescription->_ref_prescription_lines as $_prescription_line) {
      $_prescription_line->signee = 1;
      $_prescription_line->store();
    }

    $prescription->loadRefsPrescriptionLineMixes();
    foreach ($prescription->_ref_prescription_line_mixes as $_prescription_line_mix) {
      $_prescription_line_mix->signature_prat = 1;
      $_prescription_line_mix->store();
    }

    $prescription->loadRefsLinesElement();
    foreach ($prescription->_ref_prescription_lines_element as $_prescription_line_elem) {
      $_prescription_line_elem->signee = 1;
      $_prescription_line_elem->store();
    }
  }

  /**
   * @param CSejour  $sejour  CSejour object
   * @param CLit     $lit     CLit of the affectation
   * @param CService $service CService of the affectation
   *
   * @return void
   */
  function createAffectation($sejour, $lit, $service) {
    $affectation = new CAffectation();
    $affectation->sejour_id = $sejour->_id;
    $affectation->service_id = $service->_id;
    $affectation->lit_id = $lit->_id;
    $affectation->entree = $sejour->entree_prevue;
    $affectation->sortie = $sejour->sortie_prevue;

    $affectation->loadMatchingObjectEsc();
    if ($affectation && $affectation->_id) {
      CAppUI::setMsg("CAffectation-msg-Object found", UI_MSG_OK);
      return;
    }

    if ($msg = $affectation->store()) {
      CAppUI::setMsg("Ligne $this->current_line : $msg", UI_MSG_WARNING);
      return;
    }

    CAppUI::setMsg('CAffectation-msg-create', UI_MSG_OK);
  }

  /**
   * @inheritdoc
   */
  function sanitizeLine($line) {
    if (!$line) {
      return null;
    }
    $line                          = parent::sanitizeLine($line);
    $line['DN']                    = $this->formatDate($line['DN']);
    $line['DATE INTERVENTION']     = $this->formatDate($line['DATE INTERVENTION']);
    $line['DDR']                   = $this->formatDate($line['DDR']);
    $line["DATE D'ENTREE PREVUE"]  = $this->formatDate($line["DATE D'ENTREE PREVUE"]);
    $line["DATE D'ENTREE REELLE"]  = $this->formatDate($line["DATE D'ENTREE REELLE"]);
    $line["DATE DE SORTIE PREVUE"] = $this->formatDate($line["DATE DE SORTIE PREVUE"]);

    foreach ($line as &$_line) {
      $_line = utf8_decode($_line);
    }

    return $line;
  }

  /**
   * @param string $date Date to format
   *
   * @return string
   */
  function formatDate($date) {
    if (!$date) {
      return CMbDT::date();
    }

    return preg_replace('/(\d{2})\/(\d{2})\/(\d{4})/', '\\3-\\2-\\1', $date);
  }
}
