<?php
/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\CarePath;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePath;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathDoctor;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathEnum;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use ReflectionClass;

class CarePathTest extends UnitTestJfse {
  private $care_path;

  private $doctor;

  public function setUp(): void {
    parent::setUp();

    $reflection = new ReflectionClass(CarePathDoctor::class);
    $this->doctor     = $reflection->newInstanceWithoutConstructor();

    $this->care_path = CarePath::hydrate([
      'invoice_id' => 1,
      'indicator' => CarePathEnum::RECENTLY_INSTALLED_RP(),
      'declaration' => false,
      'install_date' => new DateTimeImmutable('2020-10-22'),
      'poor_md_zone_install_date' => new DateTimeImmutable('2020-10-22'),
      'doctor' => $this->doctor
    ]);
  }

  public function testGetPoorMdZoneInstallDate() {
    $this->assertEquals('20201022', $this->care_path->getPoorMdZoneInstallDate()->format('Ymd'));
  }

  public function testGetDoctor() {
    $this->assertEquals($this->doctor, $this->care_path->getDoctor());
  }

  public function testGetDeclaration() {
    $this->assertFalse($this->care_path->getDeclaration());
  }

  public function testGetInstallDate() {
    $this->assertEquals('20201022', $this->care_path->getInstallDate()->format('Ymd'));
  }

  public function testGetInvoiceId() {
    $this->assertEquals(1, $this->care_path->getInvoiceId());
  }

  public function testGetIndicator() {
    $this->assertEquals(CarePathEnum::RECENTLY_INSTALLED_RP(), $this->care_path->getIndicator());
  }
}
