<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Oms;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Classe pour gérer le mapping avec la base de données CIM
 */
class CCodeCIM10OMS extends CCodeCIM10
{
    const LANG_FR = "FR_OMS";
    const LANG_EN = "EN_OMS";
    const LANG_DE = "GE_DIMDI";

    // Lite props
    public $code;
    public $code_long;
    public $sid;
    public $level;
    public $libelle;
    public $exist;

    // Others props
    public $descr;
    public $glossaire;
    public $include;
    public $indir;
    public $notes;

    // Références
    /** @var  CCodeCIM10OMS[] */
    public $_exclude;

    /** @var  CCodeCIM10OMS[] */
    public $_levelsSup;

    /** @var  CCodeCIM10OMS[] */
    public $_levelsInf;

    // Calculated field
    public $occurrences;

    // Distant fields
    public $_favoris_id;
    public $_ref_favori;

    // Langue
    public $_lang;

    // Other
    public $_isInfo;
    public $_no_refs;

    /** @var string Contain the roman number of the chapter */
    public $_chapter;

    /**
     * Constructeur à partir du code CIM
     *
     * @param string $code     Le code CIM
     * @param bool   $loadlite Chargement
     */
    function __construct($code = "(A00-B99)", $loadlite = false)
    {
        $this->code = strtoupper($code);

        if ($loadlite) {
            $this->loadLite();
        }
    }

    /**
     * Chargement des données Lite
     *
     * @param string $lang Langue
     *
     * @return bool
     */
    function loadLite($lang = null)
    {
        if (!$lang) {
            $lang = self::getLangCIM();
        }
        $this->exist = true;
        $ds          = self::getSpec()->ds;

        $this->_lang = $lang;

        // Vérification de l'existence du code
        $query  = "SELECT COUNT(abbrev) AS total
              FROM master
              WHERE abbrev = '$this->code'";
        $result = $ds->exec($query);
        $row    = $ds->fetchArray($result);
        if ($row["total"] == 0) {
            $this->libelle = CAppUI::tr("CCodeCIM10.no_exist");
            $this->exist   = false;

            return false;
        }
        // sid
        $query     = "SELECT SID
              FROM master
              WHERE abbrev = '$this->code'";
        $result    = $ds->exec($query);
        $row       = $ds->fetchArray($result);
        $this->sid = $row["SID"] ?? null;

        // code et level
        $query           = "SELECT abbrev, code, level
              FROM master
              WHERE SID = '$this->sid'";
        $result          = $ds->exec($query);
        $row             = $ds->fetchArray($result);
        $this->code      = $row["abbrev"];
        $this->code_long = $row['code'];
        $this->level     = $row["level"] ?? null;

        // libelle
        $query         = "SELECT $this->_lang
              FROM libelle
              WHERE SID = '$this->sid'
              AND source = 'S'";
        $result        = $ds->exec($query);
        $row           = $ds->fetchArray($result);
        $this->libelle = $row[$this->_lang] ?? null;

        if ($this->level == 1) {
            $query          = "SELECT rom
              FROM chapter
              WHERE SID = '$this->sid'";
            $result         = $ds->exec($query);
            $row            = $ds->fetchArray($result);
            $this->_chapter = $row['rom'] ?? null;
        }

        return true;
    }

