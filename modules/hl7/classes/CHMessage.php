<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use DOMDocument;
use DOMNode;
use Ox\Core\CAppUI;
use Ox\Core\CValue;

/**
 * Class CHL7v2Message
 * Message HL7
 */
abstract class CHMessage extends CHL7v2SegmentGroup
{
    const DEFAULT_SEGMENT_TERMINATOR = "\r";
    const DEFAULT_ESCAPE_CHARACTER        = "\\";
    const DEFAULT_FIELD_SEPARATOR         = "|";
    const DEFAULT_COMPONENT_SEPARATOR     = "^";
    const DEFAULT_REPETITION_SEPARATOR    = "~";
    const DEFAULT_SUBCOMPONENT_SEPARATOR  = "&";
    const DEFAULT_NULL_VALUE              = '""';

    static $decorateToString = false;

    public $segmentTerminator     = self::DEFAULT_SEGMENT_TERMINATOR;
    public $escapeCharacter       = self::DEFAULT_ESCAPE_CHARACTER;
    public $fieldSeparator        = self::DEFAULT_FIELD_SEPARATOR;
    public $componentSeparator    = self::DEFAULT_COMPONENT_SEPARATOR;
    public $repetitionSeparator   = self::DEFAULT_REPETITION_SEPARATOR;
    public $subcomponentSeparator = self::DEFAULT_SUBCOMPONENT_SEPARATOR;
    public $nullValue             = self::DEFAULT_NULL_VALUE;

    public $actor;

    /**
     * @var array Fields that must be kept as is
     */
    protected $keep_original = [];

    public $strict_segment_terminator = false;

    public $escape_sequences;
    public $unescape_sequences;

    public $version;
    public $event_name;
    public $name;
    public $description;
    public $lines          = array();
    public $current_line   = 0;
    public $errors         = array();
    public $ignored_fields = array();

    /**
     * CHMessage constructor.
     *
     * @param string $version Version string
     */
    function __construct($version = null) {
        $this->version = $version;
    }

    /**
     * Get XML node name
     *
     * @return string
     */
    function getXMLName(){
        // inherit
        return "";
    }

    /**
     * Get the fields that must be kept original
     *
     * @return array
     */
    function getKeepOriginal(){
        return $this->keep_original;
    }

    /**
     * @return DOMDocument
     */
    function _toXML(DOMNode $node, $datatypes, $encoding) {
        $doc = $node->ownerDocument;

        foreach ($this->children as $_child) {
            $_child->_toXML($node, $datatypes, $encoding);
        }

        return $doc;
    }

    /**
     * Fix raw ER7 message
     *
     * @param string $data   Raw ER7 message
     * @param bool   $strict Strict on the segment terminators
     *
     * @return string
     */
    static function fixRawER7($data, $strict = false) {
        /*$segments = array(
          "MSH|^~\&|Mediboard|Mediboard|Simulator||20111125114827||ADT^A04^ADT_A01|597|D|2.5^FR^2.3|||AL|||8859/1|||",
          "EVN|A04|20111125114827|||121^FOO^Bar^^^^^^Mediboard&1.2.250.1.2.3.4&OX^L^^^RI^^^^^^^^^^||",
          "PID|1||323276^^^Mediboard&1.2.250.1.2.3.4&OX^RI||TRAVA^Ox^^^m^^L^A|||M|||4 rue "."\r".
            "test^^Baz^^17000^^H|||||||||||||||||||N||VALI|20111125114827||||||",
        );
        $data = implode("\r\n", $segments);

        if (preg_match("/\r\n[A-Z]{3}[^A-z0-9]/", $data)) {
        }
        if (preg_match("/\r[A-Z]{3}[^A-z0-9]/", $data)) {
        }
        if (preg_match("/[^\r]\n[A-Z]{3}[^A-z0-9]/", $data)) {
        }
        */

        $data = trim($data);

        if (!$strict) {
            $data = str_replace(array("\r\n", "\n"), "\r", $data);
        }

        return $data;
    }

    /**
     * Read header line and parse it
     *
     * @return void
     */
    function readHeader(){
        $first_line = $this->lines[0];
        $this->current_line++;

        // segment from line's string
        $segment = new CHL7v2Segment($this);
        $segment->parse($first_line);

        // this one will be the first segment
        $this->appendChild($segment);
    }

    /**
     * Get current line header (first chars)
     *
     * @return bool|string
     */
    function getCurrentLineHeader(){
        $line = $this->getCurrentLine();
        return substr($line, 0, strpos($line, $this->fieldSeparator));
    }

