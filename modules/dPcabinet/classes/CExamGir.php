<?php


namespace Ox\Mediboard\Cabinet;


use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Evaluation du GIR (degré de dépendance)
 *
 * @package Ox\Mediboard\Cabinet
 */
class CExamGir extends CMbObject {

  /** @var int */
  public $examgir_id;

  /** @var CSejour */
  public $sejour_id;

  /** @var date */
  public $date;

  /** @var CUser */
  public $creator_id;

  // Variables sous-codées
  /** @var string */
  public $coherence_communication;
  /** @var string */
  public $coherence_comportement;
  /** @var string */
  public $orientation_temps;
  /** @var string */
  public $orientation_espace;
  /** @var string */
  public $toilette_haut;
  /** @var string */
  public $toilette_bas;
  /** @var string */
  public $habillage_haut;
  /** @var string */
  public $habillage_moyen;
  /** @var string */
  public $habillage_bas;
  /** @var string */
  public $alimentation_se_servir;
  /** @var string */
  public $alimentation_manger;
  /** @var string */
  public $elimination_urinaire;
  /** @var string */
  public $elimination_fecale;
  /** @var string */
  public $transferts;
  /** @var string */
  public $deplacements_int;
  /** @var string */
  public $deplacements_ext;
  /** @var string */
  public $alerter;
  /** @var string */
  public $gestion;
  /** @var string */
  public $cuisine;
  /** @var string */
  public $menage;
  /** @var string */
  public $transports;
  /** @var string */
  public $achats;
  /** @var string */
  public $suivi_traitement;
  /** @var string */
  public $activites_tps_libre;

  /** @var int */
  public $score_gir;

  // Codes standard
    /** @var string */
    public $_code_coherence_communication;
    /** @var string */
    public $_code_coherence_comportement;
    /** @var string */
    public $_code_orientation_temps;
    /** @var string */
    public $_code_orientation_espace;
    /** @var string */
    public $_code_toilette_haut;
    /** @var string */
    public $_code_toilette_bas;
    /** @var string */
    public $_code_habillage_haut;
    /** @var string */
    public $_code_habillage_moyen;
    /** @var string */
    public $_code_habillage_bas;
    /** @var string */
    public $_code_alimentation_se_servir;
    /** @var string */
    public $_code_alimentation_manger;
    /** @var string */
    public $_code_elimination_urinaire;
    /** @var string */
    public $_code_elimination_fecale;

    // Codes finaux
    /** @var string */
    public $_final_code_coherence;
    /** @var string */
    public $_final_code_orientation;
    /** @var string */
    public $_final_code_toilette;
    /** @var string */
    public $_final_code_habillage;
    /** @var string */
    public $_final_code_alimentation;
    /** @var string */
    public $_final_code_elimination;
    /** @var string */
    public $_final_code_transferts;
    /** @var string */
    public $_final_code_deplacements_int;
    /** @var string */
    public $_final_code_deplacements_ext;
    /** @var string */
    public $_final_code_alerter;
    /** @var string */
    public $_final_code_gestion;
    /** @var string */
    public $_final_code_cuisine;
    /** @var string */
    public $_final_code_menage;
    /** @var string */
    public $_final_code_transports;
    /** @var string */
    public $_final_code_achats;
    /** @var string */
    public $_final_code_suivi_traitement;
    /** @var string */
    public $_final_code_activites_tps_libre;

  /** @var CSejour */
  public $_ref_sejour;

  /*
   * Liste des différentes variables du formulaire ainsi que leurs sous-variables
   */
  public const VARIABLES_ACTIVITES = [
    'discrim' => [
      'coherence'        => [
        'coherence_communication',
        'coherence_comportement'
      ],
      'orientation'      => [
        'orientation_temps',
        'orientation_espace'
      ],
      'toilette'         => [
        'toilette_haut',
        'toilette_bas'
      ],
      'habillage'        => [
        'habillage_haut',
        'habillage_moyen',
        'habillage_bas'
      ],
      'alimentation'     => [
        'alimentation_se_servir',
        'alimentation_manger'
      ],
      'elimination'      => [
        'elimination_urinaire',
        'elimination_fecale'
      ],
      'transferts'       => [],
      'deplacements_int' => [],
      'deplacements_ext' => [],
      'alerter'          => [],
    ],
    'illus'   => [
      'gestion'             => [],
      'cuisine'             => [],
      'menage'              => [],
      'transports'          => [],
      'achats'              => [],
      'suivi_traitement'    => [],
      'activites_tps_libre' => [],
    ]
  ];

  /*
   * Liste des Groupes Iso Ressources en fonction du rang atteint dans le codage
   */
  public const RANGS_GIR = [
    1 => [1],
    2 => [2, 3, 4, 5, 6, 7],
    3 => [8, 9],
    4 => [10, 11],
    5 => [12],
    6 => [13],
  ];

