<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Transformer\Parser;

use DOMNode;
use Ox\Interop\Xds\CXDSXmlDocument;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\XDSElementInterface;

class EbXmlParser implements XDSParserInterface
{

    /**
     * @param string $content
     * @param string $class_parser
     *
     * @return XDSElementInterface
     */
    public function parse(string $content, string $class_parser = EbXMLToDocumentEntry::class): XDSElementInterface
    {
        $dom = new CXDSXmlDocument();
        $dom->loadXML($content);

        return $this->parseNode($dom);
    }

    /**
     * @param DOMNode $node
     * @param string  $class_parser
     *
     * @return XDSElementInterface
     * @throws CXDSException
     */
    public function parseNode(DOMNode $node, string $class_parser = EbXMLToDocumentEntry::class): XDSElementInterface
    {
        switch ($class_parser) {
            case EbXMLToDocumentEntry::class:
                return (new EbXMLToDocumentEntry())->parseNode($node);
            case EbXMLToAssociation::class:
                return (new EbXMLToAssociation())->parseNode($node);
            case EbXMLToSubmissionSet::class:
                return (new EbXMLToSubmissionSet())->parseNode($node);
            default:
                throw new CXDSException('Invalid parser xds');
        }
    }
}
