<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$content = CView::post('message', 'str');
$encode  = CView::post('encode', 'bool');
CView::checkin();

$content_modify = $encode ? base64_encode($content) : base64_decode($content);

$xml = null;
if ($encode == 0) {
    $xml = simplexml_load_string($content_modify);

    if ($xml) {
        $dom = new CMbXMLDocument();
        $dom->loadXML($content_modify);
        $dom->formatOutput = true;
        $content_modify = $dom->saveXML();
    }
}

$smarty = new CSmartyDP();
$smarty->assign('content_modify', $content_modify);
$smarty->display('vw_manage_base64');