    /**
     * Chargement des données
     *
     * @param string $lang Langue
     *
     * @return bool
     */
    function load($lang = null)
    {
        if (!$lang) {
            $lang = self::getLangCIM();
        }
        if (!$this->loadLite($lang)) {
            return false;
        }
        $ds = self::getSpec()->ds;
        //descr
        $this->descr = [];
        $query       = "SELECT LID
              FROM descr
              WHERE SID = '$this->sid'";
        $result      = $ds->exec($query);
        while ($row = $ds->fetchArray($result)) {
            $query   = "SELECT $this->_lang
                FROM libelle
                WHERE LID = '" . $row["LID"] . "'";
            $result2 = $ds->exec($query);
            if ($row2 = $ds->fetchArray($result2)) {
                $found = $row2[$this->_lang];
                if ($found && !in_array($found, $this->descr)) {
                    $this->descr[] = $found;
                }
            }
        }

        // glossaire
        $this->glossaire = [];
        $query           = "SELECT MID
              FROM glossaire
              WHERE SID = '$this->sid'";
        $result          = $ds->exec($query);
        while ($row = $ds->fetchArray($result)) {
            $query   = "SELECT $this->_lang
                FROM memo
                WHERE MID = '" . $row["MID"] . "'";
            $result2 = $ds->exec($query);
            if ($row2 = $ds->fetchArray($result2)) {
                $found = $row2[$this->_lang];
                if ($found && !in_array($found, $this->glossaire)) {
                    $this->glossaire[] = $found;
                }
            }
        }

        //include
        $this->include = [];
        $query         = "SELECT LID
              FROM include
              WHERE SID = '$this->sid'";
        $result        = $ds->exec($query);
        while ($row = $ds->fetchArray($result)) {
            $query   = "SELECT $this->_lang
                FROM libelle
                WHERE LID = '" . $row["LID"] . "'";
            $result2 = $ds->exec($query);
            if ($row2 = $ds->fetchArray($result2)) {
                $found = $row2[$this->_lang];
                if ($found && !in_array($found, $this->include)) {
                    $this->include[] = $found;
                }
            }
        }

        //indir
        $this->indir = [];
        $query       = "SELECT LID
              FROM indir
              WHERE SID = '$this->sid'";
        $result      = $ds->exec($query);
        while ($row = $ds->fetchArray($result)) {
            $query   = "SELECT $this->_lang
                FROM libelle
                WHERE LID = '" . $row["LID"] . "'";
            $result2 = $ds->exec($query);
            if ($row2 = $ds->fetchArray($result2)) {
                $found = $row2[$this->_lang];
                if ($found && !in_array($found, $this->indir)) {
                    $this->indir[] = $found;
                }
            }
        }

        //notes
        $this->notes = [];
        $query       = "SELECT MID
              FROM note
              WHERE SID = '$this->sid'";
        $result      = $ds->exec($query);
        while ($row = $ds->fetchArray($result)) {
            $query   = "SELECT $this->_lang
                FROM memo
                WHERE MID = '" . $row["MID"] . "'";
            $result2 = $ds->exec($query);
            if ($row2 = $ds->fetchArray($result2)) {
                $found = $row2[$this->_lang];
                if ($found && !in_array($found, $this->notes)) {
                    $this->notes[] = $found;
                }
            }
        }

        // Is info ?
        $this->_isInfo = ($this->descr || $this->glossaire || $this->include || $this->indir || $this->notes);

        return true;
    }

    static $cache_layers = Cache::INNER_OUTER;

    /**
     * Chargement optimisé des codes
     *
     * @param string $code    Code
     * @param int    $niv     Niveau de chargement du code
     * @param string $lang    Langue
     * @param null   $version Version code (unused)
     *
     * @return CCodeCIM10OMS
     */
    static function get($code, $niv = self::LITE, $lang = null, $version = null)
    {
        if (!$lang) {
            $lang = self::getLangCIM();
        }
        $cache = new Cache('CCodeCIM10OMS.get', [$code, $niv, $lang], self::$cache_layers);
        if ($cache->exists()) {
            return $cache->get();
        }

        // Chargement
        $code_cim = new self($code, $niv === self::LITE);
        switch ($niv) {
            case self::FULL:
                $code_cim->load();
                $code_cim->loadRefs();
                break;
            case self::MEDIUM:
                $code_cim->load();
                break;
            case self::LITE:
            default:
                $code_cim->loadLite();
        }

        return $cache->put($code_cim, true);
    }

