<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Labo\CCatalogueLabo;
use Ox\Mediboard\Labo\CExamenLabo;

$listExams = array();

// Récuperation du mot recherché
$recherche = CValue::get("recherche");

// Chargements des analyses correspondantes
$exam = new CExamenLabo();
$limit = "30";
$where["libelle"] = "LIKE '%$recherche%' ";
$where["obsolete"] = " = '0'";
$listExams = $exam->loadList($where, null, $limit);
$countExams = $exam->countList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("search"    , 1           );
$smarty->assign("recherche" , $recherche  );
$smarty->assign("listExams" , $listExams  );
$smarty->assign("countExams", $countExams );
$smarty->assign("catalogue" , new CCatalogueLabo());

$smarty->display("inc_vw_examens_catalogues");
