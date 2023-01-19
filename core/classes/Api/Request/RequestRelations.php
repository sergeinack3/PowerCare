<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Symfony\Component\HttpFoundation\Request;

class RequestRelations implements IRequestParameter
{
    /** @var string */
    public const QUERY_KEYWORD_INCLUDE = 'relations';

    /** @var string */
    public const QUERY_KEYWORD_EXCLUDE = 'relations_excluded';

    /** @var string */
    public const QUERY_KEYWORD_NONE = 'none';

    /** @var string */
    public const QUERY_KEYWORD_ALL = 'all';

    public const RELATION_SEPARATOR = ',';

    /** @var array */
    private $includes = [];

    /** @var array */
    private $excludes = [];

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        if ($_include = $request->query->get(static::QUERY_KEYWORD_INCLUDE)) {
            $this->includes = explode(self::RELATION_SEPARATOR, $_include);
        }

        if ($_exclude = $request->query->get(static::QUERY_KEYWORD_EXCLUDE)) {
            $this->excludes = explode(self::RELATION_SEPARATOR, $_exclude);
        }
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->includes;
    }

    /**
     * @return array
     */
    public function getRelationsExcludes(): array
    {
        return $this->excludes;
    }
}
