<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Tag;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Mediboard\System\Forms\CExClassField;

/**
 * Description
 */
abstract class AbstractCExClassFieldTag implements IShortNameAutoloadable {
  /** @var string */
  private $tag;

  /**
   * AbstractCExClassFieldTag constructor.
   *
   * @param string $tag
   */
  final public function __construct(string $tag) {
    $this->tag = $tag;
  }

  /**
   * @return string
   */
  public function getTag(): string {
    return $this->tag;
  }

  /**
   * Todo: Not testable
   *
   * @return string
   */
  public function getName(): string {
    return CAppUI::tr("AbstractCExClassFieldTag.name.{$this->tag}");
  }

  /**
   * @param CExClassField $ex_class_field
   *
   * @return bool
   */
  abstract public function validate(CExClassField $ex_class_field): bool;
}
