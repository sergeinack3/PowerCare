<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;

/**
 * Class CCCAM
 */
class CCCAM implements IShortNameAutoloadable
{
    /** @var CMbObjectSpec */
    public static $spec;

    /**
     * Get object spec
     *
     * @return CMbObjectSpec
     */
    public static function getSpec(): CMbObjectSpec
    {
        if (self::$spec) {
            return self::$spec;
        }

        $spec      = new CMbObjectSpec();
        $spec->dsn = "ccamV2";
        $spec->init();

        return self::$spec = $spec;
    }

    /**
     * Charge les chapitres ayant pour parent le chapitre donné.
     * Si aucun parent n'est donné, les chapitres de niveaux 1 sont retournés
     *
     * @param string $parent Le code du parent
     *
     * @return array
     */
    public static function getListChapters(string $parent = null): array
    {
        if (!$parent) {
            $parent = '000001';
        }

        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $chapters = $cache->get("CCCAM.getListChapters-{$parent}");
        if (!$chapters) {
            self::getSpec();
            $list     = self::$spec->ds->loadList(
                "SELECT * FROM `c_arborescence` WHERE `CODEPERE` = '$parent' ORDER BY `RANG`;"
            );

            $chapters = [];
            if ($list) {
                foreach ($list as $row) {
                    $chapters[$row['RANG']] = $row;
                }
            }

            $cache->set("CCCAM.getListChapters-{$parent}", $chapters);
        }

        return $chapters;
    }

    /**
     * Charge les chapitres ayant pour parent le chapitre donné.
     * Si aucun parent n'est donné, les chapitres de niveaux 1 sont retournés
     * Les données sont retournées dans un format specifique
     *
     * @param string $parent Le code du parent
     *
     * @return array
     */
    public static function getChapters(string $parent = null): array
    {
        $list = self::getListChapters($parent);

        $chapters = [];

        foreach ($list as $chapter) {
            $code            = $chapter['CODEMENU'];
            $chapters[$code] = [
                'code' => $chapter['CODEMENU'],
                'rank' => substr($chapter['RANG'], 4, 2),
                'text' => ucfirst(CMbString::lower($chapter['LIBELLE'])),
            ];
        }

        return $chapters;
    }

    /**
     * Retourne les notes de chapitres triées par chapitres de la CCAM
     *
     * @return array
     */
    public static function getNotesChapters(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $notes = $cache->get('CCCAM.getNotesChapters-c_notesarborescence');
        if (!$notes) {
            self::getSpec();
            $list  = self::$spec->ds->loadList("SELECT * FROM `c_notesarborescence` ORDER BY `CODEMENU`;");
            $notes = [];
            if ($list) {
                foreach ($list as $row) {
                    if (!array_key_exists($row['CODEMENU'], $notes)) {
                        $notes[$row['CODEMENU']] = [];
                    }

                    $notes[$row['CODEMENU']][] = $row;
                }
            }

            $cache->set('CCCAM.getNotesChapters-c_notesarborescence', $notes);
        }

        return $notes;
    }

    public static function getAccesses(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $access = $cache->get('CCCAM.getAccesses');
        if (!$access) {
            self::getSpec();
            $list  = self::$spec->ds->loadList("SELECT * FROM `acces1` ORDER BY `CODE`;");
            $access = [];
            if ($list) {
                foreach ($list as $row) {
                    $access[] = [
                        'code' => $row['CODE'],
                        'text' => ucfirst(CMbString::lower($row['ACCES']))
                    ];
                }
            }

            $cache->set('CCCAM.getAccesses', $access);
        }

        return $access;
    }

    public static function getFirstLevelTopographies(): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $topographies = $cache->get('CCCAM.getFirstLevelTopographies');
        if (!$topographies) {
            self::getSpec();
            $list  = self::$spec->ds->loadList("SELECT * FROM `topographie1` ORDER BY `CODE`;");
            $topographies = [];
            if ($list) {
                foreach ($list as $row) {
                    $topographies[] = [
                        'code' => $row['CODE'],
                        'text' => ucfirst(CMbString::lower($row['LIBELLE']))
                    ];
                }
            }

            $cache->set('CCCAM.getFirstLevelTopographies', $topographies);
        }

