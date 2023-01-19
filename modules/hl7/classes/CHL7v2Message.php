<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use DOMNode;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHL7v2Message
 * Message HL7
 */
class CHL7v2Message extends CHMessage
{
    static $enteredHeaders = ["MSH", "FHS", "BHS"];

    static $build_mode  = "normal";
    static $handle_mode = "normal";

    static $header_segment_name    = "MSH";
    static $segment_header_pattern = "[A-Z]{2}[A-Z0-9]";
    public $extension;
    public $i18n_code;
    public $version                = "2.5";
    public $actor;
    /**
     * @inheritdoc
     */
    protected $keep_original = ["MSH.1", "MSH.2", "NTE.3", "OBX.5"];

    /**
     * CHL7v2Message constructor.
     *
     * @param string $version HL7 v2 version
     */
    function __construct($version = null)
    {
        if (preg_match("/([A-Z]{3})_(.*)/", $version ?? '', $matches)) {
            $this->extension = $version;
            $this->i18n_code = $matches[2];
            $this->version   = "2.5";
        } else {
            parent::__construct($version);
        }
    }

    /**
     * Set build mode
     *
     * @param string $build_mode Build mode (normal, etc)
     *
     * @return void
     */
    static function setBuildMode($build_mode)
    {
        self::$build_mode = $build_mode;
    }

    /**
     * Reset build mode to "normal"
     *
     * @return void
     */
    static function resetBuildMode()
    {
        self::$build_mode = "normal";
    }

    /**
     * Set handle mode
     *
     * @param string $handle_mode Handle mode
     *
     * @return void
     */
    static function setHandleMode($handle_mode)
    {
        self::$handle_mode = $handle_mode;
    }

    /**
     * Reset handle mode to "normal"
     *
     * @return void
     */
    static function resetHandleMode()
    {
        self::$handle_mode = "normal";
    }

    /**
     * Highlight a message to be displayed in a page
     *
     * @param string $msg Message
     *
     * @return string
     */
    static function highlight($msg)
    {
        $msg = str_replace("\r", "\n", $msg);

        $prefix = self::$header_segment_name;
        preg_match("/^[^$prefix]*$prefix(.)(.)(.)(.)(.)/", $msg, $matches);

        // highlight segment name
        $pattern = self::$segment_header_pattern;
        $msg     = preg_replace("/^($pattern)/m", '<strong>$1</strong>', $msg);
        $msg     = preg_replace(
            "/^(.*)/m",
            '<div class="segment">$1</div>',
            $msg
        ); // we assume $message->segmentTerminator is always \n
        $msg     = str_replace("\n", "", $msg);

        $pat = [
            $matches[1] => "<span class='fs'>$matches[1]</span>",
            $matches[2] => "<span class='cs'>$matches[2]</span>",
            $matches[3] => "<span class='scs'>$matches[3]</span>",
            $matches[4] => "<span class='re'>$matches[4]</span>",
        ];

        return "<pre class='er7'><code>" . strtr($msg, $pat) . "</code></pre>";
    }

    /**
     * Get info in a HL7v2 message with xpath query
     *
     * @param CHL7v2MessageXML $xml               xml
     * @param string           $xpath_parent_node noeud parent pour lequel on veut modifier le fils
     * @param string           $xpath_search_node noeud pour lequel on veut retourner la valeur
     * @param string           $xpath_check_node  noeud pour lequel on veut vérifier une valeur avant de modifier le
     *                                            noeud
     * @param string           $check_value       check value
     * @param string           $sub_field         sous champ
     * @param string           $delimiter         delimiter
     *
     * @return string|null
     */
    static function getIdentifier(
        $xml,
        $xpath_parent_node,
        $xpath_search_node = null,
        $xpath_check_node = null,
        $check_value = null,
        $sub_field = null,
        $delimiter = null
    ) {
        // On parcourt tous les noeuds parents
        /** @var DOMNode $_node */
        foreach ($xml->queryNodes($xpath_parent_node) as $_node) {
            // On check la valeur d'un noeud avant de modifier la valeur
            if ($xpath_check_node && $check_value) {
                $check_node = $xml->queryNode($xpath_check_node, $_node);

                // Si la valeur de vérification correspond à la valeur du noeud de vérification
                // => on retourne la valeur $search_node
                if ($check_node->nodeValue == $check_value) {
                    $change_node = $xml->queryNode($xpath_search_node, $_node);

                    // Le noeud contient un delimiteur (par exemple blabla^blabla^blabla)
                    if ($sub_field && $delimiter) {
                        $values = explode($delimiter, $change_node->nodeValue);

                        return CMbArray::get($values, $sub_field - 1);
                    } // Pas de delimiteur => on retoune la valeur
                    else {
                        return $change_node->nodeValue;
                    }
                } // Valeur de vérification incorrecte => on passe au suivant
                else {
                    continue;
                }
            } // On retourne la valeur du noeud sans vérification particulière
            else {
                $change_node = $xml->queryNode($xpath_search_node, $_node);

                // Le noeud contient un delimiteur (par exemple blabla^blabla^blabla)
                if ($sub_field && $delimiter) {
                    $values = explode($delimiter, $change_node->nodeValue);

                    return CMbArray::get($values, $sub_field - 1);
                } // Pas de delimiteur => on change la valeur
                else {
                    return $change_node->nodeValue;
                }
            }
        }
    }

