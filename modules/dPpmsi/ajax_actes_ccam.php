<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::check();
$subject_guid         = CView::get("subject_guid", "str");
$read_only            = CView::get("read_only", "bool default|0");
$modal                = CView::get("modal"    , "bool default|0");
$page                 = CView::get('page', 'num default|0');
$filter_executant_id  = CView::get('filter_executant_id', 'ref class|CMediusers');
$filter_date_min      = CView::get('filter_date_min', 'date');
$filter_facturable    = CView::get('filter_facturable', 'bool');
CView::checkin();

/** @var CCodable $subject */
$subject = CMbObject::loadFromGuid($subject_guid);

if (($read_only && !$subject->getPerm(PERM_READ)) || (!$read_only && !$subject->getPerm(PERM_EDIT))) {
  CAppUI::redirect();
  CAppUI::accessDenied();
}

$prat = new CMediusers();
$listPrats = $prat->loadExecutantsCCAM();

$subject->countActes();
$subject->loadRefsActesCCAM($filter_facturable, $filter_executant_id, $filter_date_min);
$subject->loadExtCodesCCAM();

/* On charge les codages ccam du séjour en lui précisant une date pour ne pas qu'il charge tous les codages liés au sejour */
if ($subject->_class == 'CSejour') {
  $subject->loadRefsCodagesCCAM(CMbDT::date($subject->entree), CMbDT::date($subject->sortie));

  if (!CAppUI::gconf('dPccam codage allow_ccam_cotation_sejour')) {
    $read_only = 1;
  }
}

/* If there are CCAM acts but none match the filters, a call to the loadRefsActesCCAM method will reload all the acts
 * because of the way the loadBackRefs method works
 */
if (count($subject->_ref_actes_ccam) || (count($subject->_ref_actes_ccam) === 0
    && (is_null($filter_facturable) || $filter_facturable == '') && !$filter_executant_id && !$filter_date_min)
) {
  $subject->getAssociationCodesActes();
  $subject->loadPossibleActes();
}

/* Unset the codes that are not linked with an existing act if some filters are set */
if ($filter_facturable || $filter_executant_id || $filter_date_min) {
  foreach ($subject->_ext_codes_ccam as $code_index => $_code) {
    foreach ($_code->activites as $activite_index => $activite) {
      foreach ($activite->phases as $phase_index => $phase) {
        if (!property_exists($phase, '_connected_acte') || !$phase->_connected_acte || !$phase->_connected_acte->_id) {
          unset($activite->phases[$phase_index]);
        }
      }

      if (!count($activite->phases)) {
        unset($_code->activites[$activite_index]);
      }
    }

    if (!count($_code->activites)) {
      unset($subject->_ext_codes_ccam[$code_index]);
    }
  }
}

$count_codes_ccam = false;
/* Handle the pagination of the CCAM codes */
if (!is_null($page) && $page !== '') {
  $count_codes_ccam = count($subject->_ext_codes_ccam);
  if ($page < 0) {
    $page = 0;
  }
  elseif ($page > $count_codes_ccam) {
    $page = $count_codes_ccam - ($count_codes_ccam % 10);
  }

  $subject->_ext_codes_ccam = array_slice($subject->_ext_codes_ccam, $page, 10);
}

foreach ($subject->_ext_codes_ccam as $_code) {
  foreach ($_code->activites as $activite) {
    foreach ($activite->phases as $phase) {
      $phase->_connected_acte->loadRefCodageCCAM();
    }
  }
}

$smarty = new CSmartyDP();

$smarty->assign("subject"         , $subject);
$smarty->assign("listPrats"       , $listPrats);
$smarty->assign("read_only"       , $read_only);
$smarty->assign('count_codes_ccam', $count_codes_ccam);
$smarty->assign('page'            , $page);
$smarty->assign("modal"           , $modal);

$smarty->display("inc_actes_ccam");