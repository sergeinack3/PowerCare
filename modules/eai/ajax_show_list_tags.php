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
use Ox\Core\CValue;

CCanDo::checkAdmin();

$object_class = CValue::get("object_class");

$ds = CSQLDataSource::get("std");

$where = array(
  "object_class" => "= '$object_class'"
);

// Liste des tags pour un object_class
$req = new CRequest;
$req->addTable("id_sante400");
$req->addColumn("tag");
$req->addWhere($where);
$req->addGroup("tag");

$tags = CMbArray::pluck($ds->loadList($req->makeSelect()), "tag");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("tags", $tags);
$smarty->display("inc_show_list_tags.tpl");
