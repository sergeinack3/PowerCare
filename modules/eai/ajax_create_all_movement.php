<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Ihe\CITI31DelegatedHandler;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CUserLog;

/**
 * Create mouvement
 */
CCanDo::checkAdmin();

$limit             = CView::get("limit_admit", "num");
$date_min          = CView::get('_date_min', array('dateTime', 'default' => CMbDT::dateTime("-7 day")));
$date_max          = CView::get('_date_max', array('dateTime', 'default' => CMbDT::dateTime("+1 day")));
$receiver_guid     = CView::get('receiver_guid', "str");
$list_ipp          = CView::get('list_ipp_all', "str");
$list_nda          = CView::get('list_nda_all', "str");
$cancel_movement   = CView::get('cancel_movement', "str");
$admit_in_progress = CView::get('admit_in_progress', "str");
$admit_closed      = CView::get('admit_closed', "str");
$continue          = CView::get('continue', "str");
$blank             = CView::get('blank', "str");
$tool              = CView::get("tools", "str");
$reset             = CView::get("reset", "bool");
$type_message      = CView::get("type_message", "str");
$session_stop      = CValue::getOrSession("limit_stop", 0);

if ($reset) {
  CView::setSession("limit_stop", 0);
  CView::checkin();
  CAppUI::stepAjax("Compteur en session réinitialisé");
  return;
}
else {
  CView::setSession("limit_stop", $session_stop + $limit);
  CView::checkin();
}

$group_id = CGroups::loadCurrent()->_id;

if ($admit_in_progress) {
  $where = array(
    "sejour.entree" => " BETWEEN '$date_min' AND '$date_max' ",
    "sejour.sortie_reelle" => "IS NULL",
    "sejour.group_id" => "= '$group_id'"
  );
}
elseif ($admit_closed) {
  $where = array(
    "sejour.sortie_reelle" => " BETWEEN '$date_min' AND '$date_max' ",
    "sejour.group_id" => "= '$group_id'"
  );
}
else {
  $where = array(
    "sejour.entree" => " BETWEEN '$date_min' AND '$date_max' ",
    "sejour.group_id" => "= '$group_id'"
  );
}

$where["sejour.annule"] = " = '0'";

$receiver =  (new CInteropActorFactory())->receiver()->makeHL7v2();
if ($receiver_guid) {
  $receiver = CMbObject::loadFromGuid($receiver_guid);
}

if (!$receiver_guid || !$receiver->_id) {
  CAppUI::stepAjax("CInteropReceiver.none", UI_MSG_ERROR);
}

// On crée des mouvements pour une liste de NDA
if ($list_nda) {
  $ndas = explode("|", $list_nda);

  $sejours = array();
  foreach ($ndas as $_nda) {
    $sejour = new CSejour();
    $sejour->loadFromNDA($_nda);
    if ($sejour->annule) {
      continue;
    }

    if ($sejour->_id) {
      $sejours[$sejour->_id] = $sejour;
    }
  }
}
else {
  $sejour = new CSejour();

  // On crée des mouvements pour une liste d'IPP
  if ($list_ipp) {
    $ipps = explode("|", $list_ipp);

    $where = array();

    $patient = new CPatient();
    $ljoin_patient["id_sante400"] = "id_sante400.object_id = patients.patient_id AND id_sante400.object_class = 'CPatient'";
    $where_patient['id_sante400.id400'] = CSQLDataSource::prepareIn($ipps);
    $where_patient['id_sante400.tag']   = " = '".CPatient::getTagIPP($group_id)."'";

    $ids_patient = $patient->loadIds($where_patient, null, null,  null, $ljoin_patient);

    // On enlève les doublons du tableau (par exemple si y'a appFine d'installé)
    array_unique($ids_patient);
    $where['sejour.patient_id'] = CSQLDataSource::prepareIn($ids_patient);
    $where["sejour.group_id"]   = "= '$group_id'";
    $where["sejour.annule"]     = " = '0'";

  }

  /** @var CSejour[] $sejours */
  $sejours = $sejour->loadList($where, "sejour.entree ASC", "$session_stop, $limit");
}

if (count($sejours) == 0) {
  CAppUI::stepAjax("Aucun séjour retrouvé", UI_MSG_ERROR);
}

$count_total = ($list_ipp || $list_nda) ? count($sejours) : $sejour->countList($where);
$sejours_envoyes = $session_stop + $limit;
$sejours_traites = ($sejours_envoyes > $count_total) ? $count_total : $sejours_envoyes;

if ($blank) {
  echo "<div class='small-warning'>Essai à blanc</div>";
  CAppUI::stepAjax("$count_total séjour(s) retrouvé(s)");
}
else {
  CAppUI::stepAjax($sejours_traites ." séjour(s) traité(s) sur ". $count_total);
}

CStoredObject::massLoadBackRefs($sejours, "user_logs", "user_log_id DESC");
$affectations = CStoredObject::massLoadBackRefs($sejours, "affectations", "sortie DESC");
CStoredObject::massLoadBackRefs($affectations, "user_logs", "user_log_id ASC");

