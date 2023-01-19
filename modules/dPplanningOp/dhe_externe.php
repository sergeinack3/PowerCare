<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$praticien = new CMediusers();
$praticien_id = CValue::get("praticien_id");
$praticien->load($praticien_id);
if (!$praticien->canEdit() || !$praticien->isPraticien()) {
  $praticien_id = null;
}

$patient = new CPatient();
$patient_id         = CValue::get("patient_id"            , CValue::post("patient_id"));
$patient->nom       = CValue::get("patient_nom"           , CValue::post("patient_nom"));
$patient->nom_jeune_fille = CValue::get("patient_nom_naissance", CValue::post("patient_nom_naissance"));
$patient->prenom    = CValue::get("patient_prenom"        , CValue::post("patient_prenom"));
$patient->naissance = CValue::get("patient_date_naissance", CValue::post("patient_date_naissance"));
$patient->sexe      = CValue::get("patient_sexe"          , CValue::post("patient_sexe"));
$patient->adresse   = CValue::get("patient_adresse"       , CValue::post("patient_adresse"));
$patient->cp        = CValue::get("patient_code_postal"   , CValue::post("patient_code_postal"));
$patient->ville     = CValue::get("patient_ville"         , CValue::post("patient_ville"));
$patient->tel       = CValue::get("patient_telephone"     , CValue::post("patient_telephone"));
$patient->tel2      = CValue::get("patient_mobile"        , CValue::post("patient_mobile"));

$sejour = new CSejour();
$sejour_id             = CValue::get("sejour_id"           , CValue::post("sejour_id"));
$sejour->libelle       = CValue::get("sejour_libelle"      , CValue::post("sejour_libelle"));
$sejour->type          = CValue::get("sejour_type"         , CValue::post("sejour_type"));
$sejour->entree_prevue = CValue::get("sejour_entree_prevue", CValue::post("sejour_entree_prevue"));
$sejour->sortie_prevue = CValue::get("sejour_sortie_prevue", CValue::post("sejour_sortie_prevue"));
$sejour->rques         = CValue::get("sejour_remarques"    , CValue::post("sejour_remarques"));

$sejour_intervention = CValue::get("sejour_intervention");

$intervention = new COperation();
$intervention->_datetime      = CValue::get("intervention_date"            , CValue::post("intervention_date"));
$intervention->temp_operation = CValue::get("intervention_duree"           , CValue::post("intervention_duree"));
$intervention->cote           = CValue::get("intervention_cote"            , CValue::post("intervention_cote"));
$intervention->horaire_voulu  = CValue::get("intervention_horaire_souhaite", CValue::post("intervention_horaire_souhaite"));
$intervention->codes_ccam     = CValue::get("intervention_codes_ccam"      , CValue::post("intervention_codes_ccam"));
$intervention->materiel       = CValue::get("intervention_materiel"        , CValue::post("intervention_materiel"));
$intervention->rques          = CValue::get("intervention_remarques"       , CValue::post("intervention_remarques"));

$msg_error = null;