    /**
     * Set info in a HL7v2 message with xpath query
     *
     * @param CHL7v2MessageXML $xml               xml
     * @param string           $xpath_parent_node noeud parent pour lequel on veut modifier le fils
     * @param string           $new_value         nouvelle valeur
     * @param string           $xpath_change_node noeud pour lequel on veut modifier la valeur
     * @param string           $xpath_check_node  noeud pour lequel on veut vérifier une valeur avant de modifier le
     *                                            noeud
     * @param string           $check_value       check value
     * @param string           $sub_field         sous champ
     * @param string           $delimiter         delimiter
     *
     * @return CHL7v2MessageXML|null
     */
    static function setIdentifier(
        $xml,
        $xpath_parent_node,
        $new_value,
        $xpath_change_node = null,
        $xpath_check_node = null,
        $check_value = null,
        $sub_field = null,
        $delimiter = null
    ) {
        // On parcourt tous les noeuds parents
        /** @var DOMNode $_node */
        foreach ($xml->queryNodes($xpath_parent_node) as $_node) {
            // On check la valeur d'un noeud avant de modifier la valeur
            if ($xpath_check_node && $check_value) {
                $check_node = $xml->queryNode($xpath_check_node, $_node);

                // Si la valeur de vérification correspond à la valeur du noeud de vérification
                // => on change la valeur $change_node
                if ($check_node->nodeValue == $check_value) {
                    $change_node = $xml->queryNode($xpath_change_node, $_node);

                    // Le noeud contient un delimiteur (par exemple blabla^blabla^blabla)
                    if ($sub_field && $delimiter) {
                        $values = explode($delimiter, $change_node->nodeValue);
                        // TODO test à ajouter : est ce que la clé du tableau existe
                        $values[$sub_field - 1] = $new_value;

                        $change_node->nodeValue = implode($delimiter, $values);
                    } // Pas de delimiteur => on change la valeur
                    else {
                        $change_node->nodeValue = $new_value;
                    }
                } // Valeur de vérification incorrecte => on passe au suivant
                else {
                    continue;
                }
            } // On change la valeur du noeud sans vérification particulière
            else {
                $change_node = $xml->queryNode($xpath_change_node, $_node);

                // Le noeud contient un delimiteur (par exemple blabla^blabla^blabla)
                if ($sub_field && $delimiter) {
                    $values                 = explode($delimiter, $change_node->nodeValue);
                    $values[$sub_field - 1] = $new_value;

                    // On concatene
                    $change_node->nodeValue = implode($delimiter, $values);
                } // Pas de delimiteur => on change la valeur
                else {
                    $change_node->nodeValue = $new_value;
                }
            }
        }

        return $xml;
    }

