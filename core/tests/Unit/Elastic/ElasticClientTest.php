<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic;

use Elasticsearch\ClientBuilder;
use Ox\Core\Elastic\ElasticClient;
use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Elastic\ElasticObjectMappings;
use Ox\Core\Elastic\ElasticObjectSettings;
use Ox\Core\Elastic\Exceptions\ElasticClientException;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class ElasticClientTest extends OxUnitTestCase
{
    private static string $index_id;
    /** @var ElasticObject */
    private static MockObject    $obj;
    private static ElasticClient $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$index_id = uniqid();
        self::$obj      = self::buildElasticObjectMock();
        self::$client   = ElasticObjectManager::getDsn(self::$obj)->getClient();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        ElasticObjectManager::getInstance()->clear(self::$obj);
    }

    public function testCountCallWithChrono(): void
    {
        self::$client->ping();
        self::assertEquals(1, self::$client::getChrono()->nbSteps);
        self::$client->ping();
        self::$client->ping();
        self::assertEquals(3, self::$client::getChrono()->nbSteps);
        self::assertGreaterThan(0, self::$client::getChrono()->total);
    }

    public function testCallOnNamespaceCheckCloneStatus(): void
    {
        $ref_class = new ReflectionClass(ElasticClient::class);
        $namespace = $ref_class->getProperty("namespace");
        $namespace->setAccessible(true);

        self::assertNull($namespace->getValue(self::$client));

        $indice_namespace = self::$client->indices();

        self::assertInstanceOf(ElasticClient::class, $indice_namespace);

        self::assertNull($namespace->getValue(self::$client));
        self::assertEquals("indices", $namespace->getValue($indice_namespace));
    }

    public function testCallNamespaceAndMethod(): void
    {
        $query = [
            "index" => self::$obj->getSettings()->getIndexPattern(),
            "allow_no_indices" => false,
        ];

        $indice_exists = self::$client->indices()->exists($query);

        self::assertFalse($indice_exists);

        ElasticObjectManager::init(self::$obj);

        $indice_exists = self::$client->indices()->exists($query);

        self::assertTrue($indice_exists);
    }

    public function testCallWithoutElasticClientConnectedWithoutCurl(): void
    {
        $this->expectException(ElasticClientException::class);

        $es_client = ClientBuilder::create()
            ->setHosts(["http://toto:9200/"])
            ->build();

        $client_not_connected = new ElasticClient($es_client);
        $client_not_connected->ping();
    }

    public function testCallWithoutElasticClientConnectedWithCurl(): void
    {
        $this->expectException(ElasticClientException::class);
        $options = [
            "client" => [
                "curl" => [
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_TIMEOUT        => 3,
                ],
            ],
        ];

        $es_client = ClientBuilder::create()
            ->setHosts(["http://toto:9200/"])
            ->setConnectionParams($options)
            ->setRetries(1)
            ->build();

        $client_not_connected = new ElasticClient($es_client);
        $client_not_connected->ping();
    }

    public static function buildElasticObjectMock(): MockObject
    {
        $testClass    = new self();
        $mockSettings = $testClass->getMockBuilder(ElasticObjectSettings::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getConfigDsn"])
            ->getMock();
        $mockSettings->expects($testClass->any())->method('getConfigDsn')->willReturn(
            ApplicationLog::DATASOURCE_NAME
        );

        /** @var $mockSettings ElasticObjectSettings */
        $mockSettings->setIndexName("test_index_name-" . self::$index_id);
        $mockSettings->setShards(1);
        $mockSettings->setReplicas(0);

        $mappings = new ElasticObjectMappings();

        $object = $testClass->getMockBuilder(ElasticObject::class)
            ->onlyMethods(['setMappings', 'getMappings', 'getSettings', 'setSettings'])
            ->getMock();
        $object->expects($testClass->any())->method('setMappings')->willReturn(
            $mappings
        );
        $object->expects($testClass->any())->method('getMappings')->willReturn(
            $mappings
        );
        $object->expects($testClass->any())->method('getSettings')->willReturn(
            $mockSettings
        );
        $object->expects($testClass->any())->method('setSettings')->willReturn(
            $mockSettings
        );

        return $object;
    }
}
