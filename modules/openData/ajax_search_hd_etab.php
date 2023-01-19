<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\OpenData\CHDIdentite;

CCanDo::checkRead();

$finess         = CView::get('finess', 'str');
$raison_sociale = CView::get('raison_sociale', 'str');
$champ_pmsi     = CView::get('champ_pmsi', 'str');
$cat            = CView::get('cat', 'str');
$taille_mco     = CView::get('taille_mco', 'str');
$taille_m       = CView::get('taille_m', 'str');
$taille_c       = CView::get('taille_c', 'str');
$taille_o       = CView::get('taille_o', 'str');
$nb_lits_med    = CView::get('nb_lits_med', 'num');
$nb_lits_chir   = CView::get('nb_lits_chir', 'num');
$nb_lits_obs    = CView::get('nb_lits_obs', 'num');

$start = CView::get('start', 'num default|0');
$step  = CView::get('step', 'num default|50');

CView::checkin();

$ds = CSQLDataSource::get('hospi_diag');

$query = new CRequest();
$query->addTable('hd_etablissement');

$identite = new CHDIdentite();
$year     = $identite->getLastYear();
if ($year) {
  $query->addLJoin(
    "`hd_identite` ON (`hd_etablissement`.hd_etablissement_id = `hd_identite`.hd_etablissement_id AND `hd_identite`.annee = $year)"
  );
}
else {
  $query->addLJoin(
    "`hd_identite` ON (`hd_etablissement`.hd_etablissement_id = `hd_identite`.hd_etablissement_id)"
  );
}


$where = array();

if ($finess) {
  $where['finess'] = $ds->prepareLike("%$finess%");
}

if ($raison_sociale) {
  $where[] = $ds->prepareLikeMulti($raison_sociale, 'raison_sociale');
}

if ($champ_pmsi) {
  $where['champ_pmsi'] = $ds->prepare('= ?', $champ_pmsi);
}

if ($cat) {
  $where['cat'] = $ds->prepare('= ?', $cat);
}

if ($taille_mco) {
  $where['taille_mco'] = $ds->prepare('= ?', $taille_mco);
}

if ($taille_m) {
  $where['taille_m'] = $ds->prepare('= ?', $taille_m);
}

if ($taille_c) {
  $where['taille_c'] = $ds->prepare('= ?', $taille_c);
}

if ($taille_o) {
  $where['taille_o'] = $ds->prepare('= ?', $taille_o);
}

if ($nb_lits_chir || $nb_lits_med || $nb_lits_obs) {
  if ($nb_lits_chir) {
    $where[] = '`hd_identite`.nb_lits_chir ' . $ds->prepare('> ?', $nb_lits_chir - 50)
      . ' AND `hd_identite`.nb_lits_chir ' . $ds->prepare('< ?', $nb_lits_chir + 50);
  }

  if ($nb_lits_med) {
    $where[] = '`hd_identite`.nb_lits_med ' . $ds->prepare('> ?', $nb_lits_med - 50)
      . ' AND `hd_identite`.nb_lits_med ' . $ds->prepare('< ?', $nb_lits_med + 50);
  }

  if ($nb_lits_obs) {
    $where[] = '`hd_identite`.nb_lits_obs ' . $ds->prepare('> ?', $nb_lits_obs - 50)
      . ' AND `hd_identite`.nb_lits_obs ' . $ds->prepare('< ?', $nb_lits_obs + 50);
  }
}

$query->addWhere($where);
$query->addOrder('raison_sociale');

$total = $ds->loadResult($query->makeSelectCount());

$query->select = array();
$query->addSelect(
  array(
    '`hd_etablissement`.hd_etablissement_id',
    '`hd_etablissement`.finess',
    '`hd_etablissement`.raison_sociale',
    '`hd_etablissement`.champ_pmsi',
    '`hd_etablissement`.cat',
    '`hd_etablissement`.taille_mco',
    '`hd_etablissement`.taille_m',
    '`hd_etablissement`.taille_c',
    '`hd_etablissement`.taille_o',
    '`hd_identite`.nb_lits_med',
    '`hd_identite`.nb_lits_chir',
    '`hd_identite`.nb_lits_obs',
  )
);
$query->setLimit("$start,$step");

$result = $ds->loadList($query->makeSelect());

$smarty = new CSmartyDP();
$smarty->assign('etabs', $result);
$smarty->assign('start', $start);
$smarty->assign('step', $step);
$smarty->assign('total', $total);
$smarty->display('inc_search_hd_etab.tpl');