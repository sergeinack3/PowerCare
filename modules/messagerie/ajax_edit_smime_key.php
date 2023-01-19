<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CSMimeKey;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkEdit();

$source_id = CView::get('source_id', 'num');

CView::checkin();

/** @var CSourcePOP $source */
$source = CMbObject::loadFromGuid("CSourcePOP-$source_id");

/** @var CSMimeKey $s_mime_key */
$s_mime_key = $source->loadUniqueBackRef('smime_key');

if (!$s_mime_key->_id) {
  $s_mime_key->source_id = $source->_id;
}

$s_mime_key->isCertificateSet();

$smarty = new CSmartyDP();
$smarty->assign('smime_key', $s_mime_key);
$smarty->display('inc_edit_smime_key.tpl');