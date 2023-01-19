<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\OpenData\CCSVImportHospiDiag;

CCanDo::checkRead();

$annees = CCSVImportHospiDiag::$annees;
$ds     = CSQLDataSource::get('hospi_diag', true);

$champ_pmsi = null;
$categories = null;
$taille_mco = null;
$taille_m   = null;
$taille_c   = null;
$taille_o   = null;

$tables_exists = $ds->hasTable('hd_etablissement');
if ($ds && $tables_exists) {
  $query      = "SELECT DISTINCT `champ_pmsi` FROM `hd_etablissement`;";
  $result     = $ds->loadList($query);
  $champ_pmsi = ($result) ? CMbArray::pluck($result, 'champ_pmsi') : array();

  $query      = "SELECT DISTINCT `cat` FROM `hd_etablissement`;";
  $result     = $ds->loadList($query);
  $categories = ($result) ? CMbArray::pluck($result, 'cat') : array();

  $query      = "SELECT DISTINCT `taille_mco` FROM `hd_etablissement` ORDER BY `taille_mco`;";
  $result     = $ds->loadList($query);
  $taille_mco = ($result) ? CMbArray::pluck($result, 'taille_mco') : array();

  $query    = "SELECT DISTINCT `taille_m` FROM `hd_etablissement` ORDER BY `taille_m`;";
  $result   = $ds->loadList($query);
  $taille_m = ($result) ? CMbArray::pluck($result, 'taille_m') : array();

  $query    = "SELECT DISTINCT `taille_c` FROM `hd_etablissement` ORDER BY `taille_c`;";
  $result   = $ds->loadList($query);
  $taille_c = ($result) ? CMbArray::pluck($result, 'taille_c') : array();

  $query    = "SELECT DISTINCT `taille_o` FROM `hd_etablissement` ORDER BY `taille_o`;";
  $result   = $ds->loadList($query);
  $taille_o = ($result) ? CMbArray::pluck($result, 'taille_o') : array();

}

$smarty = new CSmartyDP();
$smarty->assign('table_exists', $tables_exists);
$smarty->assign('annees', $annees);
$smarty->assign('ds', $ds);
$smarty->assign('champ_pmsi', $champ_pmsi);
$smarty->assign('categories', $categories);
$smarty->assign('taille_mco', $taille_mco);
$smarty->assign('taille_m', $taille_m);
$smarty->assign('taille_c', $taille_c);
$smarty->assign('taille_o', $taille_o);
$smarty->display('vw_hd.tpl');