    /**
     * Get current line (or another one with the offset) as string
     *
     * @param int $offset Offset from the current line
     *
     * @return string
     */
    function getCurrentLine($offset = 0){
        return CValue::read($this->lines, $this->current_line+$offset, null);
    }

    /**
     * Iterate on the nodes in the spec
     *
     * @param CHL7v2DOMElement $current_node  Current node
     * @param CHL7v2Entity     $current_group Current group
     *
     * @return array
     */
    static function getNext(CHL7v2DOMElement $current_node, CHL7v2Entity $current_group) {
        // On remet les compteurs d'utilisation a zero
        CHL7v2::d("RESET", "green");
        $current_node->reset();

        $next = $current_node->getNextSibling();
        if ($next) {
            CHL7v2::d(" --> Suivant = frere");
            $current_node = $next;
        } else {
            $parent = $current_node->getParent();

            if ($parent && (!$parent->isOpen() || $parent->isEmpty())) {
                CHL7v2::d(" --> Suivant = suivant du parent");
                return self::getNext($parent, $current_group->parent);
            }

            CHL7v2::d(" --> Suivant = parent");
            $current_node = $parent;
            $current_group = $current_group->parent;
        }

        return array($current_node, $current_group);
    }

    /**
     * Handle current line
     *
     * @param CHL7v2DOMElement $current_node  Current node in the spec
     * @param CHL7v2Entity     $current_group Current group in the spec
     *
     * @return void
     */
    function handleLine(CHL7v2DOMElement $current_node, CHL7v2Entity $current_group) {
        // Increment du nb d'occurences
        $current_node->markUsed();

        // On enregistre le segment dans le groupe courant
        $_segment = new CHL7v2Segment($current_group);
        $_segment->parse($this->getCurrentLine());
        $current_group->appendChild($_segment);

        // On avance dans le fichier
        $this->current_line++;
        CHL7v2::d(" --> ### Creation du segment ###, ligne suivante : $this->current_line");
    }

    /**
     * Read the segments of the message
     *
     * @return void
     */
    function readSegments() {
        $specs = $this->getSpecs();

        /**
         * Premier segment/groupe dans le fichier de spec
         *
         * @var CHL7v2DOMElement $current_node
         */
        $current_node = $specs->query("/message/segments/*")->item(0);

        /**
         * Groupe courant dans lequel on va placer les CHL7v2Segment créés
         *
         * @var CHL7v2SegmentGroup $current_group
         */
        $current_group = $this;
        //$lines_count = count($this->lines);

        $n = 100000; // pour eviter les boucles infinies !

        // tant que la ligne actuelle n'est pas vide
        while ($n-- && trim($this->getCurrentLine() ?? '')/* && $current_node && $this->current_line < $lines_count*/) {
            if (!$current_node) {
                if (!$current_group) {
                    $this->error(CHL7v2Exception::UNEXPECTED_SEGMENT, $this->getCurrentLine());
                    break;
                }

                $segment_error       = new CHL7v2Segment($current_group);
                $segment_error->name = $this->getCurrentLineHeader();

                $this->error(CHL7v2Exception::UNEXPECTED_SEGMENT, $this->getCurrentLine(), $segment_error);
                break;
            }

            switch ($current_node->nodeName) {
                // SEGMENT //
                case "segment":
                    CHL7v2::d($current_node->getSegmentHeader()." ".$current_node->state(), "red");

                    $handled = false;
                    if ($this->getCurrentLineHeader() == "") {
                        break 2;
                    }

                    // spec du segment (champs, etc)
                    $seg_schema = $this->getSchema(
                        self::PREFIX_SEGMENT_NAME,
                        $this->getCurrentLineHeader(),
                        $this->getMessage()->extension
                    );

                    if ($seg_schema == false) {
                        $segment_error       = new CHL7v2Segment($current_group);
                        $segment_error->name = $this->getCurrentLineHeader();

                        $this->error(CHL7v2Exception::UNKOWN_SEGMENT_TYPE, $this->getCurrentLine(), $segment_error);
                        break 2;
                    }

                    // Si la spec correspond a la ligne courante
                    if ($this->getCurrentLineHeader() === $current_node->getSegmentHeader()) {
                        $this->handleLine($current_node, $current_group);
                        $current_node->markNotEmpty();
                        $handled = true;
                    } // Segment non requis, on passe au suivant
                    elseif (!$current_node->isRequired()) {
                        CHL7v2::d(" --> Segment non présent et non requis");
                        [$current_node, $current_group] = self::getNext($current_node, $current_group);
                        break;
                    }

                    // Si le segment est requis et que le groupe est ouvert, alors erreur
                    // pas de parent si à la racine (fils de <segments>) : bizarre
                    else {
                        if (!$current_node->getParent() || $current_node->getParent()->isOpen()) {
                            CHL7v2::d(" --> !!!!!!!!!!!!!!!!! Segment non present et groupe requis");
                            $this->error(CHL7v2Exception::SEGMENT_MISSING, $current_node->nodeValue);
                        }
                    }

                    // le segment est multiple, on reste sur lui
                    if ($handled && $current_node->isUnbounded()) {
                        CHL7v2::d(" --> Segment multiple");
                    } // Segment unique : Segment/groupe suivant ou suivant du parent
                    else {
                        CHL7v2::d(" --> Segment unique, passage au suivant");
                        [$current_node, $current_group] = self::getNext($current_node, $current_group);
                    }
                    break;


                // GROUP //
                case "group":
                    CHL7v2::d($current_group->name." ".$current_node->state(), "red");
                    $current_node->markEmpty();

                    if ($current_node->isUnbounded() || !$current_node->isUsed()) {
                        $current_node->setAttribute("mbOpen", 0);

                        CHL7v2::d(" --> Groupe multiple ou pas encore utilisé, on entre dedans");
                        $current_group = new CHL7v2SegmentGroup($current_group, $current_node);

                        $current_node = $current_node->getFirstChild();
                    } else {
                        CHL7v2::d(" --> Groupe utilisé ou pas multiple, on prend le parent ou frere");
                        [$current_node, $current_group] = self::getNext($current_node, $current_group);
                    }

                    break;

                // custom attributes, should never get there
                default:
                    CHL7v2::d($current_node->getName());
                    $current_node = $current_node->getNextSibling();
                    break;
            }
        }

        $this->purgeEmptyGroups();

        // pas forcément utile : mais ceci donne tous les segments dans
        // l'ordre de parcours, comme si on le faisait recursivement
        // $c = $specs->xpath("//segment | //group");
    }

