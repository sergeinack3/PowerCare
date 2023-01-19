<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CAppUI;
use Ox\Interop\Dmp\Antares\CDMPAntaresXML;

/**
 * DMP document sender
 */
class CDMPSender extends CDocumentSender {
  /**
   * @inheritdoc
   */
  function send(CDocumentItem $docItem) {
    if ($xml = CDMPAntaresXML::generateXML($docItem)) {
      return true;
    }

    CAppUI::stepAjax("Document non valide", UI_MSG_ERROR);
    return false;
  }

  /**
   * @inheritdoc
   */
  function cancel(CDocumentItem $docItem) {
  }

  /**
   * @inheritdoc
   */
  function resend(CDocumentItem $docItem) {
  }

  /**
   * @inheritdoc
   */
  function getSendProblem(CDocumentItem $docItem) {
  }
}
