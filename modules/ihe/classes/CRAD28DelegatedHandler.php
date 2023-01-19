<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;

/**
 * Class CRAD28DelegatedHandler
 * RAD28 Delegated Handler - Structured Report Export
 */
class CRAD28DelegatedHandler extends CITIDelegatedHandler
{
    /** @var string[] Classes eligible for handler */
    public static $handled = ['CFile', 'CCompteRendu'];

    /** @var string Message profile */
    protected $profil = 'SINR';

    /** @var string Message */
    protected $message = 'ORU';

    /** @var string Transaction */
    protected $transaction = 'RAD28';

    /**
     * @inheritDoc
     */
    public static function isHandled(CStoredObject $mbObject)
    {
        return in_array($mbObject->_class, self::$handled);
    }

    /**
     * @see parent::onAfterStore()
     */
    public function onAfterStore(CStoredObject $object): bool
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        return true;
    }
}
