<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Core\FileUtil\CCSVFile;

/**
 * Description
 */
class CCSVImportHospiDiag extends CMbCSVObjectImport {
  const GET_ETABLISSEMENT_ID_CACHE = 'CCSVImportHospiDiag.getEtablissementID';

  protected $dir_path;
  protected $line;
  protected $year;
  protected $update;

  protected $regexp_extract = "/(.*)\s?:\s(\d+)/";

  static public $annees = array(
    '2016', '2015', '2014', '2013', '2012', '2011', '2010',
  );

  /**
   * @inheritdoc
   */
  function __construct($file_path, $dir_path, $year, $update) {
    parent::__construct($file_path);
    $this->dir_path = $dir_path;
    $this->year     = $year;
    $this->update = $update;
  }

  /**
   * @inheritdoc
   */
  function import() {
    $this->openFile();
    $this->setColumnNames();
    $this->current_line = 1;

    while ($this->line = $this->readAndSanitizeLine()) {
      $this->current_line++;

      $etablissement = $this->importEtablissement();
      if ($etablissement === null) {
        continue;
      }

      $this->importFinance($etablissement);
      $this->importObject('CHDQualite', $etablissement);
      $this->importObject('CHDResshum', $etablissement);

      // Get fields for process
      $convert_fields = CHDProcess::$fields;
      if ($this->year < 2012) {
        unset($convert_fields['P8new']);
        $convert_fields['P8'] = 'nb_examens_bio_par_technicien';
      }
      $fields = $this->getFields($convert_fields);
      $this->importObject('CHDProcess', $etablissement, $fields);

      $this->importActivite($etablissement);

      $this->importIdentite($etablissement);
    }

    $this->csv->close();

    $file_path = "$this->dir_path/pdmreg$this->year.csv";
    if (file_exists($file_path)) {
      $this->importActiviteReg($file_path);
    }
    else {
      CAppUI::setMsg("CFile-not-exists", UI_MSG_WARNING, $file_path);
    }

    $file_path = "$this->dir_path/pdmza$this->year.csv";
    if (file_exists($file_path)) {
      $this->importActiviteZone($file_path);
    }
    else {
      CAppUI::setMsg("CFile-not-exists", UI_MSG_WARNING, $file_path);
    }
  }

  /**
   * Import a CHDIdentite from CSV
   *
   * @param CHDEtablissement $etab Etablissement to attach the object to
   *
   * @return void
   */
  function importIdentite($etab) {
    $identite                      = new CHDIdentite();
    $identite->hd_etablissement_id = $etab->_id;
    $identite->annee               = $this->year;

    $identite->loadMatchingObjectEsc();

    if (!$this->update && $identite && $identite->_id) {
      CAppUI::setMsg('CHDIdentite-msg-found', UI_MSG_OK);

      return;
    }

    $all_fields = array_merge(
      CHDIdentite::$fields['volumetrie'],
      CHDIdentite::$fields['infrastructure'],
      CHDIdentite::$fields['informatisation'],
      CHDIdentite::$fields['rh']
    );
    $fields = $this->getFields($all_fields);
    $identite->bind($fields);

    $identite = $this->identiteParseFields($identite, CHDIdentite::$fields_group_activite);
    $identite = $this->identiteParseFields($identite, CHDIdentite::$fields_activite_realisees, false);
    $identite = $this->importIdentiteDoubleFields($identite);

    $new = $identite->_id ? 'modify' : 'create';
    if ($msg = $identite->store()) {
      CAppUI::setMsg("mod-openData-import-line-%d-error-%s", UI_MSG_WARNING, $this->current_line, $msg);

      return;
    }

    CAppUI::setMsg("CHDIdentite-msg-$new", UI_MSG_OK);
  }

  /**
   * @param CHDIdentite $identite CHDIdentite object to complete
   * @param array       $fields   Fields to add
   * @param bool        $libelle  Add a libelle field or not
   *
   * @return CHDIdentite
   */
  function identiteParseFields($identite, $fields, $libelle = true) {
    foreach ($fields as $_field_csv => $_field_class) {
      if ($this->line[$_field_csv]) {
        preg_match($this->regexp_extract, $this->line[$_field_csv], $matches);
        if ($libelle) {
          $field_libelle            = $_field_class . '_libelle';
          $identite->$field_libelle = $matches[1];
        }
        $identite->$_field_class = isset($matches[2]) ? $matches[2] : '';
      }
    }

    return $identite;
  }

