<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Transformer\Parser;

use DOMNode;
use Ox\Interop\Xds\Structure\XDSElementInterface;

/**
 * Parse XDS interface
 */
interface XDSParserInterface
{
    /**
     * @param string $content
     *
     * @return XDSElementInterface|null
     */
    public function parse(string $content): ?XDSElementInterface;

    /**
     * @param string $node
     *
     * @return XDSElementInterface|null
     */
    public function parseNode(DOMNode $node): ?XDSElementInterface;
}