    /**
     * Get info in a HL7v2 message with xpath query
     *
     * @param CHL7v2MessageXML $xml               xml
     * @param string           $xpath_parent_node noeud parent pour lequel on veut modifier le fils
     * @param string           $xpath_search_node noeud pour lequel on veut modifier la valeur
     * @param string           $xpath_check_node  noeud pour lequel on veut vérifier une valeur avant de modifier le
     *                                            noeud
     * @param string           $check_value       check value
     * @param string           $sub_field         sous champ
     * @param string           $delimiter         delimiter
     *
     * @return array|null
     */
    static function getMultipleValues(
        $xml,
        $xpath_parent_node,
        $xpath_search_node = null,
        $xpath_check_node = null,
        $check_value = null,
        $sub_field = null,
        $delimiter = null
    ) {
        $values_found = [];

        // On parcourt tous les noeuds parents
        /** @var DOMNode $_node */
        foreach ($xml->queryNodes($xpath_parent_node) as $_node) {
            // On check la valeur d'un noeud avant de modifier la valeur
            if ($xpath_check_node && $check_value) {
                $check_node = $xml->queryNode($xpath_check_node, $_node);

                // Si la valeur de vérification correspond à la valeur du noeud de vérification
                // => on retourne la valeur $search_node
                if ($check_node->nodeValue == $check_value) {
                    $change_node = $xml->queryNode($xpath_search_node, $_node);

                    // Le noeud contient un delimiteur (par exemple blabla^blabla^blabla)
                    if ($sub_field && $delimiter) {
                        $values         = explode($delimiter, $change_node->nodeValue);
                        $values_found[] = CMbArray::get($values, $sub_field - 1);
                    } // Pas de delimiteur => on retoune la valeur
                    else {
                        $values_found[] = $change_node->nodeValue;
                    }
                } // Valeur de vérification incorrecte => on passe au suivant
                else {
                    continue;
                }
            } // On retourne la valeur du noeud sans vérification particulière
            else {
                $change_node = $xml->queryNode($xpath_search_node, $_node);

                // Le noeud contient un delimiteur (par exemple blabla^blabla^blabla)
                if ($sub_field && $delimiter) {
                    $values         = explode($delimiter, $change_node->nodeValue);
                    $values_found[] = CMbArray::get($values, $sub_field - 1);
                } // Pas de delimiteur => on change la valeur
                else {
                    $values_found[] = $change_node->nodeValue;
                }
            }
        }

        return $values_found;
    }

    /**
     * Get patient matching with HL7v2 message
     *
     * @param CHL7v2MessageXML $xml      xml
     * @param int              $group_id Group ID
     *
     * @return CPatient[]|null
     */
    static function getPatients($xml, $group_id)
    {
        /** @var DOMNode $_node */
        $node_PID5 = $xml->queryNode("//PID.5");

        // Récupération Nom/Prénom/Date de naissance dans le message
        $nom       = $xml->queryNode("XPN.1/FN.1", $node_PID5)->nodeValue;
        $prenom    = $xml->queryNode("XPN.2", $node_PID5)->nodeValue;
        $naissance = $xml->queryNode("//PID.7/TS.1")->nodeValue;

        if (!$nom || !$prenom || !$naissance) {
            return null;
        }

        $where              = [];
        $naissance          = CMbDT::date($naissance);
        $where["naissance"] = "= '$naissance' ";
        $where["nom"]       = "= '$nom' ";
        $where["prenom"]    = "= '$prenom' ";

        $patient  = new CPatient();
        $patients = $patient->loadList($where);

        /** @var CPatient $_patient */
        foreach ($patients as $_patient) {
            $_patient->loadIPP($group_id);
        }

        return $patients;
    }

    /**
     * Get first name, last name, birthday of patient in HL7v2 message
     *
     * @param CHL7v2MessageXML $xml xml
     *
     * @return array
     * @throws \Exception
     */
    static function getInfoPatient($xml)
    {
        // Récupération Nom/Prénom/Date de naissance dans le message
        $nom       = null;
        $prenom    = null;
        $naissance = null;

        $nodes_PID5 = $xml->query("//PID.5");
        foreach ($nodes_PID5 as $_PID5) {
            $fn1 = $xml->queryTextNode("XPN.1/FN.1", $_PID5);

            switch ($xml->queryTextNode("XPN.7", $_PID5)) {
                case "D":
                    $nom = $fn1;
                    break;

                case "L":
                    // Dans le cas où l'on a pas de nom de nom de naissance le legal name
                    // est le nom du patient
                    if ($nodes_PID5->length == 1) {
                        $nom = $fn1;
                    }
                    break;

                default:
                    $nom = $fn1;
            }

            // Prenom(s)
            $prenom = $xml->queryTextNode("XPN.2", $_PID5);
        }

        $node_PID7 = $xml->queryNode("//PID.7");
        if ($node_PID7) {
            $naissance = $xml->queryTextNode("TS.1", $node_PID7);
        }

        if (!$nom || !$prenom || !$naissance) {
            return null;
        }

        $infos              = [];
        $infos["nom"]       = $nom;
        $infos["prenom"]    = $prenom;
        $infos["naissance"] = CMbDT::date($naissance);

        return $infos;
    }

