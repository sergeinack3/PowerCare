<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use DOMDocument;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Interop\Hl7\CHL7v2;
use Ox\Interop\Hl7\CHL7v2DataType;
use Ox\Interop\Hl7\CHL7v2DOMDocument;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHMessage;

/**
 * Class CHPrimSanteMessage
 * Message hprim sante
 */
class CHPrimSanteMessage extends CHMessage {
  static $header_segment_name    = "H";
  static $segment_header_pattern = "H|P|A|C|L|OBR|OBX|FAC|ACT|REG|AP|AC|ERR";

  static $versions = array(
    "H2.1",
    "H2.2",
    "H2.3",
    "H2.4",
    "H2.5",
  );

  protected $keep_original = array("H.1");

  public $version = "2.1";
  public $type;
  public $extension;
  public $type_liaison;

  /**
   * @inheritdoc
   */
  static function getVersions() {
    return self::$versions;
  }

  /**
   * Get the name of header segment
   *
   * @return string
   */
  function getHeaderSegmentName() {
    return self::$header_segment_name;
  }

  /**
   * Transform to xml
   *
   * @param String $event_code    code event
   * @param bool   $hpr_datatypes data type
   * @param string $encoding      encoding
   *
   * @return DOMDocument
   */
  function toXML($event_code = null, $hpr_datatypes = true, $encoding = "utf-8") {
    $name = $this->getXMLName();

    $dom = CHPrimSanteMessageXML::getEventType($event_code);

    $root = $dom->addElement($dom, $name);
    $dom->addNameSpaces($name);

    return $this->_toXML($root, $hpr_datatypes, $encoding);
  }

  /**
   * Return the message data in array format
   *
   * @return array
   */
  public function toArray() {
    $message = new CHprimSanteMessageView($this->toXML());

    return $message->toArray();
  }

  /**
   * Return the message data in HTML (for the laboratory results)
   *
   * @return string
   */
  public function toHTML() {
    $message = new CHprimSanteMessageView($this->toXML());

    return $message->toHTML();
  }

  /**
   * Get the name xml
   *
   * @return String
   */
  function getXMLName(){
    return $this->children[0]->fields[6]->data;
  }

  /**
   * Check the formed
   *
   * @param String $data                      data
   * @param bool   $strict_segment_terminator segment terminator strict
   *
   * @return bool
   * @throws CHL7v2Exception
   */
  static function isWellFormed($data, $strict_segment_terminator = false) {
    // remove all chars before H
    $h_pos = strpos($data, self::$header_segment_name);

    if ($h_pos === false) {
      throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $data);
    }

    $data = substr($data, $h_pos);
    $data = self::fixRawER7($data, $strict_segment_terminator);

    // first tokenize the segments
    if (($data == null) || (strlen($data) < 4)) {
      throw new CHL7v2Exception(CHL7v2Exception::EMPTY_MESSAGE, $data);
    }

    $fieldSeparator = $data[1];

    // valid separator
    if (!preg_match("/[^a-z0-9]/i", $fieldSeparator) ) {
      throw new CHL7v2Exception(CHL7v2Exception::INVALID_SEPARATOR, substr($data, 0, 10));
    }

    $lines = self::split(self::DEFAULT_SEGMENT_TERMINATOR, $data);

    // validation de la syntaxe : chaque ligne doit commencer par 1 lettre + un separateur + au moins une donnée
    $sep_preg = preg_quote($fieldSeparator);

    $pattern = self::$segment_header_pattern;

