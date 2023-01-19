<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

/**
 * DMP document sender
 */
class CDocumentSourceSender extends CDocumentSender {
  /**
   * @inheritdoc
   */
  function send(CDocumentItem $docItem) {
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
