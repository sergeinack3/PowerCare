<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\UserManagement;

use Ox\Mediboard\Jfse\Domain\UserManagement\User;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserConfiguration;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserParameter;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class UserConfigurationTest extends UnitTestJfse
{
    /** @var UserConfiguration  */
    private $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = UserConfiguration::hydrate([
            'user' => User::hydrate(['id' => 1]),
            'conventions_folder_path' => '/home/991111170/',
            'cerfas_list' => [
                'fse_000000001.pdf',
                'fse_000000002.pdf',
                'fse_000000006.pdf',
            ],
            'parameters' => [
                UserParameter::hydrate(['id' => 127, 'name' => 'Contrat tarifaire', 'value' => 1]),
                UserParameter::hydrate(['id' => 52,  'name' => 'TP AMO', 'value' => true])
            ]
        ]);
    }

    public function testGetUser(): void
    {
        $this->assertEquals(User::hydrate(['id' => 1]), $this->config->getUser());
    }

    public function testGetConventionsFolderPath(): void
    {
        $this->assertEquals('/home/991111170/', $this->config->getConventionsFolderPath());
    }

    public function testGetCerfasList(): void
    {
        $this->assertEquals([
            'fse_000000001.pdf',
            'fse_000000002.pdf',
            'fse_000000006.pdf',
        ], $this->config->getCerfasList());
    }

    public function testGetParameters(): void
    {
        $this->assertEquals([
            UserParameter::hydrate(['id' => 127, 'name' => 'Contrat tarifaire', 'value' => 1]),
            UserParameter::hydrate(['id' => 52,  'name' => 'TP AMO', 'value' => true])
        ], $this->config->getParameters());
    }
}
