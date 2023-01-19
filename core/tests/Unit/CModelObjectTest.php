<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CClassMap;
use Ox\Core\CModelObject;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Admin\CUser;
use Ox\Tests\OxUnitTestCase;

class CModelObjectTest extends OxUnitTestCase
{

    public function testSampleObjectException(){
        $this->expectException(CModelObjectException::class);
        CModelObject::getSampleObject(CClassMap::class);
    }

    public function testSampleObject(){
        $o = CModelObject::getSampleObject(CUser::class);
        $this->assertInstanceOf(CUser::class, $o);
        $this->assertNotNull($o->user_username);

        $o = CUser::getSampleObject();
        $this->assertInstanceOf(CUser::class, $o);
    }
}
