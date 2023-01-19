<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cim10\Atih\CCIM10CategoryATIH;
use Ox\Mediboard\Cim10\Atih\CCodeCIM10ATIH;
use Ox\Mediboard\Cim10\Gm\CCategoryCIM10GM;
use Ox\Mediboard\Cim10\Gm\CCodeCIM10GM;
use Ox\Mediboard\Cim10\Oms\CCodeCIM10OMS;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Classe pour gérer le mapping avec la base de données CIM
 */
class CCodeCIM10 implements IShortNameAutoloadable
{
    const LANG_FR = "FR_OMS";
    const LANG_EN = "EN_OMS";
    const LANG_DE = "GE_DIMDI";

    const GET_USED_CODES_FOR_CACHE = 'CCodeCIM10.getUsedCodesFor';

    static $cache_layers = Cache::INNER_OUTER;

    static $OID = "2.16.840.1.113883.6.3";

    // niveaux de chargement
    const LITE   = 1;
    const MEDIUM = 2;
    const FULL   = 3;

    public $exist;
    // Calculated field
    public $occurrences;

    // Distant fields
    /** @var bool Indicate if the code is a category */
    public $_is_category;
    public $_favoris_id;
    public $_ref_favori;

    // Langue
    public $_lang;

    // Other
    public $_isInfo;
    public $_no_refs;

    /** @var CMbObjectSpec */
    static $spec;

    /**
     * Get object spec
     *
     * @return CMbObjectSpec
     */
    static function getSpec()
    {
        if (self::$spec) {
            return self::$spec;
        }

        $spec      = new CMbObjectSpec();
        $spec->dsn = "cim10";
        $spec->init();

        return self::$spec = $spec;
    }

    /**
     * Return the data source
     *
     * @return CSQLDataSource
     */
    public static function getDS()
    {
        if (!self::$spec) {
            self::getSpec();
        }

        return self::$spec->ds;
    }

    /**
     * Retourne un object CodeCIM10 en fonction de la version en configuration
     *
     * @param string $code     Le code CIM
     * @param string $version  La base à utiliser (oms ou atih)
     * @param bool   $loadlite Chargement
     *
     * @return CCodeCIM10
     */
    function construct($code = "(A00-B99)", $version = null, $loadlite = false)
    {
        $code = strtoupper($code);

        $version = self::getVersion($version);

        switch ($version) {
            case 'atih':
                if (strpos($code, '(') !== false || strpos($code, '-') !== false) {
                    $code = null;
                }

                $object = new CCodeCIM10ATIH($code, $loadlite);
                break;
            case 'gm':
                if (strpos($code, '(') !== false || strpos($code, '-') !== false) {
                    $code = null;
                }

                $object = new CCodeCIM10GM($code, $loadlite);
                break;
            case 'oms':
            default:
                $object = new CCodeCIM10OMS($code, $loadlite);
        }

        return $object;
    }

    /**
     * Chargement des données Lite
     *
     * @param string $lang Langue
     *
     * @return bool
     */
    public function loadLite($lang = null)
    {
        return false;
    }

    /**
     * Chargement des données
     *
     * @param string $lang Langue
     *
     * @return bool
     */
    public function load($lang = null)
    {
        return false;
    }

    /**
     * Check if the code is a favori for the given user
     *
     * @param CMediusers $user The user
     *
     * @return bool
     */
    public function isFavori($user = null)
    {
        if (!CCodeCIM10::isCategory($this->code)) {
            if (!$user) {
                $user = CMediusers::get();
            }

            $favori = CFavoriCIM10::getFromCode($this->code, $user);
            $favori->loadRefsTagItems();
            $this->_favoris_id = $favori->_id;
            $this->_ref_favori = $favori;
        }

        return $this->_favoris_id != null;
    }

