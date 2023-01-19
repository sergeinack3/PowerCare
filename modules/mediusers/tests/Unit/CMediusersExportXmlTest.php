<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Tests\Unit;

use Ox\Core\CMbPath;
use Ox\Core\Generators\CObjectGenerator;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Populate\Generators\CGroupsGenerator;
use Ox\Mediboard\Mediusers\CMediusersExportXml;
use Ox\Mediboard\Populate\Generators\CFunctionsGenerator;
use Ox\Mediboard\Populate\Generators\CMediusersGenerator;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CMediusersExportXmlTest extends OxUnitTestCase
{
    /** @var string string */
    protected $export_dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->export_dir = dirname(__DIR__, 4) . CMediusersExportXml::DIR_TEMP;
        if (is_dir($this->export_dir)) {
            CMbPath::emptyDir($this->export_dir);
        }
    }

    public function testCheckPermOk(): void
    {
        $group = CGroups::loadCurrent();
        $export = new CMediusersExportXml($group->_id);
        $this->assertTrue($this->invokePrivateMethod($export, 'checkPerm'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCheckPermKo(): void
    {
        $group = CGroups::loadCurrent();

        $user = CUser::get();
        CPermObject::$users_cache[$user->_id]['CGroups'][$group->_id] = 0;

        $export = new CMediusersExportXml($group->_id);
        $this->assertEquals(['access-forbidden', UI_MSG_ERROR], $export->exportMediusers());
    }

    /**
     * @dataProvider constructTreeProvider
     */
    public function testConstructTree(array $args, array $expected): void
    {
        $export = new CMediusersExportXml(...$args);
        $this->assertEquals($expected, $this->invokePrivateMethod($export, 'constructTrees'));
    }

    public function testExportNoUsers(): void
    {
        $group = new CGroups();
        $group->code = uniqid();
        $group->_name = uniqid();
        $group->text = uniqid();
        $this->storeOrFailed($group);

        $export = new CMediusersExportXml($group->_id);
        $this->assertEquals(['CMediusers-nb-to-export', UI_MSG_OK, 0], $export->exportMediusers());
    }

    public function testExportProfiles(): void
    {
        $mock = $this->getMockBuilder(CMediusersExportXml::class)
            ->onlyMethods(['download'])
            ->setConstructorArgs([CGroups::loadCurrent()->_id, null, true])
            ->getMock();

        if (is_dir($this->export_dir)) {
            $this->assertTrue(CMbPath::isEmptyDir($this->export_dir));
        }

        $this->assertNull($mock->exportMediusers());
        $this->assertFileExists($this->getFileName($mock));
    }

    /**
     * @group schedules
     */
    public function testExportMediusers(): void
    {
        $group = CGroups::loadCurrent();
        // Create mediusers to export
        for ($i = 0; $i < 5; $i++) {
            (new CMediusersGenerator())->setGroup($group->_id)->generate();
        }

        $mock = $this->getMockBuilder(CMediusersExportXml::class)
            ->onlyMethods(['download'])
            ->setConstructorArgs([$group->_id])
            ->getMock();

        if (is_dir($this->export_dir)) {
            $this->assertTrue(CMbPath::isEmptyDir($this->export_dir));
        }

        $this->assertNull($mock->exportMediusers());
        $this->assertFileExists($this->getFileName($mock));
    }

    public function testExportNoUsersInFunction(): void
    {
        $group_id = CGroups::loadCurrent()->_id;
        $function = (new CFunctionsGenerator())->setForce(true)->setGroup($group_id)->generate();

        $export = new CMediusersExportXml($group_id, $function->_id);
        $this->assertEquals(['CMediusers-nb-to-export', UI_MSG_OK, 0], $export->exportMediusers());
    }

    public function constructTreeProvider(): array
    {
        return [
            'empty_profile' => [[null, null, true], [[], []]],
            'empty_mediusers' => [[], [CMediusersExportXml::MEDIUSER_BW, CMediusersExportXml::MEDIUSER_FWD]],
            'mediusers_tarif' => [
                [null, null, false, false, false, false, false, true],
                [
                    array_merge_recursive(CMediusersExportXml::MEDIUSER_BW, CMediusersExportXml::TARIFS),
                    CMediusersExportXml::MEDIUSER_FWD
                ]
            ],
            'mediusers_planning' => [
                [null, null, false, false, false, false, false, false, true],
                [
                    array_merge_recursive(CMediusersExportXml::MEDIUSER_BW, CMediusersExportXml::MEDIUSER_PLA_CONS),
                    CMediusersExportXml::MEDIUSER_FWD
                ]
            ],
            'mediusers_perm' => [
                [null, null, false, true],
                [
                    array_merge_recursive(CMediusersExportXml::MEDIUSER_BW, CMediusersExportXml::USER_PERM_BACK),
                    array_merge_recursive(CMediusersExportXml::MEDIUSER_FWD, CMediusersExportXml::USER_PERM_FWD)
                ]
            ],
            'mediusers_pref' => [
                [null, null, false, false, true],
                [
                    array_merge_recursive(CMediusersExportXml::MEDIUSER_BW, CMediusersExportXml::USER_PREF),
                    CMediusersExportXml::MEDIUSER_FWD
                ]
            ],
            'mediusers_perm_functionnal' => [
                [null, null, false, false, false, false, true],
                [
                    array_merge_recursive(CMediusersExportXml::MEDIUSER_BW, CMediusersExportXml::USER_PREF),
                    CMediusersExportXml::MEDIUSER_FWD
                ]
            ],
        ];
    }

    private function getFileName(CMediusersExportXml $mock) : string
    {
        return $this->export_dir . '/' . $this->invokePrivateMethod($mock, 'sanitizeFileName') . '.zip';
    }
}
