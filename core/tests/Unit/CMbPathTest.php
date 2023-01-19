<?php
/**
 * @package Mediboard\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Core\CMbPath;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CMbPathTest
 */
class CMbPathTest extends OxUnitTestCase
{
    protected $root_dir;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->root_dir = rtrim(CAppUI::conf("root_dir"), "\\/");
    }

    /**
     * Reduce a string by removing <part>/.. with part alphanumeric or _ values
     *
     * @param string $reduce   Path to reduce
     * @param string $expected Expected result
     *
     * @dataProvider reduceProvider
     */
    public function testReduce($reduce, $expected)
    {
        $this->assertEquals($expected, CMbPath::reduce($reduce));
    }


    /**
     * Get the two last lines of the file
     */
    //  public function testTailCustom() {
    //    $file    = $this->root_dir . '/tmp/tailCustomFile';
    //    $content = "testDeSkip\nsecondeLigne\nSome more lines\nLast line is here.";
    //    @file_put_contents($file, $content);
    //    if (file_exists($file)) {
    //      $this->assertEquals("Some more lines\nLast line is here.", CMbPath::tailWithSkip($file, 2));
    //      unlink($file);
    //
    //      $this->assertFalse(CMbPath::tailWithSkip($file, 2, 1));
    //    }
    //    else {
    //      self::markTestSkipped('Pipeline concurence bug');
    //    }
    //  }

    /**
     * Guess the mime type from a file name
     * Use $this->mime array for types
     *
     * @param string $file_name Name to find mime from
     * @param string $mime      Expected mime type
     *
     * @dataProvider mimeProvider
     */
    public function testGuessMimeType($file_name, $mime)
    {
        $this->assertEquals($mime, CMbPath::guessMimeType($file_name));
    }


    /**
     * Force the creation of a subtree of directories
     */
    public function testForceDir()
    {
        $dir = $this->root_dir . "/tmp/test/force/dir";

        if (file_exists($dir)) {
            rmdir($dir);
            rmdir(dirname($dir));
            rmdir(dirname(dirname($dir)));
        }

        $this->assertDirectoryDoesNotExist($dir);

        CMbPath::forceDir($dir);
        $this->assertDirectoryExists($dir);

        if (file_exists($dir)) {
            rmdir($dir);
            rmdir(dirname($dir));
            rmdir(dirname(dirname($dir)));
        }
    }

    /**
     * Test the error thrown by forceDir with enmpty $dir
     */
    public function testForceDirError()
    {
        $this->expectWarning();
        CMbPath::forceDir("");
    }

    /**
     * Empty a directory containing folders and files
     */
    public function testEmptyDir()
    {
        $dir      = $this->root_dir . "/tmp/testEmptyDir";
        $sub_dir  = $dir . "/sub_dir";
        $file     = $dir . "/dummy_file";
        $sub_file = $sub_dir . "/dummy_file";

        if (!file_exists($file)) {
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            touch($file);
        }

        if (!file_exists($sub_file)) {
            if (!file_exists($sub_dir)) {
                mkdir($sub_dir);
            }

            touch($sub_file);
        }

        $this->assertFileExists($file);

        CMbPath::emptyDir($dir);

        $this->assertFileDoesNotExist($file);

        rmdir($dir);
    }

    /**
     * Test the purge of a succession of empty dirs
     */
    public function testPurgeEmptySubdirs()
    {
        $dir             = $this->root_dir . "/tmp/testPurgeEmptySubdirs";
        $empty_sub_dir   = $dir . "/empty_sub";
        $empty_sub_dir_2 = $empty_sub_dir . "/empty_sub_2";

        if (file_exists($empty_sub_dir_2)) {
            rmdir($empty_sub_dir_2);
        }

        if (file_exists($empty_sub_dir)) {
            rmdir($empty_sub_dir);
        }

        if (file_exists($dir)) {
            rmdir($dir);
        }

        mkdir($dir);
        mkdir($empty_sub_dir);
        mkdir($empty_sub_dir_2);

        $this->assertEquals(3, CMbPath::purgeEmptySubdirs($dir));
        $this->assertDirectoryDoesNotExist($empty_sub_dir_2);
        $this->assertDirectoryDoesNotExist($empty_sub_dir);
        $this->assertDirectoryDoesNotExist($dir);
    }

    /**
     * Test the purge of a succession of empty dirs with a file in the second directory
     */
    public function testPurgeEmptySubdirsFile()
    {
        $dir             = $this->root_dir . "/tmp/testPurgeEmptySubdirs";
        $empty_sub_dir   = $dir . "/empty_sub";
        $file            = $empty_sub_dir . "/dummy_file";
        $empty_sub_dir_2 = $empty_sub_dir . "/empty_sub_2";

        if (file_exists($empty_sub_dir_2)) {
            rmdir($empty_sub_dir_2);
        }

        if (file_exists($file)) {
            unlink($file);
        }

        if (file_exists($empty_sub_dir)) {
            rmdir($empty_sub_dir);
        }

        if (file_exists($dir)) {
            rmdir($dir);
        }

        mkdir($dir);
        mkdir($empty_sub_dir);
        touch($file);
        mkdir($empty_sub_dir_2);

        $this->assertEquals(1, CMbPath::purgeEmptySubdirs($dir));
        $this->assertDirectoryDoesNotExist($empty_sub_dir_2);
        $this->assertDirectoryExists($empty_sub_dir);
        $this->assertFileExists($file);
        $this->assertDirectoryExists($dir);

        unlink($file);
        rmdir($empty_sub_dir);
        rmdir($dir);
    }

    /**
     * Get two lines from a file
     */
    //  public function testTailWithSkip(): void {
    //    $file    = $this->root_dir . '/tmp/tailWithSkipFile';
    //    $content = "testDeSkip\nsecondeLigne\nSome more lines\nLast line is here.";
    //    @file_put_contents($file, $content);
    //    if (file_exists($file)) {
    //      $this->assertEquals("secondeLigne\nSome more lines", CMbPath::tailWithSkip($file, 2, 1));
    //      unlink($file);
    //
    //      $this->assertFalse(CMbPath::tailWithSkip($file, 2, 1));
    //    }
    //    else {
    //      self::markTestSkipped('Pipeline concurence bug.');
    //    }
    //  }

    /**
     * Check if a directory is empty or not
     */
    public function testIsEmptyDir()
    {
        $dir  = $this->root_dir . "/tmp/testIsEmptyDir";
        $file = $dir . "/dummy_file";

        if (file_exists($file)) {
            unlink($file);
        }

        if (file_exists($dir)) {
            rmdir($dir);
        }

        mkdir($dir);

        $this->assertTrue(CMbPath::isEmptyDir($dir));

        touch($file);

        $this->assertFalse(CMbPath::isEmptyDir($dir));

        if (file_exists($file)) {
            unlink($file);
        }

        if (file_exists($dir)) {
            rmdir($dir);
        }
    }


    /**
     * @depends testIsEmptyDir
     */
    public function testRmEmptyDir()
    {
        $dir  = $this->root_dir . '/tmp/rmEmptyDir';
        $file = $dir . '/dummy_file';

        if (!file_exists($file)) {
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            touch($file);
        }

        $this->assertFalse(CMbPath::rmEmptyDir($dir));

        unlink($file);

        $this->assertTrue(CMbPath::rmEmptyDir($dir));
    }

    /**
     * Compare file names or dir names
     */
    public function testCmpFiles()
    {
        $dir_a  = $this->root_dir . '/tmp/dir_a';
        $dir_b  = $this->root_dir . '/tmp/dir_b';
        $file_a = $this->root_dir . '/tmp/file_a';
        $file_b = $this->root_dir . '/tmp/file_b';

        if (!file_exists($dir_a)) {
            mkdir($dir_a);
        }

        if (!file_exists($dir_b)) {
            mkdir($dir_b);
        }

        if (!file_exists($file_a)) {
            touch($file_a);
        }

        if (!file_exists($file_b)) {
            touch($file_b);
        }

        // Same names
        $this->assertEquals(0, CMbPath::cmpFiles($dir_a, $dir_a));
        $this->assertEquals(0, CMbPath::cmpFiles($file_a, $file_a));

        // First arg is more than second arg
        $this->assertTrue(CMbPath::cmpFiles($dir_b, $dir_a) > 0);
        $this->assertTrue(CMbPath::cmpFiles($file_b, $file_a) > 0);
        $this->assertTrue(CMbPath::cmpFiles($file_b, $dir_a) > 0);

        // First arg is less than second arg
        $this->assertTrue(CMbPath::cmpFiles($dir_a, $dir_b) < 0);
        $this->assertTrue(CMbPath::cmpFiles($file_a, $file_b) < 0);
        $this->assertTrue(CMbPath::cmpFiles($dir_a, $file_a) < 0);

        unlink($file_a);
        unlink($file_b);
        rmdir($dir_a);
        rmdir($dir_b);
    }

    /**
     * @depends testEmptyDir
     */
    public function testRemoveEmptyPath()
    {
        $this->expectWarning();
        CMbPath::remove("");
    }

    /**
     * @depends testEmptyDir
     */
    public function testRemoveFile()
    {
        $file = $this->root_dir . '/tmp/testRemoveFile';
        if (!file_exists($file)) {
            touch($file);
        }

        $this->assertTrue(CMbPath::remove($file));
    }

    /**
     * Remove a directory and all its sub_dirs or files
     *
     * @depends testEmptyDir
     */
    public function testRemoveDir()
    {
        $dir     = $this->root_dir . '/tmp/testRemoveDir';
        $file    = $dir . '/dummy_file';
        $sub_dir = $dir . '/sub_dir';

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        if (!file_exists($file)) {
            touch($file);
        }

        if (!file_exists($sub_dir)) {
            mkdir($sub_dir);
        }

        $this->assertTrue(CMbPath::remove($dir));
    }

    /**
     * Get the string after the last . in a path
     * Return null for directories or path without .
     */
    public function testGetExtension()
    {
        $this->assertEquals("exe", CMbPath::getExtension($this->root_dir . '/tmp/test.exe'));
        $this->assertEquals("txt", CMbPath::getExtension($this->root_dir . '/tmp/test.exe.txt'));
        $this->assertNull(CMbPath::getExtension($this->root_dir . '/tmp/test'));
        $this->assertNull(CMbPath::getExtension($this->root_dir . '/tmp/test.ex/e'));
    }

    /**
     * @return array
     */
    public function mimeProvider()
    {
        return [
            ["test.file.js", "application/x-javascript"],
            ["test.file.json", "application/json"],
            ["test.file.jpg", "image/jpg"],
            ["test.file.jpeg", "image/jpg"],
            ["test.file.jpe", "image/jpg"],
            ["test.file.png", "image/png"],
            ["test.file.gif", "image/gif"],
            ["test.file.bmp", "image/bmp"],
            ["test.file.tiff", "image/tiff"],
            ["test.file.tif", "image/tif"],
            ["test.file.css", "text/css"],
            ["test.file.xml", "application/xml"],
            ["test.file.doc", "application/msword"],
            ["test.file.docx", "application/msword"],
            ["test.file.dot", "application/msword"],
            ["test.file.xls", "application/vnd.ms-excel"],
            ["test.file.xlt", "application/vnd.ms-excel"],
            ["test.file.xlm", "application/vnd.ms-excel"],
            ["test.file.xld", "application/vnd.ms-excel"],
            ["test.file.xla", "application/vnd.ms-excel"],
            ["test.file.xlc", "application/vnd.ms-excel"],
            ["file.xlw", "application/vnd.ms-excel"],
            ["file.xll", "application/vnd.ms-excel"],
            ["file.odt", "application/vnd.oasis.opendocument.text"],
            ["file.ppt", "application/vnd.ms-powerpoint"],
            ["file.pps", "application/vnd.ms-powerpoint"],
            ["file.rtf", "application/rtf"],
            ["file.pdf", "application/pdf"],
            ["file.html", "text/html"],
            ["file.htm", "text/html"],
            ["file.php", "text/html"],
            ["file.txt", "text/plain"],
            ["file.ini", "text/plain"],
            ["file.mpeg", "video/mpeg"],
            ["file.mpg", "video/mpeg"],
            ["file.mpe", "video/mpeg"],
            ["file.mp3", "audio/mpeg3"],
            ["file.wav", "audio/wav"],
            ["file.aiff", "audio/aiff"],
            ["file.aif", "audio/aiff"],
            ["file.avi", "video/msvideo"],
            ["file.wmv", "video/x-ms-wmv"],
            ["file.mov", "video/quicktime"],
            ["file.zip", "application/zip"],
            ["file.tar", "application/x-tar"],
            ["file.swf", "application/x-shockwave-flash"],
            ["file.nfs", "application/vnd.lotus-notes"],
            ["file.spl", "application/vnd.sante400"],
            ["file.rlb", "application/vnd.sante400"],
            ["file.svg", "image/svg+xml"],
            ["file.mpr", "multipart/related"],
            ["file.dicom", "application/dicom"],
            ["file.hl7", "application/x-hl7"],
            ["file.gfy", "text/goofy"],
            ["file.hpm", "application/x-hprim-med"],
            ["file.hps", "application/x-hprim-sante"],
            ["file.hpr", "application/x-hprim-sante"],
            ["file.pct", "image/x-pict"],
            ["file.other", "unknown/other"],
        ];
    }

    /**
     * @return array
     */
    public function reduceProvider()
    {
        return [
            ["modules/../lib", "lib"],
            ["../tests/../lib", "../lib"],
            ["986_74/../tests/Unit", "tests/Unit"],
            ["tests/Units/", "tests/Units/"],
        ];
    }

    /**
     * @return void
     */
    public function testHashDirectory(): void
    {
        $dir     = $this->root_dir . '/tmp/testHashDirectory';
        $file    = $dir . '/dummy_file';

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        file_put_contents($file, 'dummy content');

        $this->assertEquals(
            '4b09d134d60c51f3e8d3c5ca16fe45bf',
            CMbPath::hashDirectory($dir)
        );
    }
}
