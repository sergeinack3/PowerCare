<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\CConfigurationModelManager;
use Ox\Mediboard\System\CMessage;

class JfseApCvConfigurationHandler extends ObjectHandler
{
    private const ACTIVATION_FEATURE = 'jfse General apcv';
    private const DATE_ACTIVATION_FEATURE = 'jfse General apcv_date';

    /**
     * @inheritDoc
     */
    public static function isHandled(CStoredObject $object)
    {
        return parent::isHandled($object) && $object instanceof CConfiguration;
    }

    public function onAfterStore(CStoredObject $object): bool
    {
        if (!self::isHandled($object)) {
            return false;
        }

        /** @var CConfiguration $object */
        if ($object->feature === self::ACTIVATION_FEATURE) {
            $strategy = CConfigurationModelManager::getStrategy();

            $message = $this->getMessage();
            if ($strategy->objectValueModified($object, '1')) {
                $message->deb = CMbDT::dateTime();
                $message->fin  = CMbDT::dateTime('+7 days', $message->deb);
            } elseif ($strategy->objectValueModified($object, '0')) {
                $message->deb = CAppUI::gconf(self::DATE_ACTIVATION_FEATURE) . ' 08:00:00';
                $message->fin = CMbDT::date('+7 days', $message->deb) . ' 18:00:00';
            }

            $message->store();
        } elseif ($object->feature === self::DATE_ACTIVATION_FEATURE) {
            $strategy = CConfigurationModelManager::getStrategy();

            if ($strategy->objectValueModified($object) && CAppUI::gconf(self::ACTIVATION_FEATURE) !== '1') {
                $message = $this->getMessage();
                $message->deb = $object->value . ' 08:00:00';
                $message->fin = CMbDT::date('+7 days', $message->deb) . ' 18:00:00';
                $message->store();
            }
        }

        return true;
    }

    /**
     * Load the ApCV activation system message
     *
     * @return CMessage
     * @throws \Exception
     */
    private function getMessage(): CMessage
    {
        $message = new CMessage();
        $message->titre = "Activation de l\'ApCV";
        $message->loadMatchingObject();

        if (!$message->_id) {
            $message = $this->createMessage();
        }

        return $message;
    }

    /**
     * Initialize the message object
     *
     * @return CMessage
     * @throws \Exception
     */
    private function createMessage(): CMessage
    {
        $message = new CMessage();
        $message->deb   = '2023-01-01 08:00:00';
        $message->fin   = '2023-01-06 18:00:00';
        $message->titre = "Activation de l'ApCV";
        $message->corps = "L'utilisation de l'Appli Carte Vitale a été activée pour la facturation des feuilles de "
            . "soins électroniques ainsi que pour les téléservices de l'Assurance Maladie";

        return $message;
    }
}
