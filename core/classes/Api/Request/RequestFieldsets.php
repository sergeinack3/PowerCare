<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Symfony\Component\HttpFoundation\Request;

class RequestFieldsets implements IRequestParameter
{
    /** @var string */
    public const QUERY_KEYWORD = 'fieldsets';

    /** @var string */
    public const QUERY_KEYWORD_NONE = 'none';

    /** @var string */
    public const QUERY_KEYWORD_ALL = 'all';

    public const FIELDSETS_SEPARATOR = ',';

    /** @var array */
    private $fieldsets = [];


    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        if ($_filedsets = $request->query->get(self::QUERY_KEYWORD)) {
            $this->fieldsets = explode(self::FIELDSETS_SEPARATOR, $_filedsets);
        }
    }

    /**
     * @return array
     */
    public function getFieldsets(): array
    {
        return $this->fieldsets;
    }
}
