<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;

CCanDo::checkRead();

$doc_id = CView::get("doc_id", "ref class|CCompteRendu");
$only_body = CView::get("only_body", "bool default|0");

CView::checkin();

$doc = new CCompteRendu();
$doc->load($doc_id);

$doc->loadContent();

$source = $doc->_source;

$div_body = "<div id=\"body\">";

if ($only_body) {
    $pos_body = strpos($source, $div_body);

    if ($pos_body != false) {
        $source = substr($source, $pos_body + strlen($div_body), -6);
    }
}

echo json_encode(utf8_encode($source));
