<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Core\Redis\CRedisClient;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Files\CFileAddEdit;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Main CStoredObject controller
 */
class CDoObjectAddEdit
{
    public $className;
    public $objectKey;
    public $objectKeys;
    public $createMsg;
    public $modifyMsg;
    public $deleteMsg;
    public $request;
    public $redirect;
    public $redirectStore;
    public $redirectError;
    public $redirectDelete;
    public $ajax;
    public $callBack;
    public $suppressHeaders;

    public $postRedirect;
    public $_logIt;

    /** @var CStoredObject */
    public $_obj;

    /** @var CStoredObject */
    public $_old;

    /**
     * Constructor
     *
     * @param string $className Class name
     * @param int    $objectKey Object key name
     */
    public function __construct($className, $objectKey = null)
    {
        if (CApp::isReadonly()) {
            CAppUI::stepAjax("Mode-readonly-title", UI_MSG_ERROR);

            return;
        }

        global $m;

        $this->className      = $className;
        $this->postRedirect   = null;
        $this->redirect       = "m={$m}";
        $this->redirectStore  = null;
        $this->redirectError  = null;
        $this->redirectDelete = null;

        $this->createMsg = CAppUI::tr("$className-msg-create");
        $this->modifyMsg = CAppUI::tr("$className-msg-modify");
        $this->deleteMsg = CAppUI::tr("$className-msg-delete");

        $this->request =& $_POST;

        $this->_logIt = true;

        // @todo : à supprimer cf déplacement dans le doBind()
        $this->_obj = new $this->className();
        $this->_old = new $this->className();
        $this->onAfterInstanciation(); // Lancer ceci apres chaque instanciation de _obj et _old !!

        $this->objectKey  = $objectKey ?: $this->_obj->_spec->key;
        $this->objectKeys = $this->objectKey . "s";
    }

    /**
     * Bind request values to a CStoredObject
     *
     * @param bool $reinstanciate_objects Make new instances of the object (don't use the ones instanciated in the
     *                                    constructor)
     *
     * @return void
     */
    public function doBind($reinstanciate_objects = false)
    {
        $this->ajax            = CMbArray::extract($this->request, "ajax");
        $this->suppressHeaders = CMbArray::extract($this->request, "suppressHeaders");
        $this->callBack        = CMbArray::extract($this->request, "callback");
        $this->postRedirect    = CMbArray::extract($this->request, "postRedirect");

        if ($this->postRedirect) {
            $this->redirect = $this->postRedirect;
        }

        if ($reinstanciate_objects) {
            $this->_obj = new $this->className();
            $this->_old = new $this->className();
            $this->onAfterInstanciation(); // Lancer ceci apres chaque instanciation de _obj et _old !!
        }

        // Object binding
        $this->_obj->bind($this->request);

        // Old object
        $this->_old->load($this->_obj->_id);
    }

    /**
     * Action to do after CStoredObject instanciation
     *
     * @return void
     */
    public function onAfterInstanciation()
    {
    }

    /**
     * Delete object (del=1 parameter)
     *
     * @return void
     */
    public function doDelete()
    {
        if ($this->_obj->_purge) {
            set_time_limit(120);
            if ($msg = $this->_obj->purge()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
                if ($this->redirectError) {
                    $this->redirect =& $this->redirectError;
                }
            } else {
                CValue::setSession($this->objectKey);
                CAppUI::setMsg("msg-purge", UI_MSG_ALERT);
                if ($this->redirectDelete) {
                    $this->redirect =& $this->redirectDelete;
                }
            }

            return;
        }

        if ($msg = $this->_obj->delete()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
            if ($this->redirectError) {
                $this->redirect =& $this->redirectError;
            }
        } else {
            CValue::setSession($this->objectKey);
            CAppUI::setMsg($this->deleteMsg, UI_MSG_ALERT);
            if ($this->redirectDelete) {
                $this->redirect =& $this->redirectDelete;
            }
        }
    }

    /**
     * Store object
     *
     * @return void
     */
    public function doStore()
    {
        if ($msg = $this->_obj->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
            if ($this->redirectError) {
                $this->redirect =& $this->redirectError;
            }
        } else {
            $id = $this->objectKey;
            CValue::setSession($id, $this->_obj->_id);
            CAppUI::setMsg($this->_old->_id ? $this->modifyMsg : $this->createMsg, UI_MSG_OK);

            $this->handleExObject();

            $this->handleFiles();

            if ($this->redirectStore) {
                $this->redirect =& $this->redirectStore;
            }
        }
    }

