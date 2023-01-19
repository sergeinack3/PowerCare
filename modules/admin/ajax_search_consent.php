<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Admin\Rgpd\IRGPDCompliant;

CCanDo::checkAdmin();

$start               = (int)CView::get('start', 'num default|0');
$_object_class       = CView::get('_object_class', 'set list|' . implode('|', CRGPDManager::getCompliantClasses()));
$_status             = CView::get('_status', 'set list|' . implode('|', CRGPDConsent::getStatuses()));
$min_generation_date = CView::get('_min_generation_datetime', 'dateTime');
$max_generation_date = CView::get('_max_generation_datetime', 'dateTime');
$min_send_date       = CView::get('_min_send_datetime', 'dateTime');
$max_send_date       = CView::get('_max_send_datetime', 'dateTime');
$min_read_date       = CView::get('_min_read_datetime', 'dateTime');
$max_read_date       = CView::get('_max_read_datetime', 'dateTime');
$min_acceptance_date = CView::get('_min_acceptance_datetime', 'dateTime');
$max_acceptance_date = CView::get('_max_acceptance_datetime', 'dateTime');
$min_refusal_date    = CView::get('_min_refusal_datetime', 'dateTime');
$max_refusal_date    = CView::get('_max_refusal_datetime', 'dateTime');
$last_error          = CView::get('_last_error', 'set list|1|0');
$first_name          = CView::get('_first_name', 'str');
$last_name           = CView::get('_last_name', 'str');
$birth_date          = CView::get('_birth_date', 'birthDate');
$proof_hash          = CView::get('proof_hash', 'str maxLength|64');

CView::checkin();

$step  = 50;
$limit = "{$start}, {$step}";

// Todo: Handle CGroups permissions

$consent = new CRGPDConsent();
$ds      = $consent->getDS();

$where = array();
$ljoin = array();

if ($_object_class) {
  $where['object_class'] = $ds->prepareIn(explode('|', $_object_class));
}

if ($_status) {
  $where['status'] = $ds->prepareIn(explode('|', $_status));
}

if ($min_generation_date) {
  $where[] = $ds->prepare('`generation_datetime` >= ?', $min_generation_date);
}

if ($max_generation_date) {
  $where[] = $ds->prepare('`generation_datetime` <= ?', $max_generation_date);
}

if ($min_send_date) {
  $where[] = $ds->prepare('`send_datetime` >= ?', $min_send_date);
}

if ($max_send_date) {
  $where[] = $ds->prepare('`send_datetime` <= ?', $max_send_date);
}

if ($min_read_date) {
  $where[] = $ds->prepare('`read_datetime` >= ?', $min_read_date);
}

if ($max_read_date) {
  $where[] = $ds->prepare('`read_datetime` <= ?', $max_read_date);
}

if ($min_acceptance_date) {
  $where[] = $ds->prepare('`acceptance_datetime` >= ?', $min_acceptance_date);
}

if ($max_acceptance_date) {
  $where[] = $ds->prepare('`acceptance_datetime` <= ?', $max_acceptance_date);
}

if ($min_refusal_date) {
  $where[] = $ds->prepare('`refusal_datetime` >= ?', $min_refusal_date);
}

if ($max_refusal_date) {
  $where[] = $ds->prepare('`refusal_datetime` <= ?', $max_refusal_date);
}

if ($last_error) {
  $last_error = explode('|', $last_error);

  if (is_array($last_error) && count($last_error) == 1) {
    $last_error = reset($last_error);
  }
}

if (($last_error || $last_error === '0') && !is_array($last_error)) {
  $where['last_error'] = ($last_error) ? 'IS NOT NULL' : 'IS NULL';
}

if ($proof_hash) {
  $where['proof_hash'] = $ds->prepareLike("{$proof_hash}%");
}

// Identity filter aggregation
if ($first_name || $last_name || $birth_date) {
  $classes = ($_object_class) ? explode('|', $_object_class) : CRGPDManager::getCompliantClasses();
  $classes = array_unique($classes);

  $table = $consent->getSpec()->table;

  $where_identity = array(
    'first_name' => array(),
    'last_name'  => array(),
    'birth_date' => array(),
  );

  foreach ($classes as $_class) {
    /** @var IRGPDCompliant $_object */
    $_object = new $_class();
    $_spec   = $_object->getSpec();

    if ($first_name && $_field = $_object->getFirstNameField()) {
      // Let LJOIN here because of potentially non-existent field in according table
      $ljoin[$_spec->table]                                      = "{$table}.object_class = '{$_class}' AND {$table}.object_id = {$_spec->table}.{$_spec->key}";
      $where_identity['first_name']["{$_spec->table}.{$_field}"] = $ds->prepareLike("{$first_name}%");
    }

    if ($last_name && $_field = $_object->getLastNameField()) {
      // Let LJOIN here because of potentially non-existent field in according table
      $ljoin[$_spec->table]                                     = "{$table}.object_class = '{$_class}' AND {$table}.object_id = {$_spec->table}.{$_spec->key}";
      $where_identity['last_name']["{$_spec->table}.{$_field}"] = $ds->prepareLike("{$last_name}%");
    }

    if ($birth_date && $_field = $_object->getBirthDateField()) {
      // Let LJOIN here because of potentially non-existent field in according table
      $ljoin[$_spec->table]                                      = "{$table}.object_class = '{$_class}' AND {$table}.object_id = {$_spec->table}.{$_spec->key}";
      $where_identity['birth_date']["{$_spec->table}.{$_field}"] = $ds->prepare('= ?', $birth_date);
    }
  }

  foreach ($where_identity as $_filter => $_where) {
    if (!$_where) {
      continue;
    }

    $_where_or = array();

    foreach ($_where as $_field => $_clause) {
      $_where_or[] = "{$_field} {$_clause}";
    }

    $where[] = implode(' OR ', $_where_or);
  }
}

$total    = $consent->countList($where, null, $ljoin);
$consents = $consent->loadList($where, null, $limit, null, $ljoin);

CRGPDConsent::massLoadFwdRef($consents, 'object_id');

foreach ($consents as $_consent) {
  $_consent->loadTargetObject();
  $_consent->loadProofFile();
  $_consent->loadRefGroup();
}

$smarty = new CSmartyDP();
$smarty->assign('consents', $consents);
$smarty->assign('total', $total);
$smarty->assign('start', $start);
$smarty->assign('step', $step);
$smarty->display('inc_vw_consents');