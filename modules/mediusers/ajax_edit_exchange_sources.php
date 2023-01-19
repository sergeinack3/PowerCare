<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourcePOP;
use Ox\Mediboard\System\CSourceSMTP;

/**
 * Edit user exchange sources
 */
$mediuser = CMediusers::get();

// Source SMTP
$smtp_source = CExchangeSource::get("mediuser-".$mediuser->_id, CSourceSMTP::TYPE, true, null, false);

// Source POP
$pop_sources = $mediuser->loadRefsSourcePop();
// Dans le cas où l'on aucune source POP on va en créer une vide
$new_source_pop = new CSourcePOP();
$new_source_pop->object_class = $mediuser->_class;
$new_source_pop->object_id    = $mediuser->_id;
$new_source_pop->name = "SourcePOP-".$mediuser->_id.'-'.($new_source_pop->countMatchingList()+1);

// Source FTP
$archiving_source = CExchangeSource::get("archiving-".$mediuser->_guid, CSourceFTP::TYPE, true, null, false);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("smtp_sources"      , array($smtp_source));
$smarty->assign("archiving_sources" , array($archiving_source));
$smarty->assign("pop_sources"       , $pop_sources);
$smarty->assign("new_source_pop"    , $new_source_pop);

$smarty->display("inc_edit_exchange_sources.tpl");
