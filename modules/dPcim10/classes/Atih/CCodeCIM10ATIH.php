<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Atih;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Gère le mapping avec la base de donnée CIM10 version ATIH
 */
class CCodeCIM10ATIH extends CCodeCIM10
{
  /** @var int The id of the code */
  public $id;

  /** @var string The code CIM10 */
  public $code;

  /** @var string The code CIM10 */
  public $code_long;

  /** @var string Le type MCO détaille l'usage du code pour le PMSI
   *   0: Pas de restrictions, 1: Interdit en DP et en DR,
   *   2: Interdit en DP et en DR, cause externe de morbidité,
   *   3: Interdit en DP, DR et en DA, 4: Interdit en DP */
  public $type_mco;

  /* Profil SSR */

  /** @var bool Autorisé ou non en finalité principale de prise en charge */
  public $ssr_fppec;

  /** @var bool Autorisé ou non en manifestation morbide principale */
  public $ssr_mmp;

  /** @var bool Autorisé ou non en affection étiologique */
  public $ssr_ae;

  /** @var bool Autorisé ou non en DAS */
  public $ssr_das;

  /** @var string Détaille l'utilisation du code pour le codage des séjour psychiatriques
   *   0: Pas de restriction
   * 1: Interdit en DP
   * 3: Interdit en DP et en DA */
  public $type_psy;

  /** @var string Libelle court (abgrégé, en majuscule et sans accents) */
  public $libelle_court;

  /** @var string Le libellé complet */
  public $libelle;

  /** @var int L'id de la catégorie parente */
  public $category_id;

  /** @var CCIM10CategoryATIH The parent category */
  public $_category;

  /** @var string Le code principal (3 caractères) */
  public $_parent_code;

  /** @var CCodeCIM10ATIH The parent code */
  public $_parent;

  /** @var CCodeCIM10ATIH[] The direct descendants of the code */
  public $_descendants;

  /** @var int The load level of the object */
  public $_load_level;

  /**
   * Constructeur à partir du code CIM
   *
   * @param string $code  Le code CIM
   * @param int    $level Le niveau de chargement
   */
  public function __construct($code = "A00", $level = self::LITE) {
    $this->code = strtoupper($code);

    if ($level == self::LITE) {
      $this->loadLite();
    }
    else {
      $this->load();
    }

    $this->_load_level = $level;
  }

  /**
   * Chargement minimal
   *
   * @param string $lang La langue utilisée (inutile pour la CIM ATIH)
   *
   * @return bool
   */
  public function loadLite($lang = null) {
    $ds = self::getDS();

    if (!self::codeExists($this->code)) {
      $this->libelle_court = CAppUI::tr('CCodeCIM10.no_exist');
      $this->libelle       = CAppUI::tr('CCodeCIM10.no_exist');
      $this->exist         = false;

      return false;
    }

    $query  = "SELECT *
              FROM codes_atih
              WHERE code = ?1;";
    $result = $ds->exec($ds->prepare($query, $this->code));
    $data   = $ds->fetchAssoc($result);

    $this->map($data);
    $this->exist = true;

    return true;
  }

  /**
   * Charge les données complète
   *
   * @param string $lang La langue utilisée (inutile pour la CIM ATIH)
   *
   * @return bool
   */
  public function load($lang = null) {
    if (!$this->loadLite()) {
      return false;
    }

    $this->loadCategory();
    $this->loadAncestor();
    $this->loadDescendants();

    return true;
  }

  /**
   * Charge la catégorie du code
   *
   * @return void
   */
  public function loadCategory() {
    $this->_category = CCIM10CategoryATIH::get($this->category_id);
  }