  /*
   * Grille de codage pour déterminer le rang
   */
  public const GRILLE_RANGS = [
    "A" => [
      "coherence"        => [
        "B" => 0,
        "C" => 2000
      ],
      "orientation"      => [
        "B" => 0,
        "C" => 1200
      ],
      "toilette"         => [
        "B" => 16,
        "C" => 40
      ],
      "habillage"        => [
        "B" => 16,
        "C" => 40
      ],
      "alimentation"     => [
        "B" => 20,
        "C" => 60
      ],
      "elimination"      => [
        "B" => 16,
        "C" => 100
      ],
      "transferts"       => [
        "B" => 120,
        "C" => 800
      ],
      "deplacements_int" => [
        "B" => 32,
        "C" => 200
      ],
      "deplacements_ext" => [
        "B" => 0,
        "C" => 0
      ],
      "alerter"          => [
        "B" => 0,
        "C" => 0
      ],
    ],
    "B" => [
      "coherence"        => [
        "B" => 320,
        "C" => 1500
      ],
      "orientation"      => [
        "B" => 120,
        "C" => 1200
      ],
      "toilette"         => [
        "B" => 16,
        "C" => 40
      ],
      "habillage"        => [
        "B" => 16,
        "C" => 40
      ],
      "alimentation"     => [
        "B" => 0,
        "C" => 60
      ],
      "elimination"      => [
        "B" => 16,
        "C" => 100
      ],
      "transferts"       => [
        "B" => 120,
        "C" => 800
      ],
      "deplacements_int" => [
        "B" => -40,
        "C" => -80
      ],
      "deplacements_ext" => [
        "B" => 0,
        "C" => 0
      ],
      "alerter"          => [
        "B" => 0,
        "C" => 0
      ],
    ],
    "C" => [
      "coherence"        => [
        "B" => 0,
        "C" => 0
      ],
      "orientation"      => [
        "B" => 0,
        "C" => 0
      ],
      "toilette"         => [
        "B" => 16,
        "C" => 40
      ],
      "habillage"        => [
        "B" => 16,
        "C" => 40
      ],
      "alimentation"     => [
        "B" => 20,
        "C" => 60
      ],
      "elimination"      => [
        "B" => 20,
        "C" => 160
      ],
      "transferts"       => [
        "B" => 200,
        "C" => 1000
      ],
      "deplacements_int" => [
        "B" => 40,
        "C" => 400
      ],
      "deplacements_ext" => [
        "B" => 0,
        "C" => 0
      ],
      "alerter"          => [
        "B" => 0,
        "C" => 0
      ],
    ],
    "D" => [
      "coherence"        => [
        "B" => 0,
        "C" => 0
      ],
      "orientation"      => [
        "B" => 0,
        "C" => 0
      ],
      "toilette"         => [
        "B" => 0,
        "C" => 0
      ],
      "habillage"        => [
        "B" => 0,
        "C" => 0
      ],
      "alimentation"     => [
        "B" => 200,
        "C" => 2000
      ],
      "elimination"      => [
        "B" => 200,
        "C" => 400
      ],
      "transferts"       => [
        "B" => 200,
        "C" => 2000
      ],
      "deplacements_int" => [
        "B" => 0,
        "C" => 200
      ],
      "deplacements_ext" => [
        "B" => 0,
        "C" => 0
      ],
      "alerter"          => [
        "B" => 0,
        "C" => 0
      ],
    ],
    "E" => [
      "coherence"        => [
        "B" => 0,
        "C" => 400
      ],
      "orientation"      => [
        "B" => 0,
        "C" => 400
      ],
      "toilette"         => [
        "B" => 100,
        "C" => 400
      ],
      "habillage"        => [
        "B" => 100,
        "C" => 400
      ],
      "alimentation"     => [
        "B" => 100,
        "C" => 400
      ],
      "elimination"      => [
        "B" => 100,
        "C" => 800
      ],
      "transferts"       => [
        "B" => 100,
        "C" => 800
      ],
      "deplacements_int" => [
        "B" => 0,
        "C" => 200
      ],
      "deplacements_ext" => [
        "B" => 0,
        "C" => 0
      ],
      "alerter"          => [
        "B" => 0,
        "C" => 0
      ],
    ],
    "F" => [
      "coherence"        => [
        "B" => 100,
        "C" => 200
      ],
      "orientation"      => [
        "B" => 100,
        "C" => 200
      ],
      "toilette"         => [
        "B" => 100,
        "C" => 500
      ],
      "habillage"        => [
        "B" => 100,
        "C" => 500
      ],
      "alimentation"     => [
        "B" => 100,
        "C" => 500
      ],
      "elimination"      => [
        "B" => 100,
        "C" => 500
      ],
      "transferts"       => [
        "B" => 100,
        "C" => 500
      ],
      "deplacements_int" => [
        "B" => 0,
        "C" => 200
      ],
      "deplacements_ext" => [
        "B" => 0,
        "C" => 0
      ],
      "alerter"          => [
        "B" => 0,
        "C" => 0
      ],
    ],
    "G" => [
      "coherence"        => [
        "B" => 0,
        "C" => 150
      ],
      "orientation"      => [
        "B" => 0,
        "C" => 150
      ],
      "toilette"         => [
        "B" => 200,
        "C" => 300
      ],
      "habillage"        => [
        "B" => 200,
        "C" => 300
      ],
      "alimentation"     => [
        "B" => 200,
        "C" => 500
      ],
      "elimination"      => [
        "B" => 200,
        "C" => 500
      ],
      "transferts"       => [
        "B" => 200,
        "C" => 400
      ],
      "deplacements_int" => [
        "B" => 100,
        "C" => 0
      ],
      "deplacements_ext" => [
        "B" => 0,
        "C" => 0
      ],
      "alerter"          => [
        "B" => 0,
        "C" => 0
      ],
    ],
    "H" => [
      "coherence"        => [
        "B" => 0,
        "C" => 0
      ],
      "orientation"      => [
        "B" => 0,
        "C" => 0
      ],
      "toilette"         => [
        "B" => 2000,
        "C" => 3000
      ],
      "habillage"        => [
        "B" => 2000,
        "C" => 3000
      ],
      "alimentation"     => [
        "B" => 2000,
        "C" => 3000
      ],
      "elimination"      => [
        "B" => 2000,
        "C" => 3000
      ],
      "transferts"       => [
        "B" => 2000,
        "C" => 1000
      ],
      "deplacements_int" => [
        "B" => 1000,
        "C" => 1000
      ],
      "deplacements_ext" => [
        "B" => 0,
        "C" => 0
      ],
      "alerter"          => [
        "B" => 0,
        "C" => 0
      ],
    ]
  ];

