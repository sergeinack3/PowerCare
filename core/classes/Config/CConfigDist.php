<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Config;

/**
 * Class CConfigDist
 */
class CConfigDist {

  CONST FILENAME_CONFIG_CORE = 'includes/config_core.php';
  CONST FILENAME_CONFIG_DIST = 'includes/config_dist.php';
  CONST PATTERN_CONFIG_MODULE = 'modules/*/config.php';

  private $path_config_core;
  private $path_config_dist;
  private $pattern_config_module;

  /**
   * CConfigDist constructor.
   */
  public function __construct() {
    $root                        = dirname(dirname(dirname(__DIR__)));
    $this->path_config_core      = $root . DIRECTORY_SEPARATOR . static::FILENAME_CONFIG_CORE;
    $this->path_config_dist      = $root . DIRECTORY_SEPARATOR . static::FILENAME_CONFIG_DIST;
    $this->pattern_config_module = $root . DIRECTORY_SEPARATOR . static::PATTERN_CONFIG_MODULE;
  }

  /**
   * Build includes/config_dist.php
   * config_core.php + modules/etoile/config.php
   *
   * @return string
   */
  public function build() {
    // Start
    $time_start = microtime(true);

    // Suppression
    if (file_exists($this->path_config_dist)) {
      $this->delete();
    }

    // Config core
    $dPconfig = array();
    include $this->path_config_core;

    // Module config file inclusion
    $config_files = glob($this->pattern_config_module);
    foreach ($config_files as $file) {
      include_once $file;
    }

    // Save
    $this->create($dPconfig);

    // Stop
    $_time  = @round(microtime(true) - $time_start, 3);
    $_count = @count($dPconfig, true);
    $_file  = $this->path_config_dist;

    return "Generated config_dist file in {$_file} containing {$_count} configs during {$_time} sec";
  }

  /**
   * @return void
   */
  private function delete() {
    unlink($this->path_config_dist);
  }

  /**
   * @param array $dPconfig
   *
   * @return void
   */
  private function create(array $dPconfig) {
    $content = '$dPconfig = ' . var_export($dPconfig, true);
    $content = '<?php ' . PHP_EOL . ' global $dPconfig; ' . PHP_EOL . $content . ';';

    file_put_contents($this->path_config_dist, $content);
  }
}