$list_fields = array();
$patient_existant = new CPatient();
$patient_resultat = new CPatient();
$sejour_existant  = new CSejour();
$sejour_resultat  = new CSejour();
$patient_ok       = false;
$sejour_ok        = false;
$intervention_ok  = false;
if ($patient_id) {
  $patient_resultat->load($patient_id);
  if ($patient_resultat->_id) {
    $patient = $patient_resultat;
    $patient_ok = true;
  }
}
if ($praticien_id && (!$patient_ok || $sejour_id)) {
  if (!$sejour_id) {
    // Recherche d'un patient existant
    $patient_existant = clone $patient;
    $patient_existant->loadMatchingPatient();
    // S'il n'y est pas, on le store
    if (!$patient_existant->_id) {
      if (!$msg_error = $patient->check()) {
        $patient->civilite = 'guess';
        $patient->store();
        $patient_ok = true;
      }
      else {
        $msg_error = "<strong>Impossible de sauvegarder le patient :<strong> ".$msg_error;
      }

      // Sinon on vérifie qu'ils sont bien identiques
    }
    else {
      $list_fields = array("action" => "redirectDHESejour",
                           "fields" => array("nom"       => true,
                                             "prenom"    => true,
                                             "naissance" => true,
                                             "sexe"      => true,
                                             "adresse"   => true,
                                             "cp"        => true,
                                             "ville"     => true,
                                             "tel"       => true,
                                             "tel2"      => true));
      $patient->updateFormFields();
      $equals  = true;
      foreach ($list_fields["fields"] as $_field => $_state) {
        $list_fields["fields"][$_field] =
          !$patient->$_field ||
          !$patient_existant->$_field ||
          ($patient->$_field == $patient_existant->$_field);

        $equals &= $list_fields["fields"][$_field];
      }
      // On complète éventuellement le patient existant avant de le storer
      if ($equals) {
        foreach ($list_fields["fields"] as $_field => $_state) {
          $patient_existant->$_field = CValue::first($patient_existant->$_field, $patient->$_field);
        }
        if (!$msg_error = $patient_existant->store()) {
          $patient = $patient_existant;
          $patient_ok = true;
        }
        else {
          $msg_error = "<strong>Impossible de sauvegarder le patient :<strong> ".$msg_error;
        }
        // Sinon on propose à l'utilisateur de régler les problèmes
      }
      else {
        $patient_resultat = clone $patient;
        foreach ($list_fields["fields"] as $_field => $_state) {
          $patient_resultat->$_field = CValue::first($patient->$_field, $patient_existant->$_field);
        }
        $list_fields["object"]          = $patient;
        $list_fields["object_existant"] = $patient_existant;
        $list_fields["object_resultat"] = $patient_resultat;
      }
    }
    if (!$patient_ok) {
      // Création du template
      $smarty = new CSmartyDP();
      $smarty->assign("praticien_id"       , $praticien_id);
      $smarty->assign("list_fields"        , $list_fields);
      $smarty->assign("patient"            , $patient);
      $smarty->assign("sejour"             , $sejour);
      $smarty->assign("intervention"       , $intervention);
      $smarty->assign("sejour_intervention", $sejour_intervention);
      $smarty->assign("msg_error"          , $msg_error);
      $smarty->display("dhe_externe");
      return;
    }
  }

  // Gestion du séjour
  if ($sejour_id) {
    $sejour_resultat->load($sejour_id);

    CAccessMedicalData::logAccess($sejour);

    if ($sejour_resultat->_id) {
      $sejour = $sejour_resultat;
      if (!$sejour->libelle) {
        $sejour->libelle = "automatique";
      }
      $sejour_ok = true;
    }
  }
  if ($praticien_id && $sejour->libelle && !$sejour_ok) {
    $sejour->group_id = CGroups::loadCurrent()->_id;
    $sejour->praticien_id = $praticien_id;
    $sejour->patient_id = $patient->_id;
    if (!$msg_error = $sejour->check()) {
      // On recherche un séjour existant
      $sejour->updatePlainFields();
      $sejour_existant = new CSejour();
      $collisions = $sejour->getCollisions();
      // S'il n'y est pas, on le store
      if (!count($collisions)) {
        if (!$msg_error = $sejour->store()) {
          $sejour_ok = true;
        }
        // Sinon on vérifie qu'ils sont bien identiques
      }
      else {
        $sejour_existant = reset($collisions);
        $list_fields = array(
          "action" => "redirectDHESejour",
          "fields" => array(
            "group_id"      => true,
            "praticien_id"  => true,
            "patient_id"    => true,
            "libelle"       => true,
            "entree_prevue" => true,
            "sortie_prevue" => true,
            "rques"         => true)
        );
        $sejour->updateFormFields();
        $equals  = true;
        foreach ($list_fields["fields"] as $_field => $_state) {
          $list_fields["fields"][$_field] =
            !$sejour->$_field ||
            !$sejour_existant->$_field ||
            ($sejour->$_field == $sejour_existant->$_field);

          $equals &= $list_fields["fields"][$_field];
        }
        // On complète éventuellement le séjour existant avant de le storer
        if ($equals) {
          foreach ($list_fields["fields"] as $_field => $_state) {
            $sejour_existant->$_field = CValue::first($sejour_existant->$_field, $sejour->$_field);
          }
          if (!$msg_error = $sejour_existant->store()) {
            $sejour = $sejour_existant;
            $sejour_ok = true;
          }
          // Sinon on propose à l'utilisateur de régler les problèmes
        }
        else {
          $sejour_resultat = clone $sejour;
          foreach ($list_fields["fields"] as $_field => $_state) {
            $sejour_resultat->$_field = CValue::first($sejour->$_field, $sejour_existant->$_field);
          }
          $list_fields["object"] = $sejour;
          $list_fields["object_existant"] = $sejour_existant;
          $list_fields["object_resultat"] = $sejour_resultat;
        }   
      }
    }
    else {
      $msg_error = "<strong>Impossible de sauvegarder le séjour :</strong> ".$msg_error;
    }
  }
  if ($sejour->libelle && !$sejour_ok) {
    // Création du template
    $smarty = new CSmartyDP();
    $smarty->assign("praticien_id"       , $praticien_id);
    $smarty->assign("list_fields"        , $list_fields);
    $smarty->assign("patient"            , $patient);
    $smarty->assign("sejour"             , $sejour);
    $smarty->assign("intervention"       , $intervention);
    $smarty->assign("sejour_intervention", $sejour_intervention);
    $smarty->assign("msg_error"          , $msg_error);
    $smarty->display("dhe_externe");
    return;
  }

  // Gestion de l'intervention
  if ($sejour_intervention && $intervention->_datetime && $intervention->temp_operation && $intervention->cote) {
    $intervention->chir_id = $praticien_id;
    // Est-ce que la date permet de planifier
    $bloc = $intervention->updateSalle()->loadRefBloc();
    if (CMbDT::daysRelative(CMbDT::date(), CMbDT::date($intervention->_datetime)) > $bloc->days_locked) {
      $plage_op = new CPlageOp();
      $plage_op->date = CMbDT::date($intervention->_datetime);
      $plage_op->chir_id = $praticien_id;
      $listPlages = $plage_op->loadMatchingList();
      if (count($listPlages)) {
        $intervention->plageop_id = reset($listPlages)->_id;
      }
    }
    if (!$intervention->plageop_id && CMbDT::daysRelative(CMbDT::date(), CMbDT::date($intervention->_datetime)) > 2) {
      $msg_error = "aucune vacation de disponible à cette date";
    }
    else {
      $intervention->libelle = $sejour->libelle;
      $intervention->sejour_id = $sejour->_id;
      if (!$msg_error = $intervention->store()) {
        $intervention_ok = true;
      }
    }
  }
  elseif ($sejour_intervention) {
    $msg_error = "champ(s) obligatoire(s) manquant(s) :";
    if (!$intervention->_datetime) {
      $msg_error .="<br />- Date de l'intervention";
    }
    if (!$intervention->temp_operation) {
      $msg_error .="<br />- Durée de l'intervention";
    }
    if (!$intervention->cote) {
      $msg_error .="<br />- Cote de l'intervention";
    }
  }
  if ($sejour_intervention && !$intervention_ok) {
    $msg_error = "<strong>Impossible de sauvegarder l'intervention :</strong> ".$msg_error;
    // Création du template
    $smarty = new CSmartyDP();
    $smarty->assign("praticien_id"       , $praticien_id);
    $smarty->assign("patient"            , $patient);
    $smarty->assign("sejour"             , $sejour);
    $smarty->assign("intervention"       , $intervention);
    $smarty->assign("sejour_intervention", $sejour_intervention);
    $smarty->assign("msg_error"          , $msg_error);
    $smarty->display("dhe_externe");
    return;
  }
}

if ($patient_ok && !$sejour->libelle) {
  CAppUI::redirect("m=dPplanningOp&a=vw_edit_planning&chir_id=$praticien_id&operation_id=0&pat_id=".$patient->_id);
}
elseif ($patient_ok && $sejour_ok && !$sejour_intervention) {
  CAppUI::redirect("m=dPplanningOp&tab=vw_edit_sejour&sejour_id=".$sejour->_id);
}
elseif ($patient_ok && $sejour_ok && $intervention_ok && $intervention->plageop_id) {
  CAppUI::redirect("m=dPplanningOp&tab=vw_edit_planning&operation_id=".$intervention->_id);
}
elseif ($patient_ok && $sejour_ok && $intervention_ok && !$intervention->plageop_id) {
  CAppUI::redirect("m=dPplanningOp&tab=vw_edit_urgence&operation_id=".$intervention->_id);
}
else {
  $msg_error = "Erreur indéfinie";
  CApp::log($msg_error);
  // Création du template
  $smarty = new CSmartyDP();
  $smarty->assign("praticien_id", $praticien_id);
  $smarty->assign("msg_error"   , $msg_error);
  $smarty->display("dhe_externe");
}
