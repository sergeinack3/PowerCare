<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Suivi des courbes de croissance
 */
class CCourbeReference implements IShortNameAutoloadable
{

    /**
     * Tableau des données des différents graphiques de courbe de référence
     */
    static $graph_datas = [
        /* Périmètre crânien en cm pour garçons et filles de la naissance à 60 mois (5 ans) */
        'perimetre_cranien'          => [
            'label' => ["+3 &sigma;", "+2 &sigma;", "+1 &sigma;", "M", "-1 &sigma;", "-2 &sigma;", "-3 &sigma;"],
            'unit'  => 'cm',
            'f'     => [
                'name'      => 'Growth of the cranial perimeter for girls',
                'ymin'      => 30,
                'ymax'      => 60,
                'yTickSize' => 5,
                'xTickSize' => 3,
                'age'       => [
                    'age_min'  => 0,
                    'age_max'  => 60,
                    'type_age' => "mois",
                ],
            ],
            'm'     => [
                'name'      => 'Growth of the cranial perimeter for boys',
                'ymin'      => 30,
                'ymax'      => 60,
                'yTickSize' => 5,
                'xTickSize' => 3,
                'age'       => [
                    'age_min'  => 0,
                    'age_max'  => 60,
                    'type_age' => "mois",
                ],
            ],
        ],
        /* Taille en cm pour garçons et filles de 1 mois à 3 ans (36 mois) */
        'taille'                     => [
            'label' => ["+3 &sigma;", "+2 &sigma;", "+1 &sigma;", "M", "-1 &sigma;", "-2 &sigma;", "-3 &sigma;"],
            'unit'  => 'cm',
            'f'     => [
                '3'  => [
                    'name'      => 'Growth in size of girls from 1 month to 3 years',
                    'ymin'      => 40,
                    'ymax'      => 110,
                    'yTickSize' => 5,
                    'xTickSize' => 3,
                    'age'       => [
                        'age_min'  => 0,
                        'age_max'  => 36,
                        'type_age' => "mois",
                    ],
                ],
                '18' => [
                    'name'      => 'Growth in the size of girls from 1 to 18 years',
                    'ymin'      => 60,
                    'ymax'      => 200,
                    'yTickSize' => 10,
                    'xTickSize' => 1,
                    'age'       => [
                        'age_min'  => 0,
                        'age_max'  => 18,
                        'type_age' => "ans",
                    ],
                ],
            ],
            'm'     => [
                '3'  => [
                    'name'      => 'Growth in size of boys from 1 month to 3 years',
                    'ymin'      => 40,
                    'ymax'      => 110,
                    'yTickSize' => 5,
                    'xTickSize' => 3,
                    'age'       => [
                        'age_min'  => 0,
                        'age_max'  => 36,
                        'type_age' => "mois",
                    ],
                ],
                '18' => [
                    'name'      => 'Growth in the size of boys from 1 to 18 years',
                    'ymin'      => 60,
                    'ymax'      => 210,
                    'yTickSize' => 10,
                    'xTickSize' => 1,
                    'age'       => [
                        'age_min'  => 0,
                        'age_max'  => 18,
                        'type_age' => "ans",
                    ],
                ],
            ],
        ],
        /* Poids en kg pour garçons et filles de la naissance à 3 ans (36 mois) et pour filles ou garcons de 1 à 18 ans */
        'poids'                      => [
            'label' => ["99%", "97%", "90", "75%", "M(50%)", "25%", "10%", "3%", "1%"],
            'unit'  => 'kg',
            'f'     => [
                '3'  => [
                    'name'      => 'Growth of weight of girls from 1 month to 3 years',
                    'ymin'      => 0,
                    'ymax'      => 22,
                    'yTickSize' => 1,
                    'xTickSize' => 1,
                    'age'       => [
                        'age_min'  => 0,
                        'age_max'  => 36,
                        'type_age' => "mois",
                    ],
                ],
                '18' => [
                    'name'      => 'Growth in the weight of girls from 1 to 18 years',
                    'ymin'      => 0,
                    'ymax'      => 100,
                    'yTickSize' => 10,
                    'xTickSize' => 1,
                    'age'       => [
                        'age_min'  => 0,
                        'age_max'  => 18,
                        'type_age' => "ans",
                    ],
                ],
            ],
            'm'     => [
                '3'  => [
                    'name'      => 'Growth of weight of boys from 1 month to 3 years',
                    'ymin'      => 0,
                    'ymax'      => 22,
                    'yTickSize' => 1,
                    'xTickSize' => 1,
                    'age'       => [
                        'age_min'  => 0,
                        'age_max'  => 36,
                        'type_age' => "mois",
                    ],
                ],
                '18' => [
                    'name'      => 'Growth in the weight of boys from 1 to 18 years',
                    'ymin'      => 0,
                    'ymax'      => 115,
                    'yTickSize' => 10,
                    'xTickSize' => 1,
                    'age'       => [
                        'age_min'  => 0,
                        'age_max'  => 18,
                        'type_age' => "ans",
                    ],
                ],
            ],
        ],
        /* IMC en kg/m² pour garçons ou filles de 1 à 18 ans. */
        '_imc'                       => [
            'label' => ["IOTF 16", "IOTF 17", "IOTF 18.5", "IOTF 25", "IOTF 30", "IOTF 35"],
            'unit'  => 'kg/m²',
            'f'     => [
                'name'      => 'Growth of the body mass index of girls from 0 to 18 years',
                'ymin'      => 10,
                'ymax'      => 45,
                'yTickSize' => 1,
                'xTickSize' => 1,
                'age'       => [
                    'age_min'  => 0,
                    'age_max'  => 18, // Months
                    'type_age' => "ans",
                ],
            ],
            'm'     => [
                'name'      => 'Growth of the body mass index of boys from 0 to 18 years',
                'ymin'      => 10,
                'ymax'      => 45,
                'yTickSize' => 1,
                'xTickSize' => 1,
                'age'       => [
                    'age_min'  => 0,
                    'age_max'  => 18, // Months
                    'type_age' => "ans",
                ],
            ],
        ],
        'bilirubine_transcutanee'    => [
            'label'     => ["40e percentile;", "75e percentile;", "95e percentile;"],
            'unit'      => 'µmol/l',
            'name'      => 'Monitoring newborn jaundice',
            'ymin'      => 0,
            'ymax'      => 350,
            'yTickSize' => 50,
            'xTickSize' => 6,
            'age'       => [
                'age_min'  => 0,
                'age_max'  => 144,
                'type_age' => "heure",
            ],
        ],
        'bilirubine_totale_sanguine' => [
            'unit' => 'µmol/l',
            '-35'  => [
                'label'     => [
                    "PT si PN <1000g ou <28 SA",
                    "PT si PN 1000-1249g ou 28-29+65 SA",
                    "PT si PN 1250-1499g ou 30-31+6 SA",
                    "PT si PN 1500-2000g ou 32-33+6 SA",
                    "PT si PN 2000-2500g ou 34-34+6 SA",
                ],
                'name'      => 'Premature phototherapy indication curve',
                'ymin'      => 0,
                'ymax'      => 300,
                'yTickSize' => 50,
                'xTickSize' => 12,
                'age'       => [
                    'age_min'  => 0,
                    'age_max'  => 240, // Months
                    'type_age' => "heure",
                ],
            ],
            '+35'  => [
                'label'     => [
                    "PTI 35-37 SA",
                    "PTI 35-37SA ou >38 SA",
                    "PTI >38",
                    "EST 35-37 SA",
                    "EST 35-37 SA ou >38 SA",
                    "EST > 38 SA",
                ],
                'name'      => 'Phototherapy indication curve',
                'ymin'      => 0,
                'ymax'      => 450,
                'yTickSize' => 50,
                'xTickSize' => 12,
                'age'       => [
                    'age_min'  => 0,
                    'age_max'  => 168, // Months
                    'type_age' => "heure",
                ],
            ],
        ],
    ];
    public $type_age;