    /*
    function validate() {
      // @todo validate segments sequence
    }
    */

    /**
     * @return string
     */
    function getVersion(){
        return $this->version;
    }

    /**
     * @return CHL7v2DOMDocument
     */
    function getSpecs(){
        return $this->getSchema(self::PREFIX_MESSAGE_NAME, $this->event_name);
    }

    /**
     * Get the spec schema from the XML resources
     *
     * @param string $type Type (message, composite or segment)
     * @param string $name Name of the entity
     *
     * @return CHL7v2DOMDocument|null
     */
    abstract function getSchema($type, $name);

    /**
     * @inheritdoc
     */
    function getMessage() {
        return $this;
    }

    /**
     * Get message encoding
     *
     * @return string
     */
    function getEncoding() {
        return "utf-8";

        /*if (isset($this->children[0]->fields[17]->items[0])) {
          $encoding = CHL7v2TableEntry::mapFrom(211, $this->children[0]->fields[17]->items[0]->data);
        }

        return strtolower($encoding);*/
    }

    /**
     * Get data type object from its string
     *
     * @param string $datatype The datatype as string
     *
     * @return CHL7v2DataType|null
     */
    function loadDataType($datatype) {
        // To inherit
        return null;
    }

    /**
     * Register error
     *
     * @param int          $code    Error code
     * @param string       $data    Data to save in the error
     * @param CHL7v2Entity $entity  Entity
     * @param int          $level   Error level (CHL7v2Error::E_*)
     * @param bool         $ignored Ignore error
     *
     * @return void
     */
    function error($code, $data, $entity = null, $level = CHL7v2Error::E_ERROR, $ignored = false) {
        $error          = new CHL7v2Error;
        $error->line    = $this->current_line+1;
        $error->entity  = $entity;
        $error->code    = $code;
        $error->data    = $data;
        $error->level   = $level;
        $error->ignored = $ignored;

        $this->errors[]  = $error;
    }

    /**
     * Tells if the message is OK an error level
     *
     * @param int $min_level Min error level
     *
     * @return bool
     */
    function isOK($min_level = 0) {
        foreach ($this->errors as $_error) {
            if ($_error->level >= $min_level && !$_error->ignored) {
                return false;
            }
        }

        return true;
    }

