<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Security\Csrf;

use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Core\Security\Csrf\AntiCsrfRepositoryInterface;
use Ox\Core\Security\Csrf\AntiCsrfToken;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class AntiCsrfTest extends OxUnitTestCase
{
    private function getToken(): AntiCsrfToken
    {
        return AntiCsrfToken::generate('secret', [], 3600);
    }

    private function getRepositoryMock(): AntiCsrfRepositoryInterface
    {
        $repository = $this->getMockBuilder(AntiCsrfRepositoryInterface::class)->getMock();

        $repository->method('getSecret')->willReturn('secret');
        $repository->method('retrieveToken')->willReturn($this->getToken());

        return $repository;
    }

    public function testGetTokenFor(): void
    {
        $this->expectNotToPerformAssertions();

        AntiCsrf::init($this->getRepositoryMock(), 'test');

        AntiCsrf::prepare()->getToken();
    }
}
