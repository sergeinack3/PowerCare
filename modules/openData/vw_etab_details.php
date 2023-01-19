<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\OpenData\CHDActivite;
use Ox\Mediboard\OpenData\CHDActiviteZone;
use Ox\Mediboard\OpenData\CHDEtablissement;
use Ox\Mediboard\OpenData\CHDFinance;
use Ox\Mediboard\OpenData\CHDIdentite;
use Ox\Mediboard\OpenData\CHDProcess;
use Ox\Mediboard\OpenData\CHDQualite;
use Ox\Mediboard\OpenData\CHDResshum;

CCanDo::checkRead();

$etab_id = CView::get('etab_id', 'ref class|CHDEtablissement notNull');

CView::checkin();

$etab = new CHDEtablissement();
$etab->load($etab_id);

$query = new CRequest();
$query->addSelect('distinct annee');
$query->addTable('hd_activite');
$ds     = CSQLDataSource::get('hospi_diag');
$annees = $ds->loadList($query->makeSelect());
$annees = CMbArray::pluck($annees, 'annee');

// CHDActivite
$activite                      = new CHDActivite();
$activite->hd_etablissement_id = $etab_id;
$activites                     = array();

/** @var CHDActivite $_activite */
foreach ($activite->loadMatchingListEsc('annee DESC') as $_activite) {
  $activites[] = $_activite->getDisplayFields();
}
$activite_fields = (isset($activites[0])) ? array_keys($activites[0]) : array();
$activite_annees = CMbArray::pluck($activites, 'annee');

$activites = CHDActivite::addAllYears($activites, $annees, $activite_fields);

// CHDActiviteZone
$activite_zone                      = new CHDActiviteZone();
$activite_zone->zone                = $etab->finess;
$activites_zone                     = array();
$activite_zone_fields               = array();
foreach ($activite_zone->loadMatchingListEsc('annee DESC, zone') as $_activite_zone) {
  $fields = $_activite_zone->getPlainFields();
  $annee = $fields['annee'];

  $fields['zone'] = CHDEtablissement::getEtabRSForEtabID($fields['hd_etablissement_id']);

  unset($fields['hd_activite_zone_id']);
  unset($fields['hd_etablissement_id']);
  unset($fields['annee']);

  if (!$activite_zone_fields) {
    $activite_zone_fields = array_keys($fields);
  }

  if (!isset($activite_zones[$annee])) {
    $activite_zones[$annee] = array();
  }

  $activites_zone[$annee][] = $fields;
}

foreach ($activites_zone as &$_activite_zone) {
  CMbArray::pluckSort($_activite_zone, SORT_ASC, 'zone');
}

// CHDQualite
$qualite                      = new CHDQualite();
$qualite->hd_etablissement_id = $etab_id;
$qualites                     = array();

/** @var CHDQualite $_qualite */
foreach ($qualite->loadMatchingListEsc('annee DESC') as $_qualite) {
  $qualites[] = $_qualite->getDisplayFields();
}
$qualite_fields = (isset($qualites[0])) ? array_keys($qualites[0]) : array();

$qualites = CHDQualite::addAllYears($qualites, $annees, $qualite_fields);

// CHDProcess
$orga                      = new CHDProcess();
$orga->hd_etablissement_id = $etab_id;
$orgas                     = array();

/** @var CHDProcess $_orga */
foreach ($orga->loadMatchingListEsc('annee DESC') as $_orga) {
  $orgas[] = $_orga->getDisplayFields();
}
$orga_fields = (isset($orgas[0])) ? array_keys($orgas[0]) : array();

$orgas = CHDProcess::addAllYears($orgas, $annees, $orga_fields);

// CHDResshum
$resshum                      = new CHDResshum();
$resshum->hd_etablissement_id = $etab_id;
$resshums                     = array();

/** @var CHDResshum $_resshum */
foreach ($resshum->loadMatchingListEsc('annee DESC') as $_resshum) {
  $resshums[] = $_resshum->getDisplayFields();
}
$resshum_fields = (isset($resshums[0])) ? array_keys($resshums[0]) : array();

$resshums = CHDResshum::addAllYears($resshums, $annees, $resshum_fields);

// CHDFinance
$finance                      = new CHDFinance();
$finance->hd_etablissement_id = $etab_id;
$finances                     = array();

/** @var CHDFinance $_finance */
foreach ($finance->loadMatchingListEsc('annee DESC') as $_finance) {
  $finances[] = $_finance->getDisplayFields();
}
$finance_fields = (isset($finances[0])) ? array_keys($finances[0]) : array();

$finances = CHDFinance::addAllYears($finances, $annees, $finance_fields);

