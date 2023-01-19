<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Interop\Eai\CDomain;

/**
 * Add domain with idex EAI
 */
CCanDo::checkAdmin();

$domain = new CDomain();

// Récupération des objet_class
$req = new CRequest;
$req->addTable("id_sante400");
$req->addColumn("object_class");
$req->addGroup("object_class");

$ds = CSQLDataSource::get("std");
$idexs_class = CMbArray::pluck($ds->loadList($req->makeSelect()), "object_class");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domain"     , $domain);
$smarty->assign("idexs_class", $idexs_class);
$smarty->display("inc_add_domain_with_idex.tpl");
