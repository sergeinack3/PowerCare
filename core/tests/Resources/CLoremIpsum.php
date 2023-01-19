<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Resources;

/**
 * Class CLoremIpsum
 */
class CLoremIpsum {

  public $id;
  public $type;
  public $libelle;

  /**
   * CLoremIpsum constructor.
   *
   * @param int    $id
   * @param string $type
   * @param string $libelle
   */
  public function __construct($id, $type, $libelle) {
    $this->id      = $id;
    $this->type    = $type;
    $this->libelle = $libelle;
  }

  /**
   * @return int
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * @return string
   */
  public function getLibelle(): string {
    return $this->libelle;
  }

}