// Carte d'identité
$identite                      = new CHDIdentite();
$identite->hd_etablissement_id = $etab_id;
$identites_volumetrie          = array();
$identites_interv              = array();
$identites_acts                = array();
$identites_infras              = array();
$identites_infos               = array();
$identites_finances            = array();
$identites_rhs                 = array();

/** @var CHDIdentite $_identite */
foreach ($identite->loadMatchingListEsc('annee DESC') as $_identite) {
  $fields                 = $_identite->getDisplayFields();
  $identites_volumetrie[] = $fields['volumetrie'];
  $identites_interv[]     = $fields['interv'];
  $identites_acts[]       = $fields['actes'];
  $identites_infras[]     = $fields['infrastructure'];
  $identites_infos[]      = $fields['informatisation'];
  $identites_finances[]   = $fields['finances'];
  $identites_rhs[]        = $fields['rh'];
}

$identites_volumetrie_fields = (isset($identites_volumetrie[0])) ? array_keys($identites_volumetrie[0]) : array();
$identites_interv_fields     = (isset($identites_interv[0])) ? array_keys($identites_interv[0]) : array();
$identites_acts_fields       = (isset($identites_acts[0])) ? array_keys($identites_acts[0]) : array();
$identites_infras_fields     = (isset($identites_infras[0])) ? array_keys($identites_infras[0]) : array();
$identites_infos_fields      = (isset($identites_infos[0])) ? array_keys($identites_infos[0]) : array();
$identites_finances_fields   = (isset($identites_finances[0])) ? array_keys($identites_finances[0]) : array();
$identites_rhs_fields        = (isset($identites_rhs[0])) ? array_keys($identites_rhs[0]) : array();

$identites_volumetrie = CHDIdentite::addAllYears($identites_volumetrie, $annees, $identites_volumetrie_fields);
$identites_interv       = CHDIdentite::addAllYears($identites_interv, $annees, $identites_interv_fields);
$identites_acts       = CHDIdentite::addAllYears($identites_acts, $annees, $identites_acts_fields);
$identites_infras     = CHDIdentite::addAllYears($identites_infras, $annees, $identites_infras_fields);
$identites_infos      = CHDIdentite::addAllYears($identites_infos, $annees, $identites_infos_fields);
$identites_finances   = CHDIdentite::addAllYears($identites_finances, $annees, $identites_finances_fields);
$identites_rhs        = CHDIdentite::addAllYears($identites_rhs, $annees, $identites_rhs_fields);

$smarty = new CSmartyDP();
$smarty->assign('etab', $etab);
$smarty->assign('activite_fields', $activite_fields);
$smarty->assign('activites', $activites);
$smarty->assign('activites_pages', CHDActivite::$field_page);
$smarty->assign('activite_zone_fields', $activite_zone_fields);
$smarty->assign('activites_zone', $activites_zone);
$smarty->assign('activites_zone_pages', CHDActiviteZone::$field_page);
$smarty->assign('qualite_fields', $qualite_fields);
$smarty->assign('qualites', $qualites);
$smarty->assign('qualites_pages', CHDQualite::$field_page);
$smarty->assign('orga_fields', $orga_fields);
$smarty->assign('orgas', $orgas);
$smarty->assign('orgas_pages', CHDProcess::$field_page);
$smarty->assign('resshum_fields', $resshum_fields);
$smarty->assign('resshums', $resshums);
$smarty->assign('resshums_pages', CHDResshum::$field_page);
$smarty->assign('finance_fields', $finance_fields);
$smarty->assign('finances', $finances);
$smarty->assign('finances_pages', CHDFinance::$field_page);

// Identité
$smarty->assign('identites_volumetrie', $identites_volumetrie);
$smarty->assign('identites_volumetrie_fields', $identites_volumetrie_fields);
$smarty->assign('identites_interv', $identites_interv);
$smarty->assign('identites_interv_fields', $identites_interv_fields);
$smarty->assign('identites_acts', $identites_acts);
$smarty->assign('identites_acts_fields', $identites_acts_fields);
$smarty->assign('identites_infras', $identites_infras);
$smarty->assign('identites_infras_fields', $identites_infras_fields);
$smarty->assign('identites_infos', $identites_infos);
$smarty->assign('identites_infos_fields', $identites_infos_fields);
$smarty->assign('identites_finances', $identites_finances);
$smarty->assign('identites_finances_fields', $identites_finances_fields);
$smarty->assign('identites_rhs', $identites_rhs);
$smarty->assign('identites_rhs_fields', $identites_rhs_fields);
$smarty->assign('identite_pages', CHDIdentite::$field_page);
$smarty->display('vw_etab_details.tpl');