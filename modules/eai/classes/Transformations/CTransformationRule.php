<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Transformations;

/**
 * Class CTransformationRule
 * EAI transformation rule
 */

use DOMElement;
use DOMNode;
use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CStoredObject;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXML;

class CTransformationRule extends CMbObject
{
    // DB Table key
    public $transformation_rule_id;

    // DB fields
    public $name;
    public $extension;
    public $xpath_source;
    public $xpath_target;
    public $action_type;
    public $value;
    public $active;
    public $rank;
    public $transformation_rule_sequence_id;
    public $params;

    /** @var CTransformationRuleSequence */
    public $_ref_transformation_rule_sequence;

    /**
     * @see parent::getSpec()
     */
    public function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table = 'transformation_rule';
        $spec->key = 'transformation_rule_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props["name"] = "str notNull";
        $props["xpath_source"] = "str notNull";
        $props["xpath_target"] = "str";
        $props["action_type"] = "enum list|insert|delete|map|trim|sub|pad|upper|lower|copy|concat";
        $props["active"] = "bool default|0";
        $props["rank"] = "num min|1 show|0";
        $props["params"] = "text";

        $props["transformation_rule_sequence_id"] = "ref class|CTransformationRuleSequence autocomplete|text back|transformation_rules";

        return $props;
    }

    /**
     * Load rule_sequence
     *
     * @return CTransformationRuleSequence|CStoredObject
     * @throws Exception
     */
    public function loadRefTransformationRuleSequence()
    {
        return $this->_ref_transformation_rule_sequence = $this->loadFwdRef("transformation_rule_sequence_id", true);
    }

    /**
     * @see parent::store
     */
    public function store()
    {
        if (!$this->_id) {
            $transf_rule = new CTransformationRule();
            $transf_rule->transformation_rule_sequence_id = $this->transformation_rule_sequence_id;

            $this->rank = $transf_rule->countMatchingList() + 1;
        }

        return parent::store();
    }

    /**
     * Apply rule of the content
     *
     * @param string $content
     *
     * @return string
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function apply(string $content): string
    {
        $hl7_message = new CHL7v2Message();
        $hl7_message->parse($content);
        $dom = $hl7_message->toXML(null, true);

        $action = $this->action_type . 'Transformation';
        $dom = $this->$action($dom, $this->xpath_source, $this->xpath_target);

        return $dom->toER7($hl7_message);
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string         $xpath_source
     * @param string         $xpath_target
     * @param string         $value
     * @param DOMNode        $contextNode
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function insertTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target = null,
        ?string        $value = null,
        ?DOMNode       $contextNode = null,
        bool           $concatenate = false
    ): CMbXMLDocument
    {

        $xpath_sources = explode('|', $xpath_source);
        foreach ($xpath_sources as $_xpath_source) {
            $insert_nodes = $contextNode ? $dom->query($_xpath_source, $contextNode) : $dom->query($_xpath_source);

            /** @var DOMElement $_insert_node */
            foreach ($insert_nodes as $_insert_node) {
                $new_value = str_replace('"', '', $value ?: $this->params);
                $_insert_node->nodeValue = $concatenate ? $_insert_node->nodeValue . $new_value : $new_value;
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string         $xpath_source
     * @param string         $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function deleteTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target = null
    ): CMbXMLDocument
    {

        $xpath_sources = explode('|', $xpath_source);
        foreach ($xpath_sources as $_xpath_source) {
            $delete_field = true;

            $delete_nodes = $dom->query($_xpath_source);

            // Récupération de la valeur après le dernier "/" pour savoir si c'est un champ ou un segment à supprimer
            $array_path = explode('/', $_xpath_source);
            $delete_path = end($array_path);

            // Suppression segment
            if (strpos($delete_path, ".") === false) {
                $delete_field = false;
            }

            foreach ($delete_nodes as $_delete_node) {
                // Suppression d'un champ
                if ($delete_field) {
                    $_delete_node->nodeValue = '';
                } else {
                    // Suppression d'un segemnt
                    $_delete_node->parentNode->removeChild($_delete_node);
                }
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string $xpath_source
     * @param string $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function upperTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target = null
    ): CMbXMLDocument
    {

        $xpath_sources = explode('|', $xpath_source);
        foreach ($xpath_sources as $_xpath_source) {
            $source_nodes = $dom->query($_xpath_source);

            foreach ($source_nodes as $_source_node) {
                if (!$_source_node->nodeValue) {
                    continue;
                }
                $_source_node->nodeValue = strtoupper($_source_node->nodeValue);
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string $xpath_source
     * @param string $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function lowerTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target = null
    ): CMbXMLDocument
    {
        $xpath_sources = explode('|', $xpath_source);
        foreach ($xpath_sources as $_xpath_source) {
            $source_nodes = $dom->query($_xpath_source);

            foreach ($source_nodes as $_source_node) {
                if (!$_source_node->nodeValue) {
                    continue;
                }
                $_source_node->nodeValue = strtolower($_source_node->nodeValue);
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string $xpath_source
     * @param string $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function subTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target = null
    ): CMbXMLDocument
    {
        $params = explode(',', $this->params);

        $offset = CMbArray::get($params, 0);
        $length = CMbArray::get($params, 1);

        if ($offset === null || $offset === '') {
            return $dom;
        }

        $xpath_sources = explode('|', $xpath_source);
        foreach ($xpath_sources as $_xpath_source) {
            $sub_nodes = $dom->query($_xpath_source);

            foreach ($sub_nodes as $_sub_node) {
                $_sub_node->nodeValue = substr($_sub_node->nodeValue, $offset, $length);
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string $xpath_source
     * @param string $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function padTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target = null
    ): CMbXMLDocument
    {

        $params = explode(',', $this->params);

        $length = CMbArray::get($params, 0);
        $pad_string = CMbArray::get($params, 1);
        $pad_type = CMbArray::get($params, 2);

        $pad_string = str_replace('"', '', $pad_string);

        if ($length === null || $length < 0 || !$pad_string || $pad_string == '') {
            return $dom;
        }


        $xpath_sources = explode('|', $xpath_source);
        foreach ($xpath_sources as $_xpath_source) {
            $pad_nodes = $dom->query($_xpath_source);

            foreach ($pad_nodes as $_pad_node) {
                $_pad_node->nodeValue = str_pad($_pad_node->nodeValue, $length, $pad_string, constant($pad_type));
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string $xpath_source
     * @param string $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function trimTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target = null
    ): CMbXMLDocument
    {

        $this->params = str_replace('"', '', $this->params);
        // ltrim / rtrim / trim
        $method = $this->params;

        if (!$method || ($method !== 'trim' && $method !== 'ltrim' && $method !== 'rtrim')) {
            return $dom;
        }

        $xpath_sources = explode('|', $xpath_source);
        foreach ($xpath_sources as $_xpath_source) {
            $trim_nodes = $dom->query($_xpath_source);

            foreach ($trim_nodes as $_trim_node) {
                $_trim_node->nodeValue = $method($_trim_node->nodeValue);
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string $xpath_source
     * @param string $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function mapTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target = null
    ): CMbXMLDocument
    {

        // Suppression du premier caractère et du dernier caractère (parce que ce sont des " à cause de la sérialisation)
        $params = substr($this->params, 1);
        $params = substr($params, 0, -1);

        $xpath_sources = explode('|', $xpath_source);
        foreach ($xpath_sources as $_xpath_source) {
            $map_nodes = $dom->query($_xpath_source);

            foreach ($map_nodes as $_map_node) {
                $node_value = $_map_node->nodeValue;

                // Si pas de valeur => on ne fait rien
                if (!$node_value) {
                    continue;
                }

                $maps_key_value = explode(',', $params);

                $value_found = false;
                $default_value = null;
                // Example : 20210219150154|20210219150111,20210219150153|20210219150222,20210219150000
                // Default => 20210219150000
                foreach ($maps_key_value as $_map_key_value) {
                    $key_value = explode('|', $_map_key_value);
                    $key = CMbArray::get($key_value, 0);
                    $value = CMbArray::get($key_value, 1);

                    if ($key && $value) {
                        if (preg_match('#' . $key . '#', $node_value)) {
                            $value_found = true;
                            $node_value = str_replace($key, $value, $node_value);
                        }
                    }

                    // Default value
                    if ($key && !$value) {
                        $default_value = $key;
                    }
                }

                // On applique le default
                if (!$value_found && $default_value) {
                    $node_value = str_replace($node_value, $default_value, $node_value);
                }

                $_map_node->nodeValue = $node_value;
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string $xpath_source
     * @param string $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function copyTransformation(
        CMbXMLDocument $dom,
        string         $xpath_source,
        string         $xpath_target
    ): CMbXMLDocument
    {
        $scope = str_replace('"', '', $this->params);
        if ($scope !== 'all' && $scope !== 'group' && $scope !== 'segment') {
            return $dom;
        }

        $copy_node_sources = $dom->query($xpath_source);

        $xpath_targets = explode('|', $xpath_target);

        /** @var DOMElement $_copy_node_source */
        foreach ($copy_node_sources as $_copy_node_source) {
            switch ($scope) {
                case 'all':
                    foreach ($xpath_targets as $_xpath_target) {
                        $dom = $this->insertTransformation($dom, $_xpath_target, null, $_copy_node_source->nodeValue);
                    }
                    break;
                case 'group':
                    $full_path_current_node = $_copy_node_source->getNodePath();
                    $path = $this->getGroupPath($full_path_current_node);
                    $full_path_current_node = str_replace($path, '', $full_path_current_node);

                    foreach ($xpath_targets as $_xpath_target) {
                        $dom = $this->insertTransformation(
                            $dom,
                            $this->getGroupPath($_xpath_target, false),
                            null,
                            $_copy_node_source->nodeValue,
                            $dom->queryNode($full_path_current_node)
                        );
                    }
                    break;
                case 'segment':
                    $full_path_current_node = $_copy_node_source->getNodePath();
                    $path = $this->getSegmentPath($full_path_current_node);
                    $full_path_current_node = str_replace($path, '', $full_path_current_node);

                    foreach ($xpath_targets as $_xpath_target) {
                        $dom = $this->insertTransformation(
                            $dom,
                            $this->getSegmentPath($_xpath_target, false),
                            null,
                            $_copy_node_source->nodeValue,
                            $dom->queryNode($full_path_current_node)
                        );
                    }
                    break;
                default:
            }
        }

        return $dom;
    }

    /**
     * @param CMbXMLDocument $dom
     * @param string $xpath_source
     * @param string $xpath_target
     *
     * @return CMbXMLDocument
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function concatTransformation(
        CMbXMLDocument $dom,
        ?string        $xpath_source,
        ?string        $xpath_target
    ): CMbXMLDocument
    {
        $scope = str_replace('"', '', $this->params);
        if ($scope !== 'all' && $scope !== 'group' && $scope !== 'segment') {
            return $dom;
        }

        $copy_node_sources = $dom->query($xpath_source);

        $xpath_targets = explode('|', $xpath_target);

        /** @var DOMElement $_copy_node_source */
        foreach ($copy_node_sources as $_copy_node_source) {
            switch ($scope) {
                case 'all':
                    foreach ($xpath_targets as $_xpath_target) {
                        $dom = $this->insertTransformation($dom, $_xpath_target, null, $_copy_node_source->nodeValue, null, true);
                    }
                    break;
                case 'group':
                    $full_path_current_node = $_copy_node_source->getNodePath();
                    $path = $this->getGroupPath($full_path_current_node);

                    $full_path_current_node = str_replace($path, '', $full_path_current_node);
                    foreach ($xpath_targets as $_xpath_target) {

                        $dom = $this->insertTransformation(
                            $dom,
                            $this->getGroupPath($_xpath_target, false),
                            null,
                            $_copy_node_source->nodeValue,
                            $dom->queryNode($full_path_current_node),
                            true
                        );
                    }
                    break;
                case 'segment':
                    $full_path_current_node = $_copy_node_source->getNodePath();
                    $path = $this->getSegmentPath($full_path_current_node);
                    $full_path_current_node = str_replace($path, '', $full_path_current_node);

                    foreach ($xpath_targets as $_xpath_target) {
                        $dom = $this->insertTransformation(
                            $dom,
                            $this->getSegmentPath($_xpath_target, false),
                            null,
                            $_copy_node_source->nodeValue,
                            $dom->queryNode($full_path_current_node),
                            true
                        );
                    }
                    break;
                default:
            }
        }

        return $dom;
    }

    /**
     * @param string $path
     * @param bool $relative_path
     *
     * @return string|null
     */
    public function getSegmentPath(string $path, bool $relative_path = true) : ?string {
        $nodes = explode('/', $path);
        $nodes = array_reverse($nodes);

        $string_delete = '';
        $found = false;
        foreach ($nodes as $node_name) {
            if ($found) {
                continue;
            }

            if (strpos($node_name, ".") !== false) {
                $string_delete = $string_delete != '' ? $node_name . '/'. $string_delete : $node_name;
                continue;
            }

            $found = true;
        }

        if ($relative_path) {
            $string_delete = '/' . $string_delete;
        }

        return $string_delete;
    }

    /**
     * @param string $path
     * @param bool $relative_path
     * @return string|null
     */
    public function getGroupPath(string $path, bool $relative_path = true) : ?string {
        $nodes = explode('/', $path);
        $nodes = array_reverse($nodes);

        $string_delete = '';
        $found = false;
        foreach ($nodes as $node_name) {
            if ($found) {
                continue;
            }

            if (strpos($node_name, ".") !== false && strpos($node_name, "_") !== false) {
                $found = true;
                continue;
            }

            $string_delete = $string_delete != '' ? $node_name . '/'. $string_delete : $node_name;
        }

        if ($relative_path) {
            $string_delete = '/' . $string_delete;
        }

        return $string_delete;
    }
}