    /**
     * Chargement optimisé des codes
     *
     * @param string  $code    Code
     * @param integer $level   Niveau de chargement du code
     * @param string  $lang    Langue
     * @param string  $version La version de la base (oms ou atih)
     *
     * @return self
     */
    static function get($code, $level = self::LITE, $lang = null, $version = null)
    {
        if (!$lang) {
            $lang = self::getLangCIM();
        }

        $version = self::getVersion($version);

        switch ($version) {
            case 'atih':
                if (self::isCategory($code)) {
                    $object = CCIM10CategoryATIH::getByCode($code, $level);
                } else {
                    $object = CCodeCIM10ATIH::get($code, $level);
                }
                break;
            case 'gm':
                if (self::isCategory($code)) {
                    $object = CCategoryCIM10GM::getByCode($code, $level);
                } else {
                    $object = CCodeCIM10GM::get($code, $level);
                }
                break;
            case 'oms':
            default:
                $object = CCodeCIM10OMS::get($code, $level, $lang);
        }

        $object->_is_category = self::isCategory($code);

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
     * @param string     $field_type     Le type de champ (dp, dr, da, fppec, mmp, ae, das) pour déterminer si le code
     *                                   est autorisé
     * @param CMediusers $user_favorites Si renseigné, les favoris de l'utilisateur sont retournés en premiers
     *
     * @return CCodeCIM10[]
     */
    public static function findCodes(
        $code,
        $keys,
        $chapter = null,
        $category = null,
        $max_length = null,
        $where = null,
        $version = null,
        $sejour_type = null,
        $field_type = null,
        $user_favorites = null
    ) {
        if (!$version) {
            $version = self::getVersion($version);
        }

        switch ($version) {
            case 'atih':
                $results = CCodeCIM10ATIH::findCodes(
                    $code,
                    $keys,
                    $chapter,
                    $category,
                    $max_length,
                    $where,
                    null,
                    $sejour_type,
                    $field_type,
                    $user_favorites
                );
                break;
            case 'gm':
                $results = CCodeCIM10GM::findCodes(
                    $code,
                    $keys,
                    $chapter,
                    $category,
                    $max_length,
                    $where,
                    null,
                    $sejour_type,
                    $field_type,
                    $user_favorites
                );
                break;
            case 'oms':
            default:
                $results = CCodeCIM10OMS::findCodes(
                    $code,
                    $keys,
                    $chapter,
                    $category,
                    $max_length,
                    $where,
                    null,
                    $sejour_type,
                    $field_type,
                    $user_favorites
                );
                break;
        }

        $codes = [];
        foreach ($results as $result) {
            $code = CCodeCIM10::get($result['code'], self::FULL);

            if ($user_favorites) {
                $code->isFavori($user_favorites);
            }

            $codes[] = $code;
        }

        CMbArray::pluckSort($codes, SORT_DESC, '_favoris_id');

        return $codes;
    }

    /**
     * Return the main chapters of the CIM10
     *
     * @param int    $level   The loading level
     * @param string $lang    The language to use for the OMS version
     * @param string $version The CIM10 version to use
     *
     * @return array[]
     */
    public static function getChapters($level = self::LITE, $lang = null, $version = null)
    {
        $version = self::getVersion($version);

        switch ($version) {
            case 'atih':
                $chapters = CCIM10CategoryATIH::getChapters($level);
                break;
            case 'gm':
                $chapters = CCategoryCIM10GM::getChapters($level);
                break;
            case 'oms':
            default:
                /** @var CCodeCIM10OMS $code */
                $chapters = CCodeCIM10OMS::getChapters($lang, $level);
        }

        return $chapters;
    }

    /**
     *
     *
     * @param CMediusers $user        The user
     * @param string     $code        Recherche du code
     * @param string     $keywords    Recherche textuelle (libellé)
     * @param string     $chapter     Recherche par chapitre
     * @param string     $category    Recherche par categorie
     * @param string     $sejour_type Le type de séjour (mco, ssr ou psy) pour déterminer si le code est autorisé
     * @param string     $field_type  Le type de champ (dp, dr, da, fppec, mmp, ae, das) pour déterminer si le code est
     *                                autorisé
     *
     * @return array
     */
    public static function getUsedCodesFor(
        $user,
        $code = null,
        $keywords = null,
        $chapter = null,
        $category = null,
        $sejour_type = null,
        $field_type = null
    ) {
        $cache = new Cache(self::GET_USED_CODES_FOR_CACHE, $user->_guid, Cache::INNER_OUTER);

        $used_codes = [];
        if ($cache->exists()) {
            $used_codes = $cache->get();
        } else {
            $ds = CSQLDataSource::get('std');

            $query = new CRequest();
            $query->addColumn('DP', 'code');
            $query->addColumn('count(DP)', 'occurrences');
            $query->addTable('sejour');
            $query->addWhereClause('praticien_id', " = '{$user->_id}'");
            $query->addWhereClause('DP', ' IS NOT NULL');
            $query->addWhereClause('DP', " != ''");
            $query->addWhereClause('DP', CSQLDataSource::prepareNotIn(CFavoriCIM10::getListFavoris($user)));
            $query->addGroup('DP');
            $query->addOrder('occurrences DESC');

            $results = $ds->loadList($query->makeSelect());

            $used_codes = [];
            if ($results) {
                foreach ($results as $result) {
                    $used_codes[$result['code']] = $result['occurrences'];
                }

                $cache->put($used_codes);
            }
        }

        $codes = [];
        if (count($used_codes)) {
            $where = CCodeCIM10::getCodeField() . " " . CSQLDataSource::prepareIn(array_keys($used_codes));

            $codes = CCodeCIM10::findCodes(
                $code,
                $keywords,
                $chapter,
                $category,
                null,
                $where,
                null,
                $sejour_type,
                $field_type
            );

            foreach ($codes as $code) {
                if (array_key_exists($code->code, $used_codes)) {
                    $code->_favoris_id = 0;
                    $code->occurrences = $used_codes[$code->code];
                }
            }

            CMbArray::pluckSort($codes, SORT_DESC, 'occurrences');
            $codes = array_slice($codes, 0, 10, true);
        }

        return $codes;
    }

    /**
     * Reset the cache of the used xodes for the given user
     *
     * @param CMediusers $user The user
     *
     * @return void
     */
    public static function resetUsedCodesCacheFor($user)
    {
        $cache = new Cache(self::GET_USED_CODES_FOR_CACHE, $user->_guid, Cache::INNER_OUTER);

        if ($cache->exists()) {
            $cache->rem();
        }
    }

    /**
     * Return the version of the cim10
     *
     * @param string $version The version of the CIM10 to get (oms or atih)
     *
     * @return string
     */
    public static function getVersion($version = null)
    {
        if (!$version) {
            $version = CAppUI::conf('cim10 cim10_version');
        }

        return $version;
    }

    /**
     * Get the sub codes for the given code or category
     *
     * @param string $code    The code
     * @param string $lang    The language for the OMS version
     * @param string $version The version of the CIM10 (oms or atih)
     *
     * @return array
     */
    public static function getSubCodes($code, $lang = null, $version = null)
    {
        $version = self::getVersion($version);

        switch ($version) {
            case 'atih':
                $codes = CCodeCIM10ATIH::getSubCodes($code);
                break;
            case 'gm':
                $codes = CCodeCIM10GM::getSubCodes($code);
                break;
            case 'oms':
            default:
                $codes = CCodeCIM10OMS::getSubCodes($code, $lang);
        }

        return $codes;
    }

    /**
     * Ajoute le point au code CIM (après le 3 caractère)
     *
     * @param string $code Le code CIM
     * @param bool   $atih Inclure les ajouts de l'ATIH (marqués par un +), ou non (pour la BCB notamment)
     *
     * @return string
     */
    public static function addPoint($code, $atih = true)
    {
        if (!$atih && strpos($code, '+') !== false) {
            $code = substr($code, 0, strpos($code, '+'));
        }

        if (!strpos($code, ".") && strlen($code) >= 4) {
            $code = substr($code, 0, 3) . "." . substr($code, 3);
        }

        return $code;
    }

    /**
     * Récupération de la langue a utiliser pour la cim10
     *
     * @return string
     */
    public static function getLangCIM()
    {
        switch (CAppUI::pref("LOCALE")) {
            case 'de':
                $lang = CCodeCIM10::LANG_DE;
                break;
            case "en":
                $lang = CCodeCIM10::LANG_EN;
                break;
            case "fr":
            case "fr-be":
            case "nl_be":
            default:
                $lang = CCodeCIM10::LANG_FR;
        }

        return $lang;
    }

    /**
     * Return the name of the database field containing the CIM10 codes
     *
     * @param string $version The CIM10 version
     *
     * @return string
     */
    public static function getCodeField($version = null)
    {
        $version = self::getVersion($version);

        switch ($version) {
            case 'atih':
                $field = CCodeCIM10ATIH::getCodeField();
                break;
            case 'gm':
                $field = CCodeCIM10GM::getCodeField();
                break;
            case 'oms':
            default:
                $field = CCodeCIM10OMS::getCodeField();
        }

        return $field;
    }

    /**
     * Return the name of the database field containing the CIM10 code's id
     *
     * @param string $version The CIM10 version
     *
     * @return string
     */
    public static function getIdField($version = null)
    {
        $version = self::getVersion($version);

        switch ($version) {
            case 'atih':
                $field = CCodeCIM10ATIH::getIdField();
                break;
            case 'gm':
                $field = CCodeCIM10GM::getIdField();
                break;
            case 'oms':
            default:
                $field = CCodeCIM10OMS::getIdField();
        }

        return $field;
    }

    /**
     * Check if the given code is a category (or a chapter)
     *
     * @param string $code The code to check
     *
     * @return bool
     */
    public static function isCategory($code)
    {
        $test = preg_match('/[a-zA-Z]{1,5}[^0-9]/', $code) || strlen($code) < 3;

        return strpos($code, '(') !== false || strpos($code, '-') !== false || preg_match(
            '/[a-zA-Z]{1,5}[^0-9]/',
            $code
        ) || (strlen($code) < 3 && $code != '');
    }

    /**
     * Return a json of the CIM10Atih
     *
     * @return array
     */
    public static function toArray()
    {
        $ds = self::getDS();

        $tab = [];
        if ($ds->countRows('SHOW TABLES LIKE \'codes_atih\';')) {
            $tab = CCodeCIM10ATIH::toArray();
        }

        return $tab;
    }


    /**
     * Return the current version and the next available version of the CIM10 database
     *
     * @return array (string current version, string next version)
     */
    public static function getDatabaseVersions()
    {
        $version = self::getVersion();
        switch ($version) {
            case 'atih':
                return CCodeCIM10ATIH::getDatabaseVersions();
                break;
            case 'gm':
                return CCodeCIM10GM::getDatabaseVersions();
                break;
            case 'oms':
            default:
                return CCodeCIM10OMS::getDatabaseVersions();
        }
    }
}
