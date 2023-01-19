<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CStatutCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mssante\CMSSanteMail;

/**
 * Description
 */
class CElectronicDelivery extends CMbObject {
  /** @var integer Primary key */
  public $electronic_delivery_id;

  /** @var string The class of the message (CUserMail, CMSSanteMail,...) */
  public $message_class;
  /** @var integer The id of the message */
  public $message_id;
  /** @var string The class of the document (CCompteRendu or CFile) */
  public $document_class;
  /** @var integer The id of the document */
  public $document_id;

  /** @var CMSSanteMail|CUserMail */
  public $_ref_message;

  /** @var CDocumentItem */
  public $_ref_document;

  /** @var string The means of delivering the document (mail, apicrypt or mssante) */
  public $_delivery_medium;

  /** @var array The addresses of the receivers */
  public $_receivers;

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "electronic_deliveries";
    $spec->key   = "electronic_delivery_id";

    return $spec;
  }


  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  public function getProps() {
    $props = parent::getProps();

    $props['message_class']   = 'enum list|CUserMail|CMSSanteMail notNull';
    $props['message_id']      = 'ref notNull meta|message_class back|deliveries cascade';
    $props['document_class']  = 'enum list|CFile|CCompteRendu notNull';
    $props['document_id']     = 'ref notNull class|CDocumentItem meta|document_class back|deliveries cascade';

    return $props;
  }

  /**
   * Load the linked message
   *
   * @param bool $cache Cache usage
   *
   * @return CUserMail|CMSSanteMail
   */
  public function loadRefMessage($cache = true) {
    $this->_ref_message = $this->loadFwdRef('message_id', $cache);

    switch ($this->message_class) {
      case 'CMSSanteMail':
        $this->_delivery_medium = 'mssante';
        $this->_receivers = ($this->_ref_message) ? explode('|', $this->_ref_message->to) : [];
        break;
      case 'CUserMail':
        $this->_delivery_medium = 'mail';
        if ($this->_ref_message->is_apicrypt) {
          $this->_delivery_medium = 'apicrypt';
        }

        $this->_receivers = ($this->_ref_message) ? explode(',', $this->_ref_message->to) : [];
        break;
      default :
    }

    return $this->_ref_message;
  }

  /**
   * Load the linked document
   *
   * @param bool $cache Cache usage
   *
   * @return CDocumentItem
   */
  public function loadRefDocument($cache = true) {
    return $this->_ref_document = $this->loadFwdRef('document_id', $cache);
  }

    /**
     * Return the list of the recipients that have received the document by mail
     */
    public static function getMailRecipients(CDocumentItem $document): array
    {
        $ljoin = ['user_mail' => 'user_mail.user_mail_id = electronic_deliveries.message_id'];
        $where = [
            'electronic_deliveries.message_class' => " = 'CUserMail'",
            'user_mail.is_apicrypt'               => " = '0'",
        ];

        /** @var CElectronicDelivery[] $deliveries */
        $deliveries = $document->loadBackRefs(
            "deliveries",
            null,
            null,
            'electronic_deliveries.electronic_delivery_id',
            $ljoin,
            null,
            "mail_deliveries",
            $where
        );

        CMbObject::massLoadFwdRef($deliveries, 'message_id');

        $recipients = [];
        foreach ($deliveries as $delivery) {
            $delivery->loadRefMessage();
            $recipients = array_merge($recipients, $delivery->_receivers);
        }

        if ($document instanceof CCompteRendu) {
            $recipients = array_merge($recipients, self::getMailRecipients($document->loadFile()));
        }

        return $recipients;
    }

    /**
     * Return the list of the recipients that have received the document by Apicrypt
     */
    public static function getApicryptRecipients(CDocumentItem $document): array
    {
        $ljoin    = ['user_mail' => 'user_mail.user_mail_id = electronic_deliveries.message_id'];
        $where    = [
            'electronic_deliveries.message_class' => " = 'CUserMail'",
            'user_mail.is_apicrypt'               => " = '1'",
        ];

        /** @var CElectronicDelivery[] $deliveries */
        $deliveries = $document->loadBackRefs(
            "deliveries",
            null,
            null,
            'electronic_deliveries.electronic_delivery_id',
            $ljoin,
            null,
            "apicrypt_deliveries",
            $where
        );

        CMbObject::massLoadFwdRef($deliveries, 'message_id');

        $recipients = [];
        foreach ($deliveries as $delivery) {
            $delivery->loadRefMessage();
            $recipients = array_merge($recipients, $delivery->_receivers);
        }

        if ($document instanceof CCompteRendu) {
            $recipients = array_merge($recipients, self::getApicryptRecipients($document->loadFile()));
        }

        return $recipients;
    }

    /**
     * Return the list of the recipients that have received the document by MSSante
     */
    public static function getMssanteRecipients(CDocumentItem $document): array
    {
        $where = [
            'electronic_deliveries.message_class' => " = 'CMSSanteMail'",
        ];

        /** @var CElectronicDelivery[]|null $deliveries */
        $deliveries = $document->loadBackRefs(
            "deliveries",
            null,
            null,
            null,
            null,
            null,
            "mssante_deliveries",
            $where
        );

        CMbObject::massLoadFwdRef($deliveries, 'message_id');

        $recipients = [];
        foreach ($deliveries as $delivery) {
            $delivery->loadRefMessage();
            $recipients = array_merge($recipients, $delivery->_receivers);
        }

        if ($document instanceof CCompteRendu) {
            $recipients = array_merge($recipients, self::getMssanteRecipients($document->loadFile()));
        }

        return $recipients;
    }

    public function store(): void
    {
        if ($this->document_class == "CCompteRendu") {
            $statut                  = new CStatutCompteRendu();
            $statut->datetime        = CMbDT::dateTime();
            $statut->compte_rendu_id = $this->document_id;
            $statut->user_id         = CMediusers::get()->_id;
            $statut->statut          = "envoye";
            $statut->store();
        }
        parent::store();
    }
}
