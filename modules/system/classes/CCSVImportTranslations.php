<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CStoredObject;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\Module\CModule;
use Throwable;

/**
 * Description
 */
class CCSVImportTranslations extends CMbCSVObjectImport {
  protected $translations = array();
  protected $default_lang;

  /**
   * @inheritdoc
   */
  function __construct($file_path) {
    parent::__construct($file_path);
    $this->default_lang = CAppUI::pref("LOCALE", "fr");
  }

  /**
   * @inheritdoc
   */
  function import() {

  }

  /**
   * Parse the file and store informations in $this->translations
   *
   * @return void
   */
  function parseFile() {
    $this->openFile();
    $this->setColumnNames();

    $languages = CAppUI::getAvailableLanguages();
    CAppUI::loadLocales();

    while ($line = $this->readAndSanitizeLine()) {
      list(, $value, $prefix) = CAppUI::splitLocale($line['source']);

      $module = self::getCorrespondingModule($prefix, $value);
      if (!array_key_exists($module, $this->translations)) {
        $this->translations[$module] = array(
          'new'  => array(),
          'old'  => array(),
          'same' => array(),
        );
      }

      $language = (in_array($line['language'], $languages)) ? $line['language'] : $this->default_lang;

      $translation = new CTranslationOverwrite();
      $translation->source = $line['source'];
      $translation->language = $language;
      $translation->loadMatchingObjectEsc();

      $type = 'new';
      if ($translation && $translation->_id) {
        if ($line['new'] !== $translation->translation) {
          $type = 'old';
        }
        else {
          $type = 'same';
        }
      }

      $this->translations[$module][$type][] = array(
        'key'       => $line['source'],
        'old_value' => ($translation->translation) ?: '',
        'new_value' => $line['new'],
        'lang'      => $language,
      );
    }

    $this->csv->close();
  }

  /**
   * @inheritdoc
   */
  function setColumnNames() {
    $this->readAndSanitizeLine(); // Skip the title line
    $this->csv->column_names = array('source', 'old', 'new', 'language');
  }

  /**
   * Get the module corresponding to the prefix
   *
   * @param string $prefix Prefix used to search corresponding module
   * @param string $value  $value is parsed when $prefix is not a module
   *
   * @return mixed
   */
  static function getCorrespondingModule($prefix, $value) {
    if ($prefix == 'config' || $prefix == 'mod' || $prefix == 'module') {
      $split_arg   = substr($value, 0, 1);
      $value_split = explode($split_arg, $value);
      $prefix      = $value_split[1];
    }

    $cache = new Cache('CCSVImportTranslations.getCorrespondingModule', $prefix, Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    $module = null;
    if ($prefix == '__common__') {
      $module = 'common';
    }
    // Check if the prefix is the module name
    elseif (CModule::exists($prefix)) {
      $module = $prefix;
    }
    // Try to add dP to get the module name
    elseif (CModule::exists("dP$prefix")) {
      $module = "dP$prefix";
    }
    // Try with the class
    elseif (CModelObject::classExists($prefix)) {
      // Handle non instanciable objects
      try {
        $class_map = CClassMap::getInstance();
        $class_prefix = $class_map->getClassMap($class_map->getAliasByShortName($prefix));
        $module = ($class_prefix->module) ?: 'undefined';
      }
      catch (Throwable $e) {
        $module = 'undefined';
      }
    }
    else {
      $module = 'undefined';
    }

    return $cache->put($module);
  }

  /**
   * @return array
   */
  function getTranslations() {
    return $this->translations;
  }

  /**
   * Get nb hits for each module
   *
   * @param string $type Type of hits (new|old|same)
   *
   * @return array
   */
  function getNbHitsPerModule($type) {
    $count = array();

    foreach ($this->translations as $_module => $_types) {
      if (!array_key_exists($_module, $count)) {
        $count[$_module] = count($_types[$type]);
      }
      else {
        $count[$_module] += count($_types[$type]);
      }
    }

    return $count;
  }

  /**
   * Get the total nb hits for the import file
   *
   * @return array
   */
  function getNbHitsTotal() {
    $count_new = $this->getNbHitsPerModule('new');
    $count_old = $this->getNbHitsPerModule('old');
    $count_same = $this->getNbHitsPerModule('same');

    return array(
      'new' => array(
        'total' => array_sum($count_new),
        'modules' => $count_new,
      ),
      'old' => array(
        'total' => array_sum($count_old),
        'modules' => $count_old,
      ),
      'same' => array(
        'total' => array_sum($count_same),
        'modules' => $count_same,
      ),
    );
  }
}
