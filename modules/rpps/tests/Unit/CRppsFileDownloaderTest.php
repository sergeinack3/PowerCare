<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Tests\Unit;

use Exception;
use Ox\Core\CHTTPClient;
use Ox\Core\CMbPath;
use Ox\Import\Rpps\CRppsFileDownloader;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class CRppsFileDownloaderTest extends OxUnitTestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::deleteFiles(dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'download_rpps');
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::deleteFiles(dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'download_rpps');
    }

    public function testDownloadRppsFileDownloadFailed()
    {
        $mock = $this->getMockBuilder(CRppsFileDownloader::class)
            ->onlyMethods(['getFile'])
            ->getMock();

        $mock->method('getFile')->willReturn(false);

        $this->expectExceptionMessage('CRppsFileDownloader-msg-Error-File download failed');
        $mock->downloadRppsFile(CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL);
    }

    public function testDownloadRppsFileExtractFailed()
    {
        $mock = $this->getMockBuilder(CRppsFileDownloader::class)
            ->onlyMethods(['getFile', 'extractFilesFromArchive'])
            ->getMock();

        $mock->method('getFile')->willReturn(true);
        $mock->method('extractFilesFromArchive')->willReturn(false);

        $this->expectExceptionMessage('CRppsFileDownloader-msg-Error-Error while extracting files');
        $mock->downloadRppsFile(CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL);
    }

    private static function deleteFiles(string $tmp_dir)
    {
        if (is_dir($tmp_dir)) {
            CMbPath::emptyDir($tmp_dir, false);
            CMbPath::rmEmptyDir($tmp_dir);
        }
    }

    public function testExtractFiles()
    {
        $mock = $this->getMockBuilder(CRppsFileDownloader::class)
            ->onlyMethods(['getUploadDirectory'])
            ->getMock();

        $download_rpps_dir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'download_rpps';
        $mock->method('getUploadDirectory')->willReturn($download_rpps_dir);

        if (file_exists($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_Personne_activite_2020.txt')) {
            unlink($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_Personne_activite_2020.txt');
        }

        if (file_exists($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_Dipl_AutExerc_2020.txt')) {
            unlink($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_Dipl_AutExerc_2020.txt');
        }

        if (file_exists($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_SavoirFaire_2020.txt')) {
            unlink($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_SavoirFaire_2020.txt');
        }

        $this->assertFileDoesNotExist(
            $download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_Personne_activite_2020.txt'
        );
        $this->assertFileDoesNotExist(
            $download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_Dipl_AutExerc_2020.txt'
        );
        $this->assertFileDoesNotExist($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_SavoirFaire_2020.txt');

        $this->assertTrue(
            $this->invokePrivateMethod(
                $mock,
                'extractFilesFromArchive',
                dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'rpps.zip'
            )
        );

        $this->assertFileExists($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_Personne_activite_2020.txt');
        $this->assertFileExists($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_Dipl_AutExerc_2020.txt');
        $this->assertFileExists($download_rpps_dir . DIRECTORY_SEPARATOR . 'PS_LibreAcces_SavoirFaire_2020.txt');
    }

    /**
     * @depends testExtractFiles
     */
    public function testRenameExtractedFilesTest()
    {
        $mock = $this->getMockBuilder(CRppsFileDownloader::class)
            ->onlyMethods(['getUploadDirectory', 'getFile', 'extractFilesFromArchive'])
            ->getMock();

        $mock->method('getUploadDirectory')->willReturn(
            dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'download_rpps'
        );
        $mock->method('getFile')->willReturn(true);
        $mock->method('extractFilesFromArchive')->willReturn(true);

        $this->assertFileDoesNotExist($mock->getPersonneExerciceFilePath());
        $this->assertFileDoesNotExist($mock->getSavoirFaireFilePath());
        $this->assertFileDoesNotExist($mock->getDiplomeExerciceFilePath());

        $mock->downloadRppsFile(CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL);

        $this->assertFileExists($mock->getPersonneExerciceFilePath());
        $this->assertFileExists($mock->getSavoirFaireFilePath());
        $this->assertFileExists($mock->getDiplomeExerciceFilePath());

        $this->deleteFiles($this->invokePrivateMethod($mock, 'getUploadDirectory'));
    }

    public function testInitHttpClient()
    {
        $downloader = new CRppsFileDownloader();
        $tmp        = tempnam('/tmp', 'testDownload');
        $fp         = fopen($tmp, 'w+');

        /** @var CHTTPClient $http_client */
        $http_client = $this->invokePrivateMethod($downloader, 'initHttpClient', CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL, $fp);
        $this->assertEquals(
            CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL,
            $http_client->url
        );
        $this->assertFalse($http_client->option[CURLOPT_SSL_VERIFYPEER]);
        $this->assertEquals($fp, $http_client->option[CURLOPT_FILE]);

        $http_client->closeConnection();
        fclose($fp);
        unlink($tmp);
    }

    public function testIsRppsFileDownloadable(): void
    {
        $downloader = $this->getMockBuilder(CRppsFileDownloader::class)
            ->onlyMethods(['initHttpClient'])
            ->getMock();

        $http_client = $this->getMockBuilder(CHTTPClient::class)
            ->onlyMethods(['getInfo', 'head'])
            ->setConstructorArgs([CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL])
            ->getMock();
        $http_client->method('getInfo')->willReturn(200);

        $downloader->method('initHttpClient')->willReturn($http_client);

        $this->assertTrue($downloader->isRppsFileDownloadable());
    }

    public function testIsRppsFilenotDownloadable(): void
    {
        $downloader = $this->getMockBuilder(CRppsFileDownloader::class)
            ->onlyMethods(['initHttpClient'])
            ->getMock();

        $http_client = $this->getMockBuilder(CHTTPClient::class)
            ->onlyMethods(['getInfo', 'head'])
            ->setConstructorArgs([CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL])
            ->getMock();
        $http_client->method('getInfo')->willReturn(500);

        $downloader->method('initHttpClient')->willReturn($http_client);

        $this->assertFalse($downloader->isRppsFileDownloadable());
    }

    public function testIsRppsFileDownloadableThrowException(): void
    {
        $downloader = $this->getMockBuilder(CRppsFileDownloader::class)
            ->onlyMethods(['initHttpClient'])
            ->getMock();

        $http_client = $this->getMockBuilder(CHTTPClient::class)
            ->onlyMethods(['getInfo', 'head'])
            ->setConstructorArgs([CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL])
            ->getMock();
        $http_client->method('getInfo')->willThrowException(new Exception());

        $downloader->method('initHttpClient')->willReturn($http_client);

        $this->assertFalse($downloader->isRppsFileDownloadable());
    }
}
