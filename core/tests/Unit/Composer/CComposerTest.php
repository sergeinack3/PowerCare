<?php

namespace Ox\Core\Tests\Unit;

use Ox\Core\Composer\CComposer;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class CComposerTest extends OxUnitTestCase
{


    public function testVersion()
    {
        $composer = new CComposer();
        $this->assertStringStartsWith('2.', $composer->getVersion());
    }

    public function testJson(){
        $composer = new CComposer();
        $this->assertJson($composer->getJson());
    }

    public function testPrefix(){
        $composer = new CComposer();
        $this->assertNotEmpty($composer->getPrefixPsr4());
    }

    public function testChekAll(){
        $composer = new CComposer();
        $this->assertTrue($composer->checkAll());
    }

    public function testCount(){
        $composer = new CComposer();
        $this->assertGreaterThan(0, $composer->countPackages());
        $this->assertGreaterThan(0, $composer->countPackagesInstalled());
    }

    public function testLicense(){
        $composer = new CComposer();
        $this->assertNotEmpty($composer->licenses());
    }
}