foreach ($sejours as $_sejour) {
  $movements = array();

  if ($cancel_movement) {
    foreach ($_sejour->loadRefsMovements() as $_movement) {
      $_movement->cancel = 1;
      $_movement->store();
    }
  }

  $patient = $_sejour->loadRefPatient();
  $first_affectation = $_sejour->loadRefFirstAffectation();

  $_sejour->loadLogs();
  $_sejour->loadHistory();
  $_sejour->loadNDA($receiver->group_id);

  if (!$_sejour->_NDA) {
    // Génération du NDA dans le cas de la création, ce dernier n'était pas créé
    if ($msg = $_sejour->generateNDA()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);
    }
  }

  $patient->loadIPP($_sejour->group_id);
  if (!$patient->_IPP) {
    if ($msg = $patient->generateIPP()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);
    }
  }

  CAppUI::stepAjax($_sejour->_view);

  // Récupération du log de création
  $log_creation = end($_sejour->_ref_logs);

  if ($type_message) {
    $first_affectation->_id ? $movements[$log_creation->date][$type_message] =
      array("object" => $_sejour, "affectation" => $first_affectation) : $movements[$log_creation->date][$type_message] =
      array("object" => $_sejour, "affectation" => null);

    createAndSendMovement($movements, $receiver, $blank);
    continue;
  }

  // Envoi uniquement de la pré-admission
  if ($_sejour->_etat == "preadmission") {
    $first_affectation->_id ? $movements[$log_creation->date]["A05"] =
      array("object" => $_sejour, "affectation" => $first_affectation) : $movements[$log_creation->date]["A05"] =
      array("object" => $_sejour, "affectation" => null);

    createAndSendMovement($movements, $receiver, $blank);
    continue;
  }

  /** @var CSejour $sejour_init */
  $sejour_init = $_sejour->loadListByHistory($log_creation->_id);
  $sejour_init->loadRefPatient();
  $sejour_init->updateFormFields();

  // Récupération de l'admisson
  $code = in_array($sejour_init->type, CITI31DelegatedHandler::getOutpatient($_sejour->loadRefEtablissement())) ? "A04" : "A01";
  $first_affectation->_id ? $movements[$log_creation->date][$code] =
    array("object" => $sejour_init, "affectation" => $first_affectation) : $movements[$log_creation->date][$code] =
    array("object" => $sejour_init, "affectation" => null);

  // Si le type du séjour n'a pas été modifié, on a pas d'autres messages a envoyer
  if (!$_sejour->loadLastLogForField("type")->_id) {

    if ($_sejour->_etat == "cloture") {
      // Le A03 sera forcément le dernier message envoyé (d'ou le CMbDT::dateTime())
      $movements[CMbDT::dateTime()]["A03"] = array("object" => $_sejour, "affectation" => null);
    }

    createAndSendMovement($movements, $receiver, $blank);
    continue;
  }

  /** @var CUserLog $_log */
  foreach ($_sejour->_ref_logs as $_log) {
    $_log->getOldValues();
  }

  $affectation_medecine = null;

  $logs = array_reverse($_sejour->_ref_logs);
  foreach ($logs as $_log) {
    // Gestion type d'admission
    if (is_array($_log->_fields) && in_array("type", $_log->_fields)) {
      $code_type = null;
      switch ($_log->_old_values["type"]) {
        case "ambu":
          if ($_sejour->_history[$_log->_id]["type"] == "exte" || $_sejour->_history[$_log->_id]["type"] == "urg") {
            $code_type = "A07";
          }
          break;
        case "comp":
          if ($_sejour->_history[$_log->_id]["type"] == "exte" || $_sejour->_history[$_log->_id]["type"] == "urg") {
            $code_type = "A07";
          }
          break;
        case "bebe":
          if ($_sejour->_history[$_log->_id]["type"] == "comp" || $_sejour->_history[$_log->_id]["type"] == "ambu"
            || $_sejour->_history[$_log->_id]["type"] == "seances"
          ) {
            $code_type = "A06";
          }
          elseif ($_sejour->_history[$_log->_id]["type"] == "urg" || $_sejour->_history[$_log->_id]["type"] == "exte") {
            $code_type = "A07";
          }
          break;
        case "exte":
          if ($_sejour->_history[$_log->_id]["type"] == "comp" || $_sejour->_history[$_log->_id]["type"] == "ambu"
            || $_sejour->_history[$_log->_id]["type"] == "bebe" || $_sejour->_history[$_log->_id]["type"] == "seances"
          ) {
            $code_type = "A06";
          }
          break;
        case "urg":
          if ($_sejour->_history[$_log->_id]["type"] == "comp" || $_sejour->_history[$_log->_id]["type"] == "ambu"
            || $_sejour->_history[$_log->_id]["type"] == "bebe" || $_sejour->_history[$_log->_id]["type"] == "seances"
          ) {
            // on recherche la première affectation qui ni UHCD, ni URG
            $affectation_medecine = new CAffectation();
            $ljoin["service"] = "`service`.`service_id` = `affectation`.`service_id`";
            $ljoin["sejour"]  = "`affectation`.`sejour_id` = `sejour`.`sejour_id`";
            $where = array();
            $where["affectation.sejour_id"] = " = '$_sejour->_id'";
            $where["service.cancelled"]     = " = '0'";
            $where["service.uhcd"]          = " != '1'";
            $where["service.urgence"]       = " != '1'";

            $affectation_medecine->loadObject($where, "entree ASC", null, $ljoin);

            $code_type = "A06";
          }
          break;
        case "seances":
          if ($_sejour->_history[$_log->_id]["type"] == "exte" || $_sejour->_history[$_log->_id]["type"] == "urg") {
            $code_type = "A07";
          }
          break;

        default;
      }

      if ($code_type) {
        $movements[$_log->date][$code_type] = array(
          "object" => $_sejour,
          "affectation" => ($affectation_medecine && $affectation_medecine->_id) ? $affectation_medecine : null
        );
      }
    }

    // Gestion A16 et A25
    if (is_array($_log->_fields) && in_array("confirme", $_log->_fields)) {
      $old_confirme = $_log->_old_values["confirme"];
      $new_confirme = $_sejour->_history[$_log->_id]["confirme"];

      // Cas du A16
      if (!$old_confirme && $new_confirme) {
        $movements[$_log->date]["A16"] = array("object" => $_sejour, "affectation" => null);
      }

      // Cas du A25
      if ($old_confirme && !$new_confirme) {
        $movements[$_log->date]["A25"] = array("object" => $_sejour, "affectation" => null);
      }
    }
  }

  // Récupération des affectations
  /** @var CAffectation $_affectation */
  foreach ($_sejour->_ref_affectations as $_affectation) {
    if ($_affectation->_id == $first_affectation->_id) {
      continue;
    }

    if ($affectation_medecine && $affectation_medecine->_id == $_affectation->_id) {
      continue;
    }

    $log_creation_affectation = $_affectation->loadFirstLog()->date;

    $code = "A02";

    $service  = $_affectation->loadRefService();

    // Si affectation d'urgences on passe
    if ($service->urgence) {
      continue;
    }

    $_affectation->loadRefSejour();
    $_affectation->_ref_sejour->loadRefPatient();
    if ($service->externe) {
      $code = "A21";
    }

    $_affectation->loadOldObject();

    /* Affectation dans un service externe effectuée */
    if ($service->externe && !$_affectation->_old->effectue && $_affectation->effectue) {
      $code = "A22";
    }

    /* Affectation dans un service externe effectuée */
    if ($service->externe && $_affectation->_old->effectue && !$_affectation->effectue) {
      $code = "A53";
    }

    $movements[$log_creation_affectation][$code] = array("object" => $_affectation, "affectation" => $_affectation);
  }

  if ($_sejour->_etat == "cloture") {
    // Le A03 sera forcément le dernier message envoyé (d'ou le CMbDT::dateTime())
    $movements[CMbDT::dateTime()]["A03"] = array("object" => $_sejour, "affectation" => null);
  }

  createAndSendMovement($movements, $receiver, $blank);
}

