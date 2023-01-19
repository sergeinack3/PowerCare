<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic;

use Elasticsearch\ClientBuilder;
use Ox\Core\Elastic\ElasticIndexConfig;
use Ox\Core\Elastic\ElasticIndexManager;
use Ox\Core\Elastic\ElasticObjectSettings;
use Ox\Core\Elastic\Exceptions\ElasticBadRequest;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Tests\OxUnitTestCase;

class ElasticIndexManagerTest extends OxUnitTestCase
{
    private ElasticIndexManager $elasticIndexManager;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->elasticIndexManager = ElasticIndexManager::get("test_index_elastic_mediboard");
    }

    public function testGetServerStatus()
    {
        $actual = $this->elasticIndexManager->getServerStatus();
        $this->assertArrayHasKey("nodes", $actual);
        $this->assertArrayHasKey("server", $actual);
        $this->assertArrayNotHasKey("errors", $actual);
    }

    public function testGetStatus()
    {
        $actual = $this->elasticIndexManager->getStatus(new ApplicationLog());
        $this->assertArrayHasKey("nodes", $actual);
        $this->assertArrayHasKey("server", $actual);
        $this->assertArrayHasKey("index", $actual);
        $this->assertArrayHasKey("template", $actual);
        $this->assertArrayHasKey("ilm", $actual);
        $this->assertArrayNotHasKey("errors", $actual);
    }

    public function testGetIndexManager(): void
    {
        $manager = ElasticIndexManager::get("test_index_elastic_mediboard");
        $actual  = $manager->isOnline();
        $this->assertTrue($actual);
    }

    public function testIsElasticSearchOnline(): void
    {
        $actual = $this->elasticIndexManager->isOnline();
        $this->assertTrue($actual);
    }
}