    /**
     * Store additional object reference to the CExObject
     *
     * @return void
     */
    protected function handleExObject()
    {
        // Store additional object reference to the CExObject
        $ex_object_guid = CMbArray::get($this->request, "_ex_object_guid");
        if ($ex_object_guid) {
            $obj = $this->_obj;

            /** @var CExObject $ex_object */
            $ex_object = CStoredObject::loadFromGuid($ex_object_guid);
            if ($ex_object->_id) {
                $ex_object->additional_class = $obj->_class;
                $ex_object->additional_id    = $obj->_id;
                $ex_object->store();
            }
        }
    }

    /**
     * Store CFiles for each uploaded file
     *
     * Requires a multipart/form-data request, and a field named "formfile[]" for the files
     *
     * @return string|null
     */
    function handleFiles()
    {
        if (empty($_FILES) || (isset($_POST['_handle_files']) && !$_POST['_handle_files'])) {
            return null;
        }

        $obj = $this->_obj;

        $fileController          = new CFileAddEdit();
        $fileController->request = [
            "object_class" => $obj->_class,
            "object_id"    => $obj->_id,
            "ajax"         => $this->ajax,
        ];
        $fileController->doBind();

        return $fileController->doStore();
    }

    /**
     * Redirect at the end of the request
     *
     * @return void
     */
    public function doRedirect()
    {
        if ($this->redirect === null) {
            return;
        }

        // Cas ajax
        if ($this->ajax) {
            $this->doCallback();
        }

        // Cas normal
        CAppUI::redirect($this->redirect);
    }

    /**
     * Make JavaScript callback
     *
     * @return void
     */
    public function doCallback()
    {
        $messages = CAppUI::$instance->messages;

        echo CAppUI::getMsg();

        $fields                          = $this->_obj->getProperties(false, true);
        $fields[$this->_obj->_spec->key] = $this->_obj->_id;
        $fields["_guid"]                 = $this->_obj->_guid;
        $fields["_class"]                = $this->_obj->_class;
        $fields["_ui_messages"]          = $messages;
        $fields["_old_id"]               = $this->_old->_id;

        $id = $this->_obj->_id ?: 0;

        if ($this->callBack) {
            CAppUI::callbackAjax($this->callBack, $id, $fields);
        } else {
            $guid = "$this->className-$id";
            CAppUI::callbackAjax("Form.onSubmitComplete", $guid, $fields);
        }

        if (!CAppUI::$mobile) {
            CApp::rip();
        }
    }

    /**
     * Do action (store object)
     *
     * @return void
     */
    public function doIt()
    {
        if ($this->_obj->mustUseAntiCsrf()) {
            $this->request = AntiCsrf::validateParameters($this->request);
        }

        // Multiple case
        if ($object_ids = CMbArray::extract($this->request, $this->objectKeys)) {
            $request = $this->request;
            foreach (explode("-", $object_ids) as $object_id) {
                $this->request                   = $request;
                $this->request[$this->objectKey] = $object_id;
                $this->doSingle(true);
            }

            CSQLDataSource::$log = false;
            CRedisClient::$log   = false;

            $this->doRedirect();
        }

        $this->doSingle(false);

        CSQLDataSource::$log = false;
        CRedisClient::$log   = false;

        $this->doRedirect();
    }

    /**
     * Do action, single object mode
     *
     * @param bool $reinstanciate_objects Make new instances of the object (don't use the ones instanciated in the
     *                                    constructor)
     *
     * @return void
     */
    public function doSingle($reinstanciate_objects)
    {
        $this->doBind($reinstanciate_objects);

        if (CMbArray::extract($this->request, 'del')) {
            $this->doDelete();
        } else {
            $this->doStore();
        }
    }

    /**
     * Sets a error messages and redirects
     *
     * @param string $msg Message to display
     *
     * @return void
     */
    public function errorRedirect($msg)
    {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
        $this->redirect =& $this->redirectError;
        $this->doRedirect();
    }
}
