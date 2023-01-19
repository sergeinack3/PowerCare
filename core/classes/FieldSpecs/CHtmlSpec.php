<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use DOMDocument;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CModelObject;

/**
 * HTML string
 */
class CHtmlSpec extends CMbFieldSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "html";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    return "MEDIUMTEXT";
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    return $object->{$this->fieldName};
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object){
    $propValue = $object->{$this->fieldName};
    
    // Root node surrounding
    $source = utf8_encode("<div>$propValue</div>");

    //for external html content => no validation
    if (stripos($source, "<html") !== false) {
      return null;
    }

    // Entity purge
    $source = preg_replace("/&\w+;/i", "", $source);

    $dom = new DOMDocument();
    // Escape warnings, returns false if really invalid
    if (!@$dom->loadXML($source)) {
      CModelObject::warning("Error-Html-document-bad-formatted");
      return "Le document HTML est mal formé, ou la requête n'a pas pu se terminer.";
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true){
    parent::sample($object, $consistent);
    $object->{$this->fieldName} = <<<EOD
<h1>Titre 1</h1>
<p>Paragraphe</p>
<ul>
  <li>Item 1</li>
  <li>Item 2</li>
  <li>Item 3</li>
</ul>
EOD;
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className){
    return $this->getFormElementTextarea($object, $params, $value, $className);
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string
  {
    return "Un texte formaté au format html. " . parent::getLitteralDescription();
  }
}
