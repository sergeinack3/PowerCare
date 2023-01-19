<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic;

use DateTimeImmutable;
use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Elastic\ElasticObjectMappings;
use Ox\Core\Elastic\ElasticObjectSettings;
use Ox\Core\Elastic\Encoding;
use Ox\Core\Elastic\Exceptions\ElasticBadRequest;
use Ox\Core\Elastic\IndexLifeManagement\ElasticIndexLifeManager;
use Ox\Core\Elastic\IndexLifeManagement\Exceptions\ElasticIndexLifecycleManagementException;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticDeletePhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticHotPhase;
use Ox\Core\Units\TimeUnitEnum;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

use function PHPUnit\Framework\assertArrayHasKey;

class ElasticObjectManagerTest extends OxUnitTestCase
{
    private static ElasticObjectManager $manager;
    private static string               $index_id;
    /** @var ElasticObject */
    private static $obj;

    /** @var ElasticObject[] */
    private array $objs              = [];
    private int   $totalObjectNumber = 0;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$index_id = uniqid();
        self::$manager  = ElasticObjectManager::getInstance();
        self::$obj      = self::buildElasticObjectMock();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->objs = [];
        /** @var ElasticObject $obj1 */
        $obj1 = $this->buildElasticObjectMock();
        $obj1->setDate(new DateTimeImmutable());
        /** @var ElasticObject $obj2 */
        $obj2 = $this->buildElasticObjectMock();
        $obj2->setDate(new DateTimeImmutable("-1 day"));
        /** @var ElasticObject $obj3 */
        $obj3 = $this->buildElasticObjectMock();
        $obj3->setDate(new DateTimeImmutable("+1 day"));
        /** @var ElasticObject $obj4 */
        $obj4 = $this->buildElasticObjectMock();
        $obj4->setDate(new DateTimeImmutable("-1 week"));

        $this->objs[] = $obj1;
        $this->objs[] = $obj2;
        $this->objs[] = $obj3;
        $this->objs[] = $obj4;