    /**
     * Format graph axes according to flot format
     *
     * @param string $graph_name Name of the graphic
     * @param string $sexe       Patient gender
     * @param string $age        Patient age
     *
     * @return array
     */
    static function formatGraphDataset($graph_name, $sexe, $age, $SA)
    {
        $label   = [];
        $dataset = [];
        $series  = [];
        $i       = 0;
        $array   = self::getReferencePointsXMLtoArray($graph_name, $sexe, $age, $SA);

        // recupere le label
        if ($graph_name != "bilirubine_totale_sanguine") {
            foreach ($array["label"] as $_k => $_value) {
                $label[] = $_value;
            }
        } else {
            foreach ($array[$SA]["label"] as $_k => $_value) {
                $label[] = $_value;
            }
        }

        // recupere les données du fichier xml
        if ($graph_name == "taille" || $graph_name == "poids") {
            $datas = $array[$sexe][$age]["reference"];
        } elseif ($graph_name == "bilirubine_transcutanee") {
            $datas = $array["reference"];
        } elseif ($graph_name == "bilirubine_totale_sanguine") {
            $datas = $array[$SA]["reference"];
        } else {
            $datas = $array[$sexe]["reference"];
        }


        // données du fichier xml
        if ($graph_name == "poids") {
            foreach ($datas as $_values) {
                if ($i == 0) {
                    $series["-4"][] = [$i, null];
                    $series["-3"][] = [$i, null];
                    $series["-2"][] = [$i, null];
                    $series['-1'][] = [$i, null];
                    $series['M'][]  = [$i, null];
                    $series['+1'][] = [$i, null];
                    $series['+2'][] = [$i, null];
                    $series['+3'][] = [$i, null];
                    $series['+4'][] = [$i, null];
                } else {
                    $series["-4"][] = [$i, $_values["item_0"]];
                    $series["-3"][] = [$i, $_values["item_1"]];
                    $series["-2"][] = [$i, $_values["item_2"]];
                    $series['-1'][] = [$i, $_values["item_3"]];
                    $series['M'][]  = [$i, $_values["item_4"]];
                    $series['+1'][] = [$i, $_values["item_5"]];
                    $series['+2'][] = [$i, $_values["item_6"]];
                    $series['+3'][] = [$i, $_values["item_7"]];
                    $series['+4'][] = [$i, $_values["item_8"]];
                }
                $series['max'][] = [$i, $_values["item_9"]];
                $i++;
            }
        } elseif ($graph_name == "_imc") {
            foreach ($datas as $_values) {
                if ($i != 0 && $i != 1) {
                    $series["-3"][] = [$i, $_values["item_0"]];
                    $series['-1'][] = [$i, $_values["item_2"]];
                    $series['+2'][] = [$i, $_values["item_4"]];
                    $series['+3'][] = [$i, $_values["item_5"]];
                } else {
                    $series["-3"][] = [$i, null];
                    $series['-1'][] = [$i, null];
                    $series['+2'][] = [$i, null];
                    $series['+3'][] = [$i, null];
                }
                $series["-2"][]  = [$i, $_values["item_1"]];
                $series['+1'][]  = [$i, $_values["item_3"]];
                $series['max'][] = [$i, $_values["item_6"]];
                $i++;
            }
        } elseif ($graph_name == "bilirubine_transcutanee") {
            foreach ($datas as $_values) {
                if ($i != 0 && $i != 6) {
                    $series["-1"][] = [$i, $_values["item_0"]];
                    $series['0'][]  = [$i, $_values["item_1"]];
                    $series['+1'][] = [$i, $_values["item_2"]];
                } else {
                    $series["-1"][] = [$i, null];
                    $series['0'][]  = [$i, null];
                    $series['+1'][] = [$i, null];
                }
                $i += 6;
            }
        } elseif ($graph_name == "bilirubine_totale_sanguine" && $SA == "-35") {
            foreach ($datas as $_values) {
                $series["-2"][] = [$i, $_values["item_0"]];
                $series['-1'][] = [$i, $_values["item_1"]];
                $series['0'][]  = [$i, $_values["item_2"]];
                $series["+1"][] = [$i, $_values["item_3"]];
                $series['+2'][] = [$i, $_values["item_4"]];
                $i              += 12;
            }
        } elseif ($graph_name == "bilirubine_totale_sanguine" && $SA == "+35") {
            foreach ($datas as $_values) {
                $series["-3"][] = [$i, $_values["item_0"]];
                $series['-2'][] = [$i, $_values["item_1"]];
                $series['-1'][] = [$i, $_values["item_2"]];
                $series["+1"][] = [$i, $_values["item_3"]];
                $series['+2'][] = [$i, $_values["item_4"]];
                $series['+3'][] = [$i, $_values["item_5"]];
                $i              += 12;
            }
        } else {
            foreach ($datas as $_values) {
                if ($i == 0) {
                    $series["-3"][] = [$i, null];
                    $series["-2"][] = [$i, null];
                    $series['-1'][] = [$i, null];
                    $series['M'][]  = [$i, null];
                    $series['+1'][] = [$i, null];
                    $series['+2'][] = [$i, null];
                    $series['+3'][] = [$i, null];
                } else {
                    $series["-3"][] = [$i, $_values["item_0"]];
                    $series["-2"][] = [$i, $_values["item_1"]];
                    $series['-1'][] = [$i, $_values["item_2"]];
                    $series['M'][]  = [$i, $_values["item_3"]];
                    $series['+1'][] = [$i, $_values["item_4"]];
                    $series['+2'][] = [$i, $_values["item_5"]];
                    $series['+3'][] = [$i, $_values["item_6"]];
                }
                $series['max'][] = [$i, $_values["item_7"]];
                $i++;
            }
        }

        if ($graph_name == "poids") {
            $dataset[] = [
                'label'       => "$label[0]",
                'id'          => "{$graph_name}_-4",
                'data'        => $series['-4'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 0.5,
                ],
                'color'       => '#000000',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];

            $dataset[] = [
                'label'       => "$label[1]",
                'id'          => "{$graph_name}_-3",
                'data'        => $series['-3'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 1.5,
                    'fill'      => true,
                    'fillColor' => 'rgba(109, 192, 102, 0.4)',
                ],
                'color'       => 'rgb(65,23,169)',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];

            $dataset[] = [
                'label'     => "$label[2]",
                'id'        => "{$graph_name}_-2",
                'data'      => $series['-2'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];


            $dataset[] = [
                'label'     => "$label[3]",
                'id'        => "{$graph_name}_-1",
                'data'      => $series['-1'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];

            $dataset[] = [
                'label'       => "$label[4]",
                'id'          => "{$graph_name}_M",
                'data'        => $series['M'],
                'color'       => 'rgb(65,23,169)',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];
            $dataset[] = [
                'label'     => "$label[5]",
                'id'        => "{$graph_name}_+1",
                'data'      => $series['+1'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];

            $dataset[] = [
                'label'     => "$label[6]",
                'id'        => "{$graph_name}_+2",
                'data'      => $series['+2'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];

            $dataset[] = [
                'label'       => "$label[7]",
                'id'          => "{$graph_name}_+3",
                'data'        => $series['+3'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 1.5,
                ],
                'color'       => 'rgb(65,23,169)',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];

            $dataset[] = [
                'label'       => "$label[8]",
                'id'          => "{$graph_name}_+4",
                'data'        => $series['+4'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 0.5,
                ],
                'color'       => '#000000',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];

            $dataset[] = [
                'id'          => "{$graph_name}_max",
                'data'        => $series['max'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 0,
                    'fill'      => 0.4,
                ],
                'points'      => ['show' => false],
                'fillBetween' => "{$graph_name}_+3",
                'color'       => '#6DC066',
                'hoverable'   => false,
            ];
        } elseif ($graph_name == "_imc") {
            // IOTF 16
            $dataset[] = [
                'label'     => "$label[0]",
                'id'        => "{$graph_name}_-3",
                'data'      => $series['-3'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];
            // IOTF 17
            $dataset[] = [
                'label'       => "$label[1]",
                'id'          => "{$graph_name}_-2",
                'data'        => $series['-2'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 1.5,
                    'fill'      => true,
                    'fillColor' => 'rgba(109, 192, 102, 0.4)',
                ],
                'color'       => 'rgb(65,23,169)',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => true,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];
            //IOTF 18.5
            $dataset[] = [
                'label'     => "$label[2]",
                'id'        => "{$graph_name}_-1",
                'data'      => $series['-1'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];
            // IOTF 25
            $dataset[] = [
                'label'       => "$label[3]",
                'id'          => "{$graph_name}_+1",
                'data'        => $series['+1'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 1.5,
                ],
                'color'       => 'rgb(65,23,169)',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => true,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];
            // IOTF 30
            $dataset[] = [
                'label'     => "$label[4]",
                'id'        => "{$graph_name}_+2",
                'data'      => $series['+2'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];

            // IOTF 35
            $dataset[] = [
                'label'     => "$label[5]",
                'id'        => "{$graph_name}_+3",
                'data'      => $series['+3'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];

            $dataset[] = [
                'id'          => "{$graph_name}_max",
                'data'        => $series['max'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 0,
                    'fill'      => 0.4,
                ],
                'points'      => ['show' => false],
                'fillBetween' => "{$graph_name}_+1",
                'color'       => '#6DC066',
                'hoverable'   => false,
            ];
        } elseif ($graph_name == "bilirubine_transcutanee") {
            $dataset[] = [
                'label'     => "$label[0]",
                'id'        => "{$graph_name}_-1",
                'data'      => $series['-1'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(145,183,79)',
                'hoverable' => false,
            ];

            $dataset[] = [
                'label'     => "$label[1]",
                'id'        => "{$graph_name}_0",
                'data'      => $series['0'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(142,141,146)',
                'hoverable' => false,
            ];

            $dataset[] = [
                'label'     => "$label[2]",
                'id'        => "{$graph_name}_+1",
                'data'      => $series['+1'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(20,169,155)',
                'hoverable' => false,
            ];
        } elseif ($graph_name == "bilirubine_totale_sanguine" && $SA == "-35") {
            $dataset[] = [
                'label'     => "$label[0]",
                'id'        => "{$graph_name}_-2",
                'data'      => $series['-2'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(255,95,95)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[1]",
                'id'        => "{$graph_name}_-1",
                'data'      => $series['-1'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(7,133,62)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[2]",
                'id'        => "{$graph_name}_0",
                'data'      => $series['0'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(112,48,160)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[3]",
                'id'        => "{$graph_name}_+1",
                'data'      => $series['+1'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(42,202,187)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[4]",
                'id'        => "{$graph_name}_+2",
                'data'      => $series['+2'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(145,185,38)',
                'hoverable' => false,
            ];
        } elseif ($graph_name == "bilirubine_totale_sanguine" && $SA == "+35") {
            $dataset[] = [
                'label'     => "$label[0]",
                'id'        => "{$graph_name}_-3",
                'data'      => $series['-3'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(101,252,70)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[1]",
                'id'        => "{$graph_name}_-2",
                'data'      => $series['-2'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(70,188,190)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[2]",
                'id'        => "{$graph_name}_-1",
                'data'      => $series['-1'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(254,130,47)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[3]",
                'id'        => "{$graph_name}_+1",
                'data'      => $series['+1'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(176,27,191)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[4]",
                'id'        => "{$graph_name}_+2",
                'data'      => $series['+2'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(73,143,248)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'     => "$label[5]",
                'id'        => "{$graph_name}_+3",
                'data'      => $series['+3'],
                'lines'     => [
                    'show'      => true,
                    'lineWidth' => 2,
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(214,211,24)',
                'hoverable' => false,
            ];
        } else {
            $dataset[] = [
                'label'       => "$label[0]",
                'id'          => "{$graph_name}_-3",
                'data'        => $series['-3'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 0.5,
                ],
                'color'       => '#000000',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];

            $dataset[] = [
                'label'       => "$label[1]",
                'id'          => "{$graph_name}_-2",
                'data'        => $series['-2'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 1.5,
                    'fill'      => true,
                    'fillColor' => 'rgba(109, 192, 102, 0.4)',
                ],
                'color'       => 'rgb(65,23,169)',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];
            $dataset[] = [
                'label'     => "$label[2]",
                'id'        => "{$graph_name}_-1",
                'data'      => $series['-1'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'       => "$label[3]",
                'id'          => "{$graph_name}_M",
                'data'        => $series['M'],
                'color'       => 'rgb(65,23,169)',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];
            $dataset[] = [
                'label'     => "$label[4]",
                'id'        => "{$graph_name}_+1",
                'data'      => $series['+1'],
                'dashes'    => [
                    'show'       => true,
                    'lineWidth'  => 1,
                    'dashLength' => [5, 5],
                ],
                'points'    => ['show' => true, 'radius' => 0],
                'color'     => 'rgb(74,155,229)',
                'hoverable' => false,
            ];
            $dataset[] = [
                'label'       => "$label[5]",
                'id'          => "{$graph_name}_+2",
                'data'        => $series['+2'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 1.5,
                ],
                'color'       => 'rgb(65,23,169)',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];

            $dataset[] = [
                'label'       => "$label[6]",
                'id'          => "{$graph_name}_+3",
                'data'        => $series['+3'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 0.5,
                ],
                'color'       => '#000000',
                'hoverable'   => false,
                'curvedLines' => [
                    'apply'          => false,
                    'legacyOverride' => [
                        'curvePointFactor' => 42,
                        'fitPointDist'     => 0.9,
                    ],
                ],
            ];

            $dataset[] = [
                'id'          => "{$graph_name}_max",
                'data'        => $series['max'],
                'lines'       => [
                    'show'      => true,
                    'lineWidth' => 0,
                    'fill'      => 0.4,
                ],
                'points'      => ['show' => false],
                'fillBetween' => "{$graph_name}_+2",
                'color'       => '#6DC066',
                'hoverable'   => false,
            ];
        }

        return $dataset;
    }

    /**
     * Recupere les points dans un fichier xml de la courbe demandée et les insere dans le tableau
     *
     * @param string $graph_name Nom du graphique
     * @param string $sexe       Sexe du patient
     * @param string $age        Age du patient
     * @param string $SA         SA
     *
     * @return array tableau des données pour le graphique
     */
    public static function getReferencePointsXMLtoArray($graph_name, $sexe, $age, $SA)
    {
        // Recupere le fichier xml et met les données sous forme de tableau
        $filename = $graph_name;
        if ($graph_name != "bilirubine_transcutanee" && $graph_name != "bilirubine_totale_sanguine") {
            $filename .= "_" . $sexe;
        }
        if ($graph_name == "taille" || $graph_name == "poids") {
            $filename .= "_" . $age;
        }
        if ($graph_name == "bilirubine_totale_sanguine") {
            $filename .= "_" . $SA;
        }
        $xml_path = __DIR__ . "/../datas/$filename.xml";

        $xmlfile        = file_get_contents($xml_path);
        $xml            = simplexml_load_string($xmlfile);
        $data_reference = json_decode(json_encode($xml), true);

        if ($graph_name == "taille" || $graph_name == "poids") {
            self::$graph_datas[$graph_name][$sexe][$age]["reference"] = $data_reference;
        } elseif ($graph_name == "bilirubine_transcutanee") {
            self::$graph_datas[$graph_name]["reference"] = $data_reference;
        } elseif ($graph_name == "bilirubine_totale_sanguine") {
            self::$graph_datas[$graph_name][$SA]["reference"] = $data_reference;
        } else {
            self::$graph_datas[$graph_name][$sexe]["reference"] = $data_reference;
        }

        return self::$graph_datas[$graph_name];
    }
}