    /**
     * Dump errors as string[]
     *
     * @param int $min_level Minimal level to output
     *
     * @return string[]
     */
    function dumpErrors($min_level = 0){
        $errors = array();

        foreach ($this->errors as $_error) {
            if ($_error->level > $min_level) {
                $_code  = CAppUI::tr("CHL7v2Exception-$_error->code");
                $_entity = ($_error->entity ? $_error->entity->getPathString().", " : "");
                $errors[] = "Ligne $_error->line : $_code - $_entity $_error->data";
            }
        }

        return $errors;
    }

    /**
     * Get delimited escape sequence (\XXX\)
     *
     * @param string $seq Sequence to escapce
     *
     * @return string
     */
    private function getDelimEscSeq($seq) {
        return $this->escapeCharacter.$seq.$this->escapeCharacter;
    }

    /**
     * Initialize escape sequences for each terminator
     *
     * @return void
     */
    function initEscapeSequences() {
        if (!empty($this->escape_sequences)) {
            return;
        }

        $delimiters = array(
            $this->segmentTerminator     => "X0D",
            $this->fieldSeparator        => "F",
            $this->componentSeparator    => "S",
            $this->subcomponentSeparator => "T",
            $this->escapeCharacter       => "E",
            $this->repetitionSeparator   => "R",
        );
        $this->escape_sequences = array_map(array($this, "getDelimEscSeq"), $delimiters);

        $this->unescape_sequences = array_flip($this->escape_sequences);
    }

    /**
     * Escape a string
     *
     * @param string $str String to escape
     *
     * @return string
     */
    function escape($str){
        $str = str_replace("\r\n", "\r", $str ?? '');
        $this->initEscapeSequences();
        return strtr($str, $this->escape_sequences);
    }

    /**
     * Convert hex entity to utf-8
     *
     * @param string $h Hex entity
     *
     * @return string
     */
    static function unichr($h) {
        return mb_convert_encoding("&#x$h;", 'UTF-8', 'HTML-ENTITIES');
    }

    /**
     * Unescape a string
     *
     * @param string $str String to escape
     *
     * @return string
     */
    function unescape($str) {
        $this->initEscapeSequences();

        /*if ($str === $this->nullValue) {
          return null; //"__NULL__";
        }*/

        $str = strtr($str, $this->unescape_sequences);

        $esc = preg_quote($this->escapeCharacter, "/");

        //  \Xxx\ => ascii char of xx
        $str = preg_replace_callback(
            "/{$esc}X(\\d\\d){$esc}/",
            function ($matches) {
                return chr(hexdec($matches[1]));
            },
            $str
        );

        //  \Cxxyy\
        /*$str = preg_replace_callback(
          "/{$esc}C([0-9A-F]{4}){$esc}/",
          function ($m) {
            return CHMessage::unichr($m[1]);
          },
          $str
        );*/

        //  \Mxxyyzz\
        $str = preg_replace("/{$esc}M([0-9A-F]{4}(?:[0-9A-F]{2})?){$esc}/", '&#x$1;', $str);

        return $str;
    }

    /**
     * Format string with HL7 special sequence to HTML
     *
     * @param string $str ER7 content
     *
     * @return string
     */
    function format($str) {
        $esc = preg_quote($this->escapeCharacter, "/");
        $str = preg_replace("/{$esc}H{$esc}(.*){$esc}N{$esc}/", '<strong>$1</strong>', $str);

        $formats = array(
            ".br" => "<br />",
            // more
        );

        $format_sequences = array_flip(array_map(array($this, "getDelimEscSeq"), array_flip($formats)));
        $str = strtr($str, $format_sequences);

        return $str;
    }

    /**
     * Get encoding caracters to insert in the message header
     *
     * @return string
     */
    function getEncodingCharacters() {
        return $this->fieldSeparator.
            $this->componentSeparator.
            $this->repetitionSeparator.
            $this->escapeCharacter.
            $this->subcomponentSeparator;
    }

    /**
     * To string magic methode
     *
     * @return string
     */
    function __toString(){
        // Il y a des lignes vides a cause des goupes imbriqués
        $str = parent::__toString();
        $sep = preg_quote($this->getMessage()->segmentTerminator, '/');

        /** @var string $str */
        $str = preg_replace("/$sep+/", $sep, $str);
        return $str;
    }

    /**
     * Flatten to HL7
     *
     * @param bool $highlight Highlight entities for the message to be inserted in an HTML page
     *
     * @return string
     */
    function flatten($highlight = false){
        $old = self::$decorateToString;
        self::$decorateToString = $highlight;

        $str = $this->__toString();

        if ($highlight) {
            $str = "<pre class='er7'>$str</pre>";
        }

        self::$decorateToString = $old;
        return $str;
    }
}
