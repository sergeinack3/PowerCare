<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Tag;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbException;
use Ox\Mediboard\System\Forms\CExClassField;

/**
 * Description
 */
abstract class CExClassFieldTagFactory implements IShortNameAutoloadable {
  /** @var string[] */
  private const TAGS = [
    'heure_saisie_constantes' => CExClassFieldTagHeureSaisieConstantes::class,
  ];

  /**
   * @param string $tag
   *
   * @return AbstractCExClassFieldTag
   * @throws CMbException
   */
  public static function getTag(string $tag): AbstractCExClassFieldTag {
    if (!isset(self::TAGS[$tag])) {
      throw new CMbException('CExClassFieldFactory-error-Unknown ExClassFieldTag: %s', $tag);
    }

    $tag_class = self::TAGS[$tag];

    $object = new $tag_class($tag);

    if (!$object instanceof AbstractCExClassFieldTag) {
      throw new CMbException('CExClassFieldFactory-error-Tag is not an instance of AbstractCExClassFieldTag: %s', $tag);
    }

    return $object;
  }

  /**
   * @return string[]
   */
  public static function getTags(): array {
    return array_keys(self::TAGS);
  }
}
