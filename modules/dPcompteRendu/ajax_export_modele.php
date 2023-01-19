<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CListeChoix;

CCanDo::checkRead();

$modele_id = CValue::get("modele_id");

$modele = new CCompteRendu();
$modele->load($modele_id);
$modele->loadContent(true);

$doc = new CMbXMLDocument(null);

$root = $doc->createElement("CCompteRendu");

$doc->appendChild($root);

foreach ($modele->getProps() as $_field => $_spec) {
  if (in_array($_field, CCompteRendu::$fields_exclude_export)
      || $modele->$_field === null
      || ($_field[0] === "_" && $_field !== "_source")
  ) {
    continue;
  }
  ${$_field} = $doc->createElement($_field);
  $textnode = $doc->createTextNode(utf8_encode($modele->$_field));
  ${$_field}->appendChild($textnode);
  $root->appendChild(${$_field});
}

// Attribut modele_id
$key = $doc->createAttribute("modele_id");
$value = $doc->createTextNode($modele->_id);
$key->appendChild($value);
$root->appendChild($key);

// Catégorie
$cat = $modele->loadRefCategory();
$key = $doc->createAttribute("cat");
$value = $doc->createTextNode(utf8_encode($cat->nom));
$key->appendChild($value);
$root->appendChild($key);

// Listes de choix
$listes = $doc->createElement("listes");

preg_match_all("/\[Liste - ([^\]]+)\]/", $modele->_source, $matches);

if (count($matches[1])) {
  $modele->loadRefUser()->loadRefFunction();
  $modele->loadRefFunction();
  $modele->loadRefGroup();

  foreach ($matches[1] as $_match) {
    $liste_choix = new CListeChoix();
    $where = array(
      "nom" => "= '" . html_entity_decode($_match, ENT_COMPAT) . "'"
    );

    switch ($modele->_owner) {
      default:
      case "prat":
        $where[] = "liste_choix.user_id     =     '$modele->user_id' OR
                    liste_choix.function_id = '" . $modele->_ref_user->function_id . "' OR
                    liste_choix.group_id    = '" . $modele->_ref_user->_ref_function->group_id . "'";
        break;
      case "func":
        $where[] = "liste_choix.function_id =     '$modele->function_id' OR
                    liste_choix.group_id    = '" . $modele->_ref_function->group_id . "'";
        break;
      case "etab":
        $where[] = "liste_choix.group_id = '" . $modele->group_id . "'";
    }

    if ($liste_choix->loadObject($where)) {
      $liste = $doc->createElement("liste");
      $nom = $doc->createAttribute("nom");
      $value = $doc->createTextNode(utf8_encode($_match));
      $nom->appendChild($value);
      $liste->appendChild($nom);

      foreach ($liste_choix->_valeurs as $_valeur) {
        $choix = $doc->createElement("choix");
        $value = $doc->createTextNode(utf8_encode($_valeur));
        $choix->appendChild($value);
        $liste->appendChild($choix);
      }

      $listes->appendChild($liste);
    }
  }
}

$root->appendChild($listes);

$content = $doc->saveXML();

header('Content-Type: text/xml');
header('Content-Disposition: inline; filename="'.$modele->nom.'.xml"');
header('Content-Length: '.strlen($content).';');

echo $content;
