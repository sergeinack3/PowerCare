<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\CompteRendu\CCompteRendu;

CCanDo::checkAdmin();

$request = "
SELECT compte_rendu.compte_rendu_id
FROM content_html
LEFT JOIN compte_rendu ON compte_rendu.content_id = content_html.content_id
WHERE compte_rendu.object_id IS NULL
AND compte_rendu.compte_rendu_id IS NOT NULL
AND content RLIKE '<table[ a-z0-9%=:;\"]+align='
";

$compte_rendu_ids = CSQLDataSource::get("std")->loadColumn($request);

// Total
$count = count($compte_rendu_ids);

// Compte-rendus à traiter
$list = array_slice($compte_rendu_ids, 0, 30);
$compte_rendu = new CCompteRendu();
$compte_rendus = $compte_rendu->loadList(array("compte_rendu_id" => CSQLDataSource::prepareIn($list)));

CStoredObject::massLoadFwdRef($compte_rendus, "content_id");

foreach ($compte_rendus as $_compte_rendu) {
  $_compte_rendu->loadContent();
  $_compte_rendu->loadRefUser()->loadRefFunction();
  $_compte_rendu->loadRefFunction();
  $_compte_rendu->loadRefGroup();
}

$smarty = new CSmartyDP();

$smarty->assign("compte_rendus", $compte_rendus);
$smarty->assign("count"        , $count);

$smarty->display("vw_correct_aligns");