<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Generators;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\Generators\CObjectGenerator;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * @deprecated Will be replaced by fixtures
 */
class CUserGenerator extends CObjectGenerator
{
    static $mb_class    = CUser::class;
    static $dependances = [CGroups::class];

    /** @var CUser $object */
    protected $object;

    /**
     * @inheritDoc
     */
    function generate()
    {
        $names                        = $this->getRandomNames();
        $this->object->user_username  = uniqid();
        $this->object->user_last_name = CMbArray::get($names, 0)->firstname;

        // NO user_type 1 allowed here.
        $this->object->user_type = $this->type ? $this->type : rand(2, 24);


        if ($msg = $this->object->store()) {
            dump($msg);
            CAppUI::setMsg($msg, UI_MSG_ERROR);
        } else {
            CAppUI::setMsg("CPatientUser-msg-create", UI_MSG_OK);
            $this->trace(static::TRACE_STORE);
        }

        return $this->object;
    }
}