  /*
   * Valeurs limites des rangs
   */
  public const TESTS_GROUPES = [
    "A" => [
      "1" => 4380,
      "2" => 4140,
      "3" => 3390
    ],
    "B" => [
      "4" => 2016
    ],
    "C" => [
      "5" => 1700,
      "6" => 1432
    ],
    "D" => [
      "7" => 2400
    ],
    "E" => [
      "8" => 1200
    ],
    "F" => [
      "9" => 800
    ],
    "G" => [
      "10" => 650
    ],
    "H" => [
      "11" => 4000,
      "12" => 2000,
      "13" => 0
    ],
  ];

  public const GROUPES = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'examgir';
    $spec->key   = 'examgir_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props               = parent::getProps();
    $props["sejour_id"]  = "ref notNull class|CSejour back|exams_gir";
    $props["creator_id"] = "ref notNull class|CMediusers back|exams_gir";
    $props["date"]       = "dateTime notNull";

    $props["coherence_communication"] = "set list|s|t|c|h";
    $props["coherence_comportement"]  = "set list|s|t|c|h";
    $props["orientation_temps"]       = "set list|s|t|c|h";
    $props["orientation_espace"]      = "set list|s|t|c|h";
    $props["toilette_haut"]           = "set list|s|t|c|h";
    $props["toilette_bas"]            = "set list|s|t|c|h";
    $props["habillage_haut"]          = "set list|s|t|c|h";
    $props["habillage_moyen"]         = "set list|s|t|c|h";
    $props["habillage_bas"]           = "set list|s|t|c|h";
    $props["alimentation_se_servir"]  = "set list|s|t|c|h";
    $props["alimentation_manger"]     = "set list|s|t|c|h";
    $props["elimination_urinaire"]    = "set list|s|t|c|h";
    $props["elimination_fecale"]      = "set list|s|t|c|h";
    $props["transferts"]              = "set list|s|t|c|h";
    $props["deplacements_int"]        = "set list|s|t|c|h";
    $props["deplacements_ext"]        = "set list|s|t|c|h";
    $props["alerter"]                 = "set list|s|t|c|h";
    $props["gestion"]                 = "set list|s|t|c|h";
    $props["cuisine"]                 = "set list|s|t|c|h";
    $props["menage"]                  = "set list|s|t|c|h";
    $props["transports"]              = "set list|s|t|c|h";
    $props["achats"]                  = "set list|s|t|c|h";
    $props["suivi_traitement"]        = "set list|s|t|c|h";
    $props["activites_tps_libre"]     = "set list|s|t|c|h";
    $props["score_gir"]               = "num show|0";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "Score GIR: $this->score_gir";
  }

  /**
   * Charge le séjour
   *
   * @return CSejour|CStoredObject
   * @throws Exception
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  /**
   * Calcul le score de GIR à partir du codage des variables
   *
   * @param $groupe
   * @param $codages
   *
   * @return int|string
   */
  function computeScoreGir($groupe, $codages) {
    // On calcule la somme des valeurs sur le groupe
    $somme = $this->calculSommationValeurVariables(self::GROUPES[$groupe], $codages);
    // On détermine le rang à partir de la somme
    $rang = $this->getRang(self::GROUPES[$groupe], $somme);
    if ($rang === 0) {
      // Si on à pas récupéré de rang on recommence sur le groupe suivant
      return $this->computeScoreGir($groupe + 1, $codages);
    }

    // On récupère le score GIR à partir du rang
    return $this->score_gir = $this->getScoreGir($rang);
  }

  /**
   * Calcul de la somme des valeurs des variables codées
   *
   * @param $groupe
   * @param $codages
   *
   * @return int
   */
  function calculSommationValeurVariables($groupe, $codages) {
    $somme = 0;
    foreach ($codages as $_var => $_valeur) {
      // Les variables codées "A" sont ignorées
      if ($_valeur === "A") {
        continue;
      }
      $somme += self::GRILLE_RANGS[$groupe][$_var][$_valeur];
    }

    return $somme;
  }

  /**
   * Détermine le rang à partir de la somme des valeurs
   *
   * @param $groupe
   * @param $somme
   *
   * @return array|int|string
   */
  function getRang($groupe, $somme) {
    $rang = 0;
    foreach (self::TESTS_GROUPES[$groupe] as $_rang => $_total) {
      if ($somme >= $_total) {
        $rang = $_rang;
        break;
      }
    }

    return $rang;
  }

  /**
   * On détermine le score GIR à partir du rang
   *
   * @param $rang
   *
   * @return int
   */
  function getScoreGir($rang) {
    foreach (self::RANGS_GIR as $_score => $_values) {
      if (in_array($rang, $_values)) {
        return $_score;
      }
    }

    return 0;
  }

    /**
     * Calcul les codes de chaque question ainsi que les codes finaux
     *
     * @return void
     */
    function computeAllCodes(): void
    {
        foreach (self::VARIABLES_ACTIVITES as $_all_fields_gir) {
            foreach ($_all_fields_gir as $_chapter => $_fields) {
                $final_code = "";

                if (property_exists($this, "_final_code_$_chapter") && property_exists($this, $_chapter)) {
                    if ($this->{$_chapter} == "s|t|c|h") {
                        $this->{"_final_code_$_chapter"} = "A";
                    } elseif (is_null($this->{$_chapter})) {
                        $this->{"_final_code_$_chapter"} = "C";
                    } else {
                        $this->{"_final_code_$_chapter"} = "B";
                    }
                }

                foreach ($_fields as $_field) {
                    if ($this->{$_field} == "s|t|c|h") {
                        $this->{"_code_$_field"} = "A";
                        $final_code              .= "A";
                    } elseif (is_null($this->{$_field})) {
                        $this->{"_code_$_field"} = "C";
                        $final_code              .= "C";
                    } else {
                        $this->{"_code_$_field"} = "B";
                        $final_code              .= "B";
                    }

                    switch ($_chapter) {
                        default:
                        case 'coherence':
                        case 'orientation':
                        case 'elimination':
                            switch ($final_code) {
                                case "AA":
                                    $this->{"_final_code_$_chapter"} = "A";
                                    break;
                                case "AB":
                                case "BA":
                                case "BB":
                                    $this->{"_final_code_$_chapter"} = "B";
                                    break;
                                default:
                                    $this->{"_final_code_$_chapter"} = "C";
                                    break;
                            }
                            break;
                        case 'toilette':
                            switch ($final_code) {
                                case "AA":
                                    $this->{"_final_code_$_chapter"} = "A";
                                    break;
                                case "CC":
                                    $this->{"_final_code_$_chapter"} = "C";
                                    break;
                                default:
                                    $this->{"_final_code_$_chapter"} = "B";
                                    break;
                            }
                            break;
                        case 'alimentation':
                            switch ($final_code) {
                                case "AA":
                                    $this->{"_final_code_$_chapter"} = "A";
                                    break;
                                case "AC":
                                case "BC":
                                case "CA":
                                case "CB":
                                case "CC":
                                    $this->{"_final_code_$_chapter"} = "C";
                                    break;
                                default:
                                    $this->{"_final_code_$_chapter"} = "B";
                                    break;
                            }
                            break;
                        case 'habillage':
                            switch ($final_code) {
                                case "AAA":
                                    $this->{"_final_code_$_chapter"} = "A";
                                    break;
                                case "CCC":
                                    $this->{"_final_code_$_chapter"} = "C";
                                    break;
                                default:
                                    $this->{"_final_code_$_chapter"} = "B";
                                    break;
                            }
                            break;
                    }
                }
            }
        }
    }
}
