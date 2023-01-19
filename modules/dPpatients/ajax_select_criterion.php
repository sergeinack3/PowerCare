<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CSearchCriteria;

$search_criteria_id = CView::get("search_criteria_id", "ref class|CSearchCriteria");
CView::checkin();

$list_criterion = array();

$criteria = new CSearchCriteria();
$criteria->load($search_criteria_id);

$list_criterion["search_criteria_id"]    = $criteria->_id;
$list_criterion["title"]                 = $criteria->title;
$list_criterion["user_id"]               = $criteria->user_id;
$list_criterion["date_min"]              = $criteria->date_min;
$list_criterion["date_max"]              = $criteria->date_max;
$list_criterion["patient_id"]            = $criteria->patient_id;
$list_criterion["ald"]                   = $criteria->ald;
$list_criterion['group_by_patient']      = $criteria->group_by_patient;
$list_criterion["pat_name"]              = $criteria->pat_name;
$list_criterion["sexe"]                  = $criteria->sexe;
$list_criterion["age_min"]               = $criteria->age_min;
$list_criterion["age_max"]               = $criteria->age_max;
$list_criterion["medecin_traitant"]      = $criteria->medecin_traitant;
$list_criterion["medecin_traitant_view"] = $criteria->medecin_traitant_view;
$list_criterion["only_medecin_traitant"] = $criteria->only_medecin_traitant;
$list_criterion["rques"]                 = $criteria->rques;
$list_criterion["libelle_evenement"]     = $criteria->libelle_evenement;
$list_criterion["section_choose"]        = $criteria->section_choose;

// Dossier médical
$list_criterion["hidden_list_antecedents_cim10"] = $criteria->hidden_list_antecedents_cim10;
$list_criterion["antecedents_text"]              = $criteria->antecedents_text;
$list_criterion["allergie_text"]                 = $criteria->allergie_text;
$list_criterion["hidden_list_pathologie_cim10"]  = $criteria->hidden_list_pathologie_cim10;
$list_criterion["pathologie_text"]               = $criteria->pathologie_text;
$list_criterion["hidden_list_probleme_cim10"]    = $criteria->hidden_list_probleme_cim10;
$list_criterion["probleme_text"]                 = $criteria->probleme_text;

//Consultation
$list_criterion["motif"]          = $criteria->motif;
$list_criterion["rques_consult"]  = $criteria->rques_consult;
$list_criterion["examen_consult"] = $criteria->examen_consult;
$list_criterion["conclusion"]     = $criteria->conclusion;

//Séjour
$list_criterion["libelle"]       = $criteria->libelle;
$list_criterion["type"]          = $criteria->type;
$list_criterion["rques_sejour"]  = $criteria->rques_sejour;
$list_criterion["convalescence"] = $criteria->convalescence;

//Intervention
$list_criterion["libelle_interv"] = $criteria->libelle_interv;
$list_criterion["rques_interv"]   = $criteria->rques_interv;
$list_criterion["examen"]         = $criteria->examen;
$list_criterion["materiel"]       = $criteria->materiel;
$list_criterion["exam_per_op"]    = $criteria->exam_per_op;
$list_criterion["codes_ccam"]     = $criteria->codes_ccam;

//Prescription
$list_criterion["produit"]             = $criteria->produit;
$list_criterion["code_cis"]            = $criteria->code_cis;
$list_criterion["code_ucd"]            = $criteria->code_ucd;
$list_criterion["libelle_produit"]     = $criteria->libelle_produit;
$list_criterion["classes_atc"]         = $criteria->classes_atc;
$list_criterion["composant"]           = $criteria->composant;
$list_criterion["keywords_composant"]  = $criteria->keywords_composant;
$list_criterion["indication"]          = $criteria->indication;
$list_criterion["keywords_indication"] = $criteria->keywords_indication;
$list_criterion["type_indication"]     = $criteria->type_indication;
$list_criterion["commentaire"]         = $criteria->commentaire;

CApp::json($list_criterion);