  /**
   * Charge l'ancêtre direct du code
   *
   * @return void
   */
  public function loadAncestor() {
    if (strlen($this->code) > 3) {
      $this->_parent_code = substr(str_replace(array('+', '.'), '', $this->code), 0, -1);
      $this->_parent = self::get($this->_parent_code, self::LITE);

      /* Handle the few cases where there are no direct ancestors codes (Z04801 to Z048) */
      if (!$this->_parent || !$this->_parent->exist) {
        $this->_parent_code = substr($this->_parent_code, 0, -1);
        $this->_parent = self::get($this->_parent_code, self::LITE);
      }

      if ($this->_parent && $this->_parent->exist) {
        $this->_parent->_category = $this->_category;
        $this->_parent->loadAncestor();
      }
    }
  }

  /**
   * Load the directs descendants of the code
   *
   * @return void
   */
  public function loadDescendants() {
    $this->_descendants = array();
    $ds                 = self::getDS();

    $query  = "SELECT code FROM codes_atih
              WHERE code LIKE '{$this->code}%' AND 
              CHAR_LENGTH(REPLACE(code, '+', '')) = CHAR_LENGTH('{$this->code}') + 1;";
    $results = $ds->loadList($query);

    /* Handle the few cases where there are no direct descendants codes (Z04801 to Z048) */
    if (!$results || !count($results)) {
      $query  = "SELECT code FROM codes_atih
              WHERE code LIKE '{$this->code}%' AND 
              CHAR_LENGTH(REPLACE(code, '+', '')) = CHAR_LENGTH('{$this->code}') + 2;";
      $results = $ds->loadList($query);
    }

    if ($results && count($results)) {
      foreach ($results as $result) {
        $_descendant            = self::get($result['code'], self::LITE);
        $_descendant->_category = $this->_category;
        $_descendant->_parent   = $this;
        $_descendant->loadDescendants();

        $this->_descendants[] = $_descendant;
      }
    }
  }

  /**
   * Map the data from the Datasource to the object
   *
   * @param array $data The data from the database
   *
   * @return void
   */
  protected function map($data) {
    foreach ($data as $field => $value) {
      if (property_exists($this, $field)) {
        $this->$field = $value;
      }
    }

    $this->code_long = $this->code;
  }

  /**
   * Charge le Code depuis le cache ou la base de données
   *
   * @param string $code    Le code CIM10
   * @param int    $level   Le niveau de chargement
   * @param string $lang    Langue
   * @param string $version La version de la base (oms ou atih)
   *
   * @return CCodeCIM10ATIH
   */
  public static function get($code, $level = self::LITE, $lang = null, $version = null) {
    $cache = new Cache('CCodeCIM10ATIH.get', array($code), self::$cache_layers);

    if ($cache->exists()) {
      $object = $cache->get();
    }
    else {
      $object = new self($code, $level);
      $cache->put($object, true);
    }

    /* If the object was loaded with the lite level, and we ask a greater load level, we lod it full and update the cache */
    if ($level !== self::LITE && ($object->_load_level === self::LITE || is_null($object->_load_level))) {
      $object->loadCategory();
      $object->loadAncestor();
      $object->loadDescendants();
      $object->_load_level = $level;
      $cache->put($object, true);
    }

    return $object;
  }

