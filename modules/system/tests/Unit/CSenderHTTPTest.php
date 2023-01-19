<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CSenderHTTP;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CSenderHTTPTest extends OxUnitTestCase
{
    public function testLoadFromUserNotFound(): void
    {
        $this->assertEquals(null, CSenderHTTP::loadFromUser(CUser::get()));
        $this->assertEquals(null, CSenderHTTP::loadFromUser(new CUser()));
    }

    public function testLoadFromUserFound(): void
    {
        $sender_http = new CSenderHTTP();
        $sender_http->nom      = 'CSenderHTTP-'. rand(1,100);
        $sender_http->actif    = 1;
        $sender_http->role     = CAppUI::conf("instance_role");
        $sender_http->user_id  = CUser::get()->_id;
        $sender_http->group_id =  CGroups::loadCurrent()->_id;
        $sender_http->store();

        $this->assertEquals($sender_http->_id, CSenderHTTP::loadFromUser(CUser::get())->_id);

        $sender_http->delete();
    }
}
