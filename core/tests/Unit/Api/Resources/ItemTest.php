<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Resources;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Etags;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFormats;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Serializers\JsonApiSerializer;
use Ox\Core\Tests\Resources\CLoremIpsum;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CUserLog;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class ItemTest extends OxUnitTestCase
{
    public $user;

    public function getUserFromFixtures(): CUser
    {
        if ($this->user === null) {
            $this->user = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        }

        return $this->user;
    }

    public function testFailedConstructFromString(): void
    {
        $this->expectException(ApiException::class);
        $item = new Item('lorem ipsum');
    }

    public function testFailedConstructFromInt(): void
    {
        $this->expectException(ApiException::class);
        $item = new Item(1234);
    }

    public function testFailedConstructFromNull(): void
    {
        $this->expectException(ApiException::class);
        $item = new Item(null);
    }

    public function testFailedConstructFromBool(): void
    {
        $this->expectException(ApiException::class);
        $item = new Item(true);
    }

    public function testConstructFromArray(): void
    {
        $datas = [
            'lorem' => 'ipsum',
            'foo'   => 'bar',
            'toto'  => 'tata',
        ];
        $item  = new Item($datas);
        $this->assertEquals($item->getDatas(), $datas);
    }

    public function testConstructFromObject(): void
    {
        $lorem = new CLoremIpsum(123, 'foo', 'testConstructFromObject');
        $item  = new Item($lorem);
        $this->assertEquals($item->getDatas(), $lorem);
    }

    public function testConstructFromCModelObject(): void
    {
        $user = $this->getUserFromFixtures();
        $item = new Item($user);
        $this->assertEquals($item->getDatas(), $user);
    }

    public function testMetas()
    {
        $user = $this->getUserFromFixtures();
        $item = new Item($user);
        $this->invokePrivateMethod($item, 'setDefaultMetas');
        $metas = $item->getMetas();
        $this->assertIsArray($metas);
    }

    public function testAdditionalDatas()
    {
        $user  = $this->getUserFromFixtures();
        $item  = new Item($user);
        $datas = $item->addAdditionalDatas(
            [
                'foo' => 'bar',
            ]
        )->transform();

        $this->assertArrayHasKey('foo', $datas['datas']);
        $this->assertEquals($datas['datas']['foo'], 'bar');
    }


    public function testFieldsets()
    {
        $item = new Item(new CSejour());
        $item->setModelFieldsets('none');
        $this->assertEquals(['current' => []], $item->getModelFieldsets());
    }

    public function testFieldsetsFailed()
    {
        $item = new Item(new CSejour());
        $this->expectException(ApiException::class);
        $item->setModelFieldsets('lorem');
    }

    public function testFieldsetsFailedNotModelObject()
    {
        $item = new Item(['lorem' => 'ipsum']);
        $this->expectException(ApiException::class);
        $item->setModelFieldsets('all');
    }

    public function testRelations()
    {
        $item = new Item(new CSejour());
        $item->setModelRelations('all');
        $this->assertNotEmpty($item->getModelRelations());
    }

    public function testRelationsFailed()
    {
        $item = new Item(new CSejour());
        $this->expectException(ApiException::class);
        $item->setModelRelations('lorem');
    }

    public function testRelationsFailedNotModelObject()
    {
        $item = new Item(['lorem' => 'ipsum']);
        $this->expectException(ApiException::class);
        $item->setModelRelations('all');
    }

    public function testIsModelObject()
    {
        $item = new Item(new CSejour());
        $this->assertTrue($item->isModelObjectResource());
    }

    public function testIsModelObjectFailed()
    {
        $item = new Item(['lorem' => 'ipsum']);
        $this->assertFalse($item->isModelObjectResource());
    }

    public function testRequestUrl()
    {
        $item = new Item(['lorem' => 'ipsum']);
        $item->setRequestUrl('http://www.lorem.ipsum');
        $this->assertEquals('http://www.lorem.ipsum', $item->getRequestUrl());
    }

    public function testRecursionDepth()
    {
        $item = new Item(new CSejour());
        $this->assertEquals($item->getRecursionDepth(), 0);
    }

    public function testFormat()
    {
        $item = new Item(['lorem' => 'ipsum']);
        $item->setFormat(RequestFormats::FORMAT_JSON);
        $this->assertEquals(RequestFormats::FORMAT_JSON, $item->getFormat());
    }

    public function testFormatFailed()
    {
        $item = new Item(['lorem' => 'ipsum']);
        $this->expectException(ApiException::class);
        $item->setFormat('toto_tata');
    }

    public function testSerializer()
    {
        $item = new Item(['lorem' => 'ipsum']);
        $item->setSerializer(JsonApiSerializer::class);
        $this->assertEquals(JsonApiSerializer::class, $item->getSerializer());
    }

    public function testSerializerFailed()
    {
        $item = new Item(['lorem' => 'ipsum']);
        $this->expectException(ApiException::class);
        $item->setSerializer('joe/bar/team');
    }

    public function testJsonSerializable()
    {
        $datas = ['lorem' => 'ipsum', 'id' => "1234"];
        $item  = new Item($datas);
        $this->assertJson(json_encode($item));
    }

    public function testCreateFromRequest()
    {
        $request     = Request::create('http://www.phpunit?relations=user&fieldsets=none');
        $request_api = RequestApi::createFromRequest($request);

        $log      = new CUserLog();
        $log->_id = 1234;
        $item     = Item::createFromRequest($request_api, $log);

        $this->assertEquals(['user'], $item->getModelRelations());
        $this->assertEquals(['current' => []], $item->getModelFieldsets());
        $this->assertNotNull($item->getRequestUrl());
    }

    public function testFormatFieldsetsByRelations()
    {
        $fieldsets = ['foo', 'bar', 'patient.foo', 'patient.bar'];
        $expected  = [
            Item::CURRENT_RELATION_NAME => ['foo', 'bar'],
            'patient'                   => ['foo', 'bar'],
        ];

        $item   = new Item(new CUser());
        $actual = $this->invokePrivateMethod($item, 'formatFieldsetByRelation', $fieldsets);

        $this->assertEquals($expected, $actual);
    }

    public function testAddModelFieldsets()
    {
        $fieldsets = [CUser::FIELDSET_DEFAULT, CUser::FIELDSET_EXTRA, 'patient.foo', 'patient.bar'];
        $expected  = [
            Item::CURRENT_RELATION_NAME => [CUser::FIELDSET_DEFAULT, CUser::FIELDSET_EXTRA],
            'patient'                   => ['foo', 'bar'],
        ];

        $item = new Item(new CUser());
        $item->addModelFieldset($fieldsets);
        $actual = $item->getModelFieldsets();

        $this->assertEquals($expected, $actual);
    }

    public function testAddModelFieldsetException()
    {
        $fieldsets = ['foo', 'bar', 'patient.foo', 'patient.bar'];

        $item = new Item(new CUser());
        $this->expectException(ApiException::class);
        $item->addModelFieldset($fieldsets);
    }

    public function testAddModelFieldsetDeep()
    {
        $fieldsets = [
            CUser::FIELDSET_DEFAULT,
            CUser::FIELDSET_EXTRA,
            CUser::RELATION_RGPD . '.' . CRGPDConsent::FIELDSET_DEFAULT,
            CUser::RELATION_RGPD . '.' . CRGPDConsent::FIELDSET_EXTRA,
        ];

        $item = new Item(new CUser());
        $item->addModelFieldset($fieldsets);
        $actual = $item->serialize();
        $this->assertNotEmpty($actual);
    }


    public function testRemoveFieldsets()
    {
        $fieldsets = [CUser::FIELDSET_DEFAULT, CUser::FIELDSET_EXTRA, 'foo.bar', 'bar.foo'];
        $expected  = [Item::CURRENT_RELATION_NAME => [CUser::FIELDSET_EXTRA], 'bar' => ['foo'], 'foo' => []];

        $item = new Item(new CUser());
        $item->addModelFieldset($fieldsets);

        $actual = $item->removeModelFieldset([CUser::FIELDSET_DEFAULT, 'foo.bar']);
        $this->assertTrue($actual);
        $this->assertEquals($expected, $item->getModelFieldsets());
    }

    public function testRemoveFieldsetsFailed()
    {
        $fieldsets = [CUser::FIELDSET_DEFAULT, CUser::FIELDSET_EXTRA, 'foo.bar', 'bar.foo'];

        $item = new Item(new CUser());
        $item->addModelFieldset($fieldsets);

        $actual = $item->removeModelFieldset(['foo']);
        $this->assertFalse($actual);
    }

    public function testHasModelRelation()
    {
        $relations = [CUser::RELATION_RGPD];

        $item = new Item(new CUser());
        $item->setModelRelations($relations);

        $this->assertTrue($item->hasModelrelation(CUser::RELATION_RGPD));
        $this->assertFalse($item->hasModelrelation('foo'));
    }

    public function testHasModelFieldsets()
    {
        $fieldsets = [CUser::FIELDSET_DEFAULT, CUser::RELATION_RGPD . '.' . CRGPDConsent::FIELDSET_EXTRA];

        $item = new Item(new CUser());
        $item->setModelFieldsets($fieldsets);

        $this->assertTrue($item->hasModelFieldset(CUser::FIELDSET_DEFAULT));
        $this->assertTrue($item->hasModelFieldset(CUser::RELATION_RGPD . '.' . CRGPDConsent::FIELDSET_EXTRA));
        $this->assertFalse($item->hasModelFieldset('foo'));
        $this->assertFalse($item->hasModelFieldset(CUser::RELATION_RGPD . '.foo'));
    }

    public function testgetFieldsetsByRelation()
    {
        $fieldsets = [CUser::FIELDSET_DEFAULT, CUser::RELATION_RGPD . '.' . CRGPDConsent::FIELDSET_EXTRA];

        $item = new Item(new CUser());
        $item->setModelFieldsets($fieldsets);

        $expected = [CRGPDConsent::FIELDSET_EXTRA];
        $actual   = $item->getFieldsetsByRelation(CUser::RELATION_RGPD);
        $this->assertEquals($expected, $actual);

        $expected = [CUser::FIELDSET_DEFAULT];
        $actual   = $item->getFieldsetsByRelation();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws ApiException
     */
    public function testAdddFieldsetOnRelation(): void
    {
        $expected  = null;
        $fieldsets = [CRGPDConsent::FIELDSET_DEFAULT, CRGPDConsent::FIELDSET_EXTRA, CRGPDConsent::RELATION_FILES];
        $item      = new Item(new CUser());

        // nothing do if relation is not set
        $item->addFieldsetsOnRelation(CUSer::RELATION_RGPD, $fieldsets);
        $this->assertEquals($expected, $item->getFieldsetsByRelation(CUSer::RELATION_RGPD));

        $expected = $fieldsets;
        $item->setModelRelations(CUSer::RELATION_RGPD);
        $item->addFieldsetsOnRelation(CUSer::RELATION_RGPD, $fieldsets);
        $this->assertEquals($expected, $item->getFieldsetsByRelation(CUSer::RELATION_RGPD));
    }

    public function testAddRelation(): void
    {
        /** @var CUser $user */
        $user = $this->getUserFromFixtures();;
        $mediuser  = $user->loadRefMediuser();
        $item_user = new Item($user);

        $item_mediuser = new Item($mediuser);
        $item_user->addAdditionalRelation($item_mediuser);

        $this->assertEquals($item_user->getAdditionnalRelations(), [$item_mediuser]);
    }

    /**
     * @dataProvider addRelationTransformProvider
     */
    public function testTransformCustomRelation($data): void
    {
        $relation_item = $this->getUserFromFixtures();
        $item          = new Item($data);

        $item_relation = new Item($relation_item);
        $item_relation->setModelFieldsets(CUser::FIELDSET_DEFAULT);

        $item->addAdditionalRelation($item_relation);

        $item->transform();

        $transformed = $item->getDatasTransformed();

        $this->assertEquals(
            [CUser::RESOURCE_TYPE => [$item_relation->transform()]],
            $transformed['relationships']
        );
    }

    public function testTransformWithoutRelationDoesNotHaveNode(): void
    {
        $item = new Item(new Etags([uniqid()]));
        $item->transform();
        $this->assertArrayNotHasKey('relationships', $item->getDatasTransformed());
    }

    public function testAddAdditionalRelations(): void
    {
        $relation1   = new Item(['_id' => 'item1', '_type' => 'test', 'lorem' => 'ipsum']);
        $relation2   = new Item(['_id' => 'item2', '_type' => 'test', 'foo' => 'bar']);
        $collection1 = new Collection([$relation1, $relation2]);
        $relation3   = new CPatient();
        $relation4   = ['Test' => 'test'];

        $item = new Item(['foo' => 'bar']);
        $item->addAdditionalRelations([$relation1, $relation2, $collection1, $relation3, $relation4]);

        foreach ([$relation1, $relation2, $collection1] as $resource) {
            $this->assertTrue(in_array($resource, $item->getAdditionnalRelations()));
        }

        foreach ([$relation3, $relation4] as $resource) {
            $this->assertFalse(in_array($resource, $item->getAdditionnalRelations()));
        }
    }

    public function addRelationTransformProvider(): array
    {
        return [
            'model_object' => [
                $this->getObjectFromFixturesReference(
                    CPatient::class,
                    SimplePatientFixtures::SAMPLE_PATIENT
                ),
            ],
            'object'       => [new Etags([uniqid(), uniqid()])],
            'array'        => [['lorem' => 'ipsum']],
        ];
    }
}
