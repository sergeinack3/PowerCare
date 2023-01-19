<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Files\CFile;
use ReturnTypeWillChange;

if (!class_exists(DOMDocument::class)) {
    return;
}

class CMbXMLDocument extends DOMDocument implements IShortNameAutoloadable
{
    public $schemapath;
    public $schemafilename;
    public $documentfilename;
    public $now;

    function __construct($encoding = "iso-8859-1")
    {
        parent::__construct("1.0", $encoding);

        $this->preserveWhiteSpace = false;
        $this->formatOutput       = true;
    }

    static function insertTextElement($element, $name, $value, $attrs = null)
    {
        /** @var CMbXMLDocument $dom */
        $dom = $element->ownerDocument;

        $tag = $dom->addElement($element, $name, $value);

        if ($attrs) {
            foreach ($attrs as $key => $value) {
                $dom->addAttribute($tag, $key, $value);
            }
        }

        return $tag;
    }

    /**
     * Nettoie du code HTML
     *
     * @param string $html the html string
     *
     * @return string the cleaned html
     */
    static function sanitizeHTML($html)
    {
        //check if html is present
        if (!preg_match("/<html/", $html)) {
            $html = '<html><head><title>E-mail</title></head><body>' . $html . '</body></html>';
        }

        //=>XML
        $html = CMbString::convertHTMLToXMLEntities($html);

        //load & repair dom
        $document                     = new CMbXMLDocument();
        $document->preserveWhiteSpace = false;
        @$document->loadHTML($html);

        //remove scripts tag
        $xpath  = new DOMXPath($document);
        $filter = ["//script", "//meta", "//applet", "//iframe"]; //some dangerous
        foreach ($filter as $_filter) {
            $elements = $xpath->query($_filter);
            foreach ($elements as $_element) {
                $_element->parentNode->removeChild($_element);
            }
        }

        $html = $document->saveHTML();

        //Cleanup after save
        $html = preg_replace("/<!DOCTYPE(.*?)>/", '', $html);
        $html = preg_replace("/\/\/>/mu", "/>", $html);
        $html = preg_replace("/nowrap/", '', $html);
        $html = preg_replace("/<[b|h]r([^>]*)>/", "<br $1/>", $html);
        $html = preg_replace("/<img([^>]+)>/", "<img$1/>", $html);

        return $html;
    }

    function setDocument($documentfilename)
    {
        $this->documentfilename = $documentfilename;
    }

    function setSchema($schemafilename)
    {
        $this->schemapath     = dirname($schemafilename);
        $this->schemafilename = $schemafilename;
    }

    /**
     * Try to load and validate XML File
     *
     * @param string $docPath Uploaded file temporary path
     *
     * @return string Store-like message
     */
    function loadAndValidate($docPath)
    {
        // Chargement
        if (!$this->load($docPath)) {
            return "Le fichier fourni n'est pas un document XML bien formé";
        }

        // Validation
        if ($this->checkSchema() && !$this->schemaValidate()) {
            return "Document invalide";
        }

        return null;
    }

    function checkSchema()
    {
        if (!$this->schemafilename) {
            trigger_error("You haven't set the schema", E_USER_WARNING);

            return false;
        }
        if (!is_dir($this->schemapath)) {
            trigger_error("Schema directory is missing ($this->schemapath/)", E_USER_WARNING);

            return false;
        }

        if (!is_file($this->schemafilename)) {
            trigger_error("Schema is missing ($this->schemafilename)", E_USER_WARNING);

            return false;
        }

        return true;
    }

    /**
     * Try to validate the document against a schema
     * will trigger errors when not validating
     *
     * @param string $filename       Path of schema, use document inline schema if null
     * @param bool   $returnErrors   Return errors, or false
     * @param bool   $display_errors Display errors
     *
     * @return bool
     */
    #[ReturnTypeWillChange]
    function schemaValidate($filename = null, $returnErrors = false, $display_errors = true)
    {
        if (!$filename) {
            $filename = $this->schemafilename;
        }

        // Enable user error handling
        libxml_use_internal_errors(true);

        if (!parent::schemaValidate($filename)) {
            $errors = $this->libxml_display_errors($display_errors);

            return $returnErrors ? $errors : false;
        }

        return true;
    }

    function libxml_display_errors($display_errors = true)
    {
        $errors       = libxml_get_errors();
        $chain_errors = "";

        foreach ($errors as $error) {
            $chain_errors .= preg_replace(
                    '/( in\ \/(.*))/',
                    '',
                    strip_tags($this->libxml_display_error($error))
                ) . "\n";
            if ($display_errors) {
                trigger_error($this->libxml_display_error($error), E_USER_WARNING);
            }
        }
        libxml_clear_errors();

        return $chain_errors;
    }

    function libxml_display_error($error)
    {
        $return = "<br/>\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "<b>Warning $error->code</b>: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in <b>$error->file</b>";
        }
        $return .= " on line <b>$error->line</b>\n";

