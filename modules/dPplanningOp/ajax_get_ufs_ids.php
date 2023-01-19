<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::check();

$chir_id     = CView::get("chir_id", "ref class|CMediusers");
$type_sejour = CView::get("type_sejour", "str");

CView::checkin();

$chir = CMediusers::get($chir_id);

$chir->loadRefsUfsMedicales($type_sejour);
$chir->loadRefUfMedicaleSecondaire($type_sejour);

$function = $chir->loadRefFunction();

$function->loadRefsUfsMedicales($type_sejour);
$function->loadRefUfMedicaleSecondaire($type_sejour);

$secondary_functions = $chir->loadRefsSecondaryFunctions();

$principale_chir = array_keys($chir->_ref_ufs_medicales);
$principale_cab  = array_keys($function->_ref_ufs_medicales);

$secondaires = array_merge(
  array_keys($chir->_ref_uf_medicale_secondaire),
  array_keys($function->_ref_uf_medicale_secondaire)
);

foreach ($secondary_functions as $_secondary_function) {
  $_secondary_function->loadRefsUfsMedicales($type_sejour);
  $_secondary_function->loadRefUfMedicaleSecondaire($type_sejour);

  $principale_cab = array_merge($principale_cab, array_keys($_secondary_function->_ref_ufs_medicales));
  $secondaires    = array_merge($secondaires   , array_keys($_secondary_function->_ref_uf_medicale_secondaire));
}

$uf_ids = array(
  "principale_chir" => $principale_chir,
  "principale_cab"  => $principale_cab,
  "secondaires"     => $secondaires
);

CApp::json($uf_ids);