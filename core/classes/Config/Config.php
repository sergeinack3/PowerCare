<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Config;

use Exception;

/**
 * Lightweight compatibility reimplementation of PEAR_Config package (PHPArray parser only)
 */
final class Config {
  /** @var mixed Datasource: can be a file url, a dsn, an object... */
  private $datasrc;

  /** @var Config_Container Container object */
  private $container;

  /**
   * Config2 constructor.
   *
   * Creates a root container
   */
  public function __construct() {
    $this->container = new Config_Container('section', 'root');
  }

  /**
   * Returns the root container
   *
   * @return Config_Container
   */
  public function getContainer() {
    return $this->container;
  }

  /**
   * Parses the datasource contents
   *
   * This method will parse the datasource given and fill the root
   * Config_Container object with other Config_Container objects.
   *
   * @param string|array $datasrc Datasource to parse
   * @param array        $options Parser options
   *
   * @return Config_Container
   * @throws Exception
   */
  public function parseConfig($datasrc, $options = array()) {
    $this->datasrc = $datasrc;

    $parser = new Config_Container_PHPArray($options);
    $parser->parseDatasrc($datasrc, $this);

    return $this->getContainer();
  }

  /**
   * Writes the container contents to the datasource.
   *
   * @param string|array $datasrc Datasource to write to
   * @param array        $options Parser options
   *
   * @return bool
   * @throws Exception
   */
  public function writeConfig($datasrc = null, $options = array()) {
    $datasrc = ($datasrc) ?: $this->datasrc;

    return $this->getContainer()->writeDatasrc($datasrc, $options);
  }
}