if ($continue && count($sejours) > 0) {
  CAppUI::js("automatic$tool()");
}

function createAndSendMovement($movements, $receiver = null, $blank = null) {
  $iti31 = new CITI31DelegatedHandler();

  ksort($movements);

  echo "<ul>";

  foreach ($movements as $_movement_by_date) {
    foreach ($_movement_by_date as $code => $_information) {
      if ($receiver) {
        $receivers = array($receiver);
      }

      /** @var CSejour|CAffectation $object */
      $object      = $_information["object"];
      $affectation = $_information["affectation"];

      /** @var CSejour $sejour */
      $sejour = $object->_class == "CSejour" ? $object : $object->loadRefSejour();

      foreach ($receivers as $_receiver) {
        /** @var CReceiverHL7v2 $_receiver */
        $_receiver->loadConfigValues();

        $iti_hl7_version = $_receiver->_configs["ITI31_HL7_version"];

        $i18n_code = null;
        if ($iti_hl7_version && preg_match("/([A-Z]{3})_(.*)/", $iti_hl7_version, $matches)) {
          $i18n_code = $matches[1];
        }

        if ($i18n_code) {
          $i18n_code = "_$i18n_code";
        }

        $class           = "CHL7v2EventADT".$code.$i18n_code;
        $event = new $class;

        $object->_receiver = $_receiver;

        $class_supported = "CHL7EventADT".$code.$i18n_code;
        if (!$_receiver->isMessageSupported($class_supported)) {
          continue;
        }

        if ($blank) {
          echo "<li> Échange " . $code . " prévu pour le destinataire " . $_receiver->nom . "</li>";
          continue;
        }

        if (!$iti31->createMovement($code, $sejour, $affectation)) {
          echo "<li> <div class=\"error\"></div> Impossible de créer le mouvement ".$code ." pour le destinataire ". $_receiver->nom ."</li>";
        }

        try {
          $_receiver->sendEvent($event, $object);
        }
        catch (Exception $e) {
          CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
        }

        echo "<li> Échange ".$code ." généré pour le destinataire ". $_receiver->nom ."</li>";
      }
    }
  }

  echo "</ul><br/>";
}
