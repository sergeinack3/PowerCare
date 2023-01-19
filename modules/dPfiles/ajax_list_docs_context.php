<?php 
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CContextDoc;

CCanDo::checkEdit();

$context_doc_id = CView::get("context_doc_id", "ref class|CContextDoc");

CView::checkin();

$context_doc = new CContextDoc();
$context_doc->load($context_doc_id);

$context_doc->loadRefsDocs();

$smarty = new CSmartyDP();

$smarty->assign("context_doc", $context_doc);

$smarty->display("inc_list_docs_context");