<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Wsdl;

use Countable;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CWSDL;

/**
 * Filesystem implementation of WSDLRepositoryInterface
 */
class FileWSDLRepository implements WSDLRepositoryInterface, IShortNameAutoloadable, Countable {
  use WSDLNameGeneratorTrait;

  /** @var string */
  private $dirname;

  /**
   * FileWSDLRepository constructor.
   *
   * @param string $prefix
   *
   * @throws CMbException
   */
  public function __construct(string $prefix) {
    $this->dirname = CAppUI::getTmpPath($prefix);

    if (!CMbPath::forceDir($this->dirname)) {
      throw new CMbException("FileWSDLRepository-error-Unable to create '%s' directory", $this->dirname);
    }
  }

  /**
   * Generate a WSDL filepath
   *
   * @param string $wsdl_name WSDL name
   *
   * @return string
   */
  private function generateWSDLFilepath(string $wsdl_name): string {
    return "{$this->dirname}/{$wsdl_name}.xml";
  }

  /**
   * @inheritDoc
   */
  public function find(?string $login, ?string $token, string $module, string $tab, string $classname, string $wsdl_mode): ?CWSDL {
    $wsdl_name              = static::generateWSDLName($login, $token, $module, $tab, $classname);
    $soap_server_local_file = $this->generateWSDLFilepath($wsdl_name);

    if (!file_exists($soap_server_local_file)) {
      return null;
    }

    if (!is_readable($soap_server_local_file)) {
      throw new CMbException('FileWSDLRepository-error-Unable to read WSDL file');
    }

    $file_content = file_get_contents($soap_server_local_file);

    if ($file_content === false) {
      throw new CMbException('FileWSDLRepository-error-Unable to get WSDL file content');
    }

    $wsdl = WSDLFactory::createFromString($wsdl_mode, $classname, $wsdl_name, $file_content);

    if ($wsdl->loadXML($file_content) === false) {
      throw new CMbException('FileWSDLRepository-error-Unable to load WSDL XML content');
    }

    return $wsdl;
  }

  /**
   * @inheritDoc
   */
  public function save(CWSDL $wsdl) {
    $soap_server_local_file = $this->generateWSDLFilepath($wsdl->getName());

    return (file_put_contents($soap_server_local_file, $wsdl->saveXML()) !== false);
  }

  /**
   * @inheritDoc
   */
  public function delete(CWSDL $wsdl) {
    $soap_server_local_file = $this->generateWSDLFilepath($wsdl->getName());

    if (!file_exists($soap_server_local_file)) {
      return true;
    }

    if (!is_writable($soap_server_local_file)) {
      throw new CMbException("FileWSDLRepository-error-WSDL file '%s' is not writable", $soap_server_local_file);
    }

    return unlink($soap_server_local_file);
  }

  /**
   * @inheritDoc
   */
  public function flush() {
    $count_files = count($this);
    CMbPath::emptyDir($this->dirname);
    $after = count($this);
    return $count_files - $after;
  }


    /**
     * @return int
     */
    public function count(): int
    {
        return CMbPath::countFiles($this->dirname);
    }
}
