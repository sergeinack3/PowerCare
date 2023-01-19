<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkread();

$keyword      = CView::request('keyword', 'str');
$uf_type      = CView::request('uf_type', 'enum list|hebergement|medicale|soins');
$sejour_id    = CView::request('sejour_id', 'ref class|CSejour');
$service_id   = CView::request('service_id', 'ref class|CService');
$praticien_id = CView::request('praticien_id', 'ref class|CMediusers');
$function_id  = CView::request('function_id', 'ref class|CFunctions');
$entree       = CView::request('entree', 'dateTime');
$sortie       = CView::request('sortie', 'dateTime');
$sejour_type  = CView::request('sejour_type', 'enum list|comp|ambu|exte|seances|ssr|psy|urg|consult');

CView::checkin();

$auf      = new CAffectationUniteFonctionnelle();
$ufs      = array();
$contexts = array();

$group = CGroups::loadCurrent();

$sejour = new CSejour();
if ($sejour_id) {
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);
}

if ($sejour->entree_prevue) {
  $entree = CMbDT::date($sejour->entree_prevue);
}
elseif ($entree) {
  $entree = CMbDT::date($entree);
}

if ($sejour->sortie_prevue) {
  $sortie = CMbDT::date($sejour->sortie_prevue);
}
elseif ($sortie) {
  $sortie = CMbDT::date($sortie);
}

if ($service_id && in_array($uf_type, array('soins', 'hebergement'))) {
  /** @var CService $service */
  $service = CService::loadFromGuid("CService-$service_id");

  $contexts['CService'] = array();
  foreach ($auf->loadListFor($service) as $_auf) {
    $_uf = $_auf->loadRefUniteFonctionnelle();
    if ((!$_uf->date_debut || !$sortie || $_uf->date_debut < $sortie) && (!$_uf->date_fin || !$entree || $_uf->date_fin > $entree)) {
      $contexts['CService'][$_uf->_id] = $_uf;
    }
  }

  if (empty($contexts['CService'])) {
    unset($contexts['CService']);
  }
}

if ($uf_type == 'medicale') {
  $praticien = new CMediusers();
  if ($praticien_id) {
    $praticien->load($praticien_id);
  }
  elseif ($sejour->_id) {
    $praticien = $sejour->loadRefPraticien();
  }

  if ($praticien->_id) {
    $contexts['CMediusers'] = array();
    foreach ($auf->loadListFor($praticien) as $_auf) {
      $_uf = $_auf->loadRefUniteFonctionnelle();
      if ((!$_uf->date_debut || !$sortie || $_uf->date_debut < $sortie) && (!$_uf->date_fin || !$entree || $_uf->date_fin > $entree)) {
        $contexts['CMediusers'][$_uf->_id] = $_uf;
        $context                           = true;
      }
    }

    foreach ($praticien->loadBackRefs('ufs_secondaires') as $_auf) {
      $_uf = $_auf->loadRefUniteFonctionnelle();
      if ((!$_uf->date_debut || !$sortie || $_uf->date_debut < $sortie) && (!$_uf->date_fin || !$entree || $_uf->date_fin > $entree)) {
        $contexts['CMediusers'][$_uf->_id] = $_uf;
        $context                           = true;
      }
    }

    if (empty($contexts['CMediusers'])) {
      unset($contexts['CMediusers']);
    }
  }

  $function = new CFunctions();
  if ($function_id) {
    $function->load($function_id);
  }
  elseif ($praticien->_id) {
    $function = $praticien->loadRefFunction();
  }

  if ($function->_id) {
    $contexts['CFunctions'] = array();
    foreach ($auf->loadListFor($function) as $_auf) {
      $_uf = $_auf->loadRefUniteFonctionnelle();
      if ((!$_uf->date_debut || !$sortie || $_uf->date_debut < $sortie) && (!$_uf->date_fin || !$entree || $_uf->date_fin > $entree)) {
        $contexts['CFunctions'][$_uf->_id] = $_uf;
        $context                           = true;
      }
    }

    foreach ($function->loadBackRefs('ufs_secondaires') as $_auf) {
      $_uf = $_auf->loadRefUniteFonctionnelle();
      if ((!$_uf->date_debut || !$sortie || $_uf->date_debut < $sortie) && (!$_uf->date_fin || !$entree || $_uf->date_fin > $entree)) {
        $contexts['CMediusers'][$_uf->_id] = $_uf;
        $context                           = true;
      }
    }

    if (empty($contexts['CFunctions'])) {
      unset($contexts['CFunctions']);
    }
  }
}

$uf = new CUniteFonctionnelle();

$where = array(
  'group_id' => " = $group->_id",
  'type'     => " = '$uf_type'"
);

if ($sejour_type) {
  $where['type_sejour'] = " = '$sejour_type'";
}
elseif ($sejour->_id) {
  $where['type_sejour'] = " = '$sejour->type'";
}

if ($entree) {
  $where[] = "date_debut IS NULL OR date_debut <= '$entree'";
}

if ($sortie) {
  $where[] = "date_fin IS NULL OR date_fin >= '$sortie'";
}

if ($keyword) {
  $where[] = "code LIKE '%$keyword%' OR libelle LIKE '%$keyword%'";
}

$ufs = $uf->loadList($where, 'code DESC', null, 'uf_id');

$smarty = new CSmartyDP();
$smarty->assign('contexts', $contexts);
$smarty->assign('ufs', $ufs);
$smarty->display('inc_uf_autocomplete.tpl');