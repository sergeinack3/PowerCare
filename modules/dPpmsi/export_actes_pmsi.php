<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

global $m, $tab;

CCanDo::check();

$module = CView::get("module", "str default|$m");
$object_class = CView::get("object_class", 'str');
$unlock_dossier = CView::get("unlock_dossier", 'bool default|0');
$object_id = CView::get("object_id", 'ref meta|object_class', true);
$operation_id = CView::post("mb_operation_id", "ref class|COperation");
$sejour_id = CView::post("mb_sejour_id", "ref class|CSejour");

CView::checkin();

$canUnlockActes = $module == "dPpmsi" || CModule::getCanDo("dPsalleOp")->admin;

if (null == $object_class) {
  CAppUI::stepMessage(UI_MSG_WARNING, "$tab-msg-mode-missing");
  return;
}

$NDA = "";
$IPP = "";

$group_guid = null;
switch ($object_class) {
  case "COperation":
    $object = new COperation();

    if (!$operation_id) {
      $operation_id = $object_id;
    }

    // Chargement de l'opération et génération du document
    if ($object->load($operation_id)) {
      $object->loadRefs();
      $codes = array();
      foreach (explode("|", $object->codes_ccam) as $code) {
        if (strpos($code, '*') !== false) {
          list($count, $code) = explode('*', $code);
          for ($i = 0; $i < $count; $i++) {
            $codes[] = $code;
          }
        }
        else {
          $codes[] = $code;
        }
      }
      $actes = CMbArray::pluck($object->_ref_actes_ccam, "code_acte");

      foreach ($object->_ref_actes_ccam as $acte_ccam) {
        $acte_ccam->loadRefsFwd();
      }

      // Suppression des actes non codés
      if (CAppUI::gconf("dPsalleOp CActeCCAM del_acts_not_rated")) {
        foreach ($codes as $_key => $_code) {
          $key = array_search($_code, $actes);
          if ($key === false) {
            unset($codes[$_key]);
          }
          else {
            unset($actes[$key]);
          }
        }
      }
      $object->_codes_ccam = $codes;

      $mbSejour =& $object->_ref_sejour;
      $mbSejour->loadRefsFwd();
      $mbSejour->loadNDA();
      $group_guid = $mbSejour->loadRefEtablissement()->_guid;
      $NDA = $mbSejour->_NDA;
      $mbSejour->_ref_patient->loadIPP();
      $IPP = $mbSejour->_ref_patient->_IPP;
      if (isset($_POST["sc_patient_id"  ])) {
        $mbSejour->_ref_patient->_IPP = $_POST["sc_patient_id"  ];
      }
      if (isset($_POST["sc_venue_id"    ])) {
        $mbSejour->_NDA               = $_POST["sc_venue_id"    ];
      }
      if (isset($_POST["cmca_uf_code"   ])) {
        $object->code_uf              = $_POST["cmca_uf_code"   ];
      }
      if (isset($_POST["cmca_uf_libelle"])) {
        $object->libelle_uf           = $_POST["cmca_uf_libelle"];
      }
    }
    break;

  case "CSejour":
    $object = new CSejour();

    if (!$sejour_id) {
      $sejour_id = $object_id;
    }

    // Chargement du séjour et génération du document
    if ($object->load($sejour_id)) {
      $object->loadRefs();
      $object->loadRefDossierMedical();
      $object->loadNDA();
      $group_guid = $object->loadRefEtablissement()->_guid;
      $NDA = $object->_NDA;
      $object->_ref_patient->loadIPP();
      $IPP = $object->_ref_patient->_IPP;
      if (isset($_POST["sc_patient_id"  ])) {
        $object->_ref_patient->_IPP = $_POST["sc_patient_id"  ];
      }
      if (isset($_POST["sc_venue_id"    ])) {
        $object->_NDA               = $_POST["sc_venue_id"    ];
      }
    }
    break;
}

CAccessMedicalData::logAccess($object);

// Facturation de l'opération où du séjour
$object->facture     = 1;
if ($unlock_dossier) {
  $object->facture = 0;
}
else {
  $object->_force_sent = true;
}

$object->loadLastLog();

try {
  $msg = $object->store();
  if ($msg) {
      CAppUI::stepMessage(UI_MSG_ERROR, $msg);
  }
}
catch(CMbException $e) {
  // Cas d'erreur on repasse la facturation à l'état précédent
  $object->facture = 0;
  if ($unlock_dossier) {
    $object->facture = 1;
  }
  $object->store();

  $e->stepAjax();
}

$object->countExchanges("pmsi", "evenementServeurActe");

if (!$unlock_dossier) {
  // Flag les actes CCAM en envoyés
  foreach ($object->_ref_actes_ccam as $_acte_ccam) {
    $_acte_ccam->sent = 1;
    $_acte_ccam->_no_synchro_eai = true;
    if ($msg = $_acte_ccam->store()) {
      CAppUI::stepMessage(UI_MSG_ERROR, $msg);
    }
  }
}

$order = "date_production DESC";

// Création du template
$smarty = new CSmartyDP("modules/dPpmsi");
$smarty->assign("canUnlockActes", $canUnlockActes);
$smarty->assign("object", $object);
$smarty->assign("IPP", $IPP);
$smarty->assign("NDA", $NDA);
$smarty->assign("module", $module);
$smarty->assign("group_guid", $group_guid);
$smarty->display("inc_export_actes_pmsi");
