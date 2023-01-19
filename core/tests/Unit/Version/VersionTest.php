<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Version;

use BrowscapPHP\Helper\Filesystem;
use Ox\Core\CMbDT;
use Ox\Core\Exceptions\VersionException;
use Ox\Core\Version\Builder;
use Ox\Core\Version\Version;
use Ox\Tests\OxUnitTestCase;

/**
 * Tests for Version class
 */
class VersionTest extends OxUnitTestCase
{
    public function testGetVersionData(): void
    {
        $datetime = CMbDT::dateTime();

        $version = new Version(
            [
                'major' => 10,
                'minor' => 20,
                'patch' => 30,
                'build' => 40,
                'datetime_build' => $datetime,
            ]
        );

        $this->assertEquals(10, $version->getMajor());
        $this->assertEquals(20, $version->getMinor());
        $this->assertEquals(30, $version->getPatch());
        $this->assertEquals(40, $version->getBuild());
        $this->assertEquals($datetime, $version->getDatetimeBuild());
        $this->assertEquals('10.20.30.40', (string) $version);
        $this->assertEquals(
            [
                'major' => 10,
                'minor' => 20,
                'patch' => 30,
                'build' => 40,
                'datetime_build' => $datetime,
                'string' => '10.20.30.40',
                'version' => '10.20.30',
                'date' => null,
                'relative' => null,
                'releaseCode' => null,
                'releaseDate' => null,
                'releaseDateComplete' => null,
                'releaseRev' => null,
                'releaseTitle' => null,
                'title' => null,
            ],
            $version->toArray()
        );
    }

    /**
     * @throws VersionException
     */
    public function testGetReleaseData(): void
    {
        $tmpDir = dirname(__DIR__, 4) . '/tmp/' ;

        $versionFile    = $tmpDir . 'version.php';
        $releaseXmlFile = $tmpDir . 'release.xml';
        $bundleYmlFile  = $tmpDir . 'bundle.yml';

        $code = '2022_02';
        $releaseDateAtom = '2022-02-07T11:43:22+01:00';
        $releaseDate = '2022-02-07 11:43:22';
        $updateDate  = '2022-02-21 10:00:00';
        $sha  = 'c96197ad62fb17158079f5553c7641d8b1519072';
        $bundleUuid  = 'd3b224de-0fbd-42bc-81a3-224991ad72e3';

        $xml = '<?xml version="1.0"?>'
            . '<release code="' . $code . '" date="' . $releaseDateAtom . '" '
            . 'revision="' . $sha . '"/>';

        $yml = "uuid: " . $bundleUuid . PHP_EOL
            . "release_code: '" . $code . "'" . PHP_EOL
            . "last_update: '" . $updateDate . "'";

        $fs = new Filesystem();
        $fs->dumpFile(
            $releaseXmlFile,
            $xml
        );
        $fs->dumpFile(
            $bundleYmlFile,
            $yml
        );

        Builder::buildVersion($versionFile, $bundleYmlFile, $releaseXmlFile);

        $version = new Version(include $versionFile);

        $this->assertEquals($code, $version->getCode());
        $this->assertEquals($bundleUuid, $version->getRevision());

        $this->assertIsArray($version->getDateRelative());
        $this->assertArrayHasKey('unit', $version->getDateRelative());
        $this->assertArrayHasKey('count', $version->getDateRelative());
        $this->assertArrayHasKey('locale', $version->getDateRelative());

        $this->assertEquals($releaseDateAtom, $version->getCompleteDate());
        $this->assertEquals($releaseDate, $version->getReleaseDate());

        $this->assertStringContainsString($bundleUuid, $version->getTitle());
        $this->assertStringNotContainsString($code, $version->getTitle());

        $this->assertEquals($updateDate, $version->getUpdateDate());
        $this->assertMatchesRegularExpression(
            '/^' . $code . '-\d+$/',
            $version->getKey()
        );

        /* This ensures fields used in php or templates files are available from the data array */
        $data = $version->toArray();
        $this->assertArrayHasKey('date', $data);
        $this->assertArrayHasKey('relative', $data);
        $this->assertArrayHasKey('releaseCode', $data);
        $this->assertArrayHasKey('releaseDate', $data);
        $this->assertArrayHasKey('releaseDateComplete', $data);
        $this->assertArrayHasKey('releaseRev', $data);
        $this->assertArrayHasKey('releaseTitle', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('version', $data);
    }
}
