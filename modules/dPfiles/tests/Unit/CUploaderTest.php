<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CUploader;
use Ox\Tests\OxUnitTestCase;

class CUploaderTest extends OxUnitTestCase {

  public function test__construct() {
    $uploader = new CUploader();
    $this->assertInstanceOf(CUploader::class, $uploader);
  }

  public function test_getUploadDir() {
    $this->assertStringContainsString("/upload", CUploader::getUploadDir());
  }

  public function test_sanitize() {
    $this->assertStringNotContainsString("..", CUploader::sanitize("/tmp/../tmp/"));
  }

  public function test_getMaxUploadSize() {
    $uploader = new CUploader();
    $this->assertIsInt($uploader->getMaxUploadSize());
  }
}
