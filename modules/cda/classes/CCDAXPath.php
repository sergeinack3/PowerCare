<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use DateTime;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Ox\Core\CMbDT;
use Ox\Core\CMbXPath;
use Ox\Interop\Dmp\CDMPFile;

/**
 * Xpath class for CDA
 */
class CCDAXPath extends CMbXPath
{
    /** @var string */
    public const PREFIX_NS = 'cda';
    public const PREFIX_SCHEMA = 'xsi';

    /**
     * @see parent::__construct()
     */
    function __construct($xml)
    {
        parent::__construct($xml);
        $this->registerNamespace(self::PREFIX_NS, "urn:hl7-org:v3");
        $this->registerNamespace(self::PREFIX_SCHEMA, "http://www.w3.org/2001/XMLSchema-instance");
    }

    /**
     * Return the name of the document
     *
     * @return string
     */
    function getTitle()
    {
        return $this->queryTextNode("//cda:title");
    }

    /**
     * Return the create date of the document
     *
     * @return datetime|Null
     */
    function getCreateDate()
    {
        $create_date = $this->queryAttributNode("/cda:ClinicalDocument/cda:effectiveTime", null, "value");
        if ($create_date) {
            $create_date = CMbDT::dateTime($create_date);
        }

        return $create_date;
    }

    /**
     * Return the custodian organization node
     *
     * @return DOMElement
     */
    function getCustodianOrganization()
    {
        return $this->queryUniqueNode("//cda:custodian/cda:assignedCustodian/cda:representedCustodianOrganization");
    }

    /**
     * Return the Author of the document(legal author or the first author)
     *
     * @return DOMNode
     */
    function getAuthor()
    {
        $auteur = $this->queryUniqueNode("//cda:legalAuthenticator/cda:assignedEntity");
        if (!$auteur) {
            $auteur = $this->query("//cda:author/cda:assignedAuthor")->item(0);
        }

        return $auteur;
    }

    /**
     * Get the telecom of node
     *
     * @param DOMNode $node Node
     *
     * @return array
     */
    function getTelecom($node)
    {
        $telecoms = [];
        $nodes    = $this->query("./cda:assignedAuthor/cda:telecom", $node);

        foreach ($nodes as $_node) {
            $telecoms[] = $this->queryTextNode("/@value", $_node);
        }

        return $telecoms;
    }

    /**
     * Get the last name and the firstname
     *
     * @param DOMNode $node Node
     *
     * @return string
     */
    function getName($node)
    {
        $name = $this->queryTextNode("./cda:family[not(@*)]", $node);
        if (!$name) {
            $name = $this->queryTextNode("./cda:family[@qualifier='SP']", $node);
        }

        $node_given = $this->query("./cda:given");
        $given      = "";
        if ($node_given->length > 0) {
            $given = $this->queryTextNode(".", $node_given->item(0));
        }

        return "$name $given";
    }

    /**
     * Return the list of patients
     *
     * @return DOMNodeList
     */
    function getPatients()
    {
        return $this->query("//cda:recordTarget");
    }

    /**
     * Return the name of the patient
     *
     * @param DOMNode $patient Patient Node
     *
     * @return string
     */
    function getPatientName($patient)
    {
        $xpath = "./cda:patientRole/cda:patient/cda:name";
        $name  = $this->queryTextNode("$xpath/cda:family[not(@*)]", $patient);
        if (!$name) {
            $name = $this->queryTextNode("$xpath/cda:family[@qualifier='SP']", $patient);
        }

        return $name;
    }

    /**
     * return the birthDate of the patient
     *
     * @param DOMNode $patient Patient Node
     *
     * @return string|Null
     */
    function getPatientBirthDate($patient)
    {
        $birthdate = $this->queryAttributNode("./cda:patientRole/cda:patient/cda:birthTime", $patient, "value");
        if ($birthdate) {
            $birthdate = CMbDT::date($birthdate);
        }

        return $birthdate;
    }

    /**
     * Return the identifier of the object
     *
     * @param DOMNode $node node
     *
     * @return string
     */
    function getIdentifierII($node)
    {
        $root      = $this->queryAttributNode("./cda:id", $node, "root");
        $extension = $this->queryAttributNode("./cda:id", $node, "extension");
        if ($extension) {
            $root .= "@$extension";
        }

        return $root;
    }

    /**
     * Return the code of the node
     * By default the code of the document is return
     *
     * @param DomNode $node Node
     *
     * @return string
     */
    function getCodeCE($node = null)
    {
        $codeSystem = $this->queryTextNode("./@codeSystem", $node->item(0));
        $code       = $this->queryTextNode("./@code", $node->item(0));

        return "$codeSystem^$code";
    }

    /**
     * Return the document
     *
     * @param CDMPFile $dmp_file dmp file
     *
     * @return string
     */
    function getDocument(CDMPFile $dmp_file)
    {
        $body           = $this->query("//cda:ClinicalDocument/cda:component");
        $representation = $this->queryTextNode("./cda:nonXMLBody/cda:text/@representation", $body->item(0));
        $file_cda       = $this->queryTextNode("./cda:nonXMLBody/cda:text", $body->item(0));

        if ($representation == "B64") {
            $file_cda = base64_decode($file_cda);
        }

        $dmp_file->_file_type     = $this->queryTextNode("./cda:nonXMLBody/cda:text/@mediaType", $body->item(0));
        $dmp_file->_file_type_ext = $this->getFileType($dmp_file->_file_type);

        return $file_cda;
    }

    /**
     * Return the mime type
     *
     * @param String $type type
     *
     * @return null|string
     */
    function getFileType($type)
    {
        $file_type = null;

        $type = strtolower($type);

        switch ($type) {
            case "image/gif":
                $file_type = ".gif";
                break;
            case "image/jpeg":
            case "image/jpg":
                $file_type = ".jpeg";
                break;
            case "image/png":
                $file_type = ".png";
                break;
            case "application/rtf":
            case "text/rtf":
                $file_type = ".rtf";
                break;
            case "text/html":
                $file_type = ".html";
                break;
            case "image/tiff":
                $file_type = ".tiff";
                break;
            case "application/xml":
                $file_type = ".xml";
                break;
            case "application/pdf":
                $file_type = ".pdf";
                break;
            default:
                $file_type = "unknown/unknown";
                break;
        }

        return $file_type;
    }
}
