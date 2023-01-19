<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic;

use Ox\Core\Elastic\ElasticIndexConfig;
use Ox\Tests\OxUnitTestCase;

class ElasticIndexConfigTest extends OxUnitTestCase
{
    private string $connection_type = "";

    public function setUp(): void
    {
        parent::setUp();
        $this->connection_type = "http";
    }

    public function testGetBasicUrl(): void
    {
        $host          = "localhost";
        $elasticConfig = new ElasticIndexConfig($host, 9200, "", "");
        $actual        = $elasticConfig->getConnectionParams();
        $expected      = $this->connection_type . "://:@localhost:9200";
        $this->assertEquals([$expected], $actual);
    }

    public function testGetAdvancedUrl(): void
    {
        $host          = "127.0.0.1";
        $port          = 9200;
        $user          = "admin";
        $password      = "123";
        $elasticConfig = new ElasticIndexConfig($host, $port, $user, $password);
        $actual        = $elasticConfig->getConnectionParams();
        $expected      = $this->connection_type . "://admin:123@127.0.0.1:9200";
        $this->assertEquals([$expected], $actual);
    }

    public function testGetters(): void
    {
        $host          = "127.0.0.1";
        $port          = 9200;
        $user          = "admin";
        $password      = "123";
        $elasticConfig = new ElasticIndexConfig($host, $port, $user, $password);
        self::assertEquals($host, $elasticConfig->getHost());
        self::assertEquals($port, $elasticConfig->getPort());
        self::assertEquals($user, $elasticConfig->getUser());
    }
}
