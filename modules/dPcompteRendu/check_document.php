<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;

/**
 * Vérification d'une source html
 */
CCanDo::checkRead();

$doc   = new CCompteRendu();
$where = [
    //  "compte_rendu_id" => "= '47325'"
];

CApp::setTimeLimit(300);

$loops = CValue::get("loops", 100);
$trunk = CValue::get("trunk", 100);

CApp::log("loops", $loops);
CApp::log("trunk", $trunk);

$problems = [];
for ($loop = 0; $loop < $loops; $loop++) {
    $starting = $loop * $trunk;
    $ds       = $doc->_spec->ds;

    $query = "SELECT `compte_rendu`.`compte_rendu_id`, `contenthtml`.`content` 
    FROM compte_rendu, contenthtml
    WHERE compte_rendu.content_id = contenthtml.content_id
    ORDER BY compte_rendu_id DESC
    LIMIT $starting, $trunk";
    $docs  = $ds->loadHashList($query);
    foreach ($docs as $doc_id => $doc_source) {
        // Root node surrounding
        $source = utf8_encode("<div>$doc_source</div>");

        // Entity purge
        $source = preg_replace("/&\w+;/i", "", $source);

        // Escape warnings, returns false if really invalid
        $doc = new CMbXMLDocument();
        if (false == $validation = $doc->loadXML($source)) {
            $doc = new CCompteRendu();
            $doc->load($doc_id);
            $problems[$doc_id] = $doc;
        }
    }
}

CApp::log("Problems count", count($problems));

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("problems", $problems);

$smarty->display("check_document");
