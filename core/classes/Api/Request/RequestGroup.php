<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Etablissement\CGroups;
use Symfony\Component\HttpFoundation\Request;

class RequestGroup implements IRequestParameter
{
    public const HEADER_GROUP = 'X-ORGANIZATION-ID';

    public const QUERY_GROUP = ['group_id', 'g'];

    /** @var CGroups */
    private $current_group;

    public function __construct(Request $request)
    {
        if ($request->headers->has(self::HEADER_GROUP)) {
            $group_id = (int)$request->headers->get(self::HEADER_GROUP);
        } elseif ($this->hasQueryGroupKeyword($request)) {
            $group_id = $this->getQueryGroupKeyword($request);
        } else {
            // TODO Remove when using symfony sessionManager
            $group_id = $_SESSION['g'] ?? null;
        }

        if ($group_id) {
            try {
                $group = CGroups::findOrFail($group_id);
                if (!$group->getPerm(PERM_READ)) {
                    throw new CMbModelNotFoundException('common-error-Object not found');
                }
            } catch (CMbModelNotFoundException $e) {
                throw new ApiException($e->getMessage());
            }
        } else {
            $group = CGroups::loadCurrent();
        }

        $this->current_group = $group;

        // TODO Remove when not needed anymore
        $GLOBALS['g'] = $this->current_group->_id;
    }


    public function getGroup(): CGroups
    {
        return $this->current_group;
    }

    private function hasQueryGroupKeyword(Request $request): bool
    {
        foreach (self::QUERY_GROUP as $keyword) {
            if ($request->query->has($keyword)) {
                return true;
            }
        }

        return false;
    }

    private function getQueryGroupKeyword(Request $request): ?int
    {
        foreach (self::QUERY_GROUP as $keyword) {
            if ($request->query->has($keyword)) {
                return $request->query->getInt($keyword);
            }
        }

        return null;
    }
}