        $this->totalObjectNumber = count($this->objs);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$manager->clear(self::$obj);
    }

    public function testCreateTemplate(): void
    {
        $actual = self::$manager::createTemplate(self::$obj);
        self::assertIsArray($actual);
        self::assertTrue($actual["acknowledged"]);
    }

    public function testDeleteTemplate(): void
    {
        $actual = self::$manager->deleteTemplate(self::$obj);
        self::assertIsArray($actual);
        self::assertTrue($actual["acknowledged"]);
    }

    public function testDeleteTemplateNotExists(): void
    {
        $this->expectException(ElasticBadRequest::class);
        self::$manager->deleteTemplate(self::$obj);
    }

    public function testCreateILMOnObjectWithoutILM(): void
    {
        $this->expectException(ElasticIndexLifecycleManagementException::class);
        self::$manager::createILM(self::$obj);
    }

    public function testDeleteILMNotExists(): void
    {
        $this->expectException(ElasticBadRequest::class);
        self::$manager->deleteILM(self::$obj);
    }

    public function testCreateILM(): void
    {
        /** @var ElasticObject $obj */
        $obj    = $this->buildElasticObjectMock(true);
        $actual = self::$manager::createILM($obj);
        self::assertIsArray($actual);
        self::assertTrue($actual["acknowledged"]);
    }

    public function testDeleteILM(): void
    {
        /** @var ElasticObject $obj */
        $obj    = $this->buildElasticObjectMock(true);
        $actual = self::$manager->deleteILM($obj);
        self::assertIsArray($actual);
        self::assertTrue($actual["acknowledged"]);
    }

    public function testDeleteIndex(): void
    {
        self::$manager::createFirstIndex(self::$obj);
        $actual = self::$manager->deleteIndex(self::$obj);
        self::assertIsArray($actual);
        self::assertTrue($actual["acknowledged"]);
    }

    public function testCheckIfIndexExistsFalse(): void
    {
        $obj    = $this->objs[0];
        $result = self::$manager::checkIndexExists($obj);
        self::assertFalse($result);
    }

    public function testCreateIndex(): void
    {
        $obj = $this->objs[0];
        self::$manager::createTemplate($obj);
        $result = self::$manager::createFirstIndex($obj);
        self::assertIsArray($result);
        self::assertTrue($result["acknowledged"]);
        self::assertEquals($obj->getSettings()->getIndexNameAlone(), $result["index"]);
    }

    public function testCreateIndexThatAlreadyExists(): void
    {
        $this->expectException(ElasticBadRequest::class);
        $obj = $this->objs[0];
        self::$manager::createFirstIndex($obj);
    }

    public function testCheckIfIndexExistsTrue(): void
    {
        $obj    = $this->objs[0];
        $result = self::$manager::checkIndexExists($obj);
        self::assertTrue($result);
    }

    /**
     * This function test to store an ElasticObject using insert method
     *
     * @return void
     */
    public function testStoreWith1Log(): void
    {
        $data   = $this->objs[0];
        $result = self::$manager->store($data);
        self::assertIsObject($result);
        self::assertInstanceOf(ElasticObject::class, $result);
    }

    /**
     * This function test to store multiple ElasticObject using bulk method
     *
     * @return void
     */
    public function testStoreWithMultipleLog(): void
    {
        $result = self::$manager->store($this->objs);
        self::assertIsArray($result);
        self::assertEquals($this->totalObjectNumber, count($result));
    }

    /**
     * This function test to delete a single ElasticObject using delete method
     *
     * @return void
     */
    public function testDelete1Object(): void
    {
        $obj    = $this->objs[0];
        $obj    = self::$manager->store($obj);
        $result = self::$manager->delete($obj);
        self::assertIsArray($result);
        self::assertEquals("deleted", $result["result"]);
    }

    public function testDeleteObjectWithoutId(): void
    {
        $this->expectException(ElasticBadRequest::class);
        $this->expectExceptionMessage("ElasticObjectManager-error-Can not delete ElasticObject without id");
        $obj = $this->objs[3];
        self::$manager->delete($obj);
    }

    /**
     * This function test to delete multiple ElasticObject using deleteByQuery method
     *
     * @return void
     */
    public function testDeleteMultipleObject(): void
    {
        $objs = self::$manager->storeAndWait($this->objs);

        $result = self::$manager->deleteBulk($objs);
        self::assertIsArray($result);
        self::assertEquals($this->totalObjectNumber, $result["deleted"]);
    }

    /**
     * This function test to delete multiple ElasticObject using deleteByQuery method
     *
     * @return void
     */
    public function testDeleteMultipleObjectWithANonCElasticObject(): void
    {
        $objects = $this->objs;
        $objects = self::$manager->storeAndWait($objects);

        $objects[] = "test";
        $result    = self::$manager->deleteBulk($objects);
        self::assertIsArray($result);
        self::assertEquals($this->totalObjectNumber, $result["deleted"]);
    }

    public function testUpdate(): void
    {
        $obj = $this->objs[3];
        if (!$obj instanceof ElasticObject) {
            self::fail("Object isn't a ElasticObject");
        }
        $obj = self::$manager->store($obj);

        $obj->setDate(new DateTimeImmutable("now"));

        $result = self::$manager->update($obj);
        self::assertIsArray($result);
        self::assertEquals("updated", $result["result"]);
    }

    public function testUpdateObjectWithoutId(): void
    {
        $this->expectException(ElasticBadRequest::class);
        $this->expectExceptionMessage("ElasticObjectManager-error-Can not update ElasticObject without id");
        $obj = $this->objs[2];
        self::$manager->update($obj);
    }

    public function testLoadRef(): void
    {
        $ref_name = "user_id";
        $obj      = $this->objs[0];

        $ref      = self::$manager->loadRef($obj, $ref_name);
        $ref_info = $obj->getMappings()->getReferences()[$ref_name];
        self::assertInstanceOf($ref_info, $ref);
        self::assertEquals($obj->getRefValue($ref_name), $ref->_id);
    }

    public function testLoadRefWithUnknownID(): void
    {
        $ref_name     = "user_id";
        $obj          = $this->objs[0];
        $obj->user_id = "45646645";

        $ref = self::$manager->loadRef($obj, $ref_name);
        self::assertNull($ref);
    }


    public function testMassLoadRefs(): void
    {
        $ref_name = "user_id";
        $refs     = self::$manager->massLoadRefs($this->objs, $ref_name);
        self::assertIsArray($refs);
        foreach ($this->objs as $obj) {
            assertArrayHasKey($obj->getRefValue($ref_name), $refs);
        }
    }

    public static function buildElasticObjectMock(bool $with_ilm = false): MockObject
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
        if ($with_ilm) {
            $ilm = new ElasticIndexLifeManager($mockSettings->getILMName());
            $hot = new ElasticHotPhase();
            $hot->setRolloverOnMaxAge(7, TimeUnitEnum::DAYS());
            $ilm->setHotPhase($hot);
            $delete = new ElasticDeletePhase(14, TimeUnitEnum::DAYS());
            $ilm->setDeletePhase($delete);
            $mockSettings->addIndexLifeManagement($ilm);
        }

        $mappings = new ElasticObjectMappings();
        $mappings->addRefField("user_id", CUser::class)
            ->addStringField("message", Encoding::ISO_8859_1, false, true);

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

        $object->user_id = CUser::get()->_id;
        $object->message = uniqid();

        return $object;
    }
}
