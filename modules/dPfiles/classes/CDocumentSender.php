<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Document sender abstract class
 */
abstract class CDocumentSender implements IShortNameAutoloadable {
  /**
   * Function send
   *
   * @param CDocumentItem $docItem Document
   *
   * @return null|bool
   */
  function send(CDocumentItem $docItem) {
  }

  /**
   * Function cancel
   *
   * @param CDocumentItem $docItem Document
   *
   * @return null|bool
   */
  function cancel(CDocumentItem $docItem) {
  }

  /**
   * Function resend
   *
   * @param CDocumentItem $docItem Document
   *
   * @return null|bool
   */
  function resend(CDocumentItem $docItem) {
  }

  /**
   * Get send problem
   *
   * @param CDocumentItem $docItem Document
   *
   * @return null|bool
   */
  function getSendProblem(CDocumentItem $docItem) {
  }
}
