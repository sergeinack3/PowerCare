<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;

/**
 * Class CCCAMToSQLConverter
 */
class CCCAMToSQLConverter extends Command
{
    /**
     * Tableau qui définit comment découper les lignes en fonction de leur code. Ce tableau gère les codes simples
     *  - La clé est le type de ligne (001 à 099)
     *  - Table est le nom de la table correspondant dans le base CCAM de Mediboard
     *  - Start est la valeur à laquelle on commence à découper la ligne (les caractères avant sont les données sur le
     *  type de ligne)
     *  - Occu est le nombre d'enregistrements maximum par ligne
     *  - Fields est un tableau associatif avec :
     *    - En clé le nom du champs
     *    - La taille du champs
     */
    public static $simple_types = [
        "001" => [
            "table"  => "t_modetraitement",
            "start"  => 7,
            "occu"   => 6,
            "fields" => [
                "CODE"      => 2,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
            ],
        ],
        "002" => [
            "table"  => "t_association",
            "start"  => 7,
            "occu"   => 5,
            "fields" => [
                "GRILLE"      => 3,
                "CODE"        => 1,
                "DATEDEBUT"   => 8,
                "DATEFIN"     => 8,
                "COEFFICIENT" => 4,
            ],
        ],
        "003" => [
            "table"  => "t_regletarifaire",
            "start"  => 7,
            "occu"   => 5,
            "fields" => [
                "GRILLE"      => 3,
                "CODE"        => 1,
                "DATEDEBUT"   => 8,
                "DATEFIN"     => 8,
                "COEFFICIENT" => 4,
            ],
        ],
        "004" => [
            "table"  => "t_regroupementspecialite",
            "start"  => 7,
            "occu"   => 6,
            "fields" => [
                "CODE"      => 2,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "CLASSE"    => 2,
            ],
        ],
        "005" => [
            "table"  => "t_prestationforfait",
            "start"  => 7,
            "occu"   => 6,
            "fields" => [
                "NATURE"    => 3,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "TYPE"      => 1,
            ],
        ],
        "006" => [
            "table"  => "t_modificateurage",
            "start"  => 7,
            "occu"   => 4,
            "fields" => [
                "CODE"      => 1,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "UNITE1"    => 1,
                "AGEMIN"    => 3,
                "UNITE2"    => 1,
                "AGEMAX"    => 3,
            ],
        ],
        "007" => [
            "table"  => "t_seuilexotm",
            "start"  => 7,
            "occu"   => 2,
            "fields" => [
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "METROPOLE" => 7,
                "ANTILLES"  => 7,
                "REUNION"   => 7,
                "GUYANE"    => 7,
            ],
        ],
        "008" => [
            "table"  => "t_joursferies",
            "start"  => 7,
            "occu"   => 4,
            "fields" => [
                "CAISSE"    => 3,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "TYPE"      => 1,
                "JOURFERIE" => 8,
            ],
        ],
        "009" => [
            "table"  => "t_modificateurcompat",
            "start"  => 7,
            "occu"   => 6,
            "fields" => [
                "CODE1"     => 1,
                "CODE2"     => 1,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
            ],
        ],
        "010" => [
            "table"  => "t_modificateurcoherence",
            "start"  => 7,
            "occu"   => 6,
            "fields" => [
                "CODE"              => 1,
                "DATEDEBUT"         => 8,
                "DATEFIN"           => 8,
                "CONTROLECOHERENCE" => 1,
                "PRESENCEMULTIPLE"  => 1,
            ],
        ],
        "011" => [
            "table"  => "t_modificateurforfait",
            "start"  => 7,
            "occu"   => 3,
            "fields" => [
                "GRILLE"      => 3,
                "CODE"        => 1,
                "DATEDEBUT"   => 8,
                "DATEFIN"     => 8,
                "FORFAIT"     => 7,
                "COEFFICIENT" => 4,
            ],
        ],
        "012" => [
            "table"  => "t_rembtnonconventionnes",
            "start"  => 7,
            "occu"   => 4,
            "fields" => [
                "DATEDEBUT"   => 8,
                "DATEFIN"     => 8,
                "FORFAIT"     => 7,
                "COEFFICIENT" => 4,
            ],
        ],
        "013" => [
            "table"  => "t_natureprestation",
            "start"  => 7,
            "occu"   => 6,
            "fields" => [
                "CODE"      => 3,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "LETTRECLE" => 1,
            ],
        ],
        "014" => [
            "table"  => "t_disciplinetarifaire",
            "start"  => 7,
            "occu"   => 5,
            "fields" => [
                "CODE"      => 3,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "CLASSE"    => 2,
            ],
        ],
        "015" => [
            "table"  => "t_modificateurinfooc",
            "start"  => 7,
            "occu"   => 6,
            "fields" => [
                "CODE"      => 1,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "CODEOC"    => 1,
            ],
        ],
        "017" => [
            "table"  => "t_ccamprestationngap",
            "start"  => 7,
            "occu"   => 9,
            "fields" => [
                "CODE"           => 3,
                "SPECIALITE"     => 2,
                "DATEOBLIGATION" => 8,
            ],
        ],
        "018" => [
            "table"  => "t_localisationdents",
            "start"  => 7,
            "occu"   => 6,
            "fields" => [
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "LOCDENT"   => 2,
            ],
        ],
        "019" => [
            "table"  => "t_modificateurnombre",
            "start"  => 7,
            "occu"   => 7,
            "fields" => [
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "NOMBRE"    => 1,
            ],
        ],
        "020" => [
            "table"  => "t_conceptsdivers",
            "start"  => 7,
            "occu"   => 4,
            "fields" => [
                "CODE"      => 2,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
                "CONCEPT"   => 10,
            ],
        ],
        "021" => [
            "table"  => "t_forfait_cmuc",
            "start"  => 7,
            "occu"   => 4,
            "fields" => [
                "CLE"       => 4,
                "FORFAIT"   => 3,
                "INDICE"    => 2,
                "DATEDEBUT" => 8,
                "DATEFIN"   => 8,
            ],
        ],
        "022" => [
            "table"  => "t_affectation_grille_context",
            "start"  => 7,
            "occu"   => 4,
            "fields" => [
                "PS"           => 4,
                "BENEFICIAIRE" => 4,
                "DATEDEBUT"    => 8,
                "DATEFIN"      => 8,
                "GRILLE"       => 3,
            ],
        ],
        "023" => [
            "table"  => "t_grille",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "GRILLE"  => 3,
                "LIBELLE" => 100,
            ],
        ],
        // Tables de codification
        "050" => [
            "table"  => "c_typenote",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 2,
                "LIBELLE" => 100,
            ],
        ],
        "051" => [
            "table"  => "c_conditionsgenerales",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 4,
                "LIBELLE" => 100,
            ],
        ],
        "052" => [
            "table"  => "c_classedmt",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 2,
                "LIBELLE" => 100,
            ],
        ],
        "053" => [
            "table"  => "c_exotm",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 80,
            ],
        ],
        "054" => [
            "table"  => "c_natureassurance",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 2,
                "LIBELLE" => 100,
            ],
        ],
        "055" => [
            "table"  => "c_remboursement",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 80,
            ],
        ],
        "056" => [
            "table"  => "c_fraisdeplacement",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 80,
            ],
        ],
        "057" => [
            "table"  => "c_typeacte",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 80,
            ],
        ],
        "058" => [
            "table"  => "c_typeforfait",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 100,
            ],
        ],
        "059" => [
            "table"  => "c_extensiondoc",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 100,
            ],
        ],
        "060" => [
            "table"  => "c_activite",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 100,
            ],
        ],
        "061" => [
            "table"  => "c_categoriemedicale",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 2,
                "LIBELLE" => 100,
            ],
        ],
        "062" => [
            "table"  => "c_coderegroupement",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 3,
                "LIBELLE" => 100,
            ],
        ],
        "063" => [
            "table"  => "c_categoriespecialite",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 2,
                "LIBELLE" => 100,
            ],
        ],
        "064" => [
            "table"  => "c_agrement_radio",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "TYPE"    => 2,
                "LIBELLE" => 100,
            ],
        ],
        "065" => [
            "table"  => "c_paiementseances",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 80,
            ],
        ],
        "066" => [
            "table"  => "c_phase",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 80,
            ],
        ],
        "067" => [
            "table"  => "c_dentsincomp",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 2,
                "LIBELLE" => 80,
            ],
        ],
        "068" => [
            "table"  => "c_caisseoutremer",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 3,
                "LIBELLE" => 50,
            ],
        ],
        // Libellés de concepts présents en tables paramètres TB01 à TB20
        "080" => [
            "table"  => "l_anp",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 100,
            ],
        ],
        "081" => [
            "table"  => "l_regletarifaire",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 100,
            ],
        ],
        "082" => [
            "table"  => "l_specialite",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 2,
                "LIBELLE" => 100,
            ],
        ],
        "083" => [
            "table"  => "l_modificateur",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 1,
                "LIBELLE" => 100,
            ],
        ],
        "084" => [
            "table"  => "l_dmt",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODE"    => 3,
                "LIBELLE" => 100,
            ],
        ],
        "085" => [
            "table"  => "l_context_ps",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "PS"      => 4,
                "LIBELLE" => 117,
            ],
        ],
        "086" => [
            "table"  => "l_context_beneficiaire",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "BENEFICIAIRE" => 4,
                "LIBELLE"      => 117,
            ],
        ],
        // Concepts divers
        "090" => [
            "table"  => "c_compatexotm",
            "start"  => 7,
            "occu"   => 60,
            "fields" => [
                "CODE1" => 1,
                "CODE2" => 1,
            ],
        ],
        "091" => [
            "table"  => "c_arborescence",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODEMENU" => 6,
                "CODEPERE" => 6,
                "RANG"     => 6,
                "LIBELLE"  => 100,
            ],
        ],
        "092" => [
            "table"  => "c_notesarborescence",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "CODEMENU" => 6,
                "TYPE"     => 2,
                "TEXTE"    => 100,
            ],
        ],
        "093" => [
            "table"  => "c_uniteoeuvre",
            "start"  => 7,
            "occu"   => 7,
            "fields" => [
                "CODE"      => 3,
                "DATEEFFET" => 8,
                "VALEUR"    => 6,
            ],
        ],
        // Liste des mots (glossaire)
        "099" => [
            "table"  => "g_listemots",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "MOT"        => 50,
                "DEFINITION" => 50,
            ],
        ],
    ];

    /**
     * Tableau regroupant les codes complexes les codes complexes :
     *  - La clé est le type de ligne (101 -> 350)
     *  - Table est le nom de la table correspondant dans la base CCAM de Mediboard
     *  - Start est la valeur à laquelle on commence à découper la ligne
     *  - Occu est le nombre d'enregistrements maximum par ligne
     *  - Rubriques est un tableau associatif qui permet de traiter différement chaque rubrique :
     *    - La clé est le numéro de rubrique
     *    - Le champs 'fields' dont le contenu est un tableau associatif avec :
     *      - La clé est le nom du champs
     *      - Le contenu est la taille du champs
     *
     * Dans les tailles de champs si à la place d'une taille on a "skip|x" alors on ne traitera pas ce champs et on
     * sautera x caractères
     *
     * Si un type utilise plusieurs tables (ou qu'un champ d'une ligne doit être importé dans une autre table), on
     * ajoute une clé
     * {typerubrique} et une fonction pour gérer ce cas de figure.
     */
    public static $complex_types = [
        // Tables principales
        // - Niveau ACTE
        "101"   => [
            "table"     => "p_acte",
            "start"     => 7,
            "occu"      => 1,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "CODE"         => 13,
                        "LIBELLECOURT" => 70,
                        "TYPE"         => 1,
                        "SEXE"         => 1,
                        "DATECREATION" => 8,
                        "DATEFIN"      => 8,
                    ],
                ],
                "02" => [
                    "fields" => [
                        "ASSURANCE1"  => 2,
                        "ASSURANCE2"  => 2,
                        "ASSURANCE3"  => 2,
                        "ASSURANCE4"  => 2,
                        "ASSURANCE5"  => 2,
                        "ASSURANCE6"  => 2,
                        "ASSURANCE7"  => 2,
                        "ASSURANCE8"  => 2,
                        "ASSURANCE9"  => 2,
                        "ASSURANCE10" => 2,
                        "DEPLACEMENT" => 1,
                    ],
                ],
                "03" => [
                    "fields" => [
                        "ARBORESCENCE1"     => 6,
                        "ARBORESCENCE2"     => 6,
                        "ARBORESCENCE3"     => 6,
                        "ARBORESCENCE4"     => 6,
                        "ARBORESCENCE5"     => 6,
                        "ARBORESCENCE6"     => 6,
                        "ARBORESCENCE7"     => 6,
                        "ARBORESCENCE8"     => 6,
                        "ARBORESCENCE9"     => 6,
                        "ARBORESCENCE10"    => 6,
                        "PLACEARBORESCENCE" => 12,
                        "CODESTRUCTURE"     => 13,
                        "CODEPRECEDENT"     => 13,
                        "CODESUIVANT"       => 13,
                    ],
                ],
                "50" => [
                    "fields" => [
                        "LIBELLELONG" => 100,
                    ],
                ],
            ],
        ],
        "110"   => [
            "table"     => "p_acte_infotarif",
            "start"     => 7,
            "occu"      => 1,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "DATEEFFET"       => 8,
                        "DATEARRETE"      => 8,
                        "DATEPUBLICATION" => 8,
                        "REMBOURSEMENT"   => 1,
                        "ENTENTE"         => 1,
                        "EXOTICKET1"      => 1,
                        "EXOTICKET2"      => 1,
                        "EXOTICKET3"      => 1,
                        "EXOTICKET4"      => 1,
                        "EXOTICKET5"      => 1,
                        "PRESCRIPTEUR1"   => 2,
                        "PRESCRIPTEUR2"   => 2,
                        "PRESCRIPTEUR3"   => 2,
                        "PRESCRIPTEUR4"   => 2,
                        "PRESCRIPTEUR5"   => 2,
                        "PRESCRIPTEUR6"   => 2,
                        "PRESCRIPTEUR7"   => 2,
                        "PRESCRIPTEUR8"   => 2,
                        "PRESCRIPTEUR9"   => 2,
                        "PRESCRIPTEUR10"  => 2,
                        "FORFAIT1"        => 1,
                        "FORFAIT2"        => 1,
                        "FORFAIT3"        => 1,
                        "FORFAIT4"        => 1,
                        "FORFAIT5"        => 1,
                        "FORFAIT6"        => 1,
                        "FORFAIT7"        => 1,
                        "FORFAIT8"        => 1,
                        "FORFAIT9"        => 1,
                        "FORFAIT10"       => 1,
                    ],
                ],
            ],
        ],
        "120"   => [
            "table"     => "p_acte_procedure",
            "start"     => 7,
            "occu"      => 8,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "CODEPROCEDURE" => 13,
                    ],
                ],
            ],
        ],
        "130"   => [
            "table"     => "p_acte_incompatibilite",
            "start"     => 7,
            "occu"      => 8,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "INCOMPATIBLE" => 13,
                    ],
                ],
            ],
        ],
        "140"   => [
            "table"     => "p_acte_forfait_cmuc",
            "start"     => 7,
            "occu"      => 30,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "CLE" => 4,
                    ],
                ],
            ],
        ],
        // - Niveau ACTIVITE
        "201"   => [
            "table"     => "p_activite",
            "start"     => 7,
            "occu"      => 1,
            "rubriques" => [
                '01' => [
                    'fields' => [
                        'ACTIVITE' => 1,
                    ],
                ],
            ],
        ],
        "210"   => [
            "table"     => "p_activite_classif",
            "start"     => 7,
            "occu"      => 1,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "DATEEFFET"    => 8,
                        "DATEARRETE"   => 8,
                        "DATEPUBJO"    => 8,
                        "CODEMODIF"    => "skip|10",
                        "CATMED"       => 2,
                        "SPECIALITE1"  => 2,
                        "SPECIALITE2"  => 2,
                        "SPECIALITE3"  => 2,
                        "SPECIALITE4"  => 2,
                        "SPECIALITE5"  => 2,
                        "SPECIALITE6"  => 2,
                        "SPECIALITE7"  => 2,
                        "SPECIALITE8"  => 2,
                        "SPECIALITE9"  => 2,
                        "SPECIALITE10" => 2,
                        "REGROUP"      => 3,
                    ],
                ],
            ],
        ],
        "220"   => [
            "table"     => "p_activite_associabilite",
            "start"     => 7,
            "occu"      => 8,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "ACTEASSO"     => 13,
                        "ACTIVITEASSO" => 1,
                    ],
                ],
            ],
        ],
        // - Niveau PHASE
        "301"   => [
            "table"     => "p_phase",
            "start"     => 7,
            "occu"      => 1,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "PHASE"   => 1,
                        "NBDENTS" => 2,
                        "AGEMIN"  => 3,
                        "AGEMAX"  => 3,
                    ],
                ],
                "50" => [
                    "fields" => [
                        "ICR"      => 4,
                        "CLASSANT" => 1,
                    ],
                ],
            ],
        ],
        "310"   => [
            "table"     => "p_phase_acte",
            "start"     => 7,
            "occu"      => 1,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "DATEEFFET"    => 8,
                        "DATEARRETE"   => 8,
                        "DATEPUBJO"    => 8,
                        "NBSEANCES"    => 2,
                        "UNITEOEUVRE"  => 3,
                        "COEFFUOEUVRE" => 6,
                        "CODEPAIEMENT" => 1,
                        "COEFFDOM1"    => 4,
                        "COEFFDOM2"    => 4,
                        "COEFFDOM3"    => 4,
                        "COEFFDOM4"    => 4,
                        "CHARGESCAB"   => 6,
                    ],
                ],
            ],
        ],
        "320"   => [
            "table"  => "p_phase_pu_base",
            "start"  => 7,
            "occu"   => 13,
            "fields" => [
                "GRILLE" => 3,
                "PU"     => 6,
            ],
        ],
        "350"   => [
            "table"     => "p_phase_acte_comp",
            "start"     => 7,
            "occu"      => 1,
            "rubriques" => [
                "01" => [
                    "fields" => [
                        "DATEEFFET"    => 8,
                        "SCORETRAVMED" => 6,
                        "COUTPRATIQUE" => 6,
                    ],
                ],
            ],
        ],
        "30102" => [
            "table"  => "p_phase_dentsincomp",
            "start"  => 7,
            "occu"   => 60,
            "fields" => [
                "LOCDENT" => 2,
            ],
        ],
        "10152" => [
            "table"  => "p_acte_notes",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "TYPE"  => 2,
                "TEXTE" => 100,
            ],
        ],
        "11001" => [
            "table"  => "p_acte_typeforfait",
            "start"  => 58,
            "occu"   => 10,
            "fields" => [
                "TYPE" => 1,
            ],
        ],
        "21001" => [
            "table"  => "p_activite_modificateur",
            "start"  => 31,
            "occu"   => 10,
            "fields" => [
                "MODIFICATEUR" => 1,
            ],
        ],
        "10151" => [
            "table"  => "p_acte_conditions_generales",
            "start"  => 7,
            "occu"   => 30,
            "fields" => [
                "CODECONDITION" => 4,
            ],
        ],
        "20150" => [
            "table"  => "p_activite_recom_medic",
            "start"  => 7,
            "occu"   => 1,
            "fields" => [
                "NUMERO" => 5,
                "TEXTE"  => 100,
            ],
        ],
        '10180' => [
            'table'  => 'p_acte_extension_pmsi',
            'start'  => 7,
            'occu'   => 1,
            'fields' => [
                'EXTENSIONPMSI' => 2,
                'LIBELLECOURT'  => 70,
                'DATEDEBUT'     => 8,
                'DATEFIN'       => 8,
                'LIBELLELONG'   => 0,
            ],
        ],
        '10181' => [
            'table'  => 'p_acte_extension_pmsi',
            'start'  => 9,
            'occu'   => 1,
            'fields' => [
                'LIBELLELONG' => 100,
            ],
        ],
    ];

    // Dernier code "simple"
    private const CODE_MAX_SIMPLE = '099';
    // Taille du champs "Type"
    private const TYPE_LENGTH = 3;
    // Taille du champs "Rubrique"
    private const RUBRIQUE_LENGTH = 2;
    // Taille du champs "Sequence"
    private const SEQUENCE_LENGTH = 2;

    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var string $file_path */
    protected $file_path;

    /** @var string $dump_path */
    protected $dump_path;

    /** @var string $dump_pmsi_path */
    protected $dump_pmsi_path;

    /** @var resource $fp */
    protected $fp;

    /** @var resource $fp_dump */
    protected $fp_dump;

    /** @var resource $fp_pmsi_dump */
    protected $fp_pmsi_dump;

    /** @var string $line */
    protected $line;

    protected $limit;

    protected $pmsi;

    protected $lines;

    /** @var int $file_pos */
    protected $file_pos;

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $style = new OutputFormatterStyle('blue', null, ['bold']);
        $output->getFormatter()->setStyle('b', $style);

        $style = new OutputFormatterStyle(null, 'red', ['bold']);
        $output->getFormatter()->setStyle('error', $style);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('ox-convert:ccam')
            ->setDescription('Convert CCAM database to MySQL dump')
            ->setHelp('CCAM NX file to MySQL dump converter')
            ->addOption(
                'file-path',
                'f',
                InputOption::VALUE_REQUIRED,
                'CCAM NX file path'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                "Number of actes to extract at each iteration",
                1000
            )
            ->addOption(
                'pmsi',
                'p',
                InputOption::VALUE_OPTIONAL,
                "Indicate if the file contains PMSI extensions",
                false
            );
    }

    /**
     * @return void
     * @throws Exception
     *
     */
    protected function getParams(): void
    {
        $this->file_path = $this->input->getOption('file-path');
        $this->limit     = $this->input->getOption('limit');
        $this->pmsi      = ($this->input->getOption('pmsi') !== false);

        if (!is_file($this->file_path) || !is_readable($this->file_path)) {
            throw new Exception("Cannot read file {$this->file_path}");
        }
    }

    /**
     * Output timed text
     *
     * @param string $text Text to print
     *
     * @return void
     */
    protected function out(string $text): void
    {
        $this->output->writeln(CMbDT::strftime("[%Y-%m-%d %H:%M:%S]") . " - $text");
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input  = $input;
        $this->output = $output;

        $this->getParams();

        // Open the two files (input/output)
        $this->fp = fopen($this->file_path, 'r');

        if (!$this->fp) {
            throw new Exception("Cannot read file {$this->file_path}");
        }

        $this->dump_path = tempnam(__DIR__ . '/../../../tmp', 'ccam_');
        $this->fp_dump   = fopen($this->dump_path, 'w');

        if (!$this->fp_dump) {
            throw new Exception("Cannot write to file {$this->dump_path}");
        }

        if ($this->pmsi) {
            $this->dump_pmsi_path = tempnam(__DIR__ . '/../../../tmp', 'ccam_pmsi_');
            $this->fp_pmsi_dump   = fopen($this->dump_pmsi_path, 'w');

            if (!$this->fp_pmsi_dump) {
                throw new Exception("Cannot write to file {$this->dump_pmsi_path}");
            }
        }

        $first_line = $this->readLine();
        if (
            !$first_line || substr($first_line, 0, self::TYPE_LENGTH) !== '000'
            || strpos(substr($first_line, 41, 6), 'CCAM') === false
        ) {
            throw new Exception("File is not a NX CCAM file");
        }

        $this->extractSimpleLines();

        while (($nb = $this->extractComplexLines()) >= $this->limit) {
            $this->out("$nb actes extracted.");
        }

        $this->out("$nb actes extracted.");

        fclose($this->fp_dump);
        fclose($this->fp);

        $this->storeToArchive();

        $this->out("Extraction ended");

        return self::SUCCESS;
    }

    /**
     * Read the next line from the file
     *
     * @return string
     */
    protected function readLine(): ?string
    {
        $line = fgets($this->fp);
        if ($line === false) {
            $line = null;
        }
        return $line;
    }

    /**
     * Sanitize the values
     *
     * @param string $values Values to sanitize
     *
     * @return string
     */
    protected function sanitizeValues(string $values): string
    {
        $values = trim(str_replace(["\\", "'"], ["\\\\", "\'"], $values));
        if ($values === '') {
            $values = "NULL";
        }

        return $values;
    }

    /**
     * Extract the simple lines (001 -> 099)
     *
     * @return void
     */
    protected function extractSimpleLines(): void
    {
        $old_type  = null;
        $type      = null;
        $to_insert = [];
        while ($this->line = $this->readLine()) {
            $type     = substr($this->line, 0, self::TYPE_LENGTH);
            $rubrique = substr($this->line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);
            $sequence = substr($this->line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);

            if ($type > self::CODE_MAX_SIMPLE) {
                fseek($this->fp, $this->file_pos);
                break;
            }

            if (!array_key_exists($type, self::$simple_types)) {
                continue;
            }

            switch ($type) {
                case "051":
                case "081":
                case "091":
                    $long_field = 'LIBELLE';
                    break;
                case "092":
                    $long_field = 'TEXTE';
                    break;
                case "099":
                    $long_field = 'DEFINITION';
                    break;
                default:
                    $long_field = null;
                    break;
            }

            $old_type = ($old_type !== null) ? $old_type : $type;
            if ($old_type != $type && $to_insert) {
                $this->writeInsert($old_type, $to_insert);
                $to_insert = [];
                $old_type  = $type;
            }

            $values = $this->convertCode(
                $type,
                self::$simple_types[$type]['start'],
                self::$simple_types[$type]['occu'],
                $rubrique,
                $sequence,
                $long_field
            );

            if ($values) {
                $to_insert[] = implode(",", $values);
            }
            $this->file_pos = ftell($this->fp);
        }

        // Write the last insert
        if ($old_type && $to_insert) {
            $this->writeInsert($old_type, $to_insert);
        }

        $this->out('Codes 001 to 099 extracted');
    }

    /**
     * Convert a CCAM line into a SQL INSERT
     *
     * @param string $type       Type of the line
     * @param int    $start      Start at char n°
     * @param int    $nb_occu    Number of occurences
     * @param string $rubrique   Rubrique of the line
     * @param string $sequence   Sequence of the line
     * @param string $long_field Name of the long field
     *
     * @return array
     */
    protected function convertCode(
        string $type,
        int $start,
        int $nb_occu,
        string $rubrique = null,
        string $sequence = null,
        string $long_field = null
    ): array {
        // Values will contain each line to insert
        $values   = [];
        $no_field = 0;

        for ($i = 0; $i < $nb_occu; $i++) {
            // If an offset is needed
            $line  = [];
            $error = 0;

            // For each field of the table
            foreach (self::$simple_types[$type]['fields'] as $_field_name => $_size) {
                $content = substr($this->line, $start, $_size);

                // Check how many fields are sets to 0
                if (!trim($content) || $content == str_pad('', $_size, 0)) {
                    $error++;
                }

                $content = trim($this->sanitizeValues($content));
                if ($content === 'NULL') {
                    $line[$_field_name] = $content;
                } elseif ($long_field === null || $long_field != $_field_name) {
                    $line[$_field_name] = "'$content'";
                } else {
                    $line[$_field_name] = "'$content";
                }
                $start += $_size;
            }
            // If too many errors ignore the line
            if (count(self::$simple_types[$type]['fields']) - 1 < $error) {
                continue;
            }

            if ($long_field) {
                $line = $this->getNextSequence($line, $type, $rubrique, $sequence, $long_field);
            }

            $values[] = "(" . implode(', ', $line) . ")";
        }

        if (!$values || empty($values) || $no_field == $nb_occu) {
            return [];
        }

        return $values;
    }

    /**
     * Write an insert instruction to the file
     *
     * @param string $type   Type of the line
     * @param array  $values Values to insert
     *
     * @return void
     */
    protected function writeInsert(string $type, array $values): void
    {
        $table = self::$simple_types[$type]["table"];
        if (!isset(self::$simple_types[$type]['fields']) || !is_array(self::$simple_types[$type]['fields'])) {
            return;
        }
        $fields = implode(', ', array_keys(self::$simple_types[$type]['fields']));
        $query  = "INSERT INTO $table ($fields) VALUES ";

        $query .= implode(',', $values) . ";\n";
        fwrite($this->fp_dump, $query);
    }

    /**
     * @param array  $values     Line for which fields have to be completed
     * @param string $type       Type of line
     * @param string $rubrique   Type of rubrique
     * @param string $sequence   Type of sequence
     * @param string $long_field Name of the long field
     *
     * @return array
     */
    protected function getNextSequence(
        array $values,
        string $type,
        string $rubrique,
        string $sequence,
        string $long_field
    ): array {
        $this->file_pos = ftell($this->fp);
        while ($this->line = $this->readLine()) {
            $new_type     = substr($this->line, 0, self::TYPE_LENGTH);
            $new_rubrique = substr($this->line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);
            $new_sequence = substr($this->line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);

            if (
                $new_type != $type || $new_rubrique != $rubrique
                || $new_sequence == $sequence || $new_sequence == '01'
            ) {
                break;
            }

            $start = self::$simple_types[$type]['start'];
            foreach (self::$simple_types[$type]['fields'] as $_field => $_size) {
                if ($_field != $long_field) {
                    $start += $_size;
                    continue;
                }

                $values[$long_field] .= $this->sanitizeValues(substr($this->line, $start, $_size));
            }
            $this->file_pos = ftell($this->fp);
        }

        if (strpos($values[$long_field], "'") !== false) {
            $values[$long_field] .= "'";
        }

        fseek($this->fp, $this->file_pos);

        return $values;
    }

    /**
     * Extract the complex lines (codes 101 to 350)
     *
     * @return int
     * @todo   Reduce cyclomatic complexity
     */
    protected function extractComplexLines(): int
    {
        $i              = 0;
        $this->lines    = [];
        $this->file_pos = ftell($this->fp);

        // Loop over actes
        while ($this->line = $this->readLine()) {
            if ($i >= $this->limit) {
                break;
            }
            $type     = substr($this->line, 0, self::TYPE_LENGTH);
            $rubrique = substr($this->line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);
            $sequence = substr($this->line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);
            if ($type != '101' || $rubrique != '01' || $sequence != '01') {
                continue;
            }

            $acte = $this->extractCode101();
            if (!array_key_exists($type, $this->lines)) {
                $this->lines[$type] = [];
            }
            $this->lines[$type][] = $acte;

            $code_acte           = $acte['CODE'];
            $date_effet_acte     = '00000000';
            $date_effet_activite = '00000000';
            $date_effet_phase    = '00000000';
            $code_activite       = '';
            $code_phase          = '';

            $this->file_pos = ftell($this->fp);
            while ($this->line = $this->readLine()) {
                $type     = substr($this->line, 0, self::TYPE_LENGTH);
                $rubrique = substr($this->line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);

                // New acte to import
                if ($type === '101' && $rubrique === '01') {
                    fseek($this->fp, $this->file_pos);
                    break;
                }

                if (array_key_exists($type, self::$complex_types) && !array_key_exists($type, $this->lines)) {
                    $this->lines[$type] = [];
                }

                switch ($type) {
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case '110':
                        $date_effet_acte = $this->sanitizeValues(
                            substr(
                                $this->line,
                                self::$complex_types[$type]['start'],
                                self::$complex_types[$type]['rubriques'][$rubrique]['fields']['DATEEFFET']
                            )
                        );
                    case '120':
                    case '130':
                        $this->extractCodeActe($code_acte, $date_effet_acte, $type, $rubrique);
                        break;

                    case '140':
                        $this->extractCodeActe140($code_acte, $date_effet_acte, $type, $rubrique);
                        break;

                    case '201':
                        if ($rubrique == '01') {
                            $code_activite = $this->sanitizeValues(
                                substr(
                                    $this->line,
                                    self::$complex_types[$type]['start'],
                                    self::$complex_types[$type]['rubriques'][$rubrique]['fields']['ACTIVITE']
                                )
                            );

                            $this->lines[$type][] = ['CODEACTE' => $code_acte, 'ACTIVITE' => $code_activite];
                            break;
                        }

                        if ($rubrique == '50') {
                            $this->extractCodeActivite20150($code_acte, $code_activite);
                            break;
                        }
                        break;

                    case '210':
                        $date_effet_activite = $this->sanitizeValues(
                            substr(
                                $this->line,
                                self::$complex_types[$type]['start'],
                                self::$complex_types[$type]['rubriques'][$rubrique]['fields']['DATEEFFET']
                            )
                        );
                        $this->extractCodeActivite($code_acte, $code_activite, $type, $rubrique);
                        break;

                    case '220':
                        $this->extractCodeActivite220(
                            $code_acte,
                            $code_activite,
                            $date_effet_activite,
                            $type,
                            $rubrique
                        );
                        break;

                    case '301':
                        $code_phase = $this->extractPhase($code_acte, $code_activite, $type, $rubrique);
                        break;

                    case '310':
                        $date_effet_phase = $this->sanitizeValues(
                            substr(
                                $this->line,
                                self::$complex_types[$type]['start'],
                                self::$complex_types[$type]['rubriques'][$rubrique]['fields']['DATEEFFET']
                            )
                        );
                        $this->extractCodePhase310($code_acte, $code_activite, $code_phase, $type, $rubrique);
                        break;

                    case '320':
                        $this->extractCodePhase320(
                            $code_acte,
                            $code_activite,
                            $code_phase,
                            $date_effet_phase,
                            $type,
                            $this->line
                        );
                        break;

                    case '350':
                        $this->extractCodePhase350($code_acte, $code_activite, $code_phase, $type, $rubrique);
                        break;
                    default:
                        // Type is not handled
                        break;
                }

                $this->file_pos = ftell($this->fp);
            }

            $i++;
        }

        fseek($this->fp, $this->file_pos);
        $this->writeComplexInsert();

        return $i;
    }

    /**
     * Extract the 101 code corresponding to an acte
     *
     * @return array
     */
    protected function extractCode101(): array
    {
        $acte = [];
        do {
            $type     = substr($this->line, 0, self::TYPE_LENGTH);
            $rubrique = substr($this->line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);
            $sequence = substr($this->line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);

            if ($type != '101') {
                fseek($this->fp, $this->file_pos);
                break;
            }

            if ($rubrique == '51') {
                $this->file_pos = ftell($this->fp);
                $this->extractCode10151($acte['CODE']);
                continue;
            }

            if ($rubrique == '52') {
                $this->file_pos = ftell($this->fp);
                $this->extractCode10152($acte['CODE']);
                continue;
            }

            if ($rubrique == '80') {
                $this->file_pos = ftell($this->fp);
                $this->extractCode10180($acte['CODE']);
                continue;
            }

            if (
                !array_key_exists($type, self::$complex_types)
                || !array_key_exists($rubrique, self::$complex_types[$type]['rubriques'])
            ) {
                $this->file_pos = ftell($this->fp);
                continue;
            }

            $start = self::$complex_types[$type]['start'];
            foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field_name => $_size) {
                $acte[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
                $start              += $_size;
            }

            $this->file_pos = ftell($this->fp);
            if ($rubrique == '50') {
                $acte = $this->getNextComplexSequence($acte, $type, $rubrique, $sequence, 'LIBELLELONG');
            }
        } while ($this->line = $this->readLine());

        return $acte;
    }

    /**
     * Extract the code 101 rubrique 51
     *
     * @param string $code_acte Acte code
     *
     * @return void
     */
    protected function extractCode10151(string $code_acte): void
    {
        if (!array_key_exists('10151', $this->lines)) {
            $this->lines['10151'] = [];
        }

        $start = self::$complex_types['10151']['start'];
        for ($i = 0; $i < self::$complex_types['10151']['occu']; $i++) {
            $import_line = [
                "CODEACTE" => $code_acte,
            ];

            foreach (self::$complex_types['10151']['fields'] as $_field_name => $_size) {
                $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
                $start                     += $_size;
            }

            if ($import_line['CODECONDITION'] != '0000') {
                $this->lines['10151'][] = $import_line;
            }
        }
    }

    /**
     * Extract the code 101 rubrique 52
     *
     * @param string $code_acte Acte code
     *
     * @return void
     */
    protected function extractCode10152(string $code_acte): void
    {
        if (!array_key_exists('10152', $this->lines)) {
            $this->lines['10152'] = [];
        }

        $start    = self::$complex_types['10152']['start'];
        $sequence = substr($this->line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);

        $import_line = [
            'CODEACTE' => $code_acte,
        ];

        foreach (self::$complex_types['10152']['fields'] as $_field_name => $_size) {
            $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
            $start                     += $_size;
        }

        while ($this->line = $this->readLine()) {
            $type         = substr($this->line, 0, self::TYPE_LENGTH);
            $rubrique     = substr($this->line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);
            $new_sequence = substr($this->line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);

            $start = self::$complex_types['10152']['start'];

            if ($type != '101' || $rubrique != '52' || $new_sequence <= $sequence) {
                fseek($this->fp, $this->file_pos);
                break;
            }

            foreach (self::$complex_types['10152']['fields'] as $_field_name => $_size) {
                if ($_field_name == 'TEXTE') {
                    $import_line[$_field_name] .= $this->sanitizeValues(substr($this->line, $start, $_size));
                }
                $start += $_size;
            }

            $this->file_pos = ftell($this->fp);
        }

        $this->lines['10152'][] = $import_line;
        fseek($this->fp, $this->file_pos);
    }

    /**
     * Extract the data of the PMSI extensions (code 101, rubrique 80)
     *
     * @param string $code_acte The act code
     *
     * @return void
     */
    protected function extractCode10180(string $code_acte): void
    {
        if (!array_key_exists('10180', $this->lines)) {
            $this->lines['10180'] = [];
        }

        $start = self::$complex_types['10180']['start'];

        $import_line = [
            'CODEACTE' => $code_acte,
        ];

        /* Traitement des données de la rubrique 101 80 */
        foreach (self::$complex_types['10180']['fields'] as $_field_name => $_size) {
            if ($_size) {
                $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
                $start                     += $_size;
            }
        }

        /* Traitement des données de la rubrique 101 81 (libellé long de l'extension pmsi) */
        $sequence                   = 0;
        $import_line['LIBELLELONG'] = '';
        while ($this->line = $this->readLine()) {
            $type         = substr($this->line, 0, self::TYPE_LENGTH);
            $rubrique     = substr($this->line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);
            $new_sequence = substr($this->line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);

            $start = self::$complex_types['10181']['start'];

            if ($type != '101' || $rubrique != '81' || $new_sequence <= $sequence) {
                fseek($this->fp, $this->file_pos);
                break;
            }

            foreach (self::$complex_types['10181']['fields'] as $_field_name => $_size) {
                if ($_field_name == 'LIBELLELONG') {
                    $import_line[$_field_name] .= $this->sanitizeValues(substr($this->line, $start, $_size));
                }
                $start += $_size;
            }

            $this->file_pos = ftell($this->fp);
        }

        $this->lines['10180'][] = $import_line;
        fseek($this->fp, $this->file_pos);
    }

    /**
     * Extract the informations about the acte
     *
     * @param string $code_acte  Acte code
     * @param string $date_effet DATEEFFET field
     * @param string $type       Line type
     * @param string $rubrique   Line rubrique
     *
     * @return void
     */
    protected function extractCodeActe(string $code_acte, string $date_effet, string $type, string $rubrique): void
    {
        $start = self::$complex_types[$type]['start'];
        for ($i = 0; $i < self::$complex_types[$type]['occu']; $i++) {
            $import_line = [
                'CODEACTE'  => $code_acte,
                'DATEEFFET' => $date_effet,
            ];
            $nb_errors   = 0;
            foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field_name => $_size) {
                $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
                $start                     += $_size;
                if ($import_line[$_field_name] == '' || $import_line[$_field_name] === 'NULL') {
                    $nb_errors++;
                }
            }

            if ($nb_errors < count(array_keys(self::$complex_types[$type]['rubriques'][$rubrique]['fields']))) {
                $this->lines[$type][] = $import_line;

                if (!array_key_exists('11001', $this->lines)) {
                    $this->lines['11001'] = [];
                }

                for ($t = 1; $t <= 10; $t++) {
                    if (array_key_exists("FORFAIT$t", $import_line) && $import_line["FORFAIT$t"] != '') {
                        $this->lines['11001'][] = [
                            'CODEACTE'  => $code_acte,
                            'DATEEFFET' => $date_effet,
                            'TYPE'      => $import_line["FORFAIT$t"],
                        ];
                    }
                }
            }
        }
    }

    /**
     * Extract the code 140
     *
     * @param string $code_acte  Acte code
     * @param string $date_effet Field DATEEFFET
     * @param string $type       Line type
     * @param string $rubrique   Line rubrique
     *
     * @return void
     */
    protected function extractCodeActe140(string $code_acte, string $date_effet, string $type, string $rubrique): void
    {
        if (!array_key_exists($type, $this->lines)) {
            $this->lines[$type] = [];
        }

        $start = self::$complex_types[$type]['start'];
        for ($i = 0; $i < self::$complex_types[$type]['occu']; $i++) {
            $import_line = [
                'CODEACTE'  => $code_acte,
                'DATEEFFET' => $date_effet,
            ];

            foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field_name => $_size) {
                $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
                $start                     += $_size;
            }

            if ($import_line['CLE'] != '0000') {
                $this->lines[$type][] = $import_line;
            }
        }
    }

    /**
     * Extract the code 110 rubrique 01, type of forfait
     *
     * @param string $code_acte  Acte code
     * @param string $date_effet Field DATEEFFET
     *
     * @return void
     */
    protected function extractCodeTypeForfait(string $code_acte, string $date_effet): void
    {
        if (!array_key_exists('11001', $this->lines)) {
            $this->lines['11001'] = [];
        }

        $start = self::$complex_types['11001']['start'];
        for ($i = 0; $i < self::$complex_types['11001']['occu']; $i++) {
            $import_line = [
                "CODEACTE"  => $code_acte,
                "DATEEFFET" => $date_effet,
            ];

            foreach (self::$complex_types['11001']['fields'] as $_field_name => $_size) {
                $import_line[$_field_name] = substr($this->line, $start, $_size);
                $start                     += $_size;
            }

            $this->lines['11001'][] = $import_line;
        }
    }

    /**
     * Extract the informations about activity
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     * @param string $type          Line type
     * @param string $rubrique      Line rubrique
     *
     * @return void
     */
    protected function extractCodeActivite(
        string $code_acte,
        string $code_activite,
        string $type,
        string $rubrique
    ): void {
        $start = self::$complex_types[$type]['start'];
        for ($i = 0; $i < self::$complex_types[$type]['occu']; $i++) {
            $import_line = [
                'CODEACTE' => $code_acte,
                'ACTIVITE' => $code_activite,
            ];

            /**
             * @var string $_field_name
             * @var int    $_size
             */
            foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field_name => $_size) {
                if (is_string($_size)) {
                    $skip = explode('|', $_size);
                    if (strpos($skip[0], 'skip') !== false) {
                        $start += $skip[1];
                        continue;
                    }
                }

                $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
                $start                     += $_size;
            }

            $this->lines[$type][] = $import_line;
            $this->extractCodeModifActivite($code_acte, $code_activite, $import_line['DATEEFFET']);
        }
    }

    /**
     * Extract the code 201 rubrique 50
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     *
     * @return void
     */
    protected function extractCodeActivite20150(string $code_acte, string $code_activite): void
    {
        if (!array_key_exists('20150', $this->lines)) {
            $this->lines['20150'] = [];
        }

        $import_line = [
            "CODEACTE" => $code_acte,
            "ACTIVITE" => $code_activite,
        ];

        $start = self::$complex_types['20150']['start'];
        foreach (self::$complex_types['20150']['fields'] as $_field_name => $_size) {
            $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
            $start                     += $_size;
        }

        $this->file_pos = ftell($this->fp);
        while ($line = $this->readLine()) {
            $new_type     = substr($line, 0, self::TYPE_LENGTH);
            $new_rubrique = substr($line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);
            $new_sequence = substr($line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);

            if ($new_type != '201' || $new_rubrique != '50' || $new_sequence == '01') {
                fseek($this->fp, $this->file_pos);
                break;
            }

            $import_line['TEXTE'] .= $this->sanitizeValues(
                substr(
                    $line,
                    self::$complex_types['20150']['start'] + self::$complex_types['20150']['fields']['NUMERO'],
                    self::$complex_types['20150']['fields']['TEXTE']
                )
            );

            $this->file_pos = ftell($this->fp);
        }

        $this->lines['20150'][] = $import_line;
    }

    /**
     * Extract the activity modifier for the line 210 rubrique 01
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     * @param string $date_effet    Field DATEEFFET
     *
     * @return void
     */
    protected function extractCodeModifActivite(string $code_acte, string $code_activite, string $date_effet): void
    {
        if (!array_key_exists('21001', $this->lines)) {
            $this->lines['21001'] = [];
        }

        $start = self::$complex_types['21001']['start'];
        for ($i = 0; $i < self::$complex_types['21001']['occu']; $i++) {
            $import_line = [
                'CODEACTE'     => $code_acte,
                'CODEACTIVITE' => $code_activite,
                'DATEEFFET'    => $date_effet,
            ];

            $modificateur = $this->sanitizeValues(substr($this->line, $start, 1));
            if (!$modificateur) {
                break;
            }

            $import_line['MODIFICATEUR'] = $modificateur;

            $start += 1;

            $idx                        = $code_acte . $code_activite . $date_effet . $modificateur;
            $this->lines['21001'][$idx] = $import_line;
        }
    }

    /**
     * Extract the code 220 relative to an activity
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     * @param string $date_effet    DATEEFFET field
     * @param string $type          Line type
     * @param string $rubrique      Line rubrique
     *
     * @return void
     */
    protected function extractCodeActivite220(
        string $code_acte,
        string $code_activite,
        string $date_effet,
        string $type,
        string $rubrique
    ): void {
        $start = self::$complex_types[$type]['start'];
        for ($i = 0; $i < self::$complex_types[$type]['occu']; $i++) {
            $occurence_size = 0;

            foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_size) {
                $occurence_size += $_size;
            }

            $start_regle =
                $occurence_size * self::$complex_types[$type]['occu'] + self::$complex_types[$type]['start'] + $i;

            $import_line = [
                'CODEACTE'  => $code_acte,
                'ACTIVITE'  => $code_activite,
                'DATEEFFET' => $date_effet,
                'REGLE'     => substr($this->line, $start_regle, 1),
            ];

            $nb_errors = 0;
            foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field_name => $_size) {
                $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
                $start                     += $_size;

                if ($import_line[$_field_name] == '' || $import_line[$_field_name] === 'NULL') {
                    $nb_errors++;
                }
            }

            if ($nb_errors < count(self::$complex_types[$type]['rubriques'][$rubrique]['fields'])) {
                $this->lines[$type][] = $import_line;
            }
        }
    }

    /**
     * Extract the phase
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     * @param string $type          Line type
     * @param string $rubrique      Line rubrique
     *
     * @return string
     */
    protected function extractPhase(string $code_acte, string $code_activite, string $type, string $rubrique): string
    {
        $start = self::$complex_types[$type]['start'];

        $phase = [
            'CODEACTE' => $code_acte,
            'ACTIVITE' => $code_activite,
            'ICR'      => '0000',
            'CLASSANT' => '',
        ];

        foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field_name => $_size) {
            $phase[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
            $start               += $_size;
        }

        $this->file_pos = ftell($this->fp);
        while ($line = $this->readLine()) {
            $new_type     = substr($line, 0, self::TYPE_LENGTH);
            $new_rubrique = substr($line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);

            if ($new_type == '301' && $new_rubrique == '02') {
                $this->file_pos = ftell($this->fp);
                $this->extractCodePhase30102($code_acte, $code_activite, $phase['PHASE'], $line);
                continue;
            }

            if ($new_type != '301' || $new_rubrique != '50') {
                fseek($this->fp, $this->file_pos);
                break;
            }

            $start = self::$complex_types[$type]['start'];
            foreach (self::$complex_types[$new_type]['rubriques'][$new_rubrique]['fields'] as $_field_name => $_size) {
                $phase[$_field_name] = $this->sanitizeValues(substr($line, $start, $_size));
                $start               += $_size;
            }

            $this->file_pos = ftell($this->fp);
        }

        $this->lines[$type][] = $phase;

        return $phase['PHASE'];
    }

    /**
     * Extract the code 301 rubrique 02
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     * @param string $code_phase    Phase code
     * @param string $line          Line to parse (different from $this->line)
     *
     * @return void
     */
    protected function extractCodePhase30102(
        string $code_acte,
        string $code_activite,
        string $code_phase,
        string $line
    ): void {
        $start = self::$complex_types['30102']['start'];
        if (!array_key_exists('30102', $this->lines)) {
            $this->lines['30102'] = [];
        }

        for ($i = 0; $i < self::$complex_types['30102']['occu']; $i++) {
            $dent = $this->sanitizeValues(substr($line, $start, self::$complex_types['30102']['fields']['LOCDENT']));

            //var_dump($code_acte, $dent);
            if ($dent && $dent != '00') {
                $this->lines['30102'][] = [
                    'CODEACTE' => $code_acte,
                    'ACTIVITE' => $code_activite,
                    'PHASE'    => $code_phase,
                    'LOCDENT'  => $dent,
                ];
            }

            $start += self::$complex_types['30102']['fields']['LOCDENT'];
        }
    }

    /**
     * Extract the code 310 (and 320) relative to a phase
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     * @param string $code_phase    Phase code
     * @param string $type          Line type
     * @param string $rubrique      Line rubrique
     *
     * @return void
     */
    protected function extractCodePhase310(
        string $code_acte,
        string $code_activite,
        string $code_phase,
        string $type,
        string $rubrique
    ): void {
        $start = self::$complex_types[$type]['start'];

        $import_line = [
            'CODEACTE'         => $code_acte,
            'ACTIVITE'         => $code_activite,
            'PHASE'            => $code_phase,
            'PRIXUNITAIRE_G01' => '000000',
            'PRIXUNITAIRE_G02' => '000000',
            'PRIXUNITAIRE_G03' => '000000',
            'PRIXUNITAIRE_G04' => '000000',
            'PRIXUNITAIRE_G05' => '000000',
            'PRIXUNITAIRE_G06' => '000000',
            'PRIXUNITAIRE_G07' => '000000',
            'PRIXUNITAIRE_G08' => '000000',
            'PRIXUNITAIRE_G09' => '000000',
            'PRIXUNITAIRE_G10' => '000000',
            'PRIXUNITAIRE_G11' => '000000',
            'PRIXUNITAIRE_G12' => '000000',
            'PRIXUNITAIRE_G13' => '000000',
            'PRIXUNITAIRE_G14' => '000000',
            'PRIXUNITAIRE_G15' => '000000',
            'PRIXUNITAIRE_G16' => '000000',
        ];

        foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field_name => $_size) {
            $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
            $start                     += $_size;
        }

        $this->file_pos = ftell($this->fp);
        while ($line = $this->readLine()) {
            $new_type = substr($line, 0, self::TYPE_LENGTH);
            if ($new_type !== '320') {
                fseek($this->fp, $this->file_pos);
                break;
            }

            $start = 7;
            for ($i = 0; $i < 13; $i++) {
                $grille = $this->sanitizeValues(substr(substr($line, $start, 3), 1));
                if ($grille == '00') {
                    $start += 9;
                    continue;
                }
                $start                                += 3;
                $import_line["PRIXUNITAIRE_G$grille"] = $this->sanitizeValues(substr($line, $start, 6));
                $start                                += 6;
            }

            $this->extractCodePhase320(
                $code_acte,
                $code_activite,
                $code_phase,
                $import_line['DATEEFFET'],
                $new_type,
                $line
            );
            $this->file_pos = ftell($this->fp);
        }

        $this->lines[$type][] = $import_line;
    }

    /**
     * Extract the code 320
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     * @param string $code_phase    Phase code
     * @param string $date_effet    Field DATEEFFET
     * @param string $type          Line type
     * @param string $line          Line to extract
     *
     * @return void
     */
    protected function extractCodePhase320(
        string $code_acte,
        string $code_activite,
        string $code_phase,
        string $date_effet,
        string $type,
        string $line
    ): void {
        if (!array_key_exists($type, $this->lines)) {
            $this->lines[$type] = [];
        }

        $start = self::$complex_types[$type]['start'];
        for ($i = 0; $i < self::$complex_types[$type]['occu']; $i++) {
            $import_line = [
                'CODEACTE'  => $code_acte,
                'ACTIVITE'  => $code_activite,
                'PHASE'     => $code_phase,
                'DATEEFFET' => $date_effet,
            ];

            foreach (self::$complex_types[$type]['fields'] as $_field_name => $_size) {
                $import_line[$_field_name] = $this->sanitizeValues(substr($line, $start, $_size));
                $start                     += $_size;
            }

            if ($import_line['PU'] != '000000' || $import_line['GRILLE'] != '000') {
                $this->lines[$type][] = $import_line;
            }
        }
    }

    /**
     * Extract the code 350 relative to a phase
     *
     * @param string $code_acte     Acte code
     * @param string $code_activite Activity code
     * @param string $code_phase    Phase code
     * @param string $type          Line type
     * @param string $rubrique      Line rubrique
     *
     * @return void
     */
    protected function extractCodePhase350(
        string $code_acte,
        string $code_activite,
        string $code_phase,
        string $type,
        string $rubrique
    ): void {
        $start = self::$complex_types[$type]['start'];

        $import_line = [
            'CODEACTE' => $code_acte,
            'ACTIVITE' => $code_activite,
            'PHASE'    => $code_phase,
        ];

        foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field_name => $_size) {
            $import_line[$_field_name] = $this->sanitizeValues(substr($this->line, $start, $_size));
            $start                     += $_size;
        }

        $this->lines[$type][] = $import_line;
    }

    /**
     * Get the next line for the "long_libelle" fields
     *
     * @param array  $fields       Last line to complete
     * @param string $type         Type of line
     * @param string $rubrique     Line rubrique
     * @param string $sequence     Line sequence
     * @param string $long_libelle Name of the long field
     *
     * @return array
     */
    protected function getNextComplexSequence(
        array $fields,
        string $type,
        string $rubrique,
        string $sequence,
        string $long_libelle = null
    ): array {
        if (!$long_libelle) {
            return $fields;
        }

        $idx = ftell($this->fp);
        while ($line = $this->readLine()) {
            $new_type     = substr($line, 0, self::TYPE_LENGTH);
            $new_rubrique = substr($line, self::TYPE_LENGTH, self::RUBRIQUE_LENGTH);
            $new_sequence = substr($line, self::TYPE_LENGTH + self::RUBRIQUE_LENGTH, self::SEQUENCE_LENGTH);

            if (
                $new_type != $type || $new_rubrique != $rubrique
                || $new_sequence == $sequence || $new_sequence == '01'
            ) {
                break;
            }

            $start = self::$complex_types[$type]['start'];
            foreach (self::$complex_types[$type]['rubriques'][$rubrique]['fields'] as $_field => $_size) {
                if ($_field != $long_libelle) {
                    $start += $_size;
                    continue;
                }

                $fields[$long_libelle] .= $this->sanitizeValues(substr($line, $start, $_size));
            }

            $idx = ftell($this->fp);
        }

        fseek($this->fp, $idx);

        return $fields;
    }

    /**
     * Write the insert statements for complex types (101 - 350)
     *
     * @return void
     */
    protected function writeComplexInsert(): void
    {
        foreach ($this->lines as $_type => $_values) {
            $file = $this->fp_dump;
            if ($_type == '10180' && $this->pmsi) {
                $file = $this->fp_pmsi_dump;
            }

            $i     = 0;
            $l     = 0;
            $total = count($_values);

            $type_lines = [];

            $query = '';
            foreach ($_values as $_line) {
                if ($i == 0) {
                    $query = "INSERT INTO " . self::$complex_types[$_type]['table']
                        . " (" . $this->createQueryHeader(array_keys($_line)) . ") VALUES ";
                }
                $i++;
                $l++;

                foreach ($_line as $index => $value) {
                    if ($value !== 'NULL') {
                        $_line[$index]  = "'$value'";
                    }
                }
                $type_lines[] = "(" . implode(", ", $_line) . ")";

                if ($i == 5000 || $l >= $total) {
                    if ($type_lines) {
                        $query .= implode(", ", $type_lines) . ";\n";
                        fwrite($file, $query);
                        $type_lines = [];
                    }
                    $i = 0;
                }
            }
        }
    }

    /**
     * Create the header for the query
     *
     * @param array $headers Fields to add to the header
     *
     * @return string
     */
    protected function createQueryHeader(array $headers): string
    {
        return implode(', ', $headers);
    }

    /**
     * Store the dump to an archive tar.gz if possible or zip
     *
     * @return void
     * @throws Exception
     *
     */
    protected function storeToArchive(): void
    {
        $zip_path = __DIR__ . '/../../../modules/dPccam/base/';
        $tmp_path = __DIR__ . '/../../../tmp';

        // Création du répertoire tmp/ccam s'il n'existe pas déjà
        if (!file_exists($tmp_path . '/ccam')) {
            mkdir($tmp_path . '/ccam');
        }

        // Extraction de l'archive actuellement présente pour récupérer tables.sql
        if (file_exists($zip_path . 'ccam.tar.gz')) {
            CMbPath::extract($zip_path . 'ccam.tar.gz', $tmp_path . '/ccam');
        } elseif (file_exists($zip_path . 'ccam.zip')) {
            CMbPath::extract($zip_path . 'ccam.zip', $tmp_path . '/ccam');
        } else {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new Exception('No ccam archive');
        }

        // Suppression de l'ancien fichier base.sql
        if (file_exists($tmp_path . '/ccam/base.sql')) {
            unlink($tmp_path . '/ccam/base.sql');
        }

        // Déplacement du fichier où on a extrait les données
        rename($this->dump_path, $tmp_path . '/ccam/base.sql');

        if ($this->pmsi) {
            // Suppression de l'ancien fichier pmsi.sql
            if (file_exists($tmp_path . '/ccam/pmsi.sql')) {
                unlink($tmp_path . '/ccam/pmsi.sql');
            }

            // Déplacement du fichier où on a extrait les données
            rename($this->dump_pmsi_path, $tmp_path . '/ccam/pmsi.sql');
        }

        // Création de la nouvelle archive via tar si possible ou en zip sinon
        $where_is = (stripos(PHP_OS, 'WIN') !== false) ? 'where' : 'which';
        exec("$where_is tar", $tar);
        if ($tar) {
            $cmd = "tar -czf ccam.tar.gz -C {$tmp_path}/ccam ./base.sql ./tables.sql ./basedata.sql ./pmsi.sql";
            exec($cmd, $result);
        } else {
            $zip = new ZipArchive();
            $zip->open($zip_path . 'ccam.zip', ZipArchive::OVERWRITE);
            $zip->addFile($tmp_path . '/ccam/base.sql', 'ccam/base.sql');
            $zip->addFile($tmp_path . '/ccam/tables.sql', 'ccam/tables.sql');
            $zip->addFile($tmp_path . '/ccam/pmsi.sql', 'ccam/pmsi.sql');
            $zip->addFile($tmp_path . '/ccam/basedata.sql', 'ccam/basedata.sql');
            $zip->close();
        }

        // Suppression du répertoire tmp/ccam
        CMbPath::remove($tmp_path . '/ccam', false);
    }
}