        return $return;
    }

    function libxml_tabs_erros()
    {
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public static function getDefinedEncoding(string $content): string
    {
        $defined_encoding = 'UTF-8';
        if (preg_match("/<\?xml.* encoding=(?:\"|\')(?'encoding'.+)(?:\"|\').*\?>/", $content, $match)) {
            $defined_encoding = $match['encoding'] ?? $defined_encoding;
        }

        return $defined_encoding;
    }

    /**
     * Clean $data encoding
     *
     * @param string $data
     *
     * @return string
     */
    protected function checkConformityEncoding(string $data): string
    {
        $defined_encoding = $this::getDefinedEncoding($data);

        $is_utf8_encoded = CMbString::isUTF8($data);
        if (preg_match("/utf-?8/i", $defined_encoding) && !$is_utf8_encoded) {
            $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
        }

        if (preg_match("/iso-8859-1/i", $defined_encoding) && $is_utf8_encoded) {
            $data = mb_convert_encoding($data, 'ISO-8859-1', 'UTF-8');
        }

        return $data;
    }

    function loadXML($source, $options = null, $returnErrors = false)
    {
        $source = $this->checkConformityEncoding($source);

        if (!$returnErrors) {
            return parent::loadXML($source, $options ?? 0);
        }

        // Enable user error handling
        libxml_use_internal_errors(true);

        parent::loadXML($source, $options ?? 0);

        return $this->libxml_display_errors(false);
    }

    function addDateTimeElement($elParent, $elName, $dateValue = null, ?string $elNS = null)
    {
        $this->addElement($elParent, $elName, CMbDT::format($dateValue, "%Y-%m-%dT%H:%M:%S"), $elNS);
    }

    /**
     * @param DOMNode $elParent
     * @param string  $elName
     * @param string|null    $elValue
     * @param string|null    $elNS
     *
     * @return DOMElement
     */
    function addElement(DOMNode $elParent, $elName, $elValue = null, $elNS = null)
    {
        $elName  = $elName !== null ? mb_convert_encoding($elName, 'UTF-8', 'ISO-8859-1') : "";
        $elValue = $elValue !== null ? mb_convert_encoding($elValue, 'UTF-8', 'ISO-8859-1') : "";

        return $elParent->appendChild(new DOMElement($elName, $elValue, $elNS ?? ""));
    }

    function addDateTimeAttribute($elParent, $atName, $dateValue = null)
    {
        $this->addAttribute($elParent, $atName, CMbDT::format($dateValue, "%Y-%m-%dT%H:%M:%S"));
    }

    function addAttribute($elParent, $atName, $atValue)
    {
        $atName  = mb_convert_encoding($atName ?? '', 'UTF-8', 'ISO-8859-1');
        $atValue = mb_convert_encoding($atValue ?? '', 'UTF-8', 'ISO-8859-1');

        return $elParent->setAttribute($atName, $atValue);
    }

    function addAttributeYes($elParent, $atName)
    {
        $this->addAttribute($elParent, $atName, "oui");
    }

    function addAttributeNo($elParent, $atName)
    {
        $this->addAttribute($elParent, $atName, "non");
    }

    function addComment($elParent, $comment)
    {
        return $elParent->appendChild($this->createComment($comment));
    }

    function addDocumentation($elParent, $documentation = null)
    {
        if (!$documentation) {
            return;
        }

        $annotation = $this->addElement($elParent, "annotation", null, "http://www.w3.org/2001/XMLSchema");
        $this->addElement($annotation, "documentation", $documentation, "http://www.w3.org/2001/XMLSchema");
    }

    /**
     * Import a another DOMDocument to our document
     *
     * @param DOMElement|self  $nodeParent  Receiver node
     * @param DOMDocument $domDocument DOMDocument to import
     *
     * @return void
     */
    function importDOMDocument($nodeParent, $domDocument)
    {
        $nodeParent->appendChild($this->importNode($domDocument->documentElement, true));
    }

    /**
     * Purge empty elements
     */
    function purgeEmptyElements()
    {
        $this->purgeEmptyElementsNode($this->documentElement);
    }

    /**
     * Enlève les élements vide d'un noeud
     *
     * @param DOMElement $node         DOMElement
     * @param bool       $removeParent DOMElement
     *
     * @return void
     */
    function purgeEmptyElementsNode($node, $removeParent = true)
    {
        if (!$node->childNodes) {
            return;
        }

        // childNodes undefined for non-element nodes (eg text nodes)
        // Copy childNodes array
        $childNodes = [];
        foreach ($node->childNodes as $childNode) {
            $childNodes[] = $childNode;
        }

        // Browse with the copy (recursive call)
        foreach ($childNodes as $childNode) {
            $this->purgeEmptyElementsNode($childNode);
        }

        // Remove if empty
        if (!$node->nodeValue && ($node->nodeValue === null || $node->nodeValue === '') && !$node->hasChildNodes(
            ) && !$node->hasAttributes() && $removeParent) {
            //        trigger_error("Removing child node $node->nodeName in parent node {$node->parentNode->nodeName}", E_USER_NOTICE);
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Create a CFile attachment to given CMbObject
     * @return string store-like message, null if successful
     */
    function addFile(CMbObject $object)
    {
        $user = CUser::get();
        $this->saveFile();
        $file                     = new CFile();
        $file->object_id          = $object->_id;
        $file->object_class       = $object->_class;
        $file->file_name          = "$object->_guid.xml";
        $file->file_type          = "text/xml";
        $file->doc_size           = filesize($this->documentfilename);
        $file->file_date          = CMbDT::dateTime();
        $file->file_real_filename = uniqid(rand());
        $file->author_id          = $user->_id;
        $file->private            = 0;
        $file->setMoveFrom($this->documentfilename);

        return $file->store();
    }

    function saveFile()
    {
        parent::save($this->documentfilename);
    }

    function getEvenements()
    {
        return [];
    }

    function getDocumentElements()
    {
        return [];
    }
}
