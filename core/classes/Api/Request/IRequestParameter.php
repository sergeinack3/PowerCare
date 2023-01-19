<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface IRequestParameter
 */
interface IRequestParameter
{
    /**
     * IRequestApiParameter constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request);
}
