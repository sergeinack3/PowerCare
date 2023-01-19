<?php

/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Symfony\Component\HttpFoundation\Request;

class CarePathControllerTest extends UnitTestJfse
{

    public function testEditRequest(): void
    {
        $this->assertEquals(new Request(), (new CarePathController('edit'))->editRequest());
    }

    public function testStoreRequest(): void
    {
        $_POST = [
            'invoice_id'                => '1',
            'indicator'                 => 'M',
            'install_date'              => '2020-10-21',
            'poor_md_zone_install_date' => '2020-10-21',
            'declaration'               => '1',
            'first_name'                => 'John',
            'last_name'                 => 'Doe',
            'invoicing_id'              => '123456789',
        ];

        $expected = [
            'invoice_id'                => 1,
            'indicator'                 => 'M',
            'install_date'              => new DateTimeImmutable('2020-10-21'),
            'poor_md_zone_install_date' => new DateTimeImmutable('2020-10-21'),
            'declaration'               => 1,
            'first_name'                => 'John',
            'last_name'                 => 'Doe',
            'invoicing_id'              => '123456789',
        ];

        $this->assertEquals(new Request([], $expected), (new CarePathController('store'))->storeRequest());
    }
}
