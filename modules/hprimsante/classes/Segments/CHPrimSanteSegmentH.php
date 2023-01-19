<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Segments;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hprimsante\CHPrimSanteSegment;

/**
 * Class CHPRSegmentH
 * H - Represents an HPR L message segment (Message Header)
 */

class CHPrimSanteSegmentH extends CHPrimSanteSegment {
  public $name = "H";

  /**
   * @inheritdoc
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $message  = $event->message;

    $data = array();

    // H-1 : Field Separator
    $data[] = $message->fieldSeparator;

    // H-2: Encoding Characters (ST)
    $data[] = substr($message->getEncodingCharacters(), 1);

    // H-3 : Message ID
    $data[] = $event->_exchange_hpr->_id;

    // H-4 : Password
    $data[] = null;

    // H-5 : Sender ID
    $data[] = CAppUI::conf("hprimsante sending_application");

    // H-6 : Sender address
    $data[] = null;

    // H-7 : Context
    $data[] = $event->type;

    // H-8 : Sender phone
    $data[] = null;

    // H-9 : Transmission characteristics
    $data[] = null;

    // H-10 : Receiver ID
    $receiver = $event->_receiver && $event->_receiver->_id ? $event->_receiver : $event->_sender;
    $data[] = array(
      array(
        $receiver->_id,
        $receiver->nom
      )
    );

    // H-11 : Comment
    $data[] = null;

    // H-12 : Processing ID
    $data[] = (CAppUI::conf("instance_role") == "prod") ? "P" : "T";

    // H-13 : Version and Type
    $data[] = array(
      array(
        $event->version,
        $event->type_liaison,
      )
    );

    // H-14 : Date/Time of Message
    $data[] = CMbDT::dateTime();

    $this->fill($data);
  }
}