    foreach ($lines as $_line) {
      if (!preg_match("/^($pattern)$sep_preg/", $_line)) {
        throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $_line);
      }
    }

    return true;
  }

  /**
   * Load the data type
   *
   * @param String $datatype data type
   *
   * @return CHL7v2DataType|void
   */
  function loadDataType($datatype) {
    return CHPrimSanteDataType::load($this, $datatype, $this->getVersion(), $this->type);
  }

  /**
   * Parse the mdata
   *
   * @param string $data       data
   * @param bool   $parse_body parse the body
   *
   * @throws CHL7v2Exception
   *
   * @return void
   */
  function parse($data, $parse_body = true) {
    try {
      self::isWellFormed($data, $this->strict_segment_terminator);
    }
    catch (CHL7v2Exception $e) {
      $this->error($e->getMessage(), $e->extraData);
      //return false;
    }

    // remove all chars before H
    $h_pos = strpos($data, self::$header_segment_name);

    if ($h_pos === false) {
      throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $data);
    }

    $data = substr($data, $h_pos);
    $data = self::fixRawER7($data, $this->strict_segment_terminator);

    // handle "A" segments
    $field_sep = preg_quote($this->fieldSeparator);
    $patt = "/[\r\n]+A$field_sep([^\r\n]+)/s";
    $data = preg_replace($patt, "\\1", $data);

    // remove "C" segments
    $patt = "/[\r\n]+C$field_sep([^\r\n]+)/s";
    $data = preg_replace($patt, "", $data);

    parent::parse($data);

    $message = $this->data;

    // 2 to 5
    if (!isset($message[5])) {
      throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $message);
    }

    $this->fieldSeparator = $message[1];

    $nextDelimiter = strpos($message, $this->fieldSeparator, 2);
    if ($nextDelimiter > 4) {
      // usually ^
      $this->componentSeparator = $message[2];
    }
    if ($nextDelimiter > 3) {
      // usually ~
      $this->repetitionSeparator = $message[3];
    }
    if ($nextDelimiter > 4) {
      // usually \
      $this->escapeCharacter = $message[4];
    }
    if ($nextDelimiter > 5) {
      // usually &
      $this->subcomponentSeparator = $message[5];
    }

    // replace the special case of ^~& with ^~\&
    if ("^~&|" == substr($message, 2, 4)) {
      $this->escapeCharacter       = "\\";
      $this->subcomponentSeparator = "&";
      $this->repetitionSeparator   = "~";
      $this->componentSeparator    = "^";
    }

    $this->initEscapeSequences();

    $this->lines = CHL7v2::split($this->segmentTerminator, $this->data);

    // we extract the first line info "by hand"
    $first_line = CHL7v2::split($this->fieldSeparator, reset($this->lines));

    if (!isset($first_line[12])) {
      throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $message);
    }

    // version
    $this->parseRawVersion($first_line[12]);

    // message type
    $message_type = explode($this->componentSeparator, $first_line[6]);

    $this->name  = $message_type[0];
    $this->event_name = $message_type[0];

    if (!$spec = $this->getSpecs()) {
      throw new CHL7v2Exception(CHL7v2Exception::UNKNOWN_MSG_CODE);
    }

    $this->description = $spec->queryTextNode("description");

    $this->readHeader();

    // type liaison
    //$type_liaison

    if ($parse_body) {
      $this->readSegments();
    }
  }

  /**
   * Parse the raw version
   *
   * @param String $raw          raw message
   * @param String $country_code country code
   *
   * @return void
   */
  private function parseRawVersion($raw, $country_code = null){
    $parts = explode($this->componentSeparator, $raw);

    CMbArray::removeValue("", $parts);

    $this->version = $parts[0];

    // Version spécifique française spécifiée
    if (count($parts) > 1) {
      $this->type = $parts[1];
    }

    // Dans le cas où la version passée est incorrecte on met par défaut 2.3
    if (!in_array($this->version, self::$versions)) {
      $this->version = CAppUI::conf("hprimsante default_version");
    }
  }

  /**
   * Return the schema
   *
   * @param String $type type
   * @param String $name name
   *
   * @return CHL7v2DOMDocument
   */
  function getSchema($type, $name) {
    $version = $this->getVersion();

    if (isset(self::$schemas[$version][$type][$name][$this->type])) {
      return clone self::$schemas[$version][$type][$name][$this->type];
    }

    if (!in_array($version, self::$versions)) {
      $this->error(CHL7v2Exception::VERSION_UNKNOWN, $version);
    }

    // TODO $type_liaison
    $version     = strtoupper($version);
    $version_dir = preg_replace("/[^H0-9]/", "_", $version);
    $name_dir    = preg_replace("/[^A-Z0-9_]/", "", $name);

    $this->spec_filename = __DIR__."/../../../modules/hprimsante/resources/$version_dir/$type$name_dir.xml";

    if (!file_exists($this->spec_filename)) {
      // on a déjà l'erreur sur le type de segment inconnu
      //$this->error(CHL7v2Exception::SPECS_FILE_MISSING, $this->spec_filename);
      return;
    }

    $schema = new CHL7v2DOMDocument();
    $schema->registerNodeClass('DOMElement', 'CHL7v2DOMElement');
    $schema->load($this->spec_filename);

    self::$schemas[$version][$type][$name][$this->type] = $schema;

    return $this->specs = $schema;
  }

  /**
   * Highlight the message
   *
   * @param String $msg message
   *
   * @return string
   */
  static function highlight($msg){
    $msg = str_replace("\r", "\n", $msg);

    $prefix = self::$header_segment_name;
    preg_match("/^[^$prefix]*$prefix(.)(.)(.)(.)(.)/", $msg, $matches);

    // highlight segment name
    $pattern = self::$segment_header_pattern;
    $msg = preg_replace("/^($pattern)/m", '<strong>$1</strong>', $msg);
    $msg = preg_replace("/^(.*)/m", '<div class="segment">$1</div>', $msg); // we assume $message->segmentTerminator is always \n
    $msg = str_replace("\n", "", $msg);

    $pat = array(
      $matches[1] => "<span class='fs'>$matches[1]</span>",
      $matches[2] => "<span class='cs'>$matches[2]</span>",
      $matches[3] => "<span class='scs'>$matches[3]</span>",
      $matches[4] => "<span class='re'>$matches[4]</span>",
    );

    return "<pre class='er7'>".strtr($msg, $pat)."</pre>";
  }

  /**
   * Flatten
   *
   * @param bool $highlight highlight
   *
   * @return mixed|string
   */
  function flatten($highlight = false){
    $string = parent::flatten($highlight);

    if ($highlight) {
      return $string;
    }

    $lines = preg_split("/[\r\n]+/", $string);

    $lines_after = array();

    $max_length = 210;
    // 200 pour être "large" (220 normalement)

    foreach ($lines as $_line) {
      if (strlen($_line) < $max_length) {
        $lines_after[] = $_line;
        continue;
      }

      $pos = strpos($_line, $this->fieldSeparator, $max_length-1);
      if ($pos === false) {
        $pos = $max_length;
      }
      $lines_after[] = substr($_line, 0, $pos);
      while (strlen($_line) > $max_length) {
        $_line = substr($_line, $pos);
        $lines_after[] = "A{$this->fieldSeparator}".$_line;
        $length = min($max_length, strlen($_line));
        $pos = strpos($_line, $this->fieldSeparator, $length-1);
        if ($pos === false) {
          $pos = min($max_length, strlen($_line));
        }
      }
    }

    return implode("\r\n", $lines_after);
  }
}
