<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Ox\Core\Api\Exceptions\ApiRequestException;
use Symfony\Component\HttpFoundation\Request;

class RequestSort implements IRequestParameter
{
    /** @var string */
    public const QUERY_KEYWORD_SORT = 'sort';

    /** @var string */
    public const SORT_SEPARATOR = ',';

    /** @var Sort[] */
    private $fields;

    /**
     *
     * @param Request $request
     *
     * @throws ApiRequestException
     */
    public function __construct(Request $request)
    {
        $this->fields = $this->getFieldsFromRequest($request);
    }

    /**
     * @return array|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param null $default
     *
     * @return string|null
     */
    public function getSqlOrderBy($default = null): ?string
    {
        if (empty($this->fields)) {
            return $default ?: null;
        }

        // todo check if num or fileds in specs
        $return = [];
        foreach ($this->fields as $sort) {
            $return[] = $sort->toSql();
        }

        return implode(',', $return);
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws ApiRequestException
     */
    private function getFieldsFromRequest(Request $request): array
    {
        $sort = $request->query->get(static::QUERY_KEYWORD_SORT);

        if ($sort === null || $sort === '') {
            return [];
        }

        $fields = [];

        foreach (explode(self::SORT_SEPARATOR, $sort) as $_field) {
            // Prevent SQL injection #1
            if (substr_count($_field, ' ')) {
                throw new ApiRequestException('Malformated sorting fields');
            }

            switch ($_field[0]) {
                case '-':
                    $_type  = Sort::SORT_DESC;
                    $_field = substr($_field, 1);
                    break;
                case '+':
                    $_type  = Sort::SORT_ASC;
                    $_field = substr($_field, 1);
                    break;
                default:
                    $_type = Sort::SORT_ASC;
                    break;
            }

            // Prevent SQL injection #2
            $fields[] = new Sort(addslashes($_field), $_type);
        }

        return $fields;
    }
}
