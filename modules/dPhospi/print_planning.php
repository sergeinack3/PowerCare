<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CNote;

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

CCanDo::checkRead();

$group = CGroups::loadCurrent();

$filter = new CSejour();

$spec_dateMin = array(
  "dateTime",
  "default" => CMbDT::date() . " 06:00:00"
);
$spec_dateMax = array(
  "dateTime",
  "default" => CMbDT::date() . " 21:00:00"
);

$filter->_date_min            = CView::get("_date_min", $spec_dateMin);
$filter->_date_max            = CView::get("_date_max", $spec_dateMax);
$filter->_horodatage          = CView::get("_horodatage", "enum list|entree_prevue|entree_reelle|sortie_prevue|sortie_reelle default|entree_prevue");
$filter->_service             = CView::get("_service", "str default|0");
$filter->_filter_type         = CView::get("_filter_type", "str default|0");
$filter->praticien_id         = CView::get("praticien_id", "str default|0");
$filter->_specialite          = CView::get("_specialite", "str default|0");
$filter->convalescence        = CView::get("convalescence", "enum list|o|n|0 default|0");
$filter->consult_accomp       = CView::get("consult_accomp", "enum list|oui|non|0 default|0");
$filter->_admission           = CView::get("_admission", "enum list|heure_admi|heure_inter|nom default|heure_admi");
$filter->_ccam_libelle        = CView::get("_ccam_libelle", "bool default|1");
$filter->_coordonnees         = CView::get("_coordonnees", "bool default|0");
$filter->_notes               = CView::get("_notes", "bool default|0");
$filter->_nb_days             = CView::get("_nb_days", "num default|0");
$filter->_by_date             = CView::get("_by_date", "bool default|0");
$filter->_bmr_filter          = CValue::getOrSession("_bmr_filter");
$filter->_bhre_filter         = CValue::getOrSession("_bhre_filter");
$filter->_bhre_contact_filter = CValue::getOrSession("_bhre_contact_filter");
$filter->_export_csv          = CView::get("_export_csv", "bool");

CView::checkin();

if ($filter->_nb_days) {
  $filter->_date_max = CMbDT::date("+$filter->_nb_days days", CMbDT::date($filter->_date_min)) . " 21:00:00";
}

$filter->_service     = explode(",", $filter->_service);
$filter->praticien_id = explode(",", $filter->praticien_id);

if ($filter->_filter_type) {
  $filter->_filter_type = explode(",", $filter->_filter_type);
}

CMbArray::removeValue(0, $filter->praticien_id);
CMbArray::removeValue(0, $filter->_service);

$total = 0;

$sejours = new CSejour();

$where = array(
  "sejour.$filter->_horodatage" => "BETWEEN '$filter->_date_min' AND '$filter->_date_max'",
  "sejour.group_id"             => "= '$group->_id'",
  "sejour.annule"               => "= '0'"
);

$ljoin = array(
  "patients" => "patients.patient_id = sejour.patient_id",
  "users"    => "users.user_id = sejour.praticien_id"
);

// Clause de filtre par spécialité / chir
if ($filter->_specialite || $filter->praticien_id) {
  if (count($filter->praticien_id)) {
    $where["sejour.praticien_id"] = CSQLDataSource::prepareIn($filter->praticien_id);
  }
  else {
    $speChirs = new CMediusers();
    $speChirs = $speChirs->loadList(array("function_id" => "= '$filter->_specialite'"));

    $where["sejour.praticien_id"] = CSQLDataSource::prepareIn(array_keys($speChirs));
  }
}

if ($filter->_filter_type) {
  $where["sejour.type"] = CSQLDataSource::prepareIn($filter->_filter_type);
}
else {
  // On supprime les sejours d'urgence
  $where["sejour.type"] = CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence());
}

if ($filter->convalescence == "o") {
  $where[] = "sejour.convalescence IS NOT NULL AND sejour.convalescence != ''";
}

if ($filter->convalescence == "n") {
  $where[] = "sejour.convalescence IS NULL OR sejour.convalescence = ''";
}

if ($filter->consult_accomp) {
  $where["sejour.consult_accomp"] = "= '$filter->consult_accomp'";
}

