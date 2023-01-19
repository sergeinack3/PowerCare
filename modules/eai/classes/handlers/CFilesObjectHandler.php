<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\handlers;

use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CEAIObjectHandler;

/**
 * Class CSaEventObjectHandler
 * SA Event Handler
 */
class CFilesObjectHandler extends CEAIObjectHandler
{
    /** @var string[] Classes eligible for handler */
    public static $handled = ['CFile', 'CCompteRendu'];

    /**
     * @inheritdoc
     */
    public static function isHandled(CStoredObject $object)
    {
        return !$object->_ignore_eai_handlers && in_array($object->_class, self::$handled);
    }

    /**
     * @inheritdoc
     */
    public function onAfterStore(CStoredObject $object)
    {
        if (!parent::onAfterStore($object)) {
            return;
        }

        $this->sendFormatAction("onAfterStore", $object);
    }

    /**
     * @inheritdoc
     */
    public function onBeforeDelete(CStoredObject $object)
    {
        if (!parent::onBeforeDelete($object)) {
            return;
        }

        $this->sendFormatAction("onBeforeDelete", $object);
    }

    /**
     * @inheritdoc
     */
    public function onAfterDelete(CStoredObject $object)
    {
        if (!parent::onAfterDelete($object)) {
            return;
        }

        $this->sendFormatAction("onAfterDelete", $object);
    }
}
