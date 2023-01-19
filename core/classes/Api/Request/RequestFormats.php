<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Symfony\Component\HttpFoundation\Request;

class RequestFormats implements IRequestParameter
{
    /** @var string */
    public const HEADER_KEY_WORD = 'Accept';

    /** @var string */
    public const FORMAT_JSON = 'application/json';

    /** @var string */
    public const FORMAT_JSON_API = 'application/vnd.api+json';

    /** @var string */
    public const FORMAT_XML = 'application/xml';

    /** @var string */
    public const FORMAT_HTML = 'text/html';

    /** @var string */
    public const FORMAT_BODY_FORM = 'form-data';

    /** @var string */
    public const FORMAT_BODY_URL = 'x-www-form-urlencoded';

    /** @var string */
    public const FORMAT_BODY_BIN = 'binary';

    /** @var array */
    public const FORMATS = [
        self::FORMAT_JSON,
        self::FORMAT_JSON_API,
        self::FORMAT_XML,
        self::FORMAT_HTML,
    ];

    /** @var array */
    public const FORMATS_BODY = [
        self::FORMAT_BODY_FORM,
        self::FORMAT_BODY_URL,
        self::FORMAT_BODY_BIN,
    ];

    /** @var array $formats */
    private $formats;

    /**
     * CResourceLinks constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $formats       = $request->headers->get(static::HEADER_KEY_WORD, static::FORMAT_JSON);
        $this->formats = explode(',', $formats);
        //        if (!in_array(static::FORMAT_JSON, $this->formats, true) && !in_array(
        //                static::FORMAT_XML,
        //                $this->formats,
        //                true
        //            )) {
        //            // todo throw new ApiRequestException('Invalid content negociation'); & reactive TU
        //        }
    }

    /**
     * @return array
     */
    public function getFormats(): array
    {
        return $this->formats;
    }

    /**
     * @return string
     */
    public function getExpected(): string
    {
        if (in_array(static::FORMAT_XML, $this->formats, true)) {
            return static::FORMAT_XML;
        }

        return static::FORMAT_JSON;
    }
}
