<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Etag;

use Ox\Core\CController;
use Symfony\Component\HttpFoundation\JsonResponse;

class EtagController extends CController
{
    public function responseWithoutEtag()
    {
        return new JsonResponse(static::getJson());
    }

    public function responseWithEtag()
    {
        $response = new JsonResponse(static::getJson());
        $response->setEtag(static::makeEtag());

        return $response;
    }

    public static function getJson()
    {
        return json_encode(['lorem' => 'ipsum', 'foo' => 'bar']);
    }

    public static function makeEtag()
    {
        return md5(static::getJson());
    }
}
