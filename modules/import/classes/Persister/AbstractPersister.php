<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Persister;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Exception\PersisterException;

/**
 * Description
 */
abstract class AbstractPersister implements PersisterVisitorInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    public function persistObject(CStoredObject $object): CStoredObject
    {
        return $this->persist($object);
    }

    /**
     * Todo: Should use ImportableInterface instead of CStoredObject
     *
     * @param CStoredObject $object
     *
     * @return CStoredObject
     * @throws PersisterException
     */
    protected function persist(CStoredObject $object): CStoredObject
    {
        //        $create = !($object->_id);

        $object->_ignore_eai_handlers = true;

        try {
            if ($msg = $object->store()) {
                throw new PersisterException("PersisterException-error-%s : %s", $object->_class, $msg);
            }
        } catch (Exception $e) {
            throw new PersisterException("PersisterException-error-%s : %s", $object->_class, $e->getMessage());
        }

        //        $action = ($create) ? 'create' : 'found';
        //        CAppUI::setMsg("{$object->_class}-msg-{$action}", UI_MSG_OK);

        return $object;
    }
}
