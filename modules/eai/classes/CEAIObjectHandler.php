<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\IGroupRelated;

/**
 * Class CEAIObjectHandler
 * EAI Object handler
 */
class CEAIObjectHandler extends ObjectHandler
{
    /** @var array */
    public static $handled = [];

    /** @var  string Sender GUID */
    public $_eai_sender_guid;

    /**
     * Trigger action on the right handler
     *
     * @param string        $action Action name
     * @param CStoredObject $object Object
     *
     * @return void
     * @throws Exception
     */
    public function sendFormatAction($action, CStoredObject $object)
    {
        if (!$action) {
            return;
        }

        $cn_receiver_guid = CValue::sessionAbs("cn_receiver_guid");

        $receiver = new CInteropReceiver();
        // Parcours des receivers actifs
        if (!$cn_receiver_guid) {
            // On est dans le cas d'un store d'un objet depuis MB
            if (!$object->_eai_sender_guid) {
                // Dans le cas où l'on a un group_id sur l'objet on va cibler les destinataires de cet établissement
                $group_id = null;
                if ($object instanceof IGroupRelated && ($group = $object->loadRelGroup()) && $group->_id) {
                    $group_id = $group->_id;
                }

                // Tamm on force si pas de group_id retrouvé
                if (!$group_id && CAppUI::isGroup()) {
                    $group_id = CGroups::loadCurrent()->_id;
                }

                $receivers = $receiver->getObjects(true, $group_id);
            } else {
                // On est dans le cas d'un enregistrement provenant d'une interface
                $receivers = [];

                /** @var CInteropSender $sender */
                $sender = CMbObject::loadFromGuid($object->_eai_sender_guid);

                // On utilise le routeur de l'EAI
                // @todo On ne va transmettre aucun message, hormis pour les patients - attention CASSE !!
                if (CAppUI::conf("eai use_routers") && $sender->_id) {
                    // Récupération des receivers de ttes les routes actives
                    /** @var CEAIRoute[] $routes */
                    $where           = [];
                    $where["active"] = " = '1'";
                    $routes          = $sender->loadBackRefs(
                        "routes_sender",
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        $where
                    );

                    foreach ($routes as $_route) {
                        if (!$_route->active) {
                            continue;
                        }

                        $receiver                                 = $_route->loadRefReceiver();
                        $receivers[CClassMap::getSN($receiver)][] = $receiver;
                    }
                }
            }
        } // Sinon envoi destinataire sélectionné (cas sur un destinataire ciblé ex. mod-connectathon)
        else {
            if ($cn_receiver_guid === "none") {
                return;
            }
            $receiver = CMbObject::loadFromGuid($cn_receiver_guid);
            if (!$receiver || !$receiver->_id) {
                return;
            }
            $receivers[$receiver->_class][] = $receiver;
        }

        if (!$receivers) {
            return;
        }

        foreach ($receivers as $_receivers) {
            if (!$_receivers) {
                continue;
            }
            /** @var CInteropReceiver $_receiver */
            foreach ($_receivers as $_receiver) {
                // Destinataire non actif on envoi pas
                if (!$_receiver->actif) {
                    continue;
                }

                // Receiver use specific handler
                if ($_receiver->use_specific_handler) {
                    continue;
                }

                $handler = $_receiver->getFormatObjectHandler($this);
                if (!$handler) {
                    continue;
                }

                $_receiver->loadConfigValues();
                $_receiver->loadRefsMessagesSupported();

                // Affectation du receiver à l'objet
                $object->_receiver = $_receiver;

                $handlers = !is_array($handler) ? [$handler] : $handler;

                // On parcours les handlers
                foreach ($handlers as $_handler) {
                    // Récupère le handler du format
                    $format_object_handler = new $_handler();

                    // Envoi l'action au handler du format
                    try {
                        // Method may not have been implemented
                        if (is_callable([$format_object_handler, $action])) {
                            $format_object_handler->$action($object);
                        }
                    } catch (Exception $e) {
                        CAppUI::setMsg($e->getMessage(), UI_MSG_WARNING);
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function onBeforeStore(CStoredObject $object)
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        if (isset($object->_eai_sender_guid)) {
            $this->_eai_sender_guid = $object->_eai_sender_guid;
        }

        return true;
    }

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
        if (!$this->isHandled($object)) {
            return false;
        }

        if (!$object->_ref_last_log && $object->_class !== "CIdSante400") {
            return false;
        }

        // Cas d'une fusion
        if ($object->_merging) {
            return false;
        }

        if ($object->_forwardRefMerging) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onBeforeMerge(CStoredObject $object)
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        if (!$object->_merging) {
            return false;
        }

        if (isset($object->_eai_sender_guid)) {
            $this->_eai_sender_guid = $object->_eai_sender_guid;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onMergeFailure(CStoredObject $object)
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        if (isset($object->_fusion) && !$object->_fusion) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onAfterMerge(CStoredObject $object)
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        if (!$object->_merging) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onBeforeDelete(CStoredObject $object)
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        if (isset($object->_eai_sender_guid)) {
            $this->_eai_sender_guid = $object->_eai_sender_guid;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onAfterDelete(CStoredObject $object)
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        return true;
    }
}