    /**
     * Get date observation ORU R01
     *
     * @param CHL7v2MessageXML $xml                xml
     * @param string           $xpath_context_node xpath query
     * @param string           $xpath_query        xpath query
     *
     * @return string|null
     */
    static function getDateObservation($xml, $xpath_context_node, $xpath_query)
    {
        $context_node = $xml->queryNode($xpath_context_node);

        return $xml->queryTextNode($xpath_query, $context_node);
    }

    /**
     * Get admits for one patient
     *
     * @param int            $IPP              IPP patient
     * @param CInteropSender $actor            actor
     * @param string         $date_observation date observationname
     *
     * @return CSejour[]|null
     */
    static function getAdmits($IPP, $actor, $date_observation)
    {
        $idex_patient = CIdSante400::getMatch("CPatient", $actor->_tag_patient, $IPP);

        if (!$idex_patient->_id) {
            return null;
        }

        /** @var CPatient $patient */
        $patient = CMbObject::loadFromGuid("$idex_patient->object_class-$idex_patient->object_id");

        $sejour              = new CSejour();
        $where               = [];
        $where["patient_id"] = " = '$patient->_id' ";
        if ($date_observation) {
            $where["entree"] = " < '$date_observation' ";
            $where["sortie"] = " > '$date_observation' ";
        } else {
            $borne_min       = CMbDT::dateTime("-30 day");
            $borne_max       = CMbDT::dateTime("+30 day");
            $where["entree"] = " > '$borne_min' ";
            $where["sortie"] = " < '$borne_max' ";
        }
        $sejours = $sejour->loadList($where);

        /** @var CSejour $_sejour */
        foreach ($sejours as $_sejour) {
            $_sejour->loadNDA($actor->group_id);
        }

        return $sejours;
    }

    /**
     * @return string Header segment name
     */
    function getHeaderSegmentName()
    {
        return self::$header_segment_name;
    }

    /**
     * Get localized event name
     *
     * @return string
     */
    function getI18NEventName()
    {
        if ($this->i18n_code) {
            return "{$this->event_name}_{$this->i18n_code}";
        }

        return $this->event_name;
    }

    /**
     * Get the "pivot" format as a DOM Document
     *
     * @param string $event_code    Event code, to determine the root node name
     * @param bool   $hl7_datatypes Output data as HL7 types or MB types (especially for date values)
     * @param string $encoding      Encoding
     *
     * @return CHL7v2MessageXML
     */
    function toXML($event_code = null, $hl7_datatypes = true, $encoding = "utf-8")
    {
        $name = $this->getXMLName();

        $dom  = CHL7v2MessageXML::getEventType($event_code);
        $root = $dom->addElement($dom, $name);
        $dom->addNameSpaces($name);

        return $this->_toXML($root, $hl7_datatypes, $encoding);
    }

    /**
     * @inheritdoc
     */
    function getXMLName()
    {
        $field = $this->children[0]->fields[8]->items[0];
        if ($field->children[0]->data === "ACK") {
            return $field->children[0]->data;
        }

        return $field->children[0]->data . "_" . $field->children[1]->data;
    }

