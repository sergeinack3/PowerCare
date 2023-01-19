<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use DOMNode;
use DOMNodeList;

/**
 * Trait xpath CDA
 */
trait CCDAXPathTrait
{
    /** @var CCDADomDocument */
    protected $dom;

    /** @var DOMNode */
    private $root;

    private function getRoot(): DOMNode
    {
        if ($this->root) {
            return $this->root;
        }

        $xpath  = new CCDAXPath($this->dom);
        $prefix = $xpath::PREFIX_NS;

        return $this->root = $xpath->queryUniqueNode("//$prefix:ClinicalDocument");
    }

    /**
     * Get the nodeList corresponding to an XPath
     *
     * @param string       $nodeName    The XPath to the node
     * @param DOMNode|null $contextNode The context node from which the XPath starts
     * @param array|null   $data        Nodes data
     *
     * @return DOMNodeList|null
     */
    public function queryNodes(string $nodeName, DOMNode $contextNode = null): ?DOMNodeList
    {
        return $this->query("$nodeName", $contextNode);
    }

    /**
     * Query
     *
     * @param string  $nodeName    The XPath to the node
     * @param DOMNode $contextNode The context node from which the XPath starts
     *
     * @return DOMNodeList|null
     */
    public function query(string $nodeName, DOMNode $contextNode = null): ?DOMNodeList
    {
        if (!$contextNode) {
            $contextNode = $this->getRoot();
        }

        $xpath = new CCDAXPath($contextNode ? $contextNode->ownerDocument : $this->dom);
        $path  = $this->preparePath($nodeName);

        return $xpath->query($path, $contextNode);
    }

    /**
     * Get the text of a node corresponding to an XPath
     *
     * @param string       $nodeName    The XPath to the node
     * @param DOMNode|null $contextNode The context node from which the XPath starts
     * @param string       $purgeChars  The chars to remove from the text
     * @param bool         $addslashes  Escape slashes is the return string
     *
     * @return string
     */
    public function queryTextNode(
        string $nodeName,
        DOMNode $contextNode = null,
        string $purgeChars = "",
        bool $addslashes = false
    ): ?string {
        if (!$contextNode) {
            $contextNode = $this->getRoot();
        }

        $xpath = new CCDAXPath($contextNode ? $contextNode->ownerDocument : $this->dom);
        $path  = $this->preparePath($nodeName);

        return $xpath->queryTextNode($path, $contextNode, $purgeChars, $addslashes);
    }

    /**
     * Get the value of attribute
     *
     * @param DOMNode $node       Node
     * @param string  $attName    Attribute name
     * @param string  $purgeChars The input string
     *
     * @return string
     */
    public function getValueAttributNode(
        ?DOMNode $node,
        string $attName,
        string $purgeChars = ""
    ): ?string {
        if (!$node) {
            return null;
        }

        $xpath = new CCDAXPath($this->dom);

        return $xpath->getValueAttributNode($node, $attName, $purgeChars);
    }

    /**
     * Get the text of a attribute corresponding to an XPath
     *
     * @param string       $nodeName    The XPath to the node
     * @param DOMNode|null $contextNode The context node from which the XPath starts
     * @param string       $attName     Attribute name
     *
     * @return string|null
     */
    public function queryAttributeNodeValue(string $nodeName, ?DOMNode $contextNode, string $attName): ?string
    {
        if (!$contextNode) {
            $contextNode = $this->getRoot();
        }

        $xpath = new CCDAXPath($contextNode ? $contextNode->ownerDocument : $this->dom);
        $path  = $this->preparePath($nodeName);

        return $xpath->queryAttributNode($path, $contextNode, $attName);
    }

    /**
     * Get the node corresponding to an XPath
     *
     * @param string       $nodeName    The XPath to the node
     * @param DOMNode|null $contextNode The context node from which the XPath starts
     *
     * @return DOMNode|null The node
     */
    public function queryNode(string $nodeName, DOMNode $contextNode = null): ?DOMNode
    {
        if (!$contextNode) {
            $contextNode = $this->getRoot();
        }

        $xpath = new CCDAXPath($contextNode ? $contextNode->ownerDocument : $this->dom);
        $path  = $this->preparePath($nodeName);

        return $xpath->queryUniqueNode($path, $contextNode);
    }

    /**
     * Get Nodes  which match with path and contains node with a component templated id
     *
     * @param string|string[] $components
     * @param string          $template_id
     * @param DOMNode|null    $contextNode
     *
     * @return DOMNodeList|null
     */
    public function queryFromTemplateId($components, string $template_id, DOMNode $contextNode = null): ?DOMNodeList
    {
        $query_path = $this->preparePath($components);
        $query_path .= "[./templateId[@root='$template_id']]";

        return $this->query($query_path, $contextNode);
    }

    /**
     * Prepare path with prefix of cda file
     *
     * @param string|string[] $components
     *
     * @return string
     */
    public static function preparePath($components): string
    {
        if (is_string($components) && substr_count($components, '/') > 0) {
            $components = explode('/', $components);
        }

        if (!is_array($components)) {
            $components = [$components];
        }

        $prefix     = CCDAXPath::PREFIX_NS;
        $query_path = '';
        foreach ($components as $component) {
            $query_empty_slash = str_replace('/', '', $query_path);
            $slash             = ($query_empty_slash === "" ? '' : '/');
            $already_prefixed  = str_contains($component, "$prefix:");
            $is_brut           = $already_prefixed || !preg_match("/^\w/", $component);
            if ($is_brut && $component !== "") {
                $query_path .= $slash . $component;
                continue;
            }

            $query_path .= $slash . ($component ? "$prefix:$component" : "/");
        }

        return str_replace('///', '//', $query_path);
    }
}