  /**
   * Recherche de codes CIM10
   *
   * @param string     $code           Recherche du code
   * @param string     $keys           Recherche textuelle (libellé)
   * @param string     $chapter        Recherche par chapitre
   * @param string     $category       Recherche par categorie
   * @param int        $max_length     La taille maximum du code
   * @param string     $where          Clause where
   * @param string     $version        La version de la base (oms ou atih)
   * @param string     $sejour_type    Le type de séjour (mco, ssr ou psy) pour déterminer si le code est autorisé
   * @param string     $field_type     Le type de champ (dp, dr, da, fppec, mmp, ae, das) pour déterminer si le code est autorisé
   * @param CMediusers $user_favorites Si renseigné, les favoris de l'utilisateur sont retournés en premiers
   *
   * @return array
   */
  public static function findCodes(
      $code, $keys, $chapter = null, $category = null, $max_length = null,
      $where = null, $version = null, $sejour_type = null, $field_type = null, $user_favorites = null
  ) {
    $ds = self::getDS();

    $keywords = explode(' ', $keys);
    $codes    = explode(' ', $code);

    $where_keys = array();
    if ($keys && $keys != '') {
      foreach ($keywords as $keyword) {
        $where_keys[] = "codes_atih.libelle LIKE '%" . addslashes($keyword) . "%'";
      }
    }

    $where_codes = array();
    if ($code && $code != '') {
      foreach ($codes as $code) {
        $where_codes[] = "codes_atih.code LIKE '" . addslashes($code) . "%'";
      }
    }

    $fields = ['codes_atih.code'];
    $tables = ['codes_atih'];
    $ljoin = [];
    $order = [];

    $where_clauses = array();
    if ($where) {
      $where_clauses[] = "($where)";
    }

    if (count($where_codes) && count($where_keys)) {
      $where_clauses[] = ' ((' . implode(' AND ', $where_keys) . ') OR (' . implode(' OR ', $where_codes) . '))';
    }
    elseif (count($where_keys)) {
      $where_clauses[] = ' (' . implode(' AND ', $where_keys) . ')';
    }
    elseif (count($where_codes)) {
      $where_clauses[] = ' (' . implode(' AND ', $where_codes) . ')';
    }

    if ($max_length) {
      $where_clauses[] = "CHAR_LENGTH(codes_atih.code) < $max_length";
    }

    if ($chapter) {
      $ljoin[] = 'LEFT JOIN `chapters_atih` ON chapters_atih.id = codes_atih.category_id';
      $where_clauses[] = "chapters_atih.parent_id = '{$chapter}'";
    }

    if ($category) {
      $where_clauses[] = "codes_atih.category_id = '{$category}'";
    }

    if ($sejour_type && $field_type) {
      $where_clauses[] = self::getAuthorizedCodesWhereClause($sejour_type, $field_type);
    }

    if ($user_favorites) {
      $favori = new CFavoriCIM10();
      $favori->favoris_user = $user_favorites->_id;
      $favorites = CMbArray::pluck($favori->loadMatchingList(), 'favoris_code');

      $where_clauses[] = "codes_atih.code " . CSQLDataSource::prepareIn($favorites);
    }

    $query = 'SELECT ' . implode(', ', $fields) . ' FROM ' . implode(', ', $tables) . ' ';
    if (count($ljoin)) {
      $query .= implode(' ', $ljoin) . ' ';
    }
    if (count($where_clauses)) {
      $query .= 'WHERE ' . implode(' AND ', $where_clauses) . ' ';
    }

    $order[] = 'codes_atih.id';

    $query .= 'ORDER BY ' . implode(', ', $order) . ' LIMIT 0, 100';

    $results = $ds->loadList($query);

    if ($user_favorites) {
      if ($where) {
        $where = "($where) AND codes_atih.code " . CSQLDataSource::prepareNotIn($favorites);
      }
      else {
        $where = 'codes_atih.code ' . CSQLDataSource::prepareNotIn($favorites);
      }

      $results = array_merge(
        $results, self::findCodes($code, $keys, $chapter, $category, $max_length, $where, $version, $sejour_type, $field_type)
      );
    }

    return $results;
  }

  /**
   * Get the sub codes for the given code or category
   *
   * @param string $code    The code
   * @param string $lang    Langue
   * @param string $version La version de la base (oms ou atih)
   *
   * @return array
   */
  public static function getSubCodes($code, $lang = null, $version = null) {
    if (self::isCategory($code)) {
      $category = CCIM10CategoryATIH::getByCode($code, CCodeCIM10::FULL);
      if ($category->chapter) {
        $codes = $category->_categories;
      }
      else {
        $codes = $category->_children;
      }
    }
    else {
      $code  = CCodeCIM10ATIH::get($code, CCodeCIM10::FULL);
      $codes = $code->_descendants;
    }

    $results = array();
    foreach ($codes as $code) {
      $results[] = array('code' => $code->code, 'text' => $code->libelle);
    }

    return $results;
  }