if ($filter->_bmr_filter || $filter->_bhre_filter || $filter->_bhre_contact_filter) {
  $ljoin["bmr_bhre"] = "bmr_bhre.patient_id = patients.patient_id";

  $bmr_bhre = array();

  if ($filter->_bmr_filter) {
    $bmr_bhre[] = "bmr_bhre.bmr = '1'";
  }

  if ($filter->_bhre_filter) {
    $bmr_bhre[] = "bmr_bhre.bhre = '1'";
  }

  if ($filter->_bhre_contact_filter) {
    $bmr_bhre[] = "bmr_bhre.bhre_contact = '1'";
  }

  $where[] = implode(" OR ", $bmr_bhre);
}

$order = array();
$order[] = "DATE(sejour.$filter->_horodatage)";

if ($filter->_admission == "heure_admi") {
  $order[] = "TIME(sejour.$filter->_horodatage)";
}
elseif ($filter->_admission == "heure_inter") {
  $ljoin["operations"] = "operations.sejour_id = sejour.sejour_id";
  $order[]             = "operations.date ASC, operations.time_operation ASC";
}
else {
  $order[] = "patients.nom";
  $order[] = "patients.prenom";
  $order[] = "DATE(sejour.$filter->_horodatage)";
}

if (!$filter->_by_date) {
  $order = array_merge($order, ["users.user_last_name", "users.user_first_name"]);
}

$sejours = $sejours->loadList($where, $order, null, 'sejour.sejour_id', $ljoin, null, null, false);
$affectations = CStoredObject::massLoadBackRefs($sejours, 'affectations', 'sortie DESC');
CAffectation::massUpdateView($affectations);
$listDays  = array();
$listPrats = array();

// Liste des services
$service            = new CService();
$where              = array();
$where["group_id"]  = "= '$group->_id'";
$order              = "nom";
$services           = $service->loadListWithPerms(PERM_READ, $where, $order);

// ATTENTION ne pas supprimer le "&" car pose des problemes
foreach ($sejours as $key => &$sejour) {
  /** @var CSejour $sejour */
  $sejour->loadRefsAffectations();

  $sejour->_ref_first_affectation->loadRefLit()->loadRefChambre();
  $affectation = $sejour->_ref_first_affectation;
  $affectation->_ref_lit->loadCompleteView();

  $service_id = $affectation->service_id ? $affectation->service_id : $affectation->_ref_lit->_ref_chambre->service_id;

  if (!$service_id) {
    $service_id = $sejour->service_id;
  }

  if (count($filter->_service) && !in_array($service_id, $filter->_service)) {
    unset($sejours[$key]);
  }
  elseif (!$filter->_service && $affectation->_id && !in_array($service_id, array_keys($services))) {
    unset($sejours[$key]);
  }
}

$prestation_id = CAppUI::pref("prestation_id_hospi");

if (CAppUI::conf("dPhospi prestations systeme_prestations", $group) === "standard" || $prestation_id === "all") {
  $prestation_id = "";
}

$prestation = new CPrestationJournaliere();
$prestation->load($prestation_id);

CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($sejours, "praticien_id");

if ($prestation_id) {
  CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id);
}