        return $topographies;
    }

    public static function getSecondLevelTopographies(string $parent_code = null): array
    {
        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        $cache_key = $parent_code ? "CCCAM.getSecondLevelTopographies-{$parent_code}"
            : "CCCAM.getSecondLevelTopographies";

        $topographies = $cache->get($cache_key);
        if (!$topographies) {
            self::getSpec();
            $query = $parent_code ? "SELECT * FROM `topographie2`WHERE PERE = % ORDER BY `CODE`;"
                : "SELECT * FROM `topographie2` ORDER BY `CODE`;";

            $list  = self::$spec->ds->loadList(self::$spec->ds->prepare($query, $parent_code));
            $topographies = [];
            if ($list) {
                foreach ($list as $row) {
                    if (!$parent_code) {
                        if (!array_key_exists($row['PERE'], $topographies)) {
                            $topographies[$row['PERE']] = [];
                        }
                        $topographies[$row['PERE']][] = [
                            'code' => $row['CODE'],
                            'text' => ucfirst(CMbString::lower($row['LIBELLE']))
                        ];
                    } else {
                        $topographies[] = [
                            'code' => $row['CODE'],
                            'text' => ucfirst(CMbString::lower($row['LIBELLE']))
                        ];
                    }
                }
            }

            $cache->set($cache_key, $topographies);
        }

        return $topographies;
    }

    /**
     * Return the current version and the next available version of the CCAM database
     *
     * @return array (string current version, string next version)
     */
    public static function getDatabaseVersions(): array
    {
        return [
            "< 45"  => [
                [
                    "table_name" => "acces1",
                    "filters"    => [
                    ],
                ],
            ],
            "45"    => [
                [
                    "table_name" => "p_acte",
                    "filters"    => [
                        "CODE" => "= 'DBBF198'",
                    ],
                ],
            ],
            "46"    => [
                [
                    "table_name" => "p_acte",
                    "filters"    => [
                        "CODE" => "= 'QZNP086'",
                    ],
                ],
            ],
            "47"    => [
                [
                    "table_name" => "t_modificateurforfait",
                    "filters"    => [
                        "CODE"        => "K",
                        "DATEDEBUT"   => "= '20170615'",
                        "COEFFICIENT" => "= '1200'",
                    ],
                ],
            ],
            "48"    => [
                [
                    "table_name" => "p_activite_classif",
                    "filters"    => [
                        "CODEACTE" => "= 'DELF020'",
                        "REGROUP"  => "= 'ATM'",
                    ],
                ],
            ],
            "49"    => [
                [
                    "table_name" => "p_acte",
                    "filters"    => [
                        "CODE" => "= 'AAQP129'",
                    ],
                ],
            ],
            "50"    => [
                [
                    "table_name" => "p_activite_modificateur",
                    "filters"    => [
                        "CODEACTE"     => "= 'AAFA001'",
                        "DATEEFFET"    => "= '20180101'",
                        "MODIFICATEUR" => "= 'O'",
                    ],
                ],
            ],
            "51"    => [
                [
                    "table_name" => "p_acte",
                    "filters"    => [
                        "CODE" => "= 'DELF086'",
                    ],
                ],
            ],
            "52"    => [
                [
                    "table_name" => "p_acte",
                    "filters"    => [
                        "CODE" => "= 'JKQX147'",
                    ],
                ],
            ],
            "53"    => [
                [
                    "table_name" => "p_acte",
                    "filters"    => [
                        "CODE" => "= 'GEME121'",
                    ],
                ],
            ],
            "54"    => [
                [
                    "table_name" => "p_acte",
                    "filters"    => [
                        "CODE" => "= 'NEDB454'",
                    ],
                ],
            ],
            "55"    => [
                [
                    "table_name" => "p_phase_pu_base",
                    "filters"    => [
                        "CODEACTE"  => "= 'BZQK001'",
                        "PHASE"     => "= '0'",
                        "DATEEFFET" => "= '20190101'",
                        "GRILLE"    => "= '005'",
                        "PU"        => "= '005654'",
                    ],
                ],
            ],
            '56'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'HBMD351'",
                    ],
                ],
            ],
            '57'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'BFGA427'",
                    ],
                ],
            ],
            '58'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'EJSF007'",
                    ],
                ],
            ],
            '59'    => [
                [
                    'table_name' => 'p_phase_pu_base',
                    'filters'    => [
                        'CODEACTE' => " = 'LDQK001'",
                        'CODEACTE' => " = '003100'",
                    ],
                ],
            ],
            '60'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'HBJA171'",
                    ],
                ],
            ],
            '61'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'GELE308'",
                    ],
                ],
            ],
            '62'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'YYYY755'",
                    ],
                ],
            ],
            '63'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'JANH798'",
                    ],
                ],
            ],
            '64'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'JDLD659'",
                    ],
                ],
            ],
            '65'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'FEFF438'",
                    ],
                ],
            ],
            '66'    => [
                [
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => " = 'JKQJ350'",
                    ],
                ],
            ],
            '66.10' => [
                [
                    'table_name' => 'p_activite_classif',
                    'filters'    => [
                        'CODEACTE' => " = 'YYYY755'",
                        'CATMED'   => " = 'AD'",
                    ],
                ],
            ],
            '67'    => [
                [
                    'CCAM Version 67, Ajout des specialités pouvant exécuter un acte',
                    'table_name' => 'p_acte_infotarif',
                    'filters'    => [
                        'PRESCRIPTEUR2' => " IS NULL",
                    ],
                ],
            ],
            '68'    => [
                [
                    'CCAM Version 68',
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => "= 'DGKA268'",
                    ],
                ],
            ],
            '69'    => [
                [
                    'CCAM Version 69',
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => "= 'YYYY071'",
                    ],
                ],
            ],
            '70'    => [
                [
                    'CCAM Version 70',
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => "= 'BELA340'",
                    ],
                ],
            ],
            '71'    => [
                [
                    'CCAM Version 71',
                    'table_name' => 'p_acte',
                    'filters'    => [
                        'CODE' => "= 'LALA469'",
                    ],
                ],
            ],
        ];
    }
}
