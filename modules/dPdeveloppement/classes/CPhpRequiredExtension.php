<?php
/**
 * @package  Mediboard\
 * @author   SAS OpenXtrem <dev@openxtrem.com>
 * @license  https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license  https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

class CPhpRequiredExtension extends CAbstractConfigurationVariable {
  public $extensionVersion = null;
  public $currentPHPVersion = null;
  public $requiredPHPVersion = null;
  /**
   * @var bool Indicate if the extension is supposed to be installed or not.
   */
  public $supposedToBeInstalled = true;

  /**
   * Construct a CPhpRequired extension.
   * If the requiredPHPVersion is not null, a comparison will be performed and it will be possible to check, if the
   * extension can be installed on this version of PHP
   *
   * @param string $extensionName      Name of the extension
   * @param string $currentPHPVersion  Current version of PHP
   * @param null   $requiredPHPVersion Version of PHP
   */
  public function __construct($extensionName, $currentPHPVersion, $requiredPHPVersion = null) {
    parent::__construct($extensionName);

    $this->currentPHPVersion  = $currentPHPVersion;
    $this->requiredPHPVersion = $requiredPHPVersion;
  }

  public function getExtensionName() {
    return $this->varName;
  }

  public function getPHPVersion() {
    return $this->currentPHPVersion;
  }

  public function getRequiredPHPVersion() {
    return $this->requiredPHPVersion;
  }

  public function isInstalled() {
    return $this->exists();
  }

  public function setInstalled($installed) {
    $this->exists = $installed;
  }

  public function extensionVersion() {
    return $this->extensionVersion;
  }

  public function setExtensionVersion($version) {
    $this->extensionVersion = $version;
  }

  public function supposedToBeInstalled() {
    return $this->supposedToBeInstalled;
  }

  public function setSupposedToBeInstalled($supposedToBeInstalled) {
    $this->supposedToBeInstalled = $supposedToBeInstalled;
  }

  /**
   *  Compare the current version of PHP with the required PHP version
   * @return int if the current version is lower than the required, 0 if the current version match with the required, 1 is the current version is greater than the required. If the requiredPHPVersion is null, this method returns 0
   */
  public function versionCompare() {
    if ($this->requiredPHPVersion) {
      return version_compare($this->currentPHPVersion, $this->requiredPHPVersion, ">=");
    }

    return 0;
  }
}