  /**
   * @param CHDIdentite $identite CHDIdentite object to complete
   *
   * @return CHDIdentite
   */
  function importIdentiteDoubleFields($identite) {
    $identite->total_prod                          = $this->line['CI_F1_D'] ?: $this->line['CI_F1_O'];
    $identite->prod_taa                            = $this->line['CI_F2_D'] ?: $this->line['CI_F2_O'];
    $identite->prod_migac                          = $this->line['CI_F3_D'] ?: $this->line['CI_F3_O'];
    $identite->prod_merri                          = $this->line['CI_F4_D'] ?: $this->line['CI_F4_O'];
    $identite->prod_ac                             = $this->line['CI_F5_D'] ?: $this->line['CI_F5_O'];
    $identite->prod_recette_daf                    = $this->line['CI_F6_D'];
    $identite->prod_recette_mco                    = $this->line['CI_F6_O'];
    $identite->total_charges                       = $this->line['CI_F7_D'] ?: $this->line['CI_F7_O'];
    $identite->total_charges_mco                   = $this->line['CI_F8_D'];
    $identite->resultat_consolide                  = $this->line['CI_F9_D'];
    $identite->resultat_net                        = $this->line['CI_F9_O'];
    $identite->resultat_consolide_budget_principal = $this->line['CI_F10_D'];
    $identite->caf                                 = $this->line['CI_F11_D'] ?: $this->line['CI_F11_O'];
    $identite->total_bilan                         = $this->line['CI_F12_D'] ?: $this->line['CI_F12_O'];
    $identite->encours_dette                       = $this->line['CI_F13_D'] ?: $this->line['CI_F13_O'];
    $identite->fond_roulement_net_global           = $this->line['CI_F14_D'] ?: $this->line['CI_F14_O'];
    $identite->fond_roulement_besoin               = $this->line['CI_F15_D'] ?: $this->line['CI_F15_O'];
    $identite->tresorerie                          = $this->line['CI_F16_D'] ?: $this->line['CI_F16_O'];
    $identite->coeff_transition                    = $this->line['CI_F17_D'] ?: $this->line['CI_F17_O'];

    return $identite;
  }

  /**
   * Import a CHDActivite from CSV
   *
   * @param CHDEtablissement $etab Etablissement to attach the object to
   *
   * @return void
   */
  function importActivite($etab) {
    $activite                      = new CHDActivite();
    $activite->hd_etablissement_id = $etab->_id;
    $activite->annee               = $this->year;

    $activite->loadMatchingObjectEsc();

    if (!$this->update && $activite && $activite->_id) {
      CAppUI::setMsg('CHDActivite-msg-found', UI_MSG_OK);

      return;
    }

    $fields = $this->getFields(CHDActivite::$fields);
    $activite->bind($fields);

    $new = ($activite->_id) ? 'modify' : 'create';
    if ($msg = $activite->store()) {
      CAppUI::setMsg("mod-openData-import-line-%d-error-%s", UI_MSG_WARNING, $this->current_line, $msg);

      return;
    }

    CAppUI::setMsg("CHDActivite-msg-$new", UI_MSG_OK);
  }

  /**
   * Import more fields for CHDActivite
   *
   * @param string $file_path Path to the CSV file
   *
   * @return void
   */
  function importActiviteReg($file_path) {
    $fields = CHDActivite::$fields_reg;

    $this->fp  = fopen($file_path, 'r');
    $this->csv = new CCSVFile($this->fp, $this->profile);
    $this->setColumnNames();
    $this->current_line = 1;

    while ($this->line = $this->readAndSanitizeLine()) {
      $this->current_line++;
      if (!$this->line['finess']) {
        CAppUI::setMsg('CCSVImportHospiDiag-line-%d-field-%s-mandatory', UI_MSG_WARNING, $this->current_line, 'finess');
        continue;
      }

      $etab_id = $this->getEtablissementID($this->line['finess']);

      if (!$etab_id) {
        continue;
      }

      $activite                      = new CHDActivite();
      $activite->hd_etablissement_id = $etab_id;
      $activite->annee               = $this->year;

      $activite->loadMatchingObjectEsc();
      $new = ($activite->_id) ? 'modify' : 'create';

      $fields_clean = $this->getFields($fields);
      $activite->bind($fields_clean);

      if ($msg = $activite->store()) {
        CAppUI::setMsg("mod-openData-import-file-%s-line-%d-error-%s", UI_MSG_WARNING, $file_path, $this->current_line, $msg);
        continue;
      }

      CAppUI::setMsg("CHDActivite-msg-$new", UI_MSG_OK);
    }

    $this->csv->close();
  }