foreach ($sejours as $key => &$sejour) {
    $sejour->loadRefsOperations();
    $sejour->loadRefPatient();
    $sejour->loadRefsAffectations();

    $sejour->loadRefPraticien();

    foreach ($sejour->_ref_operations as $operation) {
        $operation->loadRefsFwd();
    }

    if ($filter->_notes) {
        $sejour->loadRefsNotes();
        foreach ($sejour->_ref_notes as $_id => $_note) {
            if (!$_note->public) {
                unset($sejour->_ref_notes[$_id]);
            }
        }
    }

    $curr_date = CMbDT::date(null, $sejour->{$filter->_horodatage});
    $curr_prat = $sejour->praticien_id;
    $listDays[$curr_date][$curr_prat]["praticien"] =& $sejour->_ref_praticien;
    $listDays[$curr_date][$curr_prat]["sejours"][] =& $sejour;
}
// Export CSV des patients
if ($filter->_export_csv) {
  $format_print     = CAppUI::gconf("dPhospi print_planning modele_used");
    if ($format_print === "standard") {
        $header = [
            // Sejour
            "prat_id"      => CAppUI::tr("CSejour-praticien_id"),
            "horodatage"   => CAppUI::tr("CSejour-$filter->_horodatage"),
            "type"         => CAppUI::tr("CSejour-type"),
            "duree"        => CAppUI::tr("CSejour-_duree"),
            "conval"       => CAppUI::tr("CSejour-convalescence"),
            "chambre"      => CAppUI::tr("CChambre"),
            "sej_rques"    => CAppUI::tr("CSejour-rques-court"),
            "sej_notes"    => CAppUI::tr("common-Note|pl"),
            // Intervention
            "oper_date"    => CAppUI::tr("COperation-date"),
            "oper_libelle" => CAppUI::tr("COperation-libelle"),
            "oper_cote"    => CAppUI::tr("COperation-cote"),
            "oper_bilan"   => CAppUI::tr("CProtocole-examen-court"),
            "oper_rques"   => CAppUI::tr("COperation-rques-court"),
            // Patient
            "patient"      => CAppUI::tr("CPatient"),
            "naissance"    => CAppUI::tr("CPatient-naissance"),
            "coord"        => CAppUI::tr("CPatient-adresse"),
            "pat_tel"      => CAppUI::tr("CPatient-tel"),
            "pat_rques"    => CAppUI::tr("CPatient-rques"),
        ];
    } else {
        $header = [
            "horodatage"   => CAppUI::tr("CSejour-$filter->_horodatage"),
            "patient"      => CAppUI::tr("CPatient"),
            "naissance"    => CAppUI::tr("CPatient-naissance"),
            "sexe"         => CAppUI::tr("CPatient-sexe"),
            "coord"        => CAppUI::tr("CPatient-adresse"),
            "pat_tel"      => CAppUI::tr("CPatient-tel"),
            "oper_libelle" => CAppUI::tr("COperation-libelle"),
            "oper_prat_id" => CAppUI::tr("COperation-chir_id"),
            "aff_rques"    => CAppUI::tr("CAffectation-rques"),
            "chambre"      => CAppUI::tr("CChambre"),
            "type"         => CAppUI::tr("CSejour-type"),
            "duree"        => CAppUI::tr("CSejour-_duree"),
            "sej_rques"    => CAppUI::tr("CSejour-rques-court"),
            "sej_notes"    => CAppUI::tr("common-Note|pl"),

        ];
    }
    // On vérifie les paramètres d'affichage
    if (!$filter->_notes) {
        CMbArray::extract($header, "sej_notes");
    }
    if (!$filter->_coordonnees) {
        CMbArray::extract($header, "coord");
        CMbArray::extract($header, "pat_tel");
    }
  $csv = new CCSVFile();

  $csv->writeLine($header);

  /** @var CSejour $_sejour */
  foreach ($sejours as $_sejour) {
    $_sejour->updateFormFields();
    $line = [];
    $date_ref = $filter->_horodatage;
    $_patient = $_sejour->_ref_patient;
    $last_note = is_array($_sejour->_ref_notes) ? end($_sejour->_ref_notes) : null;
      if (count($_sejour->_ref_operations)) {
          // On crée une ligne pour chaque intervention
          foreach ($_sejour->_ref_operations as $operation) {
              $operation->loadRefPraticien();
              if ($format_print === "standard") {
                  $line = [
                      // Sejour
                      "prat_id"      => $_sejour->_ref_praticien,
                      "horodatage"   => $_sejour->$date_ref,
                      "type"         => $_sejour->type,
                      "duree"        => $_sejour->_duree,
                      "conval"       => $_sejour->convalescence,
                      "chambre"      => $_sejour->_ref_last_affectation,
                      "sej_rques"    => $_sejour->rques,
                      "sej_notes"    => $last_note instanceof CNote ? $last_note->text : null,
                      // Intervention
                      "oper_date"    => $operation->date,
                      "oper_libelle" => $operation->libelle,
                      "oper_cote"    => $operation->cote,
                      "oper_bilan"   => $operation->examen,
                      "oper_rques"   => $operation->rques,
                      // Patient
                      "patient"      => $_patient->_view,
                      "naissance"    => $_patient->naissance . "($_patient->_age)",
                      "coord"        => $_patient->adresse . " " . $_patient->cp . " " . $_patient->ville,
                      "pat_tel"      => $_patient->tel,
                      "pat_rques"    => $_patient->rques,

                  ];
              } else {
                  $line = [
                      "horodatage"   => $_sejour->$date_ref,
                      "patient"      => $_patient->_view,
                      "naissance"    => $_patient->naissance . "($_patient->_age)",
                      "sexe"         => $_patient->sexe,
                      "coord"        => $_patient->adresse . " " . $_patient->cp . " " . $_patient->ville,
                      "pat_tel"      => $_patient->tel,
                      "oper_libelle" => $operation->libelle,
                      "oper_prat_id" => $operation->_ref_praticien,
                      "aff_rques"    => $_sejour->_ref_last_affectation->rques,
                      "chambre"      => $_sejour->_ref_last_affectation,
                      "type"         => $_sejour->type,
                      "duree"        => $_sejour->_duree,
                      "sej_rques"    => $_sejour->rques,
                      "sej_notes"    => $last_note instanceof CNote ? $last_note->text : null,
                  ];
              }
              if (!$filter->_notes) {
                  CMbArray::extract($line, "sej_notes");
              }
              if (!$filter->_coordonnees) {
                  CMbArray::extract($line, "coord");
                  CMbArray::extract($line, "pat_tel");
              }
              $csv->writeLine($line);
          }
      } else {
          if ($format_print === "standard") {
              $line = [
                  // Sejour
                  "prat_id"      => $_sejour->_ref_praticien,
                  "horodatage"   => $_sejour->$date_ref,
                  "type"         => $_sejour->type,
                  "duree"        => $_sejour->_duree,
                  "conval"       => $_sejour->convalescence,
                  "chambre"      => $_sejour->_ref_last_affectation,
                  "sej_rques"    => $_sejour->rques,
                  "sej_notes"    => $last_note instanceof CNote ? $last_note->text : null,
                  // Intervention
                  "oper_date"    => null,
                  "oper_libelle" => null,
                  "oper_cote"    => null,
                  "oper_bilan"   => null,
                  "oper_rques"   => null,
                  // Patient
                  "patient"      => $_patient->_view,
                  "naissance"    => $_patient->naissance . "($_patient->_age)",
                  "coord"        => $_patient->adresse . " " . $_patient->cp . " " . $_patient->ville,
                  "pat_tel"      => $_patient->tel,
                  "pat_rques"    => $_patient->rques,

              ];
          } else {
              $line = [
                  "horodatage"   => $_sejour->$date_ref,
                  "patient"      => $_patient->_view,
                  "naissance"    => $_patient->naissance . "($_patient->_age)",
                  "sexe"         => $_patient->sexe,
                  "coord"        => $_patient->adresse . " " . $_patient->cp . " " . $_patient->ville,
                  "pat_tel"      => $_patient->tel,
                  "oper_libelle" => null,
                  "oper_prat_id" => null,
                  "aff_rques"    => $_sejour->_ref_last_affectation->rques,
                  "chambre"      => $_sejour->_ref_last_affectation,
                  "type"         => $_sejour->type,
                  "duree"        => $_sejour->_duree,
                  "sej_rques"    => $_sejour->rques,
                  "sej_notes"    => $last_note instanceof CNote ? $last_note->text : null,
              ];
          }
          if (!$filter->_notes) {
              CMbArray::extract($line, "sej_notes");
          }
          if (!$filter->_coordonnees) {
              CMbArray::extract($line, "coord");
              CMbArray::extract($line, "pat_tel");
          }
        $csv->writeLine($line);
      }
  }

  $csv->stream("Export patients");

  return;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);
$smarty->assign("listDays", $listDays);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("total", count($sejours));
$smarty->assign("prestation", $prestation);

$smarty->display("print_planning.tpl");
