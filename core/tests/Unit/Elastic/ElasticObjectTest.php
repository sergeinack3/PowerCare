<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic;

use DateTimeImmutable;
use DateTimeZone;
use Ox\Core\Elastic\ElasticIndexManager;
use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Elastic\ElasticObjectMappings;
use Ox\Core\Elastic\ElasticObjectRepositories;
use Ox\Core\Elastic\ElasticObjectSettings;
use Ox\Core\Elastic\Encoding;
use Ox\Core\Elastic\Exceptions\ElasticObjectException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ElasticObjectTest extends OxUnitTestCase
{
    private static string $index_id;

    public static function setUpBeforeClass(): void
    {
        self::$index_id = uniqid();
        $object = self::buildElasticObjectMockWithDefaultMappingsAndConnectedToElastic();
        ElasticObjectManager::createTemplate($object);
        ElasticObjectManager::createFirstIndex($object);
    }

    public static function tearDownAfterClass(): void
    {
        ElasticObjectManager::getInstance()->clear(
            self::buildElasticObjectMockWithDefaultMappingsAndConnectedToElastic()
        );
    }

    public function testToArray(): void
    {
        $mappings = new ElasticObjectMappings();
        $mappings->addStringField("string", Encoding::UTF_8)
            ->addFloatField("float")
            ->addIntField("number");

        /** @var ElasticObject $object */
        $object         = $this->buildElasticObjectMock($mappings);
        $object->string = "Test - 1";
        $object->number = 102;
        $object->float  = 07.47;

        $data     = $object->toArray();
        $mappings = $object->getMappings();
        foreach ($mappings as $field => $mapping) {
            self::assertArrayHasKey($field, $data);
        }
    }

    public function testEncodingUTF8ToUTF8(): void
    {
        $mappings = new ElasticObjectMappings();
        $mappings->addStringField("utf8", Encoding::UTF_8);

        /** @var ElasticObject $object */
        $object       = $this->buildElasticObjectMock($mappings);
        $object->utf8 = "éÀÂÉÈÊÎÏÙÛÇàâéèêîïùûç";

        $data = $object->toArray();
        self::assertEquals($data["utf8"], $object->utf8);

        /** @var ElasticObject $object2 */
        $object2 = $this->buildElasticObjectMock($mappings);
        $object2 = $object2->fromArray($data);
        self::assertEquals($object2->utf8, $object->utf8);
    }

    public function testEncodingISO88591ToUTF8(): void
    {
        $mappings = new ElasticObjectMappings();
        $mappings->addStringField("iso88591", Encoding::ISO_8859_1);

        /** @var ElasticObject $object */
        $object           = $this->buildElasticObjectMock($mappings);
        $object->iso88591 = "éÀÂÉÈÊÎÏÙÛÇàâéèêîïùûç";

        $data = $object->toArray();
        self::assertNotEquals($data["iso88591"], $object->iso88591);

        /** @var ElasticObject $object2 */
        $object2 = $this->buildElasticObjectMock($mappings);
        $object2 = $object2->fromArray($data);
        self::assertEquals($object2->iso88591, $object->iso88591);
    }

    public function testFromArray(): void
    {
        $data = [
            "id"       => "azerty",
            "message"  => "TEST EXECUTION - Test1",
            "date"     => "2021-12-22T15:44:55.055600 Europe/Paris (+02:00)",
            "user_id"  => 32,
            "username" => "test",
        ];

        $mappings = new ElasticObjectMappings();
        $mappings->addStringField("message", Encoding::UTF_8)
            ->addRefField("user_id", CUser::class, false)
            ->addStringField("username", Encoding::UTF_8);

        $object = $this->buildElasticObjectMock($mappings);

        $object = $object->fromArray($data);
        self::assertEquals($data["id"], $object->getId());
        self::assertEquals($data["message"], $object->message);
        self::assertInstanceOf(DateTimeImmutable::class, $object->getDate());
        self::assertEquals("Europe/Paris", $object->getDate()->getTimezone()->getName());
        self::assertEquals($data["user_id"], $object->user_id);
    }

    public function testFromArrayWithHighlight(): void
    {
        $data     = [
            "id"        => "azerty",
            "highlight" => [
                "message" => [
                    0 => "Test",
                ],
            ],
            "message"   => "TEST EXECUTION - Test1",
            "date"      => "2021-12-22T15:44:55.055600 Europe/Paris (+02:00)",
            "user_id"   => 32,
            "username"  => "test",
        ];
        $mappings = new ElasticObjectMappings();
        $mappings->addStringField("message", Encoding::UTF_8)
            ->addRefField("user_id", CUser::class, false)
            ->addStringField("username", Encoding::UTF_8);

        $object = $this->buildElasticObjectMock($mappings);

        $object = $object->fromArray($data);
        self::assertEquals($data["id"], $object->getId());
        self::assertEquals($data["highlight"]["message"][0], $object->getHighlight()["message"]);
        self::assertInstanceOf(DateTimeImmutable::class, $object->getDate());
        self::assertEquals($data["user_id"], $object->user_id);
        self::assertEquals($data["username"], $object->username);
    }

    public function testGettingANotLoadedRef(): void
    {
        self::expectException(ElasticObjectException::class);
        $mappings = new ElasticObjectMappings();
        $mappings->addRefField("user_id", CUser::class, false);

        /** @var ElasticObject $object */
        $object = $this->buildElasticObjectMock($mappings);
        $object->getRef("user_id");
    }

    public function testSendingDateToElasticAndGettingItBackWithADateNowWithDefaultTimezone(): void
    {
        /** @var ElasticObject $object */
        $object   = self::buildElasticObjectMockWithDefaultMappingsAndConnectedToElastic();
        $date     = new DateTimeImmutable();
        $timezone = $date->getTimezone();
        $object->setDate($date);

        $manager = ElasticObjectManager::getInstance();
        $object  = $manager->store($object);

        /** @var ElasticObjectRepositories $repository */
        $repository = $this->buildElasticObjectRepositoryMock($object, $manager::getDsn($object));
        $object2    = $repository->findById($object->getId());

        $actual_date     = $object2->getDate();
        $actual_timezone = $actual_date->getTimezone();

        self::assertEquals($actual_date, $date);
        self::assertEquals($actual_timezone, $timezone);
    }

    public function testSendingDateToElasticAndGettingItBackWithABasicDateFormatWithoutTimezone(): void
    {
        /** @var ElasticObject $object */
        $object   = self::buildElasticObjectMockWithDefaultMappingsAndConnectedToElastic();
        $date     = DateTimeImmutable::createFromFormat("Y-m-d", "2022-05-01");
        $timezone = $date->getTimezone();
        $object->setDate($date);

        $manager = ElasticObjectManager::getInstance();
        $object  = $manager->store($object);

        /** @var ElasticObjectRepositories $repository */
        $repository = $this->buildElasticObjectRepositoryMock($object, $manager::getDsn($object));
        $object2    = $repository->findById($object->getId());

        $actual_date     = $object2->getDate();
        $actual_timezone = $actual_date->getTimezone();

        self::assertEquals($actual_date, $date);
        self::assertEquals($actual_timezone, $timezone);
    }

    public function testSendingDateToElasticAndGettingItBackWithADateWithNewYorkTimezone(): void
    {
        /** @var ElasticObject $object */
        $object   = self::buildElasticObjectMockWithDefaultMappingsAndConnectedToElastic();
        $timezone = new DateTimeZone("America/New_York");
        $date     = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", "2022-05-01 08:14:57", $timezone);
        $object->setDate($date);

        $manager = ElasticObjectManager::getInstance();
        $object  = $manager->store($object);

        /** @var ElasticObjectRepositories $repository */
        $repository = $this->buildElasticObjectRepositoryMock($object, $manager::getDsn($object));
        $object2    = $repository->findById($object->getId());

        $actual_date     = $object2->getDate();
        $actual_timezone = $actual_date->getTimezone();

        self::assertEquals($actual_date, $date);
        self::assertEquals($actual_timezone, $timezone);
    }


    public function buildElasticObjectMock(ElasticObjectMappings $mappings): MockObject
    {
        $mockSettings = $this->getMockBuilder(ElasticObjectSettings::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object = $this->getMockBuilder(ElasticObject::class)
            ->onlyMethods(['getMappings', 'setMappings', 'setSettings'])
            ->getMock();
        $object->expects($this->any())->method('getMappings')->willReturn(
            $mappings
        );
        $object->expects($this->any())->method('setMappings')->willReturn(
            $mappings
        );
        $object->expects($this->any())->method('setSettings')->willReturn(
            $mockSettings
        );

        return $object;
    }


    public static function buildElasticObjectMockWithDefaultMappingsAndConnectedToElastic(): MockObject
    {
        $test_class = new self();

        $mockSettings = $test_class->getMockBuilder(ElasticObjectSettings::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getConfigDsn"])
            ->getMock();
        $mockSettings->expects($test_class->any())->method('getConfigDsn')->willReturn(
            ApplicationLog::DATASOURCE_NAME
        );

        $mockSettings->setIndexName("test_index_name" . self::$index_id);
        $mockSettings->setShards(1);
        $mockSettings->setReplicas(0);

        $object = $test_class->getMockBuilder(ElasticObject::class)
            ->onlyMethods(['setMappings', 'getSettings', 'setSettings'])
            ->getMock();
        $object->expects($test_class->any())->method('setMappings')->willReturn(
            new ElasticObjectMappings()
        );
        $object->expects($test_class->any())->method('getSettings')->willReturn(
            $mockSettings
        );
        $object->expects($test_class->any())->method('setSettings')->willReturn(
            $mockSettings
        );

        return $object;
    }

    public function buildElasticObjectRepositoryMock(
        ElasticObject $elastic_object,
        ElasticIndexManager $dsn
    ): MockObject {
        $object = $this->getMockBuilder(ElasticObjectRepositories::class)
            ->onlyMethods(["getElasticObject"])
            ->setConstructorArgs([$elastic_object, $dsn])
            ->getMock();

        $object->expects($this->any())->method('getElasticObject')->willReturn(
            $elastic_object
        );

        return $object;
    }

}
