<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Tag;

use Ox\Mediboard\System\Forms\CExClassField;

/**
 * Description
 */
class CExClassFieldTagHeureSaisieConstantes extends AbstractCExClassFieldTag {
  /**
   * @inheritDoc
   */
  public function validate(CExClassField $ex_class_field): bool {
    return ($ex_class_field->prop === 'dateTime');
  }
}
