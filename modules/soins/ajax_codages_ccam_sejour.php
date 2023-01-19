<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Ccam\CDentCCAM;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id = CView::get('sejour_id', 'ref class|CSejour');
$date      = CView::get('date', 'date default|now');
$from      = CView::get('from', 'date');
$to        = CView::get('to', 'date');

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefsFwd();
$sejour->countExchanges();
$sejour->isCoded();
$sejour->canDo();
$sejour->getAssociationCodesActes();
$sejour::$possible_actes_lite = true;
$sejour->loadPossibleActes();

/* Gestion des dates */
$date_entree = CMbDT::date(null, $sejour->entree);
$date_sortie = CMbDT::date(null, $sejour->sortie);

if (is_null($from) || $from == '' || is_null($to) || $to == '') {
  if (CMbDT::daysRelative($date, $date_sortie) < 0) {
    $date = $date_sortie;
  }
  elseif (CMbDT::daysRelative($date_entree, $date) < 0) {
    $date = $date_entree;
  }

  if (CMbDT::daysRelative($date_entree, $date_sortie) < 6) {
    $from = $date_entree;
    $to   = $date_sortie;
  }
  elseif (CMbDT::daysRelative($date_entree, $date) < 2) {
    $from = $date_entree;
    $to   = CMbDT::date('+4 DAYS', $from);
  }
  elseif (CMbDT::daysRelative($date, $date_sortie) < 2) {
    $to   = $date_sortie;
    $from = CMbDT::date('-4 DAYS', $to);
  }
  else {
    $from = CMbDT::date('-2 DAYS', $date);
    $to   = CMbDT::date('+2 DAYS', $date);
  }
}
else {
  if (CMbDT::daysRelative($to, $date_sortie) < 0 && CMbDT::daysRelative($date_entree, $from) < 0) {
    $to   = $date_sortie;
    $from = $date_entree;
    $date = CMbDT::date('+' . round(CMbDT::daysRelative($from, $to)) . ' DAYS', $to);
  }
  elseif (CMbDT::daysRelative($to, $date_sortie) < 0) {
    $to   = $date_sortie;
    $from = CMbDT::date('-4 DAYS', $to);
    $date = CMbDT::date('-2 DAYS', $to);
  }
  elseif (CMbDT::daysRelative($date_entree, $from) < 0) {
    $from = $date_entree;
    $to   = CMbDT::date('+4 DAYS', $from);
    $date = CMbDT::date('+2 DAYS', $from);
  }
}

// Chargement des règles de codage
$praticiens = array();
$sejour->loadRefsCodagesCCAM($from, $to);
foreach ($sejour->_ref_codages_ccam as $_praticien_id => $_codages_by_prat) {
  foreach ($_codages_by_prat as $_codages_by_day) {
    foreach ($_codages_by_day as $_codage) {
      $_codage->loadPraticien()->loadRefFunction();

      if (!array_key_exists($_codage->praticien_id, $praticiens)) {
        $praticiens[$_codage->praticien_id] = $_codage->_ref_praticien;
      }

      $_codage->loadActesCCAM();
      $_codage->getTarifTotal();
      foreach ($_codage->_ref_actes_ccam as $_acte) {
        $_acte->getTarif();
      }
    }
  }

  if (!array_key_exists($_praticien_id, $praticiens)) {
    $_praticien = new CMediusers();
    $_praticien->load($_praticien_id);
    $_praticien->loadRefFunction();

    $praticiens[$_praticien_id] = $_praticien;
  }
}

$ext_codes_ccam = array();
foreach ($sejour->_ext_codes_ccam as $_code) {
  if (!array_key_exists($_code->code, $ext_codes_ccam)) {
    $ext_codes_ccam[$_code->code]          = array();
    $ext_codes_ccam[$_code->code]['count'] = 0;
    $ext_codes_ccam[$_code->code]['codes'] = array();
  }

  $ext_codes_ccam[$_code->code]['count']++;
  $ext_codes_ccam[$_code->code]['codes'][] = $_code;
}

// Chargement des praticiens
$listAnesths = new CMediusers;
$listAnesths = $listAnesths->loadAnesthesistes(PERM_DENY);

$listChirs = new CMediusers;
$listChirs = $listChirs->loadExecutantsCCAM(PERM_DENY);

// Liste des dents CCAM
$liste_dents = CDentCCAM::loadList();
$liste_dents = reset($liste_dents);

$user = CMediusers::get();
$user->isPraticien();
$user->isProfessionnelDeSante();

$days = CMbDT::getDays($from, $to);

$sejour->loadRefsOperations();
$operations = array();

foreach ($sejour->_ref_operations as $_operation) {
  if (in_array($_operation->date, $days)) {
    $_operation->loadExtCodesCCAM();
    $_operation->loadPossibleActes();
    $_operation->loadRefsCodagesCCAM();

    foreach ($_operation->_ref_codages_ccam as $_praticien_id => $_codages_by_prat) {
      /** @var CCodageCCAM $_codage */
      foreach ($_codages_by_prat as $_codage) {
        $_codage->loadPraticien()->loadRefFunction();
        $_codage->loadActesCCAM();
        $_codage->getTarifTotal();
        foreach ($_codage->_ref_actes_ccam as $_acte) {
          $_acte->getTarif();
        }
      }

      if (!array_key_exists($_praticien_id, $praticiens)) {
        $_praticien = new CMediusers();
        $_praticien->load($_praticien_id);
        $_praticien->loadRefFunction();

        $praticiens[$_praticien_id] = $_praticien;
      }
    }

    $operations[$_operation->_id] = $_operation;
  }
}

$smarty = new CSmartyDP();
$smarty->assign('subject', $sejour);
$smarty->assign('ext_codes_ccam', $ext_codes_ccam);
$smarty->assign('listAnesths', $listAnesths);
$smarty->assign('listChirs', $listChirs);
$smarty->assign('liste_dents', $liste_dents);
$smarty->assign('_is_dentiste', $sejour->_ref_praticien->isDentiste());
$smarty->assign('date', $date);
$smarty->assign('from', $from);
$smarty->assign('to', $to);
$smarty->assign('days', $days);
$smarty->assign('praticiens', $praticiens);
$smarty->assign('user', $user);
$smarty->assign('operations', $operations);
$smarty->display('inc_codages_ccam_sejour');