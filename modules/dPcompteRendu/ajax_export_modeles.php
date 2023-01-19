<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

CCanDo::checkRead();

$modeles_ids  = CView::post("modeles_ids", "str");
$object_class = CView::post("object_class", "str");
$owner        = CView::post("owner", "str");

CView::checkin();

$modeles_ids = explode("-", $modeles_ids);

CMbArray::removeValue(array(), $modeles_ids);

if (!count($modeles_ids)) {
  CAppUI::stepMessage(UI_MSG_WARNING, "Aucun modèle à exporter");
  CApp::rip();
}

$doc = new CMbXMLDocument(null);
$root = $doc->createElement("modeles");
$doc->appendChild($root);

$where = array("compte_rendu_id" => CSQLDataSource::prepareIn($modeles_ids));

// Récupération des header_id, footer_id, preface_id et ending_id
$ds = CSQLDataSource::get("std");

$request = new CRequest();
$request->addTable("compte_rendu");
$request->addWhere($where);

$components_ids = array();

foreach (array("header_id", "footer_id", "preface_id", "ending_id") as $_component) {
  $request->select = array();
  $request->addSelect($_component);
  $components_ids = array_merge($components_ids, $ds->loadColumn($request->makeSelect()));
}

$modeles_ids = array_unique(array_merge($components_ids, $modeles_ids));
CMbArray::removeValue("", $modeles_ids);

foreach ($modeles_ids as $_modele_id) {
  $modele = CApp::fetch("dPcompteRendu", "ajax_export_modele", array("modele_id" => $_modele_id));

  $doc_modele = new CMbXMLDocument(null);
  @$doc_modele->loadXML($modele);

  // Importation du noeud CPrescription
  $modele_importe = $doc->importNode($doc_modele->firstChild, true);

  // Ajout de ce noeud comme fils de protocoles
  $doc->documentElement->appendChild($modele_importe);
}

$filename = 'Modèles '. ($owner ? " - $owner" : '') . ($object_class ? " - ".CAppUI::tr($object_class) : '') . '.xml';

$content = $doc->saveXML();
header('Content-Type: text/xml');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.strlen($content).';');

echo $content;