  /**
   * Check if the code exists
   *
   * @param string $code The code
   *
   * @return bool
   */
  public static function codeExists($code) {
    $ds = self::getDS();

    $query  = "SELECT COUNT(id) AS total
              FROM codes_atih
              WHERE code = ?1;";
    $result = $ds->exec($ds->prepare($query, $code));
    $count  = $ds->fetchAssoc($result);

    return $count['total'] >= 1;
  }

  /**
   * Return the name of the database field containing the CIM10 codes
   *
   * @param string $version The CIM10 version
   *
   * @return string
   */
  public static function getCodeField($version = null) {
    return 'codes_atih.code';
  }

  /**
   * Return the name of the database field containing the CIM10 code's id
   *
   * @param string $version The CIM10 version
   *
   * @return string
   */
  public static function getIdField($version = null) {
    return 'id';
  }

  /**
   * Returns the list of authorized CIM10 codes for the given field and the given type of sejour
   *
   * @param string $sejour_type Le type de séjour (mco, ssr ou psy) pour déterminer si le code est autorisé
   * @param string $field_type  Le type de champ (dp, dr, da, fppec, mmp, ae, das) pour déterminer si le code est autorisé
   *
   * @return array
   */
  public static function getAuthorizedCodes($sejour_type, $field_type) {
    $cache = new Cache('CCodeCIM10ATIH.getAuthorizedCodes', array($sejour_type, $field_type), Cache::INNER_OUTER);

    if ($cache->exists()) {
      return $cache->get();
    }

    $ds    = self::getDS();
    $codes = array();

    $query = new CRequest();
    $query->addTable('codes_atih');
    $query->addColumn('code');
    $query->addWhere(self::getAuthorizedCodesWhereClause($sejour_type, $field_type));

    $results = $ds->loadList($query->makeSelect());
    if ($results) {
      $codes = CMbArray::pluck($results, 'code');;
    }

    $cache->put($codes);
    return $codes;
  }

  /**
   * Returns the where clause for getting the authorized CIM10 codes for the given field and the given type of sejour
   *
   * @param string $sejour_type Le type de séjour (mco, ssr ou psy) pour déterminer si le code est autorisé
   * @param string $field_type  Le type de champ (dp, dr, da, fppec, mmp, ae, das) pour déterminer si le code est autorisé
   *
   * @return string
   */
  protected static function getAuthorizedCodesWhereClause($sejour_type, $field_type) {
    switch (strtolower($sejour_type)) {
      case 'psy':
        switch (strtolower($field_type)) {
          case 'da':
            $clause = "type_psy IN('0', '1')";
            break;
          case 'dp':
          default:
            $clause = "type_psy = '0'";
        }
        break;
      case 'ssr':
        switch (strtolower($field_type)) {
          /* Manifestation morbide principale */
          case 'mmp':
            $clause = "ssr_mmp = '1'";
            break;
          /* Diagnostic associé significatifs */
          case 'das':
            $clause = "ssr_das = '1'";
            break;
          /* Affection étiologique */
          case 'ae':
            $clause = "ssr_ae = '1'";
            break;
          /* Finalité principale de prise en charge */
          case 'fppec':
          default:
            $clause = "ssr_fppec = '1'";
        }
        break;
      case 'mco':
      default:
        switch (strtolower($field_type)) {
          case 'da':
            $clause = "type_mco IN ('0', '1', '2', '4')";
            break;
          case 'dr':
            $clause = "type_mco IN ('0', '4')";
            break;
          case 'dp':
          default:
            $clause = "type_mco = '0'";
        }
    }

    return $clause;
  }

