<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Ssr\CActeCdARR;
use Ox\Mediboard\Ssr\CActeCsARR;
use Ox\Mediboard\Ssr\CActePrestationSSR;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CReplacement;

CCanDo::checkRead();
$sejour_id            = CView::post("sejour_id", "ref class|CSejour");
$equipement_id        = CView::post("equipement_id", "ref class|CEquipement");
$therapeute_id        = CView::post("therapeute_id", "ref class|CMediusers");
$therapeute2_id       = CView::post("therapeute2_id", "ref class|CMediusers");
$therapeute3_id       = CView::post("therapeute3_id", "ref class|CMediusers");
$patient_missing      = CView::post("patient_missing", "bool default|0");
$line_id              = CView::post("line_id", "ref class|CPrescriptionLineElement");
$cdarrs               = CView::post("cdarrs", "str");
$_cdarrs              = CView::post("_cdarrs", "str");
$csarrs               = CView::post("csarrs", "str");
$_csarrs              = CView::post("_csarrs", "str");
$prestas_ssr          = CView::post("prestas_ssr", "str");
$prestas_ssr_quantity = CView::post("prestas_ssr_quantity", "str");
$_prestas_ssr         = CView::post("_prestas_ssr", "str");
$_prestas_ssr_quantity = CView::post("_prestas_ssr_quantity", "str");
$remarque             = CView::post("remarque", "str");
$type_seance          = CView::post("_type_seance", "enum list|dediee|non_dediee|collective");
$sejours_guids        = CView::post("_sejours_guids", "str");
$seance_collective_id = CView::post("seance_collective_id", "ref class|CEvenementSSR");
$_days                = CView::post("_days", "str");
$_heure_deb           = CView::post("_heure_deb", "time");
$duree                = CView::post("duree", "num");
$date                 = CView::get("date", "date default|now", true);
CView::checkin();

$sejours_guids   = json_decode(utf8_encode(stripslashes($sejours_guids)), true);
$csarr_codes_elt = array();

// Codes CdARR
$codes_cdarrs = array();
if (is_array($cdarrs)) {
  foreach ($cdarrs as $_code) {
    $codes_cdarrs[] = $_code;
  }
}

if (is_array($_cdarrs)) {
  foreach ($_cdarrs as $_code) {
    $codes_cdarrs[] = $_code;
  }
}

// Codes CdARR
$codes_csarrs = array();
$concat_csarr = array($csarrs, $_csarrs);
foreach ($concat_csarr as $_csarrs) {
  if (is_array($_csarrs)) {
    foreach ($_csarrs as $_code) {
      $codes_csarrs[] = $_code;
    }
  }
}

// Prestations H+
$codes_prestas_ssr = array();
$concat_prestas = array(
  "presta_ssr" => array(" " => $prestas_ssr, "_" => $_prestas_ssr),
);
foreach ($concat_prestas as $type_presta => $prestas_by_type) {
  foreach ($prestas_by_type as $var_type => $prestas_ssr) {
    if (is_array($prestas_ssr)) {
      $quantites = $var_type == "_" ? $_prestas_ssr_quantity : $prestas_ssr_quantity;
      foreach ($prestas_ssr as $key_code => $_code) {
        if (isset($quantites[$key_code]) && $quantites[$key_code]) {
          if (!isset($codes_prestas_ssr[$type_presta][$_code])) {
            $codes_prestas_ssr[$type_presta][$_code] = 0;
          }
          $codes_prestas_ssr[$type_presta][$_code] += $quantites[$key_code];
        }
      }
    }
  }
}

$kine = new CMediusers();
$kine->load($therapeute_id);

$sejour = new CSejour;
$sejour->load($sejour_id);

$line = new CPrescriptionLineElement();
$line->load($line_id);

$element_prescription_id = $line->element_prescription_id;

$line = new CPrescriptionLineElement();

$ljoin = array(
  "prescription" => "prescription.prescription_id = prescription_line_element.prescription_id"
);

$where = array(
  "prescription.object_class"                         => "= 'CSejour'",
  "prescription_line_element.element_prescription_id" => "= '$element_prescription_id'"
);