    /**
     * Parse message, as a DOM document
     *
     * @param string             $data       Raw HL7 message
     * @param bool               $parse_body Parse body, or only the header
     * @param CInteropActor|null $actor      Interop actor, to get a few options from it
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function parse($data, $parse_body = true, CInteropActor $actor = null)
    {
        $this->actor = $actor;

        try {
            self::isWellFormed($data, $this->strict_segment_terminator);
        } catch (CHL7v2Exception $e) {
            $this->error($e->getMessage(), $e->extraData);
            //return false;
        }

        // remove all chars before MSH
        $msh_pos = strpos($data, self::$header_segment_name);

        if ($msh_pos === false) {
            throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $data);
        }

        $data = substr($data, $msh_pos);
        $data = self::fixRawER7($data, $this->strict_segment_terminator);

        parent::parse($data);

        $message = $this->data;

        // 4 to 7
        if (!isset($message[7])) {
            throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $message);
        }

        $this->fieldSeparator = $message[3];

        $nextDelimiter = strpos($message, $this->fieldSeparator, 4);
        if ($nextDelimiter > 4) {
            // usually ^
            $this->componentSeparator = $message[4];
        }
        if ($nextDelimiter > 5) {
            // usually ~
            $this->repetitionSeparator = $message[5];
        }
        if ($nextDelimiter > 6) {
            // usually \
            $this->escapeCharacter = $message[6];
        }
        if ($nextDelimiter > 7) {
            // usually &
            $this->subcomponentSeparator = $message[7];
        }

        // replace the special case of ^~& with ^~\&
        if ("^~&|" === substr($message, 4, 4)) {
            $this->escapeCharacter       = "\\";
            $this->subcomponentSeparator = "&";
            $this->repetitionSeparator   = "~";
            $this->componentSeparator    = "^";
        }

        $this->initEscapeSequences();

        $this->lines = CHL7v2::split($this->segmentTerminator, $this->data);

        // we extract the first line info "by hand"
        $first_line = CHL7v2::split($this->fieldSeparator, reset($this->lines));

        if (!isset($first_line[11])) {
            throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $message);
        }

        // version
        $this->parseRawVersion($first_line[11]);

        // message type
        $message_type = explode($this->componentSeparator, $first_line[8]);

        if ($message_type[0]) {
            $this->name = $message_type[0];

            if ($this->name === "ACK") {
                $this->event_name = $message_type[0];
            } else {
                if (isset($message_type[2]) && $message_type[2] && !preg_match(
                        "/^[A-Z]{3}_[A-Z\d]{2}\d$/",
                        $message_type[2]
                    )) {
                    throw new CHL7v2Exception(CHL7v2Exception::WRONG_MESSAGE_TYPE, $message_type[2]);
                }

                if (strlen($message_type[0]) !== 3 || strlen($message_type[1]) !== 3) {
                    $msg = $message_type[0] . $this->componentSeparator . $message_type[1];
                    throw new CHL7v2Exception(CHL7v2Exception::WRONG_MESSAGE_TYPE, $msg);
                }

                $this->event_name = $message_type[0] . $message_type[1];
            }
        } else {
            $this->event_name = preg_replace("/[^A-Z0-9]/", "", $message_type[2]);
            $this->name       = substr($message_type[2], 0, 3);
        }

        if (!$spec = $this->getSpecs()) {
            throw new CHL7v2Exception(CHL7v2Exception::UNKNOWN_MSG_CODE);
        }

        $this->description = $spec->queryTextNode("description");

        $this->readHeader();

        if ($parse_body) {
            $this->readSegments();
        }
    }

    /**
     * Tells if the document is well formed, based on a few checks (first chars, etc)
     *
     * @param string $data                      The HL7 message
     * @param bool   $strict_segment_terminator Be strict on segment terminators (\r) or not (\r or \n)
     *
     * @return bool
     * @throws CHL7v2Exception
     */
    static function isWellFormed($data, $strict_segment_terminator = false)
    {
        // remove all chars before MSH
        $msh_pos = strpos($data, self::$header_segment_name);
        if ($msh_pos === false) {
            throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $data);
        }

        $data = substr($data, $msh_pos);
        $data = self::fixRawER7($data, $strict_segment_terminator);

        // first tokenize the segments
        if (($data == null) || (strlen($data) < 4)) {
            throw new CHL7v2Exception(CHL7v2Exception::EMPTY_MESSAGE, $data);
        }

        $fieldSeparator = $data[3];

        // valid separator
        if (!preg_match("/[^a-z0-9]/i", $fieldSeparator)) {
            throw new CHL7v2Exception(CHL7v2Exception::INVALID_SEPARATOR, substr($data, 0, 10));
        }

        $lines = CHL7v2::split(self::DEFAULT_SEGMENT_TERMINATOR, $data);

        // validation de la syntaxe : chaque ligne doit commencer par 3 lettre + un separateur + au moins une donnée
        $sep_preg = preg_quote($fieldSeparator, '/');

