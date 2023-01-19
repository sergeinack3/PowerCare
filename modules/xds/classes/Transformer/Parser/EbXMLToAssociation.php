<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Transformer\Parser;

use DOMNode;
use Ox\Interop\Xds\CXDSXmlDocument;
use Ox\Interop\Xds\CXDSXPath;
use Ox\Interop\Xds\Structure\XDSElementInterface;

class EbXMLToAssociation implements XDSParserInterface
{
    public function parse(string $content): ?XDSElementInterface
    {
        $dom = new CXDSXmlDocument();
        $dom->loadXML($content);

        return $this->parseNode($dom);
    }

    public function parseNode(DOMNode $node): ?XDSElementInterface
    {
        $xpath = new CXDSXPath($node);

        $associations = $xpath->query('//rim:Association', $node);

        // need to implement mapping

        return null;
    }
}
