<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Transformer;

use DOMNode;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\XDSElementInterface;
use Ox\Interop\Xds\Transformer\Parser\EbXmlParser;
use Ox\Interop\Xds\Transformer\Parser\XDSParserInterface;
use Ox\Interop\Xds\Transformer\Serializer\EbXmlSerializer;
use Ox\Interop\Xds\Transformer\Serializer\XDSSerializerInterface;

class XDSTransformer
{
    /** @var string */
    public const SERIALIZE_IN_EB_XML = 'eb-xml';

    /**
     * @param XDSElementInterface $xds_element
     * @param string              $format
     *
     * @return string
     * @throws CXDSException
     */
    public static function serialize(
        XDSElementInterface $xds_element,
        string $format = self::SERIALIZE_IN_EB_XML
    ): string {
        $serializer = self::getSerializer($format);

        return $serializer->serialize($xds_element);
    }

    /**
     * @param string $format
     *
     * @return XDSSerializerInterface
     * @throws CXDSException
     */
    public static function getSerializer(string $format = self::SERIALIZE_IN_EB_XML): XDSSerializerInterface
    {
        if ($format === self::SERIALIZE_IN_EB_XML) {
            return new EbXmlSerializer();
        }

        throw CXDSException::invalidSerializerFormat($format);
    }

    /**
     * @param string $content
     *
     * @return XDSParserInterface
     */
    public static function getParser(): XDSParserInterface
    {
        // detect which parser need to be set
        return new EbXmlParser();
    }

    /**
     * @param string $content
     *
     * @return XDSElementInterface
     */
    public static function parse(string $content): XDSElementInterface
    {
        $parser = self::getParser();

        return $parser->parse($content);
    }


    /**
     * @param string $content
     *
     * @return XDSParserInterface
     * @throws CXDSException
     */
    public static function parseNode(DOMNode $node): XDSElementInterface
    {
        // detect which parser need to be set
        return (new EbXmlParser())->parseNode($node);
    }
}
