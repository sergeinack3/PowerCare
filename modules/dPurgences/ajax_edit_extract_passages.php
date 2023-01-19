<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Urgences\CExtractPassages;

CCanDo::checkAdmin();

$extract_passages_id = CView::get('extract_passages_id', 'ref class|CExtractPassages');
CView::checkin();

$passages = (new CExtractPassages())->load($extract_passages_id);
if (!$passages || !$passages->extract_passages_id) {
    CAppUI::stepAjax('CExtractPassages.none', UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign('passages', $passages);
$smarty->display('inc_edit_extract_passages');
