<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

class CPhpConfiguration extends CDashboardConfiguration {
  private $_loadedExtensions = array();

  /**
   * @var array used to the ini variables we want to extract from the ini_get_all
   */
  static $_iniVariablesWhitelist = array(
    "date.timezone",
    "session.gc_maxlifetime",
    "session.gc_probability",
    "default_socket_timeout",
    "upload_max_filesize",
    "post_max_size",
    "memory_limit",
    "max_execution_time",
    "apc.shm_size",
    "apc.ttl",
    "apc.user_ttl",
    "apc.enable_opcode_cache",
    "opcache.memory_consumption",
    "opcache.enable",
    "opcache.load_comments",
    "opcache.max_accelerated_files",
    "opcache.max_file_size",
    "opcache.max_wasted_percentage",
    "opcache.interned_strings_buffer"
  );

  static $_requiredExtensions = array(
    array("curl"),
    array("gd"),
    array("mysql"),
    array("pdo"),
    array("pdo_mysql"),
    array("mbstring"),
    array("zlib"),
    array("zip"),
    array("json"),
    array("dom"),
    array("apc"),
    array("apcu", "5.5"),
    array("zend opcache", "5.5")
  );

  /**
   * @see parent::init()
   */
  public function init() {
    $this->configuration["phpversion"] = $this->createPHPVersion();
    $phpVersion                        = $this->configuration["phpversion"]["versionNumber"];

    $phpExtensionsList = get_loaded_extensions();
    $this->configuration["requiredExtensions"] = array();

    foreach (self::$_requiredExtensions as $requiredExtension) {
      $found                          = false;
      $phpVersionRequiredForExtension = null;
      if (count($requiredExtension) > 1) {
        $phpVersionRequiredForExtension = $requiredExtension[1];
      }

      $phpExtension = new CPhpRequiredExtension($requiredExtension[0], $phpVersion, $phpVersionRequiredForExtension);
      foreach ($phpExtensionsList as $installedExtension) {
        if (strtolower($requiredExtension[0]) == strtolower($installedExtension)) {
          $extensionVersion = phpversion($installedExtension);
          $extensionVersion = $extensionVersion ? $extensionVersion : "Pas d'information de version";
          $phpExtension->setInstalled(true);
          $phpExtension->setExtensionVersion($extensionVersion);
          $found = true;
          break;
        }
      }

      //If the extension haven't been found in the installed extensions list
      if (!$found) {
        $phpExtension->setInstalled(false);
      }
      $this->configuration["requiredExtensions"][] = $phpExtension;
    }

    $this->extractIniVariables();

    /*
     * Looking for Zend Opcache extension.
     * If it exists, we must set apc.enable_opcode_cache as not required
     */
    $opCache = $this->getRequiredExtension("zend opcache");

    if ($opCache) {
      $iniVar = $this->findIniVar("apc.enable_opcode_cache");
      //If the opCache have been found, then apc.enable_opcode_cache is not required
      if ($iniVar) {
        $iniVar->mustBeDefined = false;
        //unset($iniVar);
      }
    }

    //$this->configuration["installedExtensions"] = $this->_loadedExtensions;

    $peak_memory_allocated = memory_get_peak_usage(true);
    $memory_allocated      = memory_get_usage(true);

    $this->configuration["memory_allocated"]      = $memory_allocated;
    $this->configuration["peak_memory_allocated"] = $peak_memory_allocated;
  }

  /**
   * Convenience method used to set the php version into a variable
   * it returns an associative array with the full version name and with the version number only
   * eg: array(
   * "fullVersion" => "5.6.14-0+deb8u1"
   * "versionNumber" => "5.6.14"
   * )
   * The versionNumber is used to check and compare php versions together
   * @return array
   */
  private function createPHPVersion() {
    $explodedVersion             = explode(".", PHP_VERSION);
    $phpversion                  = array();
    $phpversion["fullVersion"]   = phpversion();
    $phpversion["versionNumber"] = $explodedVersion[0] . "." . $explodedVersion[1] . "." . $explodedVersion[2];

    return $phpversion;
  }

  /**
   * Get all the ini variables
   *
   * @return void
   */
  private function extractIniVariables() {
    $iniVars = ini_get_all();

    foreach (self::$_iniVariablesWhitelist as $variableWhitelist) {
      $iniVariableObj = new CPhpIniVariable($variableWhitelist);

      foreach ($iniVars as $key => $iniVar) {
        if ($key === $variableWhitelist) {
          $iniVariableObj->exists = true;
          $iniVariableObj->setIniVarValue($iniVar);
          break;
        }
      }
      $this->configuration["iniVars"][$iniVariableObj->getVarName()] = $iniVariableObj;
    }
  }

  /**
   * Find and return an extension with the given name
   *
   * @param $extensionName
   *
   * @return CPhpRequiredExtension
   */
  public function getRequiredExtension($extensionName) {
    foreach ($this->configuration["requiredExtensions"] as $extension) {
      if (strtolower($extensionName) === strtolower($extension->getExtensionName())) {
        return $extension;
      }
    }

    return null;
  }

  /**
   * Find and return an Ini variable
   * @param int $name variable name
   *
   * @return CPhpInivariable
   */
  public function findIniVar($name) {
    foreach ($this->configuration["iniVars"] as $iniVar) {
      if ($iniVar->getVarName() === $name) {
        return $iniVar;
      }
    }

    return null;
  }

  /**
   * @see parent::fromJson($jsonData)
   */
  public static function fromJson($jsonData) {
    $phpConfiguration = new CPhpConfiguration();
    $phpversion       = $jsonData["configuration"]["phpversion"];

    //Import php version array
    $phpConfiguration->configuration["phpversion"] = $phpversion;

    //Import required extensions
    $phpJsonRequiredExtensions = $jsonData["configuration"]["requiredExtensions"];

    foreach ($phpJsonRequiredExtensions as $importedRequiredExtension) {
      $phpRequiredExtension = new CPhpRequiredExtension(
        $importedRequiredExtension["varName"],
        $importedRequiredExtension["currentPHPVersion"],
        $importedRequiredExtension["requiredPHPVersion"]
      );

      $phpRequiredExtension->exists = $importedRequiredExtension["exists"];
      $phpRequiredExtension->setSupposedToBeInstalled($importedRequiredExtension["supposedToBeInstalled"]);
      $phpRequiredExtension->setExtensionVersion($importedRequiredExtension["extensionVersion"]);

      $phpConfiguration->configuration["requiredExtensions"][] = $phpRequiredExtension;
    }

    //Import Ini Vars
    $importedIniVars = $jsonData["configuration"]["iniVars"];

    foreach ($importedIniVars as $key => $importedIniVar) {
      $iniVar = new CPhpIniVariable($importedIniVar["varName"]);
      $iniVar->setIniVarValue(array(
        "global_value" => $importedIniVar["globalValue"],
        "local_value"  => $importedIniVar["localValue"],
        "access"       => $importedIniVar["accessLevel"]
      ));
      $iniVar->exists = $importedIniVar["exists"];
      $iniVar->mustBeDefined = $importedIniVar["mustBeDefined"];

      $phpConfiguration->configuration["iniVars"][$importedIniVar["varName"]] = $iniVar;
    }

    return $phpConfiguration;
  }

}