        $pattern = self::$segment_header_pattern;
        foreach ($lines as $_line) {
            if (!$_line || (strlen($_line) == 1)) {
                continue;
            }

            if (!preg_match("/^($pattern)$sep_preg/", $_line)) {
                throw new CHL7v2Exception(CHL7v2Exception::SEGMENT_INVALID_SYNTAX, $_line);
            }
        }

        return true;
    }

    /**
     * Parse the raw version string
     *
     * @param string $raw          Version string
     * @param string $country_code Country code
     *
     * @return void
     */
    private function parseRawVersion($raw, $country_code = null)
    {
        $parts = explode($this->componentSeparator, $raw);

        CMbArray::removeValue("", $parts);

        $this->version = $version = $parts[0];

        $actor = $this->actor;

        $configs = new CHL7Config();
        if ($actor instanceof CInteropSender) {
            $exchange_hl7v2 = new CExchangeHL7v2();
            $configs        = $exchange_hl7v2->getConfigs($actor->_guid);
        }

        // Version spécifique française spécifiée
        if (!$country_code && count($parts) > 1) {
            switch ($parts[1]) {
                case "FRA":
                case "FR":
                    $this->i18n_code = "FRA";
                    $version_id      = CMbArray::get($parts, 2, "2.5");
                    $this->extension = $version = "FRA_$version_id";
                    break;

                default:
                    $this->i18n_code = $parts[1];
                    $this->extension = $version = "$parts[1]_$parts[2]";
            }
        }

        // On privilégie le code du pays sur l'acteur d'intégration
        $country_code = $configs->country_code ?: $country_code;

        // Recherche depuis le code du pays
        switch ($country_code) {
            case "FRA":
            case "FR":
                $this->i18n_code = "FRA";
                $this->extension = $version = CAppUI::conf("hl7 default_fr_version");

                break;

            default:
        }

        // Dans le cas où la version passée est incorrecte on met par défaut 2.5
        if (!in_array($version, self::$versions)) {
            $this->version = CAppUI::conf("hl7 default_version");
        }
    }

    /**
     * @inheritdoc
     */
    function getSchema($type, $name)
    {
        $extension = $this->extension;
        $version   = $this->getVersion();

        if (isset(self::$schemas[$version][$type][$name][$extension])) {
            return clone self::$schemas[$version][$type][$name][$extension];
        }

        if (!in_array($version, self::$versions)) {
            $this->error(CHL7v2Exception::VERSION_UNKNOWN, $version);
        }

        if ($extension && $extension !== "none" && preg_match("/([A-Z]{3})_(.*)/", $extension, $matches)) {
            $lang        = strtolower($matches[1]);
            $v           = "v" . str_replace(".", "_", $matches[2]);
            $version_dir = "extensions/$lang/$v";
        } else {
            $version_dir = "hl7v" . preg_replace("/\D/", "_", $version);
        }

        $name_dir = preg_replace("/[^A-Z0-9_]/", "", $name);

        $this->spec_filename = __DIR__ . "/../../../" . self::LIB_HL7 . "/$version_dir/$type$name_dir.xml";

        if (!file_exists($this->spec_filename)) {
            // on a déjà l'erreur sur le type de segment inconnu
            //$this->error(CHL7v2Exception::SPECS_FILE_MISSING, $this->spec_filename);
            return null;
        }

        $schema = new CHL7v2DOMDocument();
        $schema->registerNodeClass('DOMElement', 'CHL7v2DOMElement');
        $schema->load($this->spec_filename);
        //$schema = @simplexml_load_file($this->spec_filename, "CHL7v2SimpleXMLElement");

        self::$schemas[$version][$type][$name][$extension] = $schema;

        return $this->specs = $schema;
    }

    /**
     * @inheritdoc
     */
    function loadDataType($datatype)
    {
        return CHL7v2DataType::load($this, $datatype, $this->getVersion(), $this->extension);
    }

    /**
     * Get segment
     *
     * @param string $name Segment name
     *
     * @return CHL7v2Segment|null
     */
    function getSegmentByName($name)
    {
        foreach ($this->children as $_segment) {
            if ($_segment->name === $name) {
                return $_segment;
            }
        }

        return null;
    }
}
