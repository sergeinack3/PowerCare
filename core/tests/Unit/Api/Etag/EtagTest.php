<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Etag;

use Ox\Core\Api\Request\Etags;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class EtagTest extends OxUnitTestCase
{
    const URL = 'api/tests/unit';

    public function testCheckHasEtag()
    {
        $etags_random = [$this->forgeEtag(), $this->forgeEtag(), $this->forgeEtag()];
        $etags        = new Etags($etags_random);

        $this->assertCount(3, $etags);
        $this->assertTrue($etags->hasEtag($etags_random[rand(0, 2)]));
    }

    private function forgeEtag()
    {
        return md5(uniqid('hash', true));
    }

    public function testCreateFromRequest()
    {
        $etag_random = $this->forgeEtag();
        $request     = Request::create(static::URL);
        $request->headers->set('if_none_match', $etag_random);
        $etags = Etags::createFromRequest($request);
        $this->assertTrue($etags->hasEtag($etag_random));
    }
}