// Ajout d'un evenement dans la seance choisie
if ($seance_collective_id) {
  //Cas de la sélection de plusieurs patient pour la séance collective
  $sejours_guids["CSejour-$sejour_id"]["checked"] = 1;
  foreach ($sejours_guids as $sejour_guid => $_sejour) {
    if ($_sejour["checked"] == 1) {
      list($class, $id) = explode('-', $sejour_guid);
      $evenement            = new CEvenementSSR();
      $evenement->sejour_id = $id;
      if ($id == $sejour_id) {
        $evenement->prescription_line_element_id = $line_id;
      }
      else {
        // On mappe la ligne d'élément du séjour concerné
        $where["prescription.object_id"] = "= '$id'";
        $line->loadObject($where, null, null, $ljoin);
        $evenement->prescription_line_element_id = $line->_id;
      }
      $evenement->seance_collective_id = $seance_collective_id;
      $evenement->type_seance          = $type_seance;
      $evenement->loadMatchingObject();
      if ($evenement->_id) {
        CAppUI::displayMsg(CAppui::tr("CEvenementSSR-patient_already_present"), "CEvenementSSR-title-create");
      }
      else {
        $evt = new CEvenementSSR();
        $evt->load($seance_collective_id);
        $evt->deleteCollectivesByPlage(null, null, $sejour_id);
        $evenement->debut          = $evt->debut;
        $evenement->duree          = $evt->duree;
        $evenement->therapeute_id  = $evt->therapeute_id;
        $evenement->therapeute2_id = $evt->therapeute2_id;
        $evenement->therapeute3_id = $evt->therapeute3_id;
        $msg = $evenement->store();
        CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");

        // Actes CdARR
        foreach ($codes_cdarrs as $_code) {
          $acte                   = new CActeCdARR();
          $acte->code             = $_code;
          $acte->evenement_ssr_id = $evenement->_id;
          $msg                    = $acte->store();
          CAppUI::displayMsg($msg, "$acte->_class-msg-create");
        }

        // Line element linked to CsARR codes
        $csarr_activites = array();

        if ($codes_csarrs && $evenement) {
          $evenement       = CEvenementSSR::findOrNew($evenement->_id);
          $line_elt        = $evenement->loadRefPrescriptionLineElement();
          $csarr_codes_elt = $line_elt->_ref_element_prescription->loadRefsCsarrs();

          foreach ($csarr_codes_elt as $_code_elt) {
            $csarr_activites[$_code_elt->code] = $_code_elt;
          }
        }

        // Actes CsARR
        foreach ($codes_csarrs as $_code) {
          $acte                   = new CActeCsARR();
          $acte->code             = $_code;
          $acte->evenement_ssr_id = $evenement->_id;
          $acte->_modulateurs     = isset($csarr_activites[$_code]) && $csarr_activites[$_code]->modulateurs ? explode("|", $csarr_activites[$_code]->modulateurs) : null;
          $acte->extension        = isset($csarr_activites[$_code]) ? $csarr_activites[$_code]->code_ext_documentaire : null;
          $msg                    = $acte->store();
          CAppUI::displayMsg($msg, "$acte->_class-msg-create");
        }

        // Prestations SSR
        foreach ($codes_prestas_ssr as $type_presta => $_prestas) {
          foreach ($_prestas as $_code => $quantite) {
            $presta                   = new CActePrestationSSR();
            $presta->code             = $_code;
            $presta->quantite         = $quantite;
            $presta->type             = $type_presta;
            $presta->evenement_ssr_id = $evenement->_id;
            $msg                      = $presta->store();
            CAppUI::displayMsg($msg, "$presta->_class-msg-create");
          }
        }
      }
    }
  }
}
// Creation des evenements et eventuellement des seances si la checkbox est cochée
else {
  if (count($_days)) {
    $entree   = CMbDT::date($sejour->entree);
    $sortie   = CMbDT::date($sejour->sortie);
    $bilan    = $sejour->loadRefBilanSSR();
    $referent = $bilan->loadRefKineReferent();

    $monday = CMbDT::date("last monday", CMbDT::date("+1 day", $date));
    foreach ($_days as $_number) {
      $evenements_actes_id = array();

      $_day = CMbDT::date("+$_number DAYS", $monday);
      if (!CMbRange::in($_day, $entree, $sortie)) {
        CAppUI::setMsg("CEvenementSSR-msg-failed-bounds", UI_MSG_WARNING);
        continue;
      }

      if (!$_heure_deb || !$duree) {
        continue;
      }

      $evenement                  = new CEvenementSSR();
      $evenement->equipement_id   = $equipement_id;
      $evenement->debut           = "$_day $_heure_deb";
      $evenement->duree           = $duree;
      $evenement->remarque        = $remarque;
      $evenement->therapeute_id   = $therapeute_id;
      $evenement->therapeute2_id  = $therapeute2_id;
      $evenement->therapeute3_id  = $therapeute3_id;
      $evenement->type_seance     = $type_seance;
      $evenement->patient_missing = $patient_missing;

      // Transfert kiné référent => kiné remplaçant si disponible
      if ($therapeute_id == $referent->_id) {
        $conge = new CPlageConge();
        $conge->loadFor($therapeute_id, $_day);
        // Référent en congés
        if ($conge->_id) {
          $replacement            = new CReplacement();
          $replacement->conge_id  = $conge->_id;
          $replacement->sejour_id = $sejour->_id;
          $replacement->loadMatchingObject();
          if ($replacement->_id) {
            $evenement->therapeute_id = $replacement->replacer_id;
          }
        }
      }

      // Transfert kiné remplacant => kiné référant si présent
      if ($sejour->isReplacer($therapeute_id)) {
        $conge = new CPlageConge();
        $conge->loadFor($referent->_id, $_day);
        // Référent présent
        if (!$conge->_id) {
          $evenement->therapeute_id = $referent->_id;
        }
      }

      // Si l'evenement n'est pas une seance collective
      if ($type_seance != "collective") {
        $element_prescription = new CElementPrescription();
        $element_prescription->load($element_prescription_id);
        $element_prescription->loadRefCategory();

        $evenement->prescription_line_element_id = $line_id;
        $evenement->niveau_individuel            = $element_prescription->_niveau_ssr;
        $evenement->sejour_id                    = $sejour_id;
      }

      // Store de l'evenement ou de la nouvelle seance
      if (!$patient_missing && !$evenement->_id) {
        $evenement->deleteCollectivesByPlage(array($_day), null, $sejour_id);
        if (!$evenement->deleteIndividuellesCollisions()) {
          CAppUI::stepAjax("CEvenementSSR-msg-has_higher_event_priority", UI_MSG_WARNING);
          CApp::rip();
        }
      }
      $msg = $evenement->store();
      CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");

      $evenements_actes_id[] = $evenement->_id;

      // Si une seance a ete créée, on crée l'evenement lié a la seance, et on crée les code cdarr sur l'evenement
      if ($type_seance == "collective") {
        $evenements_actes_id = array();
        //Cas de la sélection de plusieurs patient pour la séance collective
        $sejours_guids["CSejour-$sejour_id"]["checked"] = 1;
        foreach ($sejours_guids as $sejour_guid => $_sejour) {
          if ($_sejour["checked"] == 1) {
            $sejour_collectif              = CMbObject::loadFromGuid($sejour_guid);
            $evt_ssr                       = new CEvenementSSR();
            $evt_ssr->sejour_id            = $sejour_collectif->_id;
            $evt_ssr->seance_collective_id = $evenement->_id;
            $evt_ssr->type_seance          = $type_seance;
            $evt_ssr->debut          = "$_day $_heure_deb";
            $evt_ssr->duree          = $duree;
            $evt_ssr->therapeute_id  = $therapeute_id;
            $evt_ssr->therapeute2_id = $therapeute2_id;
            $evt_ssr->therapeute3_id = $therapeute3_id;

            // On mappe la ligne d'élément du séjour concerné
            if ($evt_ssr->sejour_id == $evenement->sejour_id) {
              $evt_ssr->prescription_line_element_id = $line_id;
            }
            else {
              $where["prescription.object_id"] = "= '$evt_ssr->sejour_id'";
              $line->loadObject($where, null, null, $ljoin);
              $evt_ssr->prescription_line_element_id = $line->_id;
            }

            $msg = $evt_ssr->store();
            CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");

            // Si une seance a ete créée, les codes cdarrs seront créés sur l'evenement de la seance
            $evenements_actes_id[] = $evt_ssr->_id;
          }
        }
      }

      foreach ($evenements_actes_id as $evenement_actes_id) {
        // Actes CdARR
        foreach ($codes_cdarrs as $_code) {
          $acte                   = new CActeCdARR();
          $acte->code             = $_code;
          $acte->evenement_ssr_id = $evenement_actes_id;
          $msg                    = $acte->store();
          CAppUI::displayMsg($msg, "$acte->_class-msg-create");
        }

        // Line element linked to CsARR codes
        $csarr_activites = array();

        if ($codes_csarrs && $evenement_actes_id) {
          $evenement       = CEvenementSSR::find($evenement_actes_id);
          $line_elt        = $evenement->loadRefPrescriptionLineElement();
          $csarr_codes_elt = $line_elt->_ref_element_prescription->loadRefsCsarrs();

          foreach ($csarr_codes_elt as $_code_elt) {
            $csarr_activites[$_code_elt->code] = $_code_elt;
          }
        }

        // Actes CsARR
        foreach ($codes_csarrs as $_code) {
          $acte                   = new CActeCsARR();
          $acte->code             = $_code;
          $acte->evenement_ssr_id = $evenement_actes_id;
          $acte->commentaire      = isset($csarr_activites[$_code]) ? $csarr_activites[$_code]->commentaire : null;
          $acte->_modulateurs     = isset($csarr_activites[$_code]) && $csarr_activites[$_code]->modulateurs ? explode("|", $csarr_activites[$_code]->modulateurs) : null;
          $acte->extension        = isset($csarr_activites[$_code]) ? $csarr_activites[$_code]->code_ext_documentaire : null;
          $msg                    = $acte->store();
          CAppUI::displayMsg($msg, "$acte->_class-msg-create");
        }

        // Prestations SSR
        foreach ($codes_prestas_ssr as $type_presta => $_prestas) {
          foreach ($_prestas as $_code => $quantite) {
            $presta                   = new CActePrestationSSR();
            $presta->code             = $_code;
            $presta->quantite         = $quantite;
            $presta->type             = $type_presta;
            $presta->evenement_ssr_id = $evenement_actes_id;
            $msg                      = $presta->store();
            CAppUI::displayMsg($msg, "$presta->_class-msg-create");
          }
        }
      }
    }
  }
}
echo CAppUI::getMsg();
CApp::rip();
