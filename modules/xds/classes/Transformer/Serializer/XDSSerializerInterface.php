<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Transformer\Serializer;

use Ox\Interop\Xds\CXDSXmlDocument;
use Ox\Interop\Xds\Structure\XDSElementInterface;

/**
 * Serializer XDS interface
 */
interface XDSSerializerInterface
{
    /**
     * @param XDSElementInterface $xds_element
     *
     * @return string
     */
    public function serialize(XDSElementInterface $xds_element): string;

    /**
     * @return CXDSXmlDocument|null
     */
    public function getXmlDocument(): ?CXDSXmlDocument;
}
