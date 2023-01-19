<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request\Content;

use Exception;
use Ox\Core\Api\Request\RequestFormats;
use Ox\Core\Api\Request\IRequestParameter;
use Symfony\Component\HttpFoundation\Request;

class RequestContent implements IRequestParameter
{
    public const CONTENT_TYPE_KEYWORD = 'Content-Type';

    /** @var string */
    private $content;

    /** @var string */
    private $content_type;

    public function __construct(Request $request)
    {
        $this->content      = $request->getContent();
        $this->content_type = $request->headers->get(self::CONTENT_TYPE_KEYWORD);
    }

    public function getRawContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param bool   $json_decode
     * @param string $encode_to
     * @param string $encode_from
     *
     * @return false|mixed|resource|string|null
     * @throws Exception
     */
    public function getContent(bool $json_decode = true, string $encode_to = null, string $encode_from = 'UTF-8')
    {
        $content = $this->content;

        if ($json_decode) {
            $content_decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $content = $content_decoded;
            }
        }

        if ($encode_to) {
            array_walk_recursive(
                $content,
                function (&$item) use ($encode_to, $encode_from): void {
                    $item = mb_convert_encoding($item, $encode_to, $encode_from);
                }
            );
        }

        return $content;
    }

    /**
     * @throws RequestContentException
     */
    public function getJsonApiResource(): JsonApiResource
    {
        if (!$this->isJsonApi()) {
            throw RequestContentException::contentIsNotJsonApi();
        }

        return new JsonApiResource($this->content);
    }

    private function isJsonContent(): bool
    {
        json_decode($this->content, true);

        return json_last_error() === JSON_ERROR_NONE;
    }

    private function isJsonApi(): bool
    {
        return ($this->content_type === RequestFormats::FORMAT_JSON_API) && $this->isJsonContent();
    }
}