  /**
   * Returns the list of forbidden CIM10 codes for the given field and the given type of sejour
   *
   * @param string $sejour_type Le type de séjour (mco, ssr ou psy) pour déterminer si le code est autorisé
   * @param string $field_type  Le type de champ (dp, dr, da, fppec, mmp, ae, das) pour déterminer si le code est autorisé
   *
   * @return array
   */
  public static function getForbiddenCodes($sejour_type, $field_type) {
    $cache = new Cache('CCodeCIM10ATIH.getForbiddenCodes', array($sejour_type, $field_type), Cache::INNER_OUTER);

    if ($cache->exists()) {
      return $cache->get();
    }

    $ds    = self::getDS();
    $codes = array();

    $query = new CRequest();
    $query->addTable('codes_atih');
    $query->addColumn('code');

    switch (strtolower($sejour_type)) {
      case 'psy':
        switch (strtolower($field_type)) {
          case 'da':
            $query->addWhereClause('type_psy', " = '3'");
            break;
          case 'dp':
          default:
            $query->addWhereClause('type_psy', " <> '0'");
        }
        break;
      case 'ssr':
        switch (strtolower($field_type)) {
          /* Manifestation morbide principale */
          case 'mmp':
            $query->addWhereClause('ssr_mmp', " = '0'");
            break;
          /* Diagnostic associé significatifs */
          case 'das':
            $query->addWhereClause('ssr_das', " = '0'");
            break;
          /* Affection étiologique */
          case 'ae':
            $query->addWhereClause('ssr_ae', " = '0'");
            break;
          /* Finalité principale de prise en charge */
          case 'fppec':
          default:
            $query->addWhereClause('ssr_fppec', " = '0'");
        }
        break;
      case 'mco':
      default:
        switch (strtolower($field_type)) {
          case 'da':
            $query->addWhereClause('type_mco', " = '3'");
            break;
          case 'dr':
            $query->addWhereClause('type_mco', " IN ('1', '2', '3')");
            break;
          case 'dp':
          default:
            $query->addWhereClause('type_mco', " <> '0'");
        }
    }

    $results = $ds->loadList($query->makeSelect());
    if ($results) {
      $codes = CMbArray::pluck($results, 'code');;
    }

    $cache->put($codes);
    return $codes;
  }

  /**
   * Return a json of the CIM10Atih
   *
   * @return array
   */
  public static function toArray() {
    $ds = self::getDS();

    $data = array('chapters'=> array(), 'codes' => array());

    $query = new CRequest();
    $query->addTable('chapters_atih');
    $query->addColumn('*');
    $query->addOrder('id ASC');

    $result = $ds->exec($query->makeSelect());
    if ($result) {
      while ($row = $ds->fetchAssoc($result)) {
        $data['chapters'][] = $row;
      }
    }

    $query = new CRequest();
    $query->addTable('codes_atih');
    $query->addColumn('id');
    $query->addColumn('code');
    $query->addColumn('libelle');
    $query->addColumn('category_id', 'chapter_id');
    $query->addOrder('id ASC');

    $result = $ds->exec($query->makeSelect());
    if ($result) {
      while ($row = $ds->fetchAssoc($result)) {
        $data['codes'][] = $row;
      }
    }

    return $data;
  }

  /**
   * @inheritdoc
   */
  public static function getDatabaseVersions() {
    return [
      "CIM10 ATIH" => [
        [
          "table_name" => "codes_atih",
          "filters" => [],
        ]
      ],
      "CIM10 ATIH - 2018" => [
        [
          "table_name" => "codes_atih",
          "filters" => [
            "code" => "= 'A402'",
            "libelle" => "= '%entérocoques%'",
          ],
        ]
      ],
      "CIM10 ATIH - 2019" => [
        [
          "table_name" => "codes_atih",
          "filters" => [
            "code" => "= 'A925'"
          ],
        ]
      ],
      "CIM10 ATIH - 2020" => [
        [
          "table_name" => "codes_atih",
          "filters" => [
            "code" => "= 'U0715'",
            'libelle' => " LIKE '%COVID-19%'"
          ],
        ]
      ],
      "CIM10 ATIH - 2021" => [
        [
          "table_name" => "codes_atih",
          "filters" => [
            "code" => "= 'U119'",
            'libelle' => " LIKE '%COVID-19%'"
          ],
        ]
      ]
    ];
  }
}
