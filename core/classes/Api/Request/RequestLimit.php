<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Extractor for the limit and offset keywords from the request.
 */
class RequestLimit implements IRequestParameter
{
    /** @var string */
    public const QUERY_KEYWORD_OFFSET = 'offset';

    /** @var string */
    public const QUERY_KEYWORD_LIMIT = 'limit';

    /** @var int */
    private $offset;

    /** @var int */
    private $limit;

    /** @var bool */
    private $in_query;

    /** @var int */
    public const OFFSET_DEFAULT = 0;

    /** @var int */
    public const LIMIT_DEFAULT = 50;

    /** @var int */
    public const LIMIT_MAX = 100;

    /**
     * RequestLimit constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->in_query = (
            $request->query->get(self::QUERY_KEYWORD_OFFSET)
            || $request->query->get(self::QUERY_KEYWORD_LIMIT)
        );

        $this->offset = (int)$request->query->get('offset', static::OFFSET_DEFAULT);
        $this->limit  = (int)$request->query->get('limit', static::LIMIT_DEFAULT);
        $this->limit  = min($this->limit, static::LIMIT_MAX);
    }

    /**
     * @return string
     * @example [offset, limit]
     */
    public function getSqlLimit(): string
    {
        return "{$this->offset},{$this->limit}";
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function isInQuery(): bool
    {
        return $this->in_query;
    }
}