  /**
   * Import a CHDActiviteZone from CSV
   *
   * @param string $file_path Path to the CSV
   *
   * @return void
   */
  function importActiviteZone($file_path) {
    $fields = CHDActiviteZone::$fields;

    $this->fp  = fopen($file_path, 'r');
    $this->csv = new CCSVFile($this->fp, $this->profile);
    $this->setColumnNames();
    $this->current_line = 1;

    while ($this->line = $this->readAndSanitizeLine()) {
      $this->current_line++;
      if (!$this->line['finess']) {
        CAppUI::setMsg('CCSVImportHospiDiag-line-%d-field-%s-mandatory', UI_MSG_WARNING, $this->current_line, 'finess');
        continue;
      }

      $etab_id = $this->getEtablissementID($this->line['finess']);

      if (!$etab_id) {
        continue;
      }

      $activite_zone                      = new CHDActiviteZone();
      $activite_zone->hd_etablissement_id = $etab_id;
      $activite_zone->annee               = $this->year;
      $activite_zone->zone                = $this->line['zone'];

      $activite_zone->loadMatchingObjectEsc();

      if (!$this->update && $activite_zone && $activite_zone->_id) {
        CAppUI::setMsg("CHDActiviteZone-msg-found", UI_MSG_OK);
        continue;
      }

      $fields_clean = $this->getFields($fields);
      $activite_zone->bind($fields_clean);

      $new = ($activite_zone->_id) ? 'modify' : 'create';
      if ($msg = $activite_zone->store()) {
        CAppUI::setMsg('mod-openData-import-line-%d-error-%s', UI_MSG_WARNING, $this->current_line, $msg);
        continue;
      }

      CAppUI::setMsg("CHDActiviteZone-msg-$new", UI_MSG_OK);
    }

    $this->csv->close();
  }

