<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * The CMbXPath class
 */
class CMbXPath extends DOMXPath implements IShortNameAutoloadable
{
    /**
     * Get the numchar text of a node corresponding to an XPath
     *
     * @param string $query The XPath to the node
     * @param int|null $length If length is given and is positive, the string returned will contain at most length
     *                              characters
     * @param DOMNode $contextNode The context node from which the XPath starts
     *
     * @return string
     */
    function queryNumcharNode($query, $length, DOMNode $contextNode = null)
    {
        if (null == $text = $this->queryTextNode($query, $contextNode, " /-.")) {
            return;
        }

        $text = substr($text, 0, $length);
        $text = str_pad($text, $length, "0", STR_PAD_LEFT);
        $text = strtr($text, "O", "0"); // Usual trick

        return $text;
    }

    /**
     * Get the text of a node corresponding to an XPath
     *
     * @param string $query The XPath to the node
     * @param DOMNode $contextNode The context node from which the XPath starts
     * @param string $purgeChars The chars to remove from the text
     * @param boolean $addslashes Escape slashes is the return string
     *
     * @return string The textual value of the node
     * @throws Exception
     */
    function queryTextNode($query, DOMNode $contextNode = null, $purgeChars = "", $addslashes = false)
    {
        $text = null;
        if ($node = $this->queryUniqueNode($query, $contextNode)) {
            $text = $this->convertEncoding($node->textContent);

            if ($text == '""') {
                return '""';
            }

            $text = str_replace(str_split($purgeChars), "", $text);
            $text = trim($text);

            if ($addslashes) {
                $text = addslashes($text);
            }
        }

        return $text;
    }

    /**
     * Get the node corresponding to an XPath
     *
     * @param string $query The XPath to the node
     * @param DOMNode $contextNode The context node from which the XPath starts
     * @param boolean $optional Don't throw an exception if node not found
     *
     * @return DOMNode|null The node
     * @throws Exception
     */
    function queryUniqueNode($query, DOMNode $contextNode = null, $optional = true)
    {
        $query = utf8_encode($query);
        $nodeList = $contextNode ? parent::query($query, $contextNode) : parent::query($query);

        if ($nodeList->length > 1) {
            throw new Exception("Queried node is not unique, found $nodeList->length occurence(s) for '$query'");
        } elseif (!$optional && $nodeList->length == 0) {
            $erreur = "Impossible de trouver l'élément '$query'";

            if ($contextNode) {
                $erreur .= " dans le contexte : " . $this->nodePath($contextNode);
            }

            throw new Exception($erreur);
        } elseif (!$nodeList || $nodeList->length == 0) {
            return null;
        }

        return $nodeList->item(0);
    }

    /**
     * Convert value with ISO-8859-1 characters encoded with UTF-8
     *
     * @param string $value Value
     *
     * @return string
     */
    function convertEncoding($value)
    {
        return utf8_decode($value);
    }

    /**
     * Get the multiline text of a node corresponding to an XPath
     *
     * @param string $query The XPath to the node
     * @param DOMNode $contextNode The context node from which the XPath starts
     * @param string $prefix The string to remove from the text
     *
     * @return string The textual value of the node
     * @throws Exception
     */
    function queryMultilineTextNode($query, DOMNode $contextNode = null, $prefix = "")
    {
        $text = null;

        if ($node = $this->queryUniqueNode($query, $contextNode)) {
            $text = $this->convertEncoding($node->textContent);
            if ($prefix) {
                $text = str_replace($prefix, "", $text);
            }
        }

        return $text;
    }

    /**
     * Get the value of attribute
     *
     * @param string $query The XPath to the node
     * @param DOMNode $contextNode The context node from which the XPath starts
     * @param string $attName Attribute name
     * @param string $purgeChars The input string
     * @param bool $optional Don't throw an exception if node not found
     * @param boolean $addslashes Escape slashes is the return string
     *
     * @return string
     * @throws Exception
     *
     */
    function queryAttributNode(
        $query,
        DOMNode $contextNode = null,
        $attName = '',
        $purgeChars = "",
        $optional = true,
        $addslashes = false
    )
    {
        $text = null;

        if ($node = $this->queryUniqueNode($query, $contextNode, $optional)) {
            $text = $this->convertEncoding($node->getAttribute($attName));
            $text = str_replace(str_split($purgeChars), "", $text);
            $text = trim($text);

            if ($addslashes) {
                $text = addslashes($text);
            }
        }

        return $text;
    }

    /**
     * Get the value of attribute
     *
     * @param DOMNode $node Node
     * @param string $attName Attribute name
     * @param string $purgeChars The input string
     * @param boolean $addslashes Escape slashes is the return string
     *
     * @return string
     */
    function getValueAttributNode(DOMNode $node, $attName, $purgeChars = "", $addslashes = false, $convert = true)
    {
        $text = null;

        if ($att = $node->getAttributeNode($attName)) {
            $text = $att->value;
            if ($convert) {
                $text = $this->convertEncoding($text);
            }
            $text = str_replace(str_split($purgeChars), "", $text);
            $text = trim($text);


            if ($addslashes) {
                $text = addslashes($text);
            }
        }

        return $text;
    }

    /**
     * Get first text node
     *
     * @param string $query The XPath to the node
     * @param DOMNode $contextNode The context node from which the XPath starts
     *
     * @return string
     */
    function getFirstTextNode($query, DOMNode $contextNode = null)
    {
        $textNodes = $this->getMultipleTextNodes($query, $contextNode);

        return isset($textNodes[0]) ? $textNodes[0] : null;
    }

    /**
     * Get multiple text nodes
     *
     * @param string $query The XPath to the node
     * @param DOMNode $contextNode The context node from which the XPath starts
     *
     * @return array
     */
    function getMultipleTextNodes($query, DOMNode $contextNode = null)
    {
        $query = $this->convertEncoding($query);
        $nodeList = $contextNode ? parent::query($query, $contextNode) : parent::query($query);

        $array = [];
        foreach ($nodeList as $n) {
            $array[] = $this->convertEncoding($n->nodeValue);
        }

        return $array;
    }

    /**
     * Get first node
     *
     * @param string $query The XPath to the node
     * @param DOMNode $contextNode The context node from which the XPath starts
     * @param int $index Index
     *
     * @return DOMElement|null The node
     */
    function getNode($query, DOMNode $contextNode = null, $index = 0)
    {
        $query = utf8_encode($query);
        $nodeList = $contextNode ? parent::query($query, $contextNode) : parent::query($query);

        if ($nodeList->length < 1) {
            return null;
        }

        return $nodeList->item($index);
    }
}
