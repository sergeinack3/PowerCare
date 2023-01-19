<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CAppUI;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;
use Ox\Core\CValue;

/**
 * Long string
 */
class CTextSpec extends CMbFieldSpec {
  public $markdown;

  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "text";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    return "TEXT";
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object) {
    return null;
  }

  /**
   * @inheritdoc
   */
  function getHtmlValue($object, $params = array()) {
    $value = $object->{$this->fieldName};

    // Empty value: no paragraph
    if (!$value) {
      return "";
    }

    // Markdown case: full delegation
    if ($this->markdown) {
      // In order to prevent from double escaping
      $content = CMbString::markdown(CMbString::htmlSpecialChars(html_entity_decode($value)));
      return "<div class='markdown'>$content</div>";
    }

    // Truncate case: no breakers but inline bullets instead
    if ($truncate = CValue::read($params, "truncate")) {
      $value = CMbString::truncate($value, $truncate === true ? null : $truncate);
      $value = CMbString::htmlSpecialChars($value);
      return CMbString::nl2bull($value);
    }

    // Standard case: breakers and paragraph enhancers
    $text = "";
    $value = str_replace(array("\r\n", "\r"), "\n", $value);

    // For forms do not create a <p> tag to avoid the creation of new lines
    if (isset($params['no_paragraph'])) {
      $text = nl2br(CMbString::htmlSpecialChars($value));
    }
    else {
      $paragraphs = preg_split("/\n{2,}/", $value);
      foreach ($paragraphs as $_paragraph) {
        if (!empty($_paragraph)) {
          $_paragraph = nl2br(CMbString::htmlSpecialChars($_paragraph));
          $text .= "<p>$_paragraph</p>";
        }
      }
    }

    return $text;
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true) {
    parent::sample($object, $consistent);
    $chars = array_merge(CMbFieldSpec::$chars, array(' ', ' ', ', ', '. '));
    $object->{$this->fieldName} = self::randomString($chars, 200);
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className) {
    return $this->getFormElementTextarea($object, $params, $value, $className);
  }

  /**
   * @inheritdoc
   */
  function filter($value) {
    if (CAppUI::conf("purify_text_input")) {
      $value = CMbString::purifyHTML($value);
    }
    return parent::filter($value);
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string {
    return "Texte long. " . parent::getLitteralDescription();
  }
}
