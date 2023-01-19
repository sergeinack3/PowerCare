<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Needle
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

$keyword = reset($_POST);
$needle  = "%$keyword%";

CView::enableSlave();

// Query
$select = "SELECT CODE, LIBELLE FROM categorie_socioprofessionnelle";
$where  = "WHERE LIBELLE LIKE '$needle'";
$order  = "ORDER BY CODE";
$query  = "$select $where $order";

$ds      = CSQLDataSource::get("INSEE");
$matches = $ds->loadList($query);

// Template
$smarty = new CSmartyDP();

$smarty->assign("keyword", $keyword);
$smarty->assign("matches", $matches);
$smarty->assign("nodebug", true);

$smarty->display("inc_csp_autocomplete.tpl");