    /**
     * Load the references
     *
     * @return bool
     */
    function loadRefs()
    {
        if (!$this->loadLite($this->_lang)) {
            return false;
        }

        // Exclusions
        $this->loadExcludes();

        // Arborescence
        $this->loadArborescence();

        return true;
    }

    /**
     * Load the exlcusions
     *
     * @return void
     */
    function loadExcludes()
    {
        $ds             = self::getSpec()->ds;
        $this->_exclude = [];
        $query          = "SELECT LID, excl
              FROM exclude
              WHERE SID = '$this->sid'";
        $result         = $ds->exec($query);
        while ($row = $ds->fetchArray($result)) {
            $query   = "SELECT abbrev
                FROM master
                WHERE SID = '" . $row["excl"] . "'";
            $result2 = $ds->exec($query);
            if ($row2 = $ds->fetchArray($result2)) {
                $code_cim10 = $row2["abbrev"];
                if (array_key_exists($code_cim10, $this->_exclude) === false) {
                    $code                        = self::get($code_cim10);
                    $this->_exclude[$code_cim10] = $code;
                }
            }
        }
        ksort($this->_exclude);
    }

    /**
     * Load the ascendants and descendants codes
     *
     * @return void
     */
    function loadArborescence()
    {
        $ds     = self::getSpec()->ds;
        $query  = "SELECT *
              FROM master
              WHERE SID = '$this->sid'";
        $result = $ds->exec($query);
        $row    = $ds->fetchArray($result);

        // Niveaux superieurs
        $this->_levelsSup = [];
        for ($index = 1; $index <= 7; $index++) {
            $code_cim10_id = $row["id$index"];
            if ($code_cim10_id) {
                $query                    = "SELECT abbrev
                  FROM master
                  WHERE SID = '$code_cim10_id'";
                $result                   = $ds->exec($query);
                $row2                     = $ds->fetchArray($result);
                $code_cim10               = $row2["abbrev"];
                $code                     = self::get($code_cim10);
                $this->_levelsSup[$index] = $code;
            }
        }

        ksort($this->_levelsSup);

        // Niveaux inferieurs
        $this->_levelsInf = [];
        $query            = "SELECT *
              FROM master
              WHERE id$this->level = '$this->sid'";
        if ($this->level < 7) {
            $query .= "\nAND id" . ($this->level + 1) . " != '0'";
        }
        if ($this->level < 6) {
            $query .= "\nAND id" . ($this->level + 2) . " = '0'";
        }

        $result = $ds->exec($query);
        while ($row = $ds->fetchArray($result)) {
            $code_cim10                    = $row["abbrev"];
            $code                          = self::get($code_cim10);
            $this->_levelsInf[$code_cim10] = $code;
        }

        ksort($this->_levelsInf);
    }

    /**
     * Sommaire
     *
     * @param string $lang    Langue
     * @param int    $level   The loading level
     * @param string $version The CIM10 version to use
     *
     * @return array
     */
    public static function getChapters($lang = null, $level = self::LITE, $version = null)
    {
        if (!$lang) {
            $lang = self::getLangCIM();
        }
        $ds = self::getSpec()->ds;

        $query    = "SELECT master.code as 'code' FROM chapter 
      LEFT JOIN master ON chapter.SID = master.SID ORDER BY chap";
        $results  = $ds->loadList($query);
        $chapters = [];

        foreach ($results as $result) {
            $chapters[] = self::get($result['code'], $level, $lang);
        }

        return $chapters;
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
     * @return array
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
        $lang = self::getLangCIM();

        $ds = self::getSpec()->ds;

        $fields        = ['master.abbrev AS code'];
        $tables        = ['libelle', 'master'];
        $ljoin         = [];
        $order         = [];
        $where_clauses = ['libelle.SID = master.SID', "master.type IN('K', 'S')"];

        $keywords = explode(" ", $keys);
        $codes    = explode(" ", $code ?? '');

        $where_keys = [];
        if ($keys && $keys != '') {
            foreach ($keywords as $keyword) {
                $where_keys[] = "libelle.$lang LIKE '%" . addslashes($keyword) . "%'";
            }
        }

        $where_codes = [];
        if ($code && $code != '') {
            foreach ($codes as $code) {
                $where_codes[] = "master.abbrev LIKE '" . addslashes($code) . "%'";
            }
        }

        if (count($where_codes) && count($where_keys)) {
            $where_clauses[] = ' ((' . implode(' AND ', $where_keys) . ') OR (' . implode(' OR ', $where_codes) . '))';
        } elseif (count($where_keys)) {
            $where_clauses[] = ' (' . implode(' AND ', $where_keys) . ')';
        } elseif (count($where_codes)) {
            $where_clauses[] = ' (' . implode(' AND ', $where_codes) . ')';
        }

        if ($max_length) {
            $where_clauses[] = "CHAR_LENGTH(master.abbrev) < $max_length";
        }

        if ($chapter) {
            $where_clauses[] = "master.id1 = '{$chapter}'";
        }

        if ($category) {
            $where_clauses[] = "master.id2 = '{$category}'";
        }

        if ($where) {
            $where_clauses[] = "($where)";
        }

        if ($user_favorites) {
            $favori               = new CFavoriCIM10();
            $favori->favoris_user = $user_favorites->_id;
            $favorites            = CMbArray::pluck($favori->loadMatchingList(), 'favoris_code');

            $where_clauses[] = "master.abbrev " . CSQLDataSource::prepareIn($favorites);
        }

        $query = 'SELECT ' . implode(', ', $fields) . ' FROM ' . implode(', ', $tables) . ' ';
        if (count($ljoin)) {
            $query .= implode(' ', $ljoin) . ' ';
        }
        if (count($where_clauses)) {
            $query .= 'WHERE ' . implode(' AND ', $where_clauses) . ' ';
        }

        $order[] = 'master.SID';
        $query   .= 'GROUP BY master.SID ORDER BY ' . implode(', ', $order) . ' LIMIT 0, 100';

        $results = $ds->loadList($query);

        if ($user_favorites) {
            if ($where) {
                $where = "($where) AND master.abbrev " . CSQLDataSource::prepareNotIn($favorites);
            } else {
                $where = 'master.abbrev ' . CSQLDataSource::prepareNotIn($favorites);
            }

            $results = array_merge(
                $results,
                self::findCodes(
                    $code,
                    $keys,
                    $chapter,
                    $category,
                    $max_length,
                    $where,
                    $version,
                    $sejour_type,
                    $field_type
                )
            );
        }

        return $results;
    }

    /**
     * @inheritdoc
     */
    public static function getSubCodes($code, $lang = null, $version = null)
    {
        $codeCim = self::get($code, self::FULL);
        $master  = [];
        $i       = 0;
        foreach ($codeCim->_levelsInf as $curr_code) {
            $master[$i]["text"] = $curr_code->libelle;
            $master[$i]["code"] = $curr_code->code;
            $i++;
        }

        return $master;
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
        return 'master.abbrev';
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
        return 'sid';
    }

    /**
     * @inheritdoc
     */
    public static function getDatabaseVersions()
    {
        return [
            "CIM10 OMS"        => [
                [
                    "table_name" => "master",
                    "filters"    => [
                        "SID" => "= '19550'",
                    ],
                ],
            ],
            "CIM10 OMS - 2014" => [
                [
                    "table_name" => "memo",
                    "filters"    => [
                        "SID"  => "= '932'",
                        "date" => "LIKE '2014-01-01%'",
                        "memo" => "LIKE 'Utiliser au besoin un code supplémentaire (U85)%'",
                    ],
                ],
            ],
        ];
    }

    public function equals(self $other): bool
    {
        return $this->exist === $other->exist
            && $this->code === $other->code
            && $this->code_long === $other->code_long
            && $this->libelle === $other->libelle;
    }
}