  /**
   * Get a CHDEtablissement ID from it's finess
   *
   * @param int $finess CHDEtablissement's finess
   *
   * @return int
   */
  function getEtablissementID($finess) {
    $cache = new Cache(self::GET_ETABLISSEMENT_ID_CACHE, $finess, Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    $etab         = new CHDEtablissement();
    $etab->finess = $this->line['finess'];
    $etab->loadMatchingObjectEsc();

    if (!$etab || !$etab->_id) {
      CAppUI::setMsg('CHDActivite-line-%d-hd_etablissement_id-mandatory', UI_MSG_WARNING, $this->current_line);

      return $cache->put(null);
    }

    return $cache->put($etab->_id);
  }

  /**
   * Import a class
   *
   * @param string           $class  Name of the class to import (CHDQualite|CHDProcess|CHDResshum)
   * @param CHDEtablissement $etab   Etablissement to attach the object to
   * @param array            $fields Fields to use
   *
   * @return void
   */
  function importObject($class, $etab, $fields = array()) {
    /** @var CHDQualite|CHDProcess|CHDResshum $object */
    $object                      = new $class();
    $object->annee               = $this->year;
    $object->hd_etablissement_id = $etab->_id;

    $object->loadMatchingObjectEsc();

    if (!$this->update && $object && $object->_id) {
      CAppUI::setMsg("$class-msg-found", UI_MSG_OK);

      return;
    }

    if (!$fields) {
      $fields = $this->getFields($class::$fields);
    }

    $object->bind($fields);

    $new = ($object->_id) ? 'modify' : 'create';

    if ($msg = $object->store()) {
      CAppUI::setMsg("mod-openData-import-line-%d-error-%s", UI_MSG_WARNING, $this->current_line, $msg);

      return;
    }

    CAppUI::setMsg("$class-msg-$new", UI_MSG_OK);
  }

  /**
   * Import a CHDFinance from CSV
   *
   * @param CHDEtablissement $etab Etablissement to attach the objet to
   *
   * @return void
   */
  function importFinance($etab) {
    $finance                      = new CHDFinance();
    $finance->annee               = $this->year;
    $finance->hd_etablissement_id = $etab->_id;

    $finance->loadMatchingObjectEsc();

    if (!$this->update && $finance && $finance->_id) {
      CAppUI::setMsg('CHDFinance-msg-found', UI_MSG_OK);

      return;
    }

    $finance->marge_brute             = $this->line['F1_D'] ?: $this->line['F1_O'];
    $finance->caf                     = $this->line['F2_D'] ?: $this->line['F2_O'];
    $finance->caf_nette               = $this->line['F3_D'] ?: $this->line['F3_O'];
    $finance->duree_dette             = $this->line['F4_D'] ?: $this->line['F4_O'];
    $finance->inde_finance            = $this->line['F5_D'] ?: $this->line['F5_O'];
    $finance->intensite_invest        = $this->line['F6_D'] ?: $this->line['F6_O'];
    $finance->vetuste_equip           = $this->line['F7_D'] ?: $this->line['F7_O'];
    $finance->vetuste_bat             = $this->line['F8_D'] ?: $this->line['F8_O'];
    $finance->besoin_fonds_roulement  = $this->line['F9_D'] ?: $this->line['F9_O'];
    $finance->fond_roulement_net      = $this->line['F10_D'] ?: $this->line['F10_O'];
    $finance->creances_non_recouvrees = $this->line['F11_D'] ?: $this->line['F11_O'];
    $finance->dette_fournisseur       = $this->line['F12_D'] ?: $this->line['F12_O'];

    $new = ($finance->_id) ? 'modify' : 'create';
    if ($msg = $finance->store()) {
      CAppUI::setMsg("mod-openData-import-line-%d-error-%s", UI_MSG_WARNING, $this->current_line, $msg);
    }

    CAppUI::setMsg("CHDFinance-msg-$new", UI_MSG_OK);
  }

  /**
   * Import an CHDEtablissement from the CSV
   *
   * @return CHDEtablissement|null
   */
  function importEtablissement() {
    $fields = $this->getFields(CHDEtablissement::$fields);

    if (!$fields['finess']) {
      CAppUI::setMsg('CCSVImportHospiDiag-line-%d-field-%s-mandatory', UI_MSG_WARNING, $this->current_line, 'finess');

      return null;
    }

    if (!$fields['raison_sociale']) {
      CAppUI::setMsg('CCSVImportHospiDiag-line-%d-field-%s-mandatory', UI_MSG_WARNING, $this->current_line, 'raison sociale');

      return null;
    }

    $etablissement         = new CHDEtablissement();
    $etablissement->finess = $fields['finess'];
    $etablissement->loadMatchingObjectEsc();

    if (!$this->update && $etablissement && $etablissement->_id) {
      CAppUI::setMsg('CHDEtablissement-msg-found', UI_MSG_OK);

      return $etablissement;
    }

    $etablissement->bind($fields);

    if ($msg = $etablissement->store()) {
      CAppUI::setMsg("mod-openData-import-line-%d-error-%s", UI_MSG_WARNING, $this->current_line, $msg);

      return null;
    }

    CAppUI::setMsg('CHDEtablissement-msg-create', UI_MSG_OK);

    return $etablissement;
  }

  /**
   * Return the fields from the CSV
   *
   * @param array $fields Associative array fields from a CHDClass
   *
   * @return array
   */
  function getFields($fields) {
    $return = array();
    foreach ($fields as $_csv_key => $_class_key) {
      $return[$_class_key] = $this->line[$_csv_key];
    }

    return $return;
  }

  /**
   * @inheritdoc
   */
  function sanitizeLine($line) {
    if (!$line) {
      return array();
    }

    $line       = parent::sanitizeLine($line);
    $line_clean = array();
    foreach ($line as $_key => $_value) {
      $value = trim($_value);
      if ($value == '.z' || $value == '.v' || $value == '.c' || $value == '.m') {
        $line_clean[$_key] = '';
      }
      else {
        $line_clean[$_key] = $value;
      }
    }

    return $line_clean;
  }
}
