<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use DateTime;
use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CRPU;

/**
 * Constantes médicales
 *
 * @property float  $poids
 * @property float  $taille
 * @property float  $pouls
 * @property float  $temperature
 * @property float  $_imc
 * @property float  $_diurese
 * @property float  $ecpa_avant
 * @property float  $ecpa_apres
 * @property float  $_ecpa_total
 * @property string $ta
 * @property float  $_ta_droit_systole
 * @property float  $_ta_droit_diastole
 * @property float  $_ta_gauche_systole
 * @property float  $_ta_gauche_diastole
 * @property float  $entree_hydrique
 * @property float  $_bilan_hydrique
 * @property int    $pointure
 */
class CConstantesMedicales extends CMbObject implements ImportableInterface
{
    /** @var string */
    public const RESOURCE_TYPE = 'medicalConstant';

    /** @var string */
    public const FIELDSET_AUTHOR = 'author';
    /** @var string */
    public const FIELDSET_TARGET = 'target';
    /** @var string */
    public const FIELDSET_CONSTANT = 'constant';

    const CONV_ROUND_UP   = 3;
    const CONV_ROUND_DOWN = 2;

    public $constantes_medicales_id;

    // DB Fields
    public $user_id;
    public $creation_date;
    public $patient_id;
    public $datetime;
    public $context_class;
    public $context_id;
    public $comment;
    public $origin;

    /** @var CConsultation|CSejour|CPatient */
    public $_ref_context;

    /** @var CPatient */
    public $_ref_patient;

    /** @var CMediusers */
    public $_ref_user;

    /** @var CConstantComment[] */
    public $_refs_comments = [];

    // Forms fields
    public $_poids_g;
    public $_variation_poids_naissance_g;
    public $_variation_poids_naissance_pourcentage;
    public $_poids_initial_g;
    public $_imc_valeur;
    public $_poids_ideal;
    public $_vst;
    public $_tam;
    public $_urine_effective;
    public $_new_constantes_medicales;
    public $_unite_ta;
    public $_unite_glycemie;
    public $unite_glycemie;
    public $_unite_cetonemie;
    public $_unite_hemoglobine;
    public $_unite_ldlc;
    public $_unite_creatinine;

    public $_surface_corporelle;

    /** @var string The guid of an object that needs a reference to the CConstantesMedicales object */
    public $_object_guid;
    /** @var string The field of the object that contains the reference */
    public $_object_field;

    public $_valued_cst = [];
    public $_constant_comments;
    public $_dossier_perinat_action;
    public $_naissance_action;
    /** @var bool If true, the values will be configured to the configured unit */
    public $_convert_value = true;

    /** @var CReleveRedon[] */
    public $_ref_releve_redons;

    static $_specs_converted              = false;
    static $_latest_values                = [];
    static $_first_values                 = [];
    static $_computed_constants_compounds = [];
    static $cache_naissance;

    public static $_computed_constants = [
        '_diurese',
        '_urine_effective',
        '_imc',
        '_surface_corporelle',
        '_poids_ideal',
        '_vst',
        '_tam',
        '_bilan_hydrique',
        'motricite_d',
        '_qsofa'
    ];

    /** @var array A list of constants with a special notice that need display */
    public static $_noticed_constant = [
        '_diurese',
        '_urine_effective',
        '_imc',
        '_surface_corporelle',
        '_poids_ideal',
        '_vst',
        '_tam',
        '_peak_flow',
        '_bilan_hydrique',
        'bromage_scale',
        'motricite_d',
        'motricite_g',
        'motricite_inf_d',
        'motricite_inf_g',
        'motricite_sup_d',
        'motricite_sup_g',
        'score_motricite',
        'oms',
        '_qsofa',
    ];

    static $list_constantes = [
        "poids"                                  => [
            "type"     => "physio",
            "unit"     => "kg",
            "unit_iso" => "kg",
            "callback" => "calculImcVst",
            "min"      => "@-2",
            "max"      => "@+2",
        ],
        "poids_avant_grossesse"                  => [
            "type"     => "physio",
            "unit"     => "kg",
            "unit_iso" => "kg",
            "min"      => "@-2",
            "max"      => "@+2",
        ],
        "poids_forme"                            => [
            "type"     => "physio",
            "unit"     => "kg",
            "unit_iso" => "kg",
            "min"      => "@-2",
            "max"      => "@+2",
        ],
        "poids_moyen"                            => [
            "type"     => "physio",
            "unit"     => "kg",
            "unit_iso" => "kg",
            "min"      => "@-2",
            "max"      => "@+2",
        ],
        "_poids_g"                               => [
            "type"     => "physio",
            "unit"     => "g",
            "unit_iso" => "g",
            "plot"     => true,
            "edit"     => true,
            "min"      => "@-200",
            "max"      => "@+200",
            /* Permet d'indiquer la ou les constantes utilisées pour calculer une constante */
            "bases"    => ['poids'],
        ],
        'variation_poids'                        => [
            'type'     => 'physio',
            'unit'     => 'kg',
            'unit_iso' => 'kg',
            'readonly' => true,
            'min'      => '@-5',
            'max'      => '@+5',
        ],
        '_variation_poids_naissance_g'           => [
            'type'     => 'physio',
            'unit'     => 'g',
            'unit_iso' => 'g',
            'readonly' => true,
            'min'      => '@-5',
            'max'      => '@+5',
            'plot'     => true,
        ],
        '_variation_poids_naissance_pourcentage' => [
            'type'     => "physio",
            'unit'     => "%",
            'readonly' => true,
            'min'      => '@-10',
            'max'      => '@+10',
            'plot'     => true,
        ],
        "taille"                                 => [
            "type"     => "physio",
            "unit"     => "cm",
            "unit_iso" => "cm",
            "callback" => "calculImcVst",
            "min"      => "@-5",
            "max"      => "@+5",
        ],
        "taille_reference"                       => [
            "type"     => "physio",
            "unit"     => "cm",
            "unit_iso" => "cm",
            "callback" => "calculImcVst",
            "min"      => "@-5",
            "max"      => "@+5",
        ],
        "pouls"                                  => [
            "type"     => "physio",
            "unit"     => "puls./min",
            "min"      => 20,
            "max"      => 220,
            "norm_min" => 90,
            "colors"   => ["black"],
        ],
        "ta"                                     => [
            "type"        => "physio",
            "unit"        => "cmHg",
            "unit_iso"    => "cm",
            "formfields"  => ["_ta_systole", "_ta_diastole"],
            "min"         => 2,
            "max"         => 16,
            "norm_min"    => 8,
            "norm_max"    => 14,
            "colors"      => ["#00A8F0", "#C0D800"],
            "conversion"  => ["mmHg" => 10],
            "candles"     => true,
            "unit_config" => "unite_ta",
            "orig_unit"   => "cmHg",
        ],
        '_tam'                                   => [
            'type'        => 'physio',
            'unit'        => 'cmHg',
            'formfields'  => ['_tam'],
            'min'         => 2,
            'max'         => 16,
            'norm_min'    => 8,
            'norm_max'    => 16,
            'conversion'  => ['mmHg' => 10],
            'unit_config' => 'unite_ta',
            'orig_unit'   => 'cmHg',
            "readonly"    => true,
            /* Permet d'indiquer la ou les constantes utilisées pour calculer une constante */
            "bases"       => ['ta'],
        ],
        /* La tension moyenne, pouvant être saisie manuellement par les utilisateurs */
        'tam_manual'                             => [
            'type'        => 'physio',
            'unit'        => 'cmHg',
            'formfields'  => ['_tam_manual'],
            'min'         => 2,
            'max'         => 16,
            'norm_min'    => 8,
            'norm_max'    => 16,
            'conversion'  => ['mmHg' => 10],
            'unit_config' => 'unite_ta',
            'orig_unit'   => 'cmHg',
        ],
        "ta_gauche"                              => [
            "type"        => "physio",
            "unit"        => "cmHg",
            "formfields"  => ["_ta_gauche_systole", "_ta_gauche_diastole"],
            "min"         => 2,
            "max"         => 16,
            "norm_min"    => 8,
            "norm_max"    => 14,
            "colors"      => ["#00A8F0", "#C0D800"],
            "conversion"  => ["mmHg" => 10],
            "candles"     => true,
            "unit_config" => "unite_ta",
            "orig_unit"   => "cmHg",
        ],
        "ta_droit"                               => [
            "type"        => "physio",
            "unit"        => "cmHg",
            "formfields"  => ["_ta_droit_systole", "_ta_droit_diastole"],
            "min"         => 2,
            "max"         => 16,
            "norm_min"    => 8,
            "norm_max"    => 14,
            "colors"      => ["#00A8F0", "#C0D800"],
            "conversion"  => ["mmHg" => 10],
            "candles"     => true,
            "unit_config" => "unite_ta",
            "orig_unit"   => "cmHg",
        ],
        "ta_couche"                              => [
            "type"        => "physio",
            "unit"        => "cmHg",
            "formfields"  => ["_ta_couche_systole", "_ta_couche_diastole"],
            "min"         => 2,
            "max"         => 16,
            "norm_min"    => 8,
            "norm_max"    => 14,
            "colors"      => ["#00A8F0", "#C0D800"],
            "conversion"  => ["mmHg" => 10],
            "candles"     => true,
            "unit_config" => "unite_ta",
            "orig_unit"   => "cmHg",
        ],
        "ta_assis"                               => [
            "type"        => "physio",
            "unit"        => "cmHg",
            "formfields"  => ["_ta_assis_systole", "_ta_assis_diastole"],
            "min"         => 2,
            "max"         => 16,
            "norm_min"    => 8,
            "norm_max"    => 14,
            "colors"      => ["#00A8F0", "#C0D800"],
            "conversion"  => ["mmHg" => 10],
            "candles"     => true,
            "unit_config" => "unite_ta",
            "orig_unit"   => "cmHg",
        ],
        "ta_debout"                              => [
            "type"        => "physio",
            "unit"        => "cmHg",
            "formfields"  => ["_ta_debout_systole", "_ta_debout_diastole"],
            "min"         => 2,
            "max"         => 16,
            "norm_min"    => 8,
            "norm_max"    => 14,
            "colors"      => ["#00A8F0", "#C0D800"],
            "conversion"  => ["mmHg" => 10],
            "candles"     => true,
            "unit_config" => "unite_ta",
            "orig_unit"   => "cmHg",
        ],
        "_vst"                                   => [
            "type"     => "physio",
            "unit"     => "ml",
            "min"      => 5000,
            "max"      => 7000,
            "readonly" => true,
        ],
        "_imc"                                   => [
            "type"     => "physio",
            "unit"     => "",
            "min"      => 12,
            "max"      => 40,
            "plot"     => true,
            "readonly" => true,
        ],
        "_surface_corporelle"                    => [
            "type"     => "physio",
            "unit"     => "m²",
            "min"      => 0.00,
            "max"      => 4.00,
            "readonly" => true,
        ],
        "_poids_ideal"                           => [
            "type"     => "physio",
            "unit"     => "kg",
            "min"      => 0,
            "max"      => 150,
            "plot"     => true,
            "readonly" => true,
        ],
        "temperature"                            => [
            "type"     => "physio",
            "unit"     => "°C",
            "min"      => 36,
            "max"      => 40,
            "standard" => 37.5,
            "colors"   => ["orange"],
        ],
        "spo2"                                   => [
            "type" => "physio",
            "unit" => "%",
            "min"  => 70,
            "max"  => 100,
        ],
        "score_sensibilite"                      => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 5,
        ],
        "sens_membre_inf_d"                      => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 5,
        ],
        "sens_membre_inf_g"                      => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 5,
        ],
        "sens_membre_sup_d"                      => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 5,
        ],
        "sens_membre_sup_g"                      => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 5,
        ],
        "score_motricite"                        => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 5,
        ],
        "score_sedation"                         => [
            "type" => "physio",
            "unit" => "",
            "min"  => 70,
            "max"  => 100,
        ],
        "frequence_respiratoire"                 => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 60,
        ],
        "EVA"                                    => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 10,
        ],
        "contraction_uterine"                    => [
            "type" => "physio",
            "unit" => "/10min",
            "min"  => 0,
            "max"  => 10,
        ],
        "bruit_foetal"                           => [
            "type" => "physio",
            "unit" => "bpm",
            "min"  => 0,
            "max"  => 220,
        ],

        "inr"                  => [
            "type" => "biolo",
            "unit" => "",
            "min"  => 0,
            "max"  => 6,
        ],
        "taux_prothrombine"    => [
            "type" => "biolo",
            "unit" => "%",
            "min"  => 19,
            "max"  => 41,
        ],
        "glycemie"             => [
            "type"        => "biolo",
            "unit"        => "g/l",
            "min"         => 0,
            "max"         => 4,
            "conversion"  => ["mmol/l" => 5.56, 'mg/dl' => 100, 'µmol/l' => 5560], // 1 g/l => 5.56 mmol/l, 1 g/l => 100 mg/dl, 1 g/l => 5560 µmol/l
            "unit_config" => "unite_glycemie",
            "orig_unit"   => "g/l",
            "formfields"  => ["_glycemie"],
        ],
        "cetonemie"            => [
            "type"        => "biolo",
            "unit"        => "g/l",
            "min"         => 0,
            "max"         => 4,
            "conversion"  => ["mmol/l" => 17.2], // 1 g/l => 17.2 mmol/l
            "unit_config" => "unite_cetonemie",
            "orig_unit"   => "g/l",
            "formfields"  => ["_cetonemie"],
        ],
        "hemoglobine_rapide"   => [
            "type"        => "biolo",
            "unit"        => "g/dl",
            "min"         => 3,
            "max"         => 25,
            "conversion"  => ['g/l' => 10],
            'unit_config' => 'unite_hemoglobine',
            'orig_unit'   => 'g/dl',
            'formfields'  => ['_hemoglobine_rapide'],
        ],
        "PVC"                  => [
            "type" => "physio",
            "unit" => "cm H2O",
            "min"  => 4,
            "max"  => 16,
        ],
        "perimetre_abdo"       => [
            "type" => "physio",
            "unit" => "cm",
            "min"  => 20,
            "max"  => 200,
        ],
        "perimetre_hanches"    => [
            "type" => "physio",
            "unit" => "cm",
            "min"  => 45,
            "max"  => 200,
        ],
        "perimetre_brachial"   => [
            "type" => "physio",
            "unit" => "cm",
            "min"  => 0,
            "max"  => 300,
        ],
        "perimetre_cranien"    => [
            "type" => "physio",
            "unit" => "cm",
            "min"  => 30,
            "max"  => 60,
        ],
        "perimetre_cuisse"     => [
            "type" => "physio",
            "unit" => "cm",
            "min"  => 20,
            "max"  => 100,
        ],
        "perimetre_cou"        => [
            "type" => "physio",
            "unit" => "cm",
            "min"  => 20,
            "max"  => 50,
        ],
        "perimetre_thoracique" => [
            "type" => "physio",
            "unit" => "cm",
            "min"  => 20,
            "max"  => 150,
        ],
        'perimetre_taille'     => [
            'type' => 'physio',
            'unit' => 'cm',
            'min'  => 30,
            'max'  => 60,
        ],
        "hauteur_uterine"      => [
            "type" => "physio",
            "unit" => "cm",
            "min"  => 0,
            "max"  => 35,
        ],
        "injection"            => [
            "type"       => "physio",
            "unit"       => "",
            "formfields" => ["_inj", "_inj_essai"],
            "min"        => 0,
            "max"        => 10,
        ],
        "gaz"                  => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 1,
        ],
        "selles"               => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 3,
        ],
        "TOF"                  => [
            "type" => "physio",
            "unit" => "%",
            "min"  => 0,
            "max"  => 100,
        ],

        // Douleur
        "douleur_en"           => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 10,
        ],
        "douleur_doloplus"     => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 30,
        ],
        "douleur_algoplus"     => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 5,
        ],
        "douleur_evs"          => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 4,
        ],
        "ecpa_avant"           => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 16,
        ],
        "ecpa_apres"           => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 16,
        ],
        "_ecpa_total"          => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 32,
            "plot" => true,
        ],

        // Vision
        "vision_oeil_droit"    => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 10,
        ],
        "vision_oeil_gauche"   => [
            "type" => "physio",
            "unit" => "",
            "min"  => 0,
            "max"  => 10,
        ],

        "peak_flow"             => [
            "type" => "physio",
            "unit" => "L/min",
            "min"  => 60,
            "max"  => 900,
        ],
        "_peak_flow"            => [
            "type"     => "physio",
            "unit"     => "L/min",
            "min"      => 60,
            "max"      => 900,
            "plot"     => true,
            "readonly" => true,
        ],

        /// DRAINS ///
        "sng"                   => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => -2000,
            "max"                => 1000,
            "cumul_reset_config" => "sng_cumul_reset_hour",
        ],
        "redon"                 => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_2"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_3"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_4"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_5"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_6"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_7"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_8"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_9"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_10"              => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_11"              => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_12"              => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "redon_cumul_reset_hour",
        ],
        "redon_accordeon_1"     => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 50,
            "cumul_reset_config" => "redon_accordeon_cumul_reset_hour",
        ],
        "redon_accordeon_2"     => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 50,
            "cumul_reset_config" => "redon_accordeon_cumul_reset_hour",
        ],
        "redon_accordeon_3"     => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 50,
            "cumul_reset_config" => "redon_accordeon_cumul_reset_hour",
        ],
        "redon_accordeon_4"     => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 50,
            "cumul_reset_config" => "redon_accordeon_cumul_reset_hour",
        ],
        "redon_accordeon_5"     => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 50,
            "cumul_reset_config" => "redon_accordeon_cumul_reset_hour",
        ],
        "redon_accordeon_6"     => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 50,
            "cumul_reset_config" => "redon_accordeon_cumul_reset_hour",
        ],
        "lame_1"                => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "lame_cumul_reset_hour",
        ],
        "lame_2"                => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "lame_cumul_reset_hour",
        ],
        "lame_3"                => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "lame_cumul_reset_hour",
        ],
        "drain_1"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_cumul_reset_hour",
        ],
        "drain_2"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_cumul_reset_hour",
        ],
        "drain_3"               => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_cumul_reset_hour",
        ],
        "drain_thoracique_1"    => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_thoracique_cumul_reset_hour",
        ],
        "drain_thoracique_2"    => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_thoracique_cumul_reset_hour",
        ],
        "drain_thoracique_3"    => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_thoracique_cumul_reset_hour",
        ],
        "drain_thoracique_4"    => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_thoracique_cumul_reset_hour",
        ],
        "drain_thoracique_flow" => [
            "type" => "drain",
            "unit" => "ml",
            "min"  => 0,
            "max"  => 1000,
        ],
        "drain_pleural_1"       => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_pleural_cumul_reset_hour",
        ],
        "drain_pleural_2"       => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_pleural_cumul_reset_hour",
        ],
        "drain_pleural_3"       => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_pleural_cumul_reset_hour",
        ],
        "drain_pleural_4"       => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_pleural_cumul_reset_hour",
        ],
        "drain_mediastinal"     => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_mediastinal_cumul_reset_hour",
        ],
        "drain_shirley"         => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_mediastinal_cumul_reset_hour",
        ],
        "drain_dve"             => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 500,
            "cumul_reset_config" => "drain_dve_cumul_reset_hour",
        ],
        "drain_kher"            => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 1000,
            "cumul_reset_config" => "drain_kher_cumul_reset_hour",
        ],
        "drain_crins"           => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_crins_cumul_reset_hour",
        ],
        "drain_sinus"           => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "drain_sinus_cumul_reset_hour",
        ],
        "drain_orifice_1"       => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 1000,
            "cumul_reset_config" => "drain_orifice_cumul_reset_hour",
        ],
        "drain_orifice_2"       => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 1000,
            "cumul_reset_config" => "drain_orifice_cumul_reset_hour",
        ],
        "drain_orifice_3"       => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 1000,
            "cumul_reset_config" => "drain_orifice_cumul_reset_hour",
        ],
        "drain_orifice_4"       => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 1000,
            "cumul_reset_config" => "drain_orifice_cumul_reset_hour",
        ],
        "drain_ileostomie"      => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 3000,
            "cumul_reset_config" => "drain_ileostomie_cumul_reset_hour",
        ],
        "drain_colostomie"      => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 3000,
            "cumul_reset_config" => "drain_colostomie_cumul_reset_hour",
        ],
        "drain_gastrostomie"    => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => -3000,
            "max"                => 3000,
            "cumul_reset_config" => "drain_gastrostomie_cumul_reset_hour",
        ],
        "drain_jejunostomie"    => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => -3000,
            "max"                => 3000,
            "cumul_reset_config" => "drain_jejunostomie_cumul_reset_hour",
        ],
        "ponction_ascite"       => [
            "type" => "drain",
            "unit" => "ml",
            "min"  => 0,
            "max"  => 2000,
        ],
        "ponction_pleurale"     => [
            "type" => "drain",
            "unit" => "ml",
            "min"  => 0,
            "max"  => 2000,
        ],

        // DIURESE ///////
        "_diurese"              => [ // Diurèse reelle, calculé
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 1000,
            "plot"               => true,
            "color"              => "#00A8F0",
            "cumul_reset_config" => "diuere_24_reset_hour",
            "formula"            => [
                "diurese"            => "+",  // Miction naturelle
                "sonde_ureterale_1"  => "+",
                "sonde_ureterale_2"  => "+",
                "sonde_nephro_1"     => "+",
                "sonde_nephro_2"     => "+",
                "sonde_vesicale"     => "+",
                "catheter_suspubien" => "+",
                "bricker"            => "+",
                "entree_lavage"      => "-",
            ],
            "alert_low"          => [0, "#ff3232"],
        ],

        // Ureteral
        "sonde_ureterale_1"     => [ // gauche
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "sonde_ureterale_cumul_reset_hour",
        ],
        "sonde_ureterale_2"     => [ // droite
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "sonde_ureterale_cumul_reset_hour",
        ],

        // Nephrostomie
        "sonde_nephro_1"        => [ // gauche
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "sonde_nephro_cumul_reset_hour",
        ],
        "sonde_nephro_2"        => [ // droite
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 100,
            "cumul_reset_config" => "sonde_nephro_cumul_reset_hour",
        ],

        "sonde_vesicale"             => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 200,
            "cumul_reset_config" => "sonde_vesicale_cumul_reset_hour",
        ],
        "sonde_rectale"              => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 3000,
            "cumul_reset_config" => "sonde_rectale_cumul_reset_hour",
        ],
        "catheter_suspubien"         => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 200,
            "cumul_reset_config" => "sonde_vesicale_cumul_reset_hour",
        ],
        "bricker"                    => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 200,
            "cumul_reset_config" => "sonde_vesicale_cumul_reset_hour",
        ],
        "diurese"                    => [ // Miction naturelle
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 1000,
            "cumul_reset_config" => "diuere_24_reset_hour",
        ],
        "entree_lavage"              => [
            "type" => "drain",
            "unit" => "ml",
            "min"  => 0,
            "max"  => 200,
        ],
        // FIN DIURESE ////////
        "creatininemie"              => [
            "type"        => "biolo",
            "unit"        => "mg/L",
            "min"         => 0,
            "max"         => 30,
            "conversion"  => ["µmol/l" => 8.8402],
            "unit_config" => "unite_creatinine",
            "orig_unit"   => "mg/l",
            "formfields"  => ["_creatininemie"],
        ],
        "ph_sanguin"                 => [
            "type" => "biolo",
            "unit" => "",
            "min"  => 5,
            "max"  => 10,
        ],
        "lactates"                   => [
            "type" => "biolo",
            "unit" => "mmol/L",
            "min"  => 0,
            "max"  => 20,
        ],
        "glasgow"                    => [
            "type" => "physio",
            "unit" => "",
            "min"  => 3,
            "max"  => 15,
        ],
        'hemo_glycquee'              => [
            'type' => 'biolo',
            'unit' => '%',
            'min'  => 0,
            'max'  => 50,
        ],
        'clair_creatinine'           => [
            'type' => 'biolo',
            'unit' => 'ml/min',
            'min'  => 0,
            'max'  => 250,
        ],
        'cockroft'                   => [
            'type' => 'biolo',
            'unit' => 'ml/min',
            'min'  => 0,
            'max'  => 250,
        ],
        'mdrd'                       => [
            'type' => 'biolo',
            'unit' => 'ml/min',
            'min'  => 0,
            'max'  => 250,
        ],
        'plaquettes'                 => [
            'type' => 'biolo',
            'unit' => 'g/l',
            'min'  => 0,
            'max'  => 1000,
        ],
        'triglycerides'              => [
            'type' => 'biolo',
            'unit' => 'g/l',
            'min'  => 0,
            'max'  => 4,
        ],
        'ldlc'                       => [
            'type'        => 'biolo',
            'unit'        => 'g/l',
            'conversion'  => ['mmol/l' => 2.586],
            'unit_config' => 'unite_ldlc',
            'orig_unit'   => 'g/l',
            'formfields'  => ['_ldlc'],
            'min'         => 0,
            'max'         => 4,
        ],
        'hdlc'                       => [
            'type'        => 'biolo',
            'unit'        => 'g/l',
            'conversion'  => ['mmol/l' => 2.586],
            'unit_config' => 'unite_ldlc',
            'orig_unit'   => 'g/l',
            'formfields'  => ['_hdlc'],
            'min'         => 0,
            'max'         => 2,
        ],
        'potassium'                  => [
            'type' => 'biolo',
            'unit' => 'mmol/l',
            'min'  => 0,
            'max'  => 50,
        ],
        'sodium'                     => [
            'type' => 'biolo',
            'unit' => 'mmol/l',
            'min'  => 0,
            'max'  => 500,
        ],
        'cpk'                        => [
            'type' => 'biolo',
            'unit' => 'ui/l',
            'min'  => 0,
            'max'  => 500,
        ],
        'asat'                       => [
            'type' => 'biolo',
            'unit' => 'ui/l',
            'min'  => 0,
            'max'  => 100,
        ],
        'alat'                       => [
            'type' => 'biolo',
            'unit' => 'ui/l',
            'min'  => 0,
            'max'  => 100,
        ],
        'gammagt'                    => [
            'type' => 'biolo',
            'unit' => 'ui/l',
            'min'  => 0,
            'max'  => 100,
        ],
        'ipsc'                       => [
            'type' => 'physio',
            'min'  => 0,
            'max'  => 1.5,
            "unit" => "mmHg",
        ],
        'broadman'                   => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 10,
        ],
        'tonus_d'                    => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 2,
        ],
        'tonus_g'                    => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 2,
        ],
        'motricite_d'                => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 5,
        ],
        'motricite_g'                => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 5,
        ],
        'motricite_inf_d'            => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 5,
        ],
        'motricite_inf_g'            => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 5,
        ],
        'motricite_sup_d'            => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 5,
        ],
        'motricite_sup_g'            => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 5,
        ],
        '_urine_effective'           => [
            "type"               => "drain",
            "unit"               => "ml",
            "min"                => 0,
            "max"                => 1000,
            "plot"               => true,
            "color"              => "#00A8F0",
            "cumul_reset_config" => "urine_effective_24_reset_hour",
            "formula"            => [
                "sonde_vesicale" => "+",
                "entree_lavage"  => "-",
            ],
        ],
        'echelle_confort'            => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 10,
        ],
        'pres_artere_inv_moy'        => [
            'type'        => 'physio',
            'unit'        => 'mmHg',
            'min'         => 20,
            'max'         => 200,
            "conversion"  => ["cmHg" => 0.1],
            "unit_config" => "unite_ta",
            "orig_unit"   => "mmHg",
            'formfields'  => ['_pres_artere_inv_moy'],
        ],
        "pres_artere_invasive"       => [
            "type"        => "physio",
            "unit"        => "cmHg",
            "formfields"  => ["_pres_artere_inv_systole", "_pres_artere_inv_diastole"],
            "min"         => 2,
            "max"         => 16,
            "conversion"  => ["mmHg" => 10],
            "candles"     => true,
            "unit_config" => "unite_ta",
            "orig_unit"   => "cmHg",
        ],
        'capnometrie'                => [
            'type' => 'biolo',
            'unit' => '%',
            'min'  => 0,
            'max'  => 100,
        ],
        'bilirubine_transcutanee'    => [
            'type'       => 'physio',
            'unit'       => 'µmol',
            'formfields' => ['_bilirubine_transcutanee_front', '_bilirubine_transcutanee_sternum'],
            'min'        => 0,
            'max'        => 1000,
            'candles'    => true,
        ],
        'bilicheck'                  => [
            'type' => 'physio',
            'unit' => 'µmol',
            'min'  => 0,
            'max'  => 1000,
        ],
        'sortie_lavage'              => [
            'type' => 'drain',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 200,
        ],
        'coloration'                 => [
            'type' => 'physio',
            'unit' => '%',
            'min'  => 0,
            'max'  => 4,
        ],
        'entree_hydrique'            => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 2000,
        ],
        "_bilan_hydrique"            => [
            "type"               => "drain",
            "unit"               => "ml",
            "plot"               => true,
            "readonly"           => true,
            "min"                => -5000,
            "max"                => 5000,
            "cumul_reset_config" => "bilan_hydrique_reset_hour",
        ],
        'evi'                        => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 10,
        ],
        'presence_urine'             => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 1,
        ],
        'meconium'                   => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 1,
        ],
        /* Children's Hospital Eastern Ontario Pain Scale */
        'cheops'                     => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 4,
            'max'  => 14,
        ],
        'albuminemie'                => [
            'type' => 'biolo',
            'unit' => 'g/L',
            'min'  => 0,
            'max'  => 60,
        ],
        'prealbuminemie'             => [
            'type' => 'biolo',
            'unit' => 'g/L',
            'min'  => 0,
            'max'  => 60,
        ],
        'co_expire'                  => [
            'type' => 'physio',
            'unit' => 'ppm',
            'min'  => 0,
            'max'  => 50,
        ],
        'bnp'                        => [
            'type' => 'biolo',
            'unit' => 'pg/ml',
            'min'  => 0,
            'max'  => 50000,
        ],
        'score_white'                => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 14,
        ],
        'lait_maternel'              => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 100,
        ],
        'lait_artificiel'            => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 100,
        ],
        'fenouil'                    => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 100,
        ],
        'dextro_maltose'             => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 100,
        ],
        'reliquat_perf'              => [
            'type' => 'drain',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 2000,
        ],
        'conscience'                 => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 3,
        ],
        'early_warning_signs'        => [
            'type'     => 'physio',
            'unit'     => '',
            'min'      => '0',
            'max'      => 18,
            'plot'     => true,
            'readonly' => true,
        ],
        'echelle_ops'                => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 10,
        ],
        'echelle_visage'             => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 10,
        ],
        'score_gir'                  => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 1,
            'max'  => 6,
        ],
        'score_norton'               => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 5,
            'max'  => 20,
        ],
        'urines_residuelles'         => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 2000,
        ],
        /* Thyroid-Stimulating Hormone ultra sensible */
        'tshus'                      => [
            'type' => 'physio',
            'unit' => 'mUI/L',
            'min'  => 0,
            'max'  => 2000,
        ],
        /* Protéine C réactive */
        'crp'                        => [
            'type' => 'physio',
            'unit' => 'mg/L',
            'min'  => 0,
            'max'  => 2000,
        ],
        'ferritnemie'                => [
            'type' => 'physio',
            'unit' => 'µg/l',
            'min'  => 0,
            'max'  => 2000,
        ],
        /* Antigène Prostatique Spécifique */
        'psa'                        => [
            'type' => 'physio',
            'unit' => 'ng/ml',
            'min'  => 0,
            'max'  => 2000,
        ],
        /* Vitesse de sédimentation */
        'vs'                         => [
            'type' => 'physio',
            'unit' => 'mm/h',
            'min'  => 0,
            'max'  => 2000,
        ],
        /* Nausées */
        'nausea'                     => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 10,
        ],
        /* Vomissements */
        'vomiting'                   => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 10000,
            'cumul_reset_config' => 'vomiting_cumul_reset_hour',
        ],
        'alcoolemie'                 => [
            'type' => 'physio',
            'unit' => 'mg/L d\'air expiré',
            'min'  => 0,
            'max'  => 100,
        ],
        'bromage_scale'              => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 1,
            'max'  => 7,
        ],
        'pH_urinaire'                => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 9,
        ],
        'jackson'                    => [
            'type'               => 'drain',
            'unit'               => 'ml',
            'min'                => 0,
            'max'                => 300,
            'cumul_reset_config' => 'jackson_cumul_reset_hour',
        ],
        /// Drain Scurasil///
        'scurasil_1'                 => [
            'type'               => 'drain',
            'unit'               => 'ml',
            'min'                => 0,
            'max'                => 2000,
            'cumul_reset_config' => 'scurasil_cumul_reset_hour',
        ],
        'scurasil_2'                 => [
            'type'               => 'drain',
            'unit'               => 'ml',
            'min'                => 0,
            'max'                => 2000,
            'cumul_reset_config' => 'scurasil_cumul_reset_hour',
        ],
        'psl'                        => [
            'type'               => 'drain',
            'unit'               => 'ml',
            'min'                => 0,
            'max'                => 2000,
            'cumul_reset_config' => 'psl_cumul_reset_hour',
        ],
        'perspiration'               => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 2000,
        ],
        'oms'                        => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 4,
        ],
        'debitmetrie_urinaire'       => [
            'type' => 'physio',
            'unit' => 'ml/s',
            'min'  => 0,
            'max'  => 50,
        ],
        'liquide_gastrique'          => [
            'type' => 'physio',
            'unit' => 'ml',
            'min'  => 0,
            'max'  => 5000,
        ],
        'bilirubine_totale_sanguine' => [
            'type' => 'physio',
            'unit' => 'µmol/l',
            'min'  => 0,
            'max'  => 1000,
        ],
        'edin'                       => [
            'type' => 'physio',
            'unit' => '',
            'min'  => 0,
            'max'  => 15,
        ],
        "selles_volume"              => [
            "type" => "physio",
            "unit" => "ml",
            "min"  => 0,
            "max"  => 2000,
        ],
        "vac"                        => [
            "type" => "physio",
            "unit" => "ml",
            "min"  => 0,
            "max"  => 2000,
        ],
        "proteinurie"                => [
            "type" => "biolo",
            "unit" => "g/L",
            "min"  => 0,
            "max"  => 50,
        ],
        'pointure'                   => [
            'type' => 'physio',
            'unit' => '',
            "min"  => "@-2",
            "max"  => "@+2",
        ],
        "_qsofa"                     => [
            "type"     => "physio",
            "unit"     => '',
            "min"      => 0,
            "max"      => 3,
            "readonly" => true,
        ],
    ];

    /** @var bool Used for making the params conversion (min, max and standard) only once */
    static $unit_conversion = false;

    static $list_constantes_type = [
        "physio" => [],
        "drain"  => [],
        "biolo"  => [],
    ];

    /**
     * Constructeur de la classe, créé dynamiquement tous les champs
     */
    function __construct()
    {
        foreach (self::$list_constantes as $_constant => $_params) {
            $this->$_constant = null;

            // Champs "composites"
            if (isset($_params["formfields"])) {
                foreach ($_params["formfields"] as $_formfield) {
                    $this->$_formfield = null;
                }
            }
        }

        parent::__construct();

        // Conversion des specs
        if (self::$_specs_converted) {
            return;
        }

        $group = CGroups::loadCurrent();

        foreach (self::$list_constantes as $_params) {
            if (empty($_params["unit_config"])) {
                continue;
            }

            $unit_config = $_params["unit_config"];
            $unit        = CAppUI::conf("dPpatients CConstantesMedicales $unit_config", $group);

            if (!$unit) {
                continue;
            }

            if ($unit == $_params["orig_unit"]) {
                continue;
            }

            if (isset($_params["formfields"]) && isset($_params["conversion"])) {
                $conv = $_params["conversion"][$unit];

                $func_min = function ($matches) use ($conv) {
                    return 'min|' . ($matches[1] * $conv);
                };

                $func_max = function ($matches) use ($conv) {
                    return 'max|' . ($matches[1] * $conv);
                };

                foreach ($_params["formfields"] as $_formfield) {
                    $spec       = $this->_specs[$_formfield];
                    $spec->prop = preg_replace_callback("/min\|([0-9]+)/", $func_min, $spec->prop);
                    $spec->prop = preg_replace_callback("/max\|([0-9]+)/", $func_max, $spec->prop);

                    if (isset($spec->min)) {
                        $spec->min *= $conv;
                    }
                    if (isset($spec->max)) {
                        $spec->max *= $conv;
                    }
                }
            } else {
                trigger_error("Un champ avec conversion d'unité doit avoir au moins un 'formfield'");
            }
        }

        self::$_specs_converted = true;
    }

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'constantes_medicales';
        $spec->key   = 'constantes_medicales_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                  = parent::getProps();
        $props['user_id']       = 'ref class|CMediusers back|constantes fieldset|author';
        $props['creation_date'] = 'dateTime fieldset|author';
        $props['patient_id']    = 'ref notNull class|CPatient back|constantes fieldset|target';
        $props['datetime']      = 'dateTime notNull fieldset|default';
        $props['context_class'] = 'enum list|CConsultation|CSejour|CPatient|CConsultationPostNatale|CDossierTiers';
        $props['context_id']    = 'ref class|CMbObject meta|context_class cascade back|contextes_constante';
        $props['comment']       = 'text helped';
        $props['origin']        = 'str';

        $props['poids']                                  = 'float pos max|500 fieldset|constant';
        $props['poids_forme']                            = 'float pos max|500';
        $props['poids_avant_grossesse']                  = 'float pos max|500';
        $props['poids_moyen']                            = 'float pos max|500';
        $props['_poids_g']                               = 'num pos min|300 max|500000';
        $props['variation_poids']                        = 'str';
        $props['_variation_poids_naissance_g']           = 'str';
        $props['_variation_poids_naissance_pourcentage'] = 'str';
        $props['_poids_initial_g']                       = 'num';
        $props['taille']                                 = 'float pos min|20 max|300 fieldset|constant';
        $props['taille_reference']                       = 'float pos min|20 max|300';

        $props['ta']           = 'str maxLength|10 fieldset|constant';
        $props['_ta_systole']  = 'num pos max|50 show|1';
        $props['_ta_diastole'] = 'num pos max|50 show|1';

        $props['_tam']        = 'str maxLength|10 show|1';
        $props['tam_manual']  = 'float pos max|20';
        $props['_tam_manual'] = 'float pos max|200';

        $props['ta_gauche']           = 'str maxLength|10';
        $props['_ta_gauche_systole']  = 'num pos max|50 show|1';
        $props['_ta_gauche_diastole'] = 'num pos max|50 show|1';

        $props['ta_droit']           = 'str maxLength|10';
        $props['_ta_droit_systole']  = 'num pos max|50 show|1';
        $props['_ta_droit_diastole'] = 'num pos max|50 show|1';

        $props['ta_couche']           = 'str maxLength|10';
        $props['_ta_couche_systole']  = 'num pos max|50 show|1';
        $props['_ta_couche_diastole'] = 'num pos max|50 show|1';

        $props['ta_assis']           = 'str maxLength|10';
        $props['_ta_assis_systole']  = 'num pos max|50 show|1';
        $props['_ta_assis_diastole'] = 'num pos max|50 show|1';

        $props['ta_debout']           = 'str maxLength|10';
        $props['_ta_debout_systole']  = 'num pos max|50 show|1';
        $props['_ta_debout_diastole'] = 'num pos max|50 show|1';

        $props['_unite_ta'] = 'enum list|cmHg|mmHg';

        $props['pouls']                  = 'num pos fieldset|constant';
        $props['spo2']                   = 'float min|10 max|100';
        $props['temperature']            = 'float min|20 max|50 fieldset|constant'; // Au cas ou il y aurait des malades très malades
        $props['score_sensibilite']      = 'float min|0 max|5';
        $props['sens_membre_inf_d']      = 'float min|0 max|5';
        $props['sens_membre_inf_g']      = 'float min|0 max|5';
        $props['sens_membre_sup_d']      = 'float min|0 max|5';
        $props['sens_membre_sup_g']      = 'float min|0 max|5';
        $props['score_motricite']        = 'float min|0 max|5';
        $props['EVA']                    = 'float min|0 max|10';
        $props['score_sedation']         = 'float';
        $props['frequence_respiratoire'] = 'float pos';
        $props['contraction_uterine']    = 'float min|0 max|10';
        $props['bruit_foetal']           = 'float min|0 max|220';

        $props['glycemie']        = 'float min|0 fieldset|constant';
        $props['_glycemie']       = $props['glycemie'];
        $props['_unite_glycemie'] = 'enum list|g/l|mmol/l|mg/dl|µmol/l';
        $props['unite_glycemie']  = 'enum list|g/l|mmol/l|mg/dl|µmol/l';

        $props['cetonemie']        = 'float min|0 max|10';
        $props['_cetonemie']       = $props['cetonemie'];
        $props['_unite_cetonemie'] = 'enum list|g/l|mmol/l';

        $props['hemoglobine_rapide']  = 'float';
        $props['_hemoglobine_rapide'] = 'float';
        $props['_unite_hemoglobine']  = 'enum list|g/dl|g/l';

        $props['PVC']                  = 'float min|0';
        $props['perimetre_abdo']       = 'float min|0';
        $props['perimetre_hanches']    = 'float min|0';
        $props['perimetre_brachial']   = 'float min|0';
        $props['perimetre_cranien']    = 'float min|0';
        $props['perimetre_cuisse']     = 'float min|0';
        $props['perimetre_cou']        = 'float min|0';
        $props['perimetre_thoracique'] = 'float min|0';
        $props['perimetre_taille']     = 'num min|0';
        $props['hauteur_uterine']      = 'float min|0';
        $props['peak_flow']            = 'float min|0';
        $props['_imc']                 = 'float pos show|1';
        $props['_peak_flow']           = 'float pos show|1';
        $props['_poids_ideal']         = 'float pos show|1';
        $props['_vst']                 = 'float pos show|1';
        $props["_surface_corporelle"]  = 'float';
        $props["_qsofa"]               = 'num pos';

        $props['injection']  = 'str maxLength|10';
        $props['_inj']       = 'num pos show|1';
        $props['_inj_essai'] = 'num pos moreEquals|_inj show|1';

        $props['gaz']           = 'num min|0';
        $props['selles']        = 'num min|0';
        $props['selles_volume'] = 'num min|0';
        $props['vac']           = 'num min|0';

        $props['TOF'] = 'float min|0 max|100';

        // Douleur
        $props['douleur_en']       = 'float min|0 max|10';
        $props['douleur_doloplus'] = 'num min|0 max|30';
        $props['douleur_algoplus'] = 'num min|0 max|5';
        $props['douleur_evs']      = 'num min|0 max|4';
        $props['ecpa_avant']       = 'num min|0 max|16';
        $props['ecpa_apres']       = 'num min|0 max|16';
        $props['_ecpa_total']      = 'num min|0 max|32 show|1';

        // Vision
        $props['vision_oeil_droit']  = 'num min|0 max|10';
        $props['vision_oeil_gauche'] = 'num min|0 max|10';

        $props['redon']                            = 'float min|0';
        $props['redon_2']                          = 'float min|0';
        $props['redon_3']                          = 'float min|0';
        $props['redon_4']                          = 'float min|0';
        $props['redon_5']                          = 'float min|0';
        $props['redon_6']                          = 'float min|0';
        $props['redon_7']                          = 'float min|0';
        $props['redon_8']                          = 'float min|0';
        $props['redon_9']                          = 'float min|0';
        $props['redon_10']                         = 'float min|0';
        $props['redon_11']                         = 'float min|0';
        $props['redon_12']                         = 'float min|0';
        $props['redon_accordeon_1']                = 'float min|0';
        $props['redon_accordeon_2']                = 'float min|0';
        $props['redon_accordeon_3']                = 'float min|0';
        $props['redon_accordeon_4']                = 'float min|0';
        $props['redon_accordeon_5']                = 'float min|0';
        $props['redon_accordeon_6']                = 'float min|0';
        $props['diurese']                          = 'float min|0'; // Miction naturelle
        $props['_diurese']                         = 'float min|0 show|1'; // Vraie diurèse (calculée)
        $props['sng']                              = 'float min|-2000';
        $props['lame_1']                           = 'float min|0';
        $props['lame_2']                           = 'float min|0';
        $props['lame_3']                           = 'float min|0';
        $props['drain_1']                          = 'float min|0';
        $props['drain_2']                          = 'float min|0';
        $props['drain_3']                          = 'float min|0';
        $props['drain_thoracique_1']               = 'float min|0';
        $props['drain_thoracique_2']               = 'float min|0';
        $props['drain_thoracique_3']               = 'float min|0';
        $props['drain_thoracique_4']               = 'float min|0';
        $props['drain_thoracique_flow']            = 'float min|0';
        $props['drain_pleural_1']                  = 'float min|0';
        $props['drain_pleural_2']                  = 'float min|0';
        $props['drain_pleural_3']                  = 'float min|0';
        $props['drain_pleural_4']                  = 'float min|0';
        $props['drain_mediastinal']                = 'float min|0';
        $props['drain_shirley']                    = 'float min|0';
        $props['drain_dve']                        = 'float min|0';
        $props['drain_kher']                       = 'float min|0';
        $props['drain_crins']                      = 'float min|0';
        $props['drain_sinus']                      = 'float min|0';
        $props['drain_orifice_1']                  = 'float min|0';
        $props['drain_orifice_2']                  = 'float min|0';
        $props['drain_orifice_3']                  = 'float min|0';
        $props['drain_orifice_4']                  = 'float min|0';
        $props['drain_ileostomie']                 = 'float min|0';
        $props['drain_colostomie']                 = 'float min|0';
        $props['drain_gastrostomie']               = 'float min|-3000';
        $props['drain_jejunostomie']               = 'float min|-3000';
        $props['ponction_ascite']                  = 'float min|0';
        $props['ponction_pleurale']                = 'float min|0';
        $props['sonde_ureterale_1']                = 'float min|0';
        $props['sonde_ureterale_2']                = 'float min|0';
        $props['sonde_nephro_1']                   = 'float min|0';
        $props['sonde_nephro_2']                   = 'float min|0';
        $props['sonde_vesicale']                   = 'float min|0';
        $props['sonde_rectale']                    = 'float min|0';
        $props['catheter_suspubien']               = 'float min|0';
        $props['bricker']                          = 'float min|0';
        $props['entree_lavage']                    = 'float min|0';
        $props['creatininemie']                    = 'float min|0';
        $props['_creatininemie']                   = 'float min|0';
        $props['_unite_creatinine']                = 'enum list|mg/l|µmol/l';
        $props['ph_sanguin']                       = 'float min|0';
        $props['lactates']                         = 'float min|0';
        $props['glasgow']                          = 'float min|0';
        $props['hemo_glycquee']                    = 'float min|0';
        $props['clair_creatinine']                 = 'float min|0';
        $props['cockroft']                         = 'float min|0';
        $props['mdrd']                             = 'float min|0';
        $props['plaquettes']                       = 'num min|0';
        $props['triglycerides']                    = 'float min|0';
        $props['ldlc']                             = 'float min|0';
        $props['_ldlc']                            = 'float min|0';
        $props['_unite_ldlc']                      = 'enum list|g/l|mmol/l';
        $props['hdlc']                             = 'float min|0';
        $props['_hdlc']                            = 'float min|0';
        $props['potassium']                        = 'float min|0';
        $props['sodium']                           = 'float min|0';
        $props['cpk']                              = 'num min|0';
        $props['asat']                             = 'num min|0';
        $props['alat']                             = 'num min|0';
        $props['gammagt']                          = 'num min|0';
        $props['ipsc']                             = 'float min|0';
        $props['broadman']                         = 'num min|0';
        $props['tonus_d']                          = 'float min|0 max|2';
        $props['tonus_g']                          = 'float min|0 max|2';
        $props['motricite_d']                      = 'float min|0 max|5';
        $props['motricite_g']                      = 'float min|0 max|5';
        $props['motricite_inf_d']                  = 'float min|0 max|5';
        $props['motricite_inf_g']                  = 'float min|0 max|5';
        $props['motricite_sup_d']                  = 'float min|0 max|5';
        $props['motricite_sup_g']                  = 'float min|0 max|5';
        $props['_urine_effective']                 = 'float min|0 show|1';
        $props['echelle_confort']                  = 'num min|0 max|10';
        $props['pres_artere_inv_moy']              = 'num min|20 max|200';
        $props['_pres_artere_inv_moy']             = 'num min|20 max|200';
        $props['pres_artere_invasive']             = 'str maxLength|10';
        $props['_pres_artere_inv_systole']         = 'num pos max|50 show|1';
        $props['_pres_artere_inv_diastole']        = 'num pos max|50 show|1';
        $props['capnometrie']                      = 'num min|0 max|100';
        $props['bilirubine_transcutanee']          = 'str maxLength|20';
        $props["_bilirubine_transcutanee_front"]   = 'num pos show|1';
        $props["_bilirubine_transcutanee_sternum"] = 'num pos show|1';
        $props['bilicheck']                        = 'num min|0';
        $props['sortie_lavage']                    = 'num min|20 max|3000';
        $props['coloration']                       = 'num min|0 max|4';
        $props["entree_hydrique"]                  = "num min|0 max|5000";
        $props["_bilan_hydrique"]                  = "num min|0 max|40000";
        $props['evi']                              = 'float min|0 max|10';
        $props['presence_urine']                   = 'num min|0 max|1';
        $props['meconium']                         = 'num min|0 max|1';
        $props['cheops']                           = 'num min|4 max|14';
        $props['albuminemie']                      = 'float min|0 max|60';
        $props['prealbuminemie']                   = 'float min|0 max|60';
        $props['co_expire']                        = 'num min|0 max|50';
        $props['bnp']                              = 'num min|0';
        $props['score_white']                      = 'num min|0 max|14';
        $props['lait_maternel']                    = 'num min|0 max|100';
        $props['lait_artificiel']                  = 'num min|0 max|100';
        $props['fenouil']                          = 'num min|0 max|100';
        $props['dextro_maltose']                   = 'num min|0 max|100';
        $props['reliquat_perf']                    = 'num min|0 max|2000';
        $props['conscience']                       = 'num min|0 max|3';
        $props['early_warning_signs']              = 'num min|0 max|18 show|1';
        $props['echelle_ops']                      = 'num min|0 max|10';
        $props['echelle_visage']                   = 'num min|0 max|10';
        $props['score_gir']                        = 'num min|1 max|6';
        $props['score_norton']                     = 'num min|5 max|20';
        $props['urines_residuelles']               = 'num min|0 max|2000';
        $props['tshus']                            = 'float min|0 max|2000';
        $props['crp']                              = 'float min|0 max|2000';
        $props['ferritnemie']                      = 'float min|0 max|2000';
        $props['psa']                              = 'float min|0 max|2000';
        $props['vs']                               = 'float min|0 max|2000';
        $props['nausea']                           = 'num min|0 max|10';
        $props['vomiting']                         = 'float min|0';
        $props['alcoolemie']                       = 'float min|0';
        $props['bromage_scale']                    = 'num min|1 max|7';
        $props['pH_urinaire']                      = 'float min|5 max|9';
        $props['jackson']                          = 'float min|0 max|300';
        $props['scurasil_1']                       = 'num min|0 max|2000';
        $props['scurasil_2']                       = 'num min|0 max|2000';
        $props['psl']                              = 'num min|0 max|2000';
        $props['_constant_comments']               = 'text';
        $props['perspiration']                     = 'float min|0 max|2000';
        $props['oms']                              = 'num min|0 max|4';
        $props['debitmetrie_urinaire']             = 'float min|0 max|50';
        $props['inr']                              = 'float min|0 max|6 fieldset|constant';
        $props['taux_prothrombine']                = 'float min|19 max|41';
        $props['liquide_gastrique']                = 'num min|0 max|5000';
        $props['bilirubine_totale_sanguine']       = "num min|0";
        $props['edin']                             = "num min|0 max|15";
        $props['proteinurie']                      = "float min|0 max|50";
        $props['pointure']                         = "num";

        return $props;
    }

    /**
     * Get conversion ratio for a constant
     *
     * @param string $field Constante name
     * @param string $unit  Constante target unit
     *
     * @return float
     */
    static function getConv($field, $unit)
    {
        $conv   = 1.0;
        $params = self::$list_constantes[$field];

        if ($unit) {
            if ($unit != $params["orig_unit"]) {
                $conv = $params["conversion"][$unit];
            }
        }

        return $conv;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        static $unite_config = [];

        parent::updateFormFields();

        $this->loadRefPatient();

        $this->getIMC();
        $this->getPeakFlow();

        $this->_view = $this->getFormattedValue("datetime");

        // Calcul du poids en grammes
        if ($this->poids) {
            $this->_poids_g = $this->poids * 1000;
        }

        // Afficher le champ diurèse dans le formulaire si une des valeurs n'est pas vide
        // FIXME Utiliser "cumul_in"
        foreach (self::$list_constantes["_diurese"]["formula"] as $_field => $_sign) {
            if ($this->{$_field} && $this->_diurese == null) {
                $this->_diurese = " ";
                break;
            }
        }

        // Afficher le champ urine effective dans le formulaire si une des valeurs n'est pas vide
        foreach (self::$list_constantes["_urine_effective"]["formula"] as $_field => $_sign) {
            if ($this->{$_field} && $this->_urine_effective == null) {
                $this->_urine_effective = " ";
                break;
            }
        }

        // Détermination valeur IMC
        if ($this->poids && $this->taille) {
            $seuils = ($this->_ref_patient->sexe != 'm') ?
                [19, 24] :
                [20, 25];

            if ($this->_imc < $seuils[0]) {
                $this->_imc_valeur = 'Maigreur';
            } elseif ($this->_imc > $seuils[1] && $this->_imc <= 30) {
                $this->_imc_valeur = 'Surpoids';
            } elseif ($this->_imc > 30 && $this->_imc <= 40) {
                $this->_imc_valeur = 'Obésité';
            } elseif ($this->_imc > 40) {
                $this->_imc_valeur = 'Obésité morbide';
            }
        }

        // Calcul du poids ideal
        if ($this->taille) {
            $factor = 4;
            if ($this->_ref_patient->sexe == 'f') {
                $factor = 2;
            }
            $this->_poids_ideal = $this->taille - 100 - (($this->taille - 150) / $factor);
        }

        // Calcul du Volume Sanguin Total
        if ($this->poids) {
            $this->_vst = (($this->_ref_patient->sexe != 'm') ? 65 : 70) * $this->poids;
        }

        // Calcul de la Tension Artérielle Moyenne
        if ($this->ta) {
            [$_ta_systole, $_ta_diastole] = array_map('floatval', explode('|', $this->ta));
            if ($_ta_systole && $_ta_diastole) {
                $this->_tam = round(($_ta_systole + (2 * $_ta_diastole)) / 3, 2);
            }
        }

        //calcul Qsofa
        $this->_qsofa = 0;
        if ($this->glasgow !== null && $this->glasgow < 15) {
            $this->_qsofa++;
        }
        if ($this->ta && $_ta_systole <= 10) {
            $this->_qsofa++;
        }
        if ($this->frequence_respiratoire !== null && $this->frequence_respiratoire >= 22) {
            $this->_qsofa++;
        }


        if (empty($unite_config)) {
            $unite_config["unite_ta"]          = CAppUI::gconf('dPpatients CConstantesMedicales unite_ta');
            $unite_config["unite_glycemie"]    = CAppUI::gconf('dPpatients CConstantesMedicales unite_glycemie');
            $unite_config["unite_cetonemie"]   = CAppUI::gconf('dPpatients CConstantesMedicales unite_cetonemie');
            $unite_config["unite_hemoglobine"] = CAppUI::gconf('dPpatients CConstantesMedicales unite_hemoglobine');
            $unite_config["unite_ldlc"]        = CAppUI::gconf('dPpatients CConstantesMedicales unite_ldlc');
            $unite_config["unite_creatinine"]  = CAppUI::gconf('dPpatients CConstantesMedicales unite_creatinine');
        }

        $this->_unite_ta          = $unite_config["unite_ta"];
        $this->_unite_glycemie    = $unite_config["unite_glycemie"];
        $this->_unite_cetonemie   = $unite_config["unite_cetonemie"];
        $this->_unite_hemoglobine = $unite_config["unite_hemoglobine"];
        $this->_unite_ldlc        = $unite_config["unite_ldlc"];
        $this->_unite_creatinine  = $unite_config["unite_creatinine"];

        foreach (self::$list_constantes as $_constant => &$_params) {
            // Conversion des unités
            if (isset($_params["unit_config"]) && !self::$unit_conversion) {
                $_unit_config = '_' . $_params["unit_config"];
                $unit         = $this->$_unit_config;

                $_params_ref = &CConstantesMedicales::$list_constantes[$_constant];
                //$_params_ref["orig_unit"] = $_params_ref["unit"];
                if ($unit != $_params_ref["orig_unit"]) {
                    $conv = $_params["conversion"][$unit];

                    $_params_ref["unit"] = $unit;
                    $_params_ref["min"]  *= $conv;
                    $_params_ref["max"]  *= $conv;

                    if (isset($_params_ref["norm_min"])) {
                        $_params_ref["norm_min"] *= $conv;
                    }
                    if (isset($_params_ref["norm_max"])) {
                        $_params_ref["norm_max"] *= $conv;
                    }
                }
            }

            if ($this->$_constant === null || $this->$_constant === "") {
                continue;
            }

            if (isset($_params["formfields"])) {
                $conv = 1.0;
                if (isset($_params["unit_config"])) {
                    $form_field_unite = '_' . $_params["unit_config"];
                    $_unite           = $this->$form_field_unite;

                    if ($_params["unit_config"] == "unite_glycemie") {
                        $_unite = $this->unite_glycemie;
                    }

                    $conv = self::getConv($_constant, $_unite);
                }

                $_parts = explode("|", $this->$_constant);

                foreach ($_params["formfields"] as $_i => $_formfield) {
                    if (array_key_exists($_i, $_parts)) {
                        $this->$_formfield = floatval($_parts[$_i]);
                    }

                    if ($conv != 1.0 && is_numeric($this->$_formfield)) {
                        $this->$_formfield = round($this->$_formfield * $conv, self::CONV_ROUND_DOWN);
                    }
                }
            }
        }
        /* Permet d'éviter que les conversions des min et max soit effectuée plusieurs fois */
        self::$unit_conversion = true;

        // Calcul de l'ECPA total
        $this->_ecpa_total = null;
        if ($this->ecpa_avant !== null) {
            $this->_ecpa_total += $this->ecpa_avant;
        }
        if ($this->ecpa_apres !== null) {
            $this->_ecpa_total += $this->ecpa_apres;
        }
    }

    /**
     * @see parent::updatePlainFields()
     */
    function updatePlainFields()
    {
        $group = CGroups::loadCurrent();

        /* We make the unit conversion only if this is not a merge situation */
        if (!$this->_forwardRefMerging) {
            if ($this->_poids_g) {
                $this->poids = round($this->_poids_g / 1000, 4);
            }

            foreach (self::$list_constantes as $_constant => &$_params) {
                // If field is a
                if ($this->$_constant === null && empty($_params["formfields"])) {
                    continue;
                }

                if (isset($_params["formfields"])) {
                    $conv = 1.0;
                    if (isset($_params['conversion']) && $this->_convert_value) {
                        $form_field_unite = '_' . $_params["unit_config"];
                        $_unite           = $this->$form_field_unite;

                        // Si le champ n'a pas de valeur, on regarde en config
                        if (!$_unite) {
                            $_unite = CAppUI::conf('dPpatients CConstantesMedicales ' . $_params["unit_config"], $group);
                        }

                        if (($_params["unit_config"] == "unite_glycemie") && $this->unite_glycemie) {
                            $_unite = $this->unite_glycemie;
                        }

                        $conv = self::getConv($_constant, $_unite);
                    }

                    $_parts = [];
                    $_empty = true;

                    foreach ($_params["formfields"] as $_formfield) {
                        if (empty($this->$_formfield) && !is_numeric($this->$_formfield)) {
                            break;
                        }

                        $_empty = false;

                        $_value = $this->$_formfield;
                        $_value = CMbFieldSpec::checkNumeric($_value, false);

                        if ($conv != 1.0 && $this->_convert_value) {
                            $_value = round($_value / $conv, self::CONV_ROUND_UP);
                        }

                        $_parts[] = $_value;
                    }

                    // no value at all
                    if ($_empty) {
                        $this->$_constant = "";
                    } // not enough values
                    elseif (count($_parts) != count($_params["formfields"])) {
                        $this->$_constant = "";
                    } // it's ok
                    else {
                        $this->$_constant = implode("|", $_parts);
                    }
                }
            }
        }

        parent::updatePlainFields();
    }

    /**
     * Load context
     *
     * @return CConsultation|CSejour|CPatient
     * @throws \Exception
     */
    function loadRefContext()
    {
        if ($this->context_class && $this->context_id) {
            $this->_ref_context = new $this->context_class;
            $this->_ref_context = $this->_ref_context->getCached($this->context_id);
        }

        return $this->_ref_context;
    }

    /**
     * Get patient
     *
     * @return CPatient
     * @throws \Exception
     */
    function loadRefPatient()
    {
        return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
    }

    /**
     * @see parent::loadRefsFwd()
     */
    function loadRefsFwd()
    {
        $this->loadRefContext();
        $this->loadRefPatient();
    }

    /**
     * Charge l'utilisateur qui a enregistré la première fois la constante
     *
     * @return CMediusers
     * @throws \Exception
     */
    function loadRefUser()
    {
        if (!$this->user_id) {
            $first_log       = $this->loadFirstLog();
            $this->_ref_user = $first_log->loadRefUser();
            $this->_ref_user->loadRefMediuser()->loadRefFunction();
            $this->user_id = $this->_ref_user->_id;
            $this->store();

            return $this->_ref_user = $this->_ref_user->_ref_mediuser;
        }
        $this->_ref_user = $this->loadFwdRef("user_id", true);
        $this->_ref_user->loadRefFunction();

        return $this->_ref_user;
    }

    function getCreationDate()
    {
        if (!$this->creation_date) {
            $log                 = $this->loadCreationLog();
            $this->creation_date = $log->date;
            $this->store();
        }

        return $this->creation_date;
    }

    /**
     * @see parent::check()
     */
    function check()
    {
        if ($msg = parent::check()) {
            return $msg;
        }

        if ($this->datetime > CMbDT::dateTime()) {
            return CAppUI::tr('CConstantesMedicales-error-datetime_future');
        }

        /* Check if the object has been properly loaded,
     to avoid blocking the merges of a context (CSejour, COperation, CPatient, CConsultation) */
        if ($this->datetime) {
            // Verifie si au moins une des valeurs est remplie
            $ok = false;
            foreach (CConstantesMedicales::$list_constantes as $const => $params) {
                if ($const[0] == '_') {
                    continue;
                }

                $this->completeField($const);
                if (array_key_exists('formfields', $params)) {
                    foreach ($params['formfields'] as $_formfield) {
                        if ($this->$_formfield !== "" && $this->$_formfield !== null) {
                            $ok = true;
                            break 2;
                        }
                    }
                } elseif ($this->$const !== "" && $this->$const !== null) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                return CAppUI::tr("CConstantesMedicales-min_one_constant");
            }
        }

        return null;
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        // S'il ne reste plus qu'un seul champ et que sa valeur est passée à vide,
        // alors on supprime la constante.
        if ((!$this->_id
                && ($this->frequence_respiratoire || $this->ta || $this->spo2 || $this->pouls || $this->conscience))
            || ($this->_id
                && ($this->fieldModified('frequence_respiratoire') || $this->fieldModified('ta') || $this->fieldModified('spo2')
                    || $this->fieldModified('pouls') || $this->fieldModified('conscience')))
        ) {
            $this->computeEarlyWarningSigns();
        }

        if ($this->_id && ($this->fieldModified("taille") || $this->fieldModified("poids"))
            || !$this->_id && ($this->taille || $this->poids)
        ) {
            $this->completeField("patient_id");
            Cache::deleteKeys(Cache::DISTR, "alertes-CPatient-" . $this->patient_id . '-');
        }

        if ($this->_id && !$this->_forwardRefMerging) {
            $this->loadOldObject();

            /* Pour permettre la suppression du poids et de _poids_g */
            if (is_null($this->_poids_g)) {
                $this->_poids_g = '';
            }

            if (is_null($this->poids)) {
                $this->poids = '';
            }

            $conf_poids_g = explode('|', self::getHostConfig('selection _poids_g', $this->loadRefContext()));
            if (!$this->_poids_g && $this->_old->poids && $conf_poids_g[0] != -1) {
                $this->poids = $this->_poids_g;
            } elseif (!$this->poids && $this->_old->poids) {
                $this->_poids_g = $this->poids;
            } elseif ($this->poids != $this->_old->poids && $this->_poids_g != $this->poids * 1000) {
                $this->_poids_g = $this->poids * 1000;
            }
        }

        if (!$this->_id) {
            if (!$this->user_id) {
                $this->user_id = CMediusers::get()->_id;
            }

            if (!$this->creation_date) {
                $this->creation_date = CMbDT::dateTime();
            }

            if ($this->datetime == 'now') {
                $this->datetime = $this->creation_date;
            }
            if (!$this->constantes_medicales_id && $this->hauteur_uterine) {
                $this->checkLastConstanteTimeHauteurUterine();
            }
        }

        if (!$this->_id && !$this->_new_constantes_medicales) {
            $this->updatePlainFields();
            $constante                = new CConstantesMedicales();
            $constante->patient_id    = $this->patient_id;
            $constante->context_class = $this->context_class;
            $constante->context_id    = $this->context_id;

            if ($constante->patient_id && $constante->loadMatchingObject()) {
                foreach (CConstantesMedicales::$list_constantes as $type => $params) {
                    if (empty($this->$type) && !empty($constante->$type)) {
                        $this->$type = $constante->$type;
                    }
                }
                $this->_id = $constante->_id;
            }
        }

        if ($this->poids && (!$this->variation_poids || $this->fieldModified('variation_poids') || $this->fieldModified('poids'))) {
            $this->getWeightVariation();
        }

        $msg = parent::store();

        if ($this->_constant_comments) {
            $comments = json_decode(utf8_encode($this->_constant_comments), true);
            foreach ($comments as $_comment) {
                $comment              = new CConstantComment();
                $comment->constant_id = $this->_id;
                $comment->constant    = $_comment['constant'];
                $comment->loadMatchingObject();
                $comment->comment = utf8_decode($_comment['comment']);
                $comment->store();
            }
        }

        /* If the object guid and the field are set, set the reference to the CConstantesMedicales object */
        if ($this->_object_guid && $this->_object_field && !$msg) {
            $field  = $this->_object_field;
            $object = CMbObject::loadFromGuid($this->_object_guid);

            if ($object->_id && property_exists($object, $field)) {
                $object->$field = $this->_id;
                $msg            = $object->store();
            }
        }

        return $msg;
    }

    /**
     * @see parent::delete()
     */
    function delete()
    {
        $this->completeField("taille", "poids", "patient_id");
        if ($this->taille || $this->poids) {
            Cache::deleteKeys(Cache::DISTR, "alertes-CPatient-" . $this->patient_id . '-');
        }

        return parent::delete();
    }

    /**
     * Apply the formula of the given constant and return the result
     *
     * @param string $constant_name The name of the constant
     *
     * @return float|integer
     */
    function applyFormula($constant_name)
    {
        $value = null;
        foreach (CConstantesMedicales::$list_constantes[$constant_name]['formula'] as $_field => $_sign) {
            if (!is_null($this->$_field) && $this->$_field !== "") {
                if ($_sign === '+') {
                    $value += $this->$_field;
                } else {
                    $value -= $this->$_field;
                }
            }
        }

        return $value;
    }

    /**
     * Récupération des constantes ayant une valeur
     *
     * @return array
     */
    function getValuedConstantes()
    {
        $this->_valued_cst = [];
        foreach (self::$list_constantes as $name => $cst) {
            if ($this->$name !== null) {
                if (array_key_exists('formfields', $cst)) {
                    $value = '';
                    foreach ($cst['formfields'] as $_index => $_formfield) {
                        $value .= $_index ? '|' . $this->$_formfield : $this->$_formfield;
                    }
                } else {
                    $value = $this->$name;
                }

                $this->_valued_cst[$name] = [
                    "value"       => $value,
                    "description" => $cst,
                ];
            }
        }
    }

    /**
     * Compute the IMC
     *
     * @return void
     */
    function getIMC()
    {
        $this->setComputedConstantsCompounds();

        $poids  = null;
        $taille = null;
        if ($this->poids && $this->taille) {
            $poids  = $this->poids;
            $taille = $this->taille;
        } elseif (($this->poids && !$this->taille) || (!$this->poids && $this->taille)) {
            if (!$this->poids) {
                $taille = $this->taille;
                $poids  = $this->getComputedConstantCompound('poids');
            } elseif (!$this->taille) {
                $poids  = $this->poids;
                $taille = $this->getComputedConstantCompound('taille');
            }
        }
        if ($poids && $taille) {
            $this->_imc = round($poids / ($taille * $taille * 0.0001), 2);
            /* Calcul surface corporelle */
            $this->_surface_corporelle = round(sqrt($poids * $taille / 3600), 2);
        }
    }

    /**
     * Compute the PeakFlow
     *
     * @return void
     */
    function getPeakFlow()
    {
        $taille = null;
        if ($this->taille) {
            $taille = $this->taille;
        } else {
            $taille = $this->getComputedConstantCompound('taille');
        }

        if ($taille && $this->_ref_patient->_annees != '??' && $this->_ref_patient->sexe) {
            //(H)DEPTh = Exp[(0,544 x Log(Age)) - (0,0151 x Age) - (74,7 / Taille) + 5,48]
            //((F)DEPTh = Exp[(0,376 x Log(Age)) - (0,0120 x Age) - (58,8 / Taille) + 5,63]
            if ($this->_ref_patient->sexe == 'f') {
                $depth = round(
                    exp((0.376 * log($this->_ref_patient->_annees)) - (0.0120 * $this->_ref_patient->_annees) - (58.8 / $taille) + 5.63),
                    2
                );
            } else {
                $depth = round(exp((0.544 * log($this->_ref_patient->_annees)) - (0.0151 * $this->_ref_patient->_annees) - (74.7 / $taille) + 5.48));
            }
            $this->_peak_flow = $depth;
        }
    }

    /**
     * Compute the weight variation between the previous measure and the current
     *
     * @param string $datetime The datetime to check
     *
     * @return null
     * @throws \Exception
     */
    public function getWeightVariation($datetime = null)
    {
        $this->loadRefPatient();
        if (!$datetime) {
            $datetime = $this->datetime;
        }

        [$constantes, $datetimes] = self::getLatestFor(
            $this->_ref_patient,
            CMbDT::dateTime('-1 second', $datetime),
            ['poids']
        );

        if ($constantes->poids) {
            $this->variation_poids = round($this->poids - $constantes->poids, 3);
            $this->variation_poids = $this->variation_poids > 0 ? "+$this->variation_poids" : $this->variation_poids;
        } else {
            $this->variation_poids = '';
        }
    }

    /**
     * Set the compounds for the computed constants, who needs to be set even if there is only one compound valued
     * For now, only the compounds of the IMC are added
     *
     * @return void
     */
    function setComputedConstantsCompounds()
    {
        if ($this->_id) {
            $compounds = ['taille', 'poids'];
            if (!array_key_exists($this->patient_id, self::$_computed_constants_compounds)) {
                self::$_computed_constants_compounds[$this->patient_id] = [];
                foreach ($compounds as $_compound) {
                    self::$_computed_constants_compounds[$this->patient_id][$_compound] = [];
                }
            }

            /* Recuperation de la valeur dans latest values */
            if (array_key_exists($this->patient_id, self::$_latest_values) && array_key_exists('', self::$_latest_values[$this->patient_id])) {
                foreach ($compounds as $_compound) {
                    if (isset(self::$_latest_values[$this->patient_id][''][0]->$_compound)) {
                        $datetime = self::$_latest_values[$this->patient_id][''][1][$_compound];
                        $value    = self::$_latest_values[$this->patient_id][''][0]->$_compound;
                        if (!array_key_exists($datetime, self::$_computed_constants_compounds[$this->patient_id][$_compound])) {
                            self::$_computed_constants_compounds[$this->patient_id][$_compound][$datetime] = $value;
                        }
                    }
                }
            }

            foreach ($compounds as $_compound) {
                if (isset($this->$_compound)) {
                    if (!array_key_exists($this->datetime, self::$_computed_constants_compounds[$this->patient_id][$_compound])) {
                        self::$_computed_constants_compounds[$this->patient_id][$_compound][$this->datetime] = $this->$_compound;
                    }
                }

                ksort(self::$_computed_constants_compounds[$this->patient_id][$_compound]);
            }
        }
    }

    /**
     * Return the value of the last constant set before the datetime of the current constant
     *
     * @param string $compound The compound name
     *
     * @return null|integer
     */
    function getComputedConstantCompound($compound)
    {
        $value = null;

        if (isset(self::$_computed_constants_compounds[$this->patient_id][$compound])) {
            foreach (self::$_computed_constants_compounds[$this->patient_id][$compound] as $_datetime => $_value) {
                if ($_datetime >= $this->datetime) {
                    break;
                }

                $value = $_value;
            }
        }

        return $value;
    }

    /**
     * Load the linked ConstantComments
     *
     * @return CConstantComment[]
     * @throws \Exception
     */
    public function loadRefsComments()
    {
        $comments = $this->loadBackRefs('comments');

        foreach ($comments as $_comment) {
            $this->_refs_comments["$_comment->constant"] = $_comment;
        }

        return $this->_refs_comments;
    }

    /**
     * Get the constantes values (first or last)
     *
     * @param int|CPatient $patient      The patient to load the constantes for
     * @param string       $datetime     The reference datetime
     * @param array        $selection    A selection of constantes to load
     * @param CMbObject    $context      The context
     * @param boolean      $use_cache    Force the function to return the latest_values is already set
     * @param string       $datetime_min A minimum datetime
     * @param string       $order        Order ASC or DESC
     *
     * @return array The constantes values and dates
     * @throws \Exception
     */
    static function getFor(
        $patient,
        $datetime = null,
        $selection = [],
        $context = null,
        $use_cache = true,
        $datetime_min = null,
        $order = "DESC",
        $offset = null,
        $cumul_end = null
    ) {
        $patient_id = ($patient instanceof CPatient) ? $patient->_id : $patient;

        if ($order == "DESC" && empty($selection) && isset(self::$_latest_values[$patient_id][$datetime]) && $use_cache === true) {
            return self::$_latest_values[$patient_id][$datetime];
        } elseif (empty($selection) && isset(self::$_first_values[$patient_id][$datetime]) && $use_cache === true) {
            return self::$_first_values[$patient_id][$datetime];
        }

        if (empty($selection)) {
            $list_constantes = CConstantesMedicales::$list_constantes;
        } else {
            $list_constantes = array_intersect_key(CConstantesMedicales::$list_constantes, array_flip($selection));
        }

        // Constante que l'on va construire
        $constante = new CConstantesMedicales();
        if (!$patient_id) {
            return [$constante, []];
        }

        $constante->patient_id = $patient_id;
        $constante->datetime   = CMbDT::dateTime();
        $constante->loadRefPatient();

        $where = [
            "patient_id" => "= '$patient_id'",
        ];

        if ($context) {
            $where["context_class"]   = " = '$context->_class'";
            $where["context_id"]      = " = '$context->_id'";
            $constante->context_class = $context->_class;
            $constante->context_id    = $context->_id;
        }

        if($datetime && $datetime_min) {
            $where[] = "datetime <= '$datetime' AND datetime >= '$datetime_min'";
        } elseif ($datetime) {
            $where["datetime"] = "<= '$datetime'";
        } elseif ($datetime_min) {
            $where["datetime"] = ">= '$datetime_min'";
        }

        if (is_countable($selection) && count($selection)) {
            $ors = [];
            foreach ($selection as $_item) {
                $ors[] = "$_item IS NOT NULL";
            }
            $where[] = implode(" OR ", $ors);
        }

        $count = $constante->countList($where);

        // Load all constants instead of checking every type to reduce number of SQL queries
        /** @var self[] $all_list */
        $all_list = [];
        if ($count <= 30) {
            $all_list = $constante->loadList($where, "datetime $order");
        }

        $list_datetimes = [];
        $list_contexts  = [];
        foreach ($list_constantes as $type => $params) {
            $list_datetimes[$type] = null;

            if ($type[0] == "_") {
                continue;
            }

            // Load them, if any ...
            if ($count > 0) {
                // Load them all and dispatch
                if ($count <= 30) {
                    $_offset = $offset ? 0 : null;
                    foreach ($all_list as $_const) {
                        $_value = $_const->$type;
                        if ($_value != null) {
                            if ($_offset !== null && $_offset !== $offset) {
                                $_offset++;
                                continue;
                            }
                            $constante->$type      = $_value;
                            $list_datetimes[$type] = $_const->datetime;
                            $list_contexts[$type]  = "$_const->context_class-$_const->context_id";
                            break;
                        }
                    }
                } // Or pick them one by one
                else {
                    $_where        = $where;
                    $_where[$type] = "IS NOT NULL";
                    $_list         = $constante->loadList(
                        $_where,
                        "datetime $order",
                        $offset !== null ? "$offset,1" : null,
                        null,
                        null,
                        "patient_id"
                    );
                    if (count($_list)) {
                        $_const                = reset($_list);
                        $constante->$type      = $_const->$type;
                        $list_datetimes[$type] = $_const->datetime;
                        $list_contexts[$type]  = "$_const->context_class-$_const->context_id";
                    }
                }
            }
        }

        // Cumul de la diurese
        if ($datetime) {
            foreach ($list_constantes as $_name => $_params) {
                if (!isset($_params['readonly']) && (isset($_params["cumul_reset_config"]) || isset($_params["formula"]))) {
                    $day_defore = CMbDT::dateTime("-24 hours", $datetime);

                    if (isset($_params["cumul_reset_config"]) && !isset($_params['formula'])) {
                        $cumul_field = '_' . $_name . '_cumul';
                        $reset_hour  = str_pad(self::getHostConfig($_params["cumul_reset_config"], $context), 2, '0', STR_PAD_LEFT);

                        if ($datetime >= CMbDT::format($datetime, "%Y-%m-%d $reset_hour:00:00")) {
                            $cumul_begin = CMbDT::format($datetime, "%Y-%m-%d $reset_hour:00:00");
                            $cumul_end   = $cumul_end ?: CMbDT::format(CMbDT::date('+1 DAY', $datetime), "%Y-%m-%d $reset_hour:00:00");
                        } else {
                            $cumul_begin = CMbDT::format(CMbDT::date('-1 DAY', $datetime), "%Y-%m-%d $reset_hour:00:00");
                            $cumul_end   = $cumul_end ?: CMbDT::format($datetime, "%Y-%m-%d $reset_hour:00:00");
                        }

                        $query = new CRequest();
                        $query->addSelect("SUM(`$_name`)");
                        $query->addTable('constantes_medicales');
                        $query->addWhere(
                            ["`datetime` >= '$cumul_begin'", "`datetime` <= '$cumul_end'", "`$_name` IS NOT NULL", "`patient_id` = $patient_id"]
                        );
                        $ds                      = CSQLDataSource::get('std');
                        $constante->$cumul_field = $ds->loadResult($query->makeSelect());
                    } else {
                        // cumul de plusieurs champs (avec formule)
                        $formula = $_params["formula"];

                        foreach ($formula as $_field => $_sign) {
                            $_where          = $where;
                            $_where[$_field] = "IS NOT NULL";
                            $_where[]        = "datetime >= '$day_defore'";

                            $_list = $constante->loadList($_where);

                            foreach ($_list as $_const) {
                                if ($_sign === "+") {
                                    $constante->$_name += $_const->$_field;
                                } else {
                                    $constante->$_name -= $_const->$_field;
                                }
                            }
                        }
                    }
                }
            }
        }

        $constante->updateFormFields();

        // Don't cache partial loadings
        if (empty($selection)) {
            if ($order == "DESC") {
                self::$_latest_values[$patient_id][$datetime] = [$constante, $list_datetimes];
            } else {
                self::$_first_values[$patient_id][$datetime] = [$constante, $list_datetimes];
            }
        }

        return [$constante, $list_datetimes, $list_contexts];
    }

    /**
     * Get the latest constantes values
     *
     * @param int|CPatient $patient      The patient to load the constantes for
     * @param string       $datetime     The reference datetime
     * @param array        $selection    A selection of constantes to load
     * @param CMbObject    $context      The context
     * @param boolean      $use_cache    Force the function to return the latest_values is already set
     * @param string       $datetime_min A minimum datetime
     *
     * @return array The constantes values and dates
     * @throws \Exception
     */
    static function getLatestFor($patient, $datetime = null, $selection = [], $context = null, $use_cache = true, $datetime_min = null)
    {
        return self::getFor($patient, $datetime, $selection, $context, $use_cache, $datetime_min);
    }

    /**
     * Get the nth-latest constantes values
     *
     * @param int|CPatient $patient      The patient to load the constantes for
     * @param string       $datetime     The reference datetime
     * @param array        $selection    A selection of constantes to load
     * @param CMbObject    $context      The context
     * @param boolean      $use_cache    Force the function to return the latest_values is already set
     * @param string       $datetime_min A minimum datetime
     * @param string       $offset       The nth rank to get
     *
     * @return array The constantes values and dates
     * @throws \Exception
     */
    static function getNthLatestFor(
        $patient,
        $datetime = null,
        $selection = [],
        $context = null,
        $use_cache = true,
        $datetime_min = null,
        $offset = 0
    ) {
        return self::getFor($patient, $datetime, $selection, $context, $use_cache, $datetime_min, "DESC", $offset);
    }

    /**
     * Get the first constantes values
     *
     * @param int|CPatient $patient      The patient to load the constantes for
     * @param string       $datetime     The reference datetime
     * @param array        $selection    A selection of constantes to load
     * @param CMbObject    $context      The context
     * @param boolean      $use_cache    Force the function to return the latest_values is already set
     * @param string       $datetime_min A minimum datetime
     *
     * @return array The constantes values and dates
     * @throws \Exception
     */
    static function getFirstFor($patient, $datetime = null, $selection = [], $context = null, $use_cache = true, $datetime_min = null)
    {
        return self::getFor($patient, $datetime, $selection, $context, $use_cache, $datetime_min, "ASC");
    }

    /**
     * Détermine la couleur à afficher en fonction des seuils d'alerte définis dans les paramètres
     *
     * @param float  $value         La valeur à vérifier
     * @param array  $params        Les paramètres concernés
     * @param string $default_color Couleur par défaut
     *
     * @return string
     */
    static function getColor($value, $params, $default_color = "#4DA74D")
    {
        $color = CValue::read($params, "color", $default_color);

        // Low value alert
        if (isset($params["alert_low"])) {
            [$_low, $_low_color] = $params["alert_low"];

            if ($value < $_low) {
                $color = $_low_color;
            }
        }

        // High value alert
        if (isset($params["alert_high"])) {
            [$_high, $_high_color] = $params["alert_high"];

            if ($value > $_high) {
                $color = $_high_color;
            }
        }

        return $color;
    }

    /**
     * Build constantes grid
     *
     * @param self[]    $list            The list of CConstantesMedicales objects to build the grid from
     * @param bool      $full            Display the full list of constantes
     * @param bool      $only_with_value Only display not null values
     * @param CMbObject $host            The host for getting the configurations
     *
     * @return array
     * @throws \Exception
     */
    static function buildGrid($list, $full = true, $only_with_value = false, $host = null)
    {
        $grid        = [];
        $selection   = array_keys(CConstantesMedicales::$list_constantes);
        $cumuls_day  = [];
        $reset_hours = [];
        $cumul_names = [];

        if (!$full) {
            $conf_constantes = array_filter(CConstantesMedicales::getRanksFor('form', $host));
            $selection       = array_keys($conf_constantes);

            foreach ($list as $_constante_medicale) {
                foreach (CConstantesMedicales::$list_constantes as $_name => $_params) {
                    if ($_constante_medicale->$_name != '' && !empty($_params["cumul_in"])) {
                        $selection   = array_merge($selection, $_params["cumul_in"]);
                        $cumul_names = array_merge($cumul_names, $_params["cumul_in"]);
                    }
                }
            }

            $selection   = array_unique($selection);
            $cumul_names = array_unique($cumul_names);
        }

        if ($only_with_value || !is_array($selection)) {
            $selection = [];
        }

        $names = $selection;

        foreach ($list as $_constante_medicale) {
            $_constante_medicale->loadRefUser();
            if (!isset($grid["$_constante_medicale->datetime $_constante_medicale->_id"])) {
                $data = [
                    "comment"        => $_constante_medicale->comment,
                    "displayComment" => CConstantesMedicales::getHostConfig('comment', $host),
                    "values"         => [],
                    'comments'       => [],
                    'alerts'         => [],
                    'object'         => [
                        'id'      => $_constante_medicale->_id,
                        'context' => "$_constante_medicale->context_class-$_constante_medicale->context_id",
                    ],
                    'author'         => 0,
                    'creation_date'  => 0,
                ];
                if ($_constante_medicale->_ref_user) {
                    $data['author']        = $_constante_medicale->_ref_user->_view;
                    $data['creation_date'] = CMbDT::format($_constante_medicale->getCreationDate(), '%d/%m/%y %H:%M');
                }

                $grid["$_constante_medicale->datetime $_constante_medicale->_id"] = $data;
            }

            foreach (CConstantesMedicales::$list_constantes as $_name => $_params) {
                if (in_array($_name, $selection) || in_array($_name, $cumul_names)
                    || ($_constante_medicale->$_name !== '' && $_constante_medicale->$_name !== null)
                ) {
                    $value = null;
                    if (isset($_params["cumul_for"]) || isset($_params["formula"])) {
                        // cumul
                        if (!isset($reset_hours[$_name])) {
                            $reset_hours[$_name] = self::getResetHour($_name, $host);
                        }
                        $reset_hour = $reset_hours[$_name];

                        $day_24h = CMbDT::transform("-$reset_hour hours", $_constante_medicale->datetime, '%y-%m-%d');

                        if (!isset($cumuls_day[$_name][$day_24h])) {
                            $cumuls_day[$_name][$day_24h] = [
                                "id"       => $_constante_medicale->_id,
                                "datetime" => $_constante_medicale->datetime,
                                "value"    => null,
                                "span"     => 0,
                                "pair"     => (isset($cumuls_day[$_name]) && count($cumuls_day[$_name])) % 2 ? "odd" : "even",
                                "day"      => CMbDT::transform($day_24h, null, "%a"),
                            ];
                        }

                        if (isset($_params["cumul_for"])) {
                            // cumul simple sur le meme champ
                            $cumul_for = $_params["cumul_for"];

                            if ($_constante_medicale->$cumul_for !== null) {
                                if ($_name == '__bilan_hydrique_cumul') {
                                    if ($_constante_medicale->datetime > $cumuls_day[$_name][$day_24h]['datetime']
                                        || $cumuls_day[$_name][$day_24h]['value'] == null
                                    ) {
                                        $cumuls_day[$_name][$day_24h]["value"] = $_constante_medicale->$cumul_for;
                                    }
                                } else {
                                    $cumuls_day[$_name][$day_24h]["value"] += $_constante_medicale->$cumul_for;
                                }
                            }
                        } else {
                            // cumul de plusieurs champs (avec formule)
                            $formula = $_params["formula"];

                            foreach ($formula as $_field => $_sign) {
                                $_value = $_constante_medicale->$_field;

                                if ($_constante_medicale->$_field !== null) {
                                    if ($_sign === "+") {
                                        $cumuls_day[$_name][$day_24h]["value"] += $_value;
                                    } else {
                                        $cumuls_day[$_name][$day_24h]["value"] -= $_value;
                                    }
                                }
                            }
                        }

                        $cumuls_day[$_name][$day_24h]["span"]++;

                        $value = "__empty__";
                    } else {
                        // valeur normale
                        $spec  = self::$list_constantes[$_name];
                        $value = $_constante_medicale->$_name;

                        if (isset($spec["formfields"])) {
                            $arr = [];
                            foreach ($spec["formfields"] as $ff) {
                                if ($_constante_medicale->$ff != "") {
                                    $arr[] = $_constante_medicale->$ff;
                                }
                            }
                            $value = implode(" / ", $arr);
                        }
                    }

                    $grid["$_constante_medicale->datetime $_constante_medicale->_id"]["values"][$_name] = $value;

                    if (array_key_exists($_name, $_constante_medicale->_refs_comments)) {
                        $_comment                                                                             = $_constante_medicale->_refs_comments[$_name];
                        $grid["$_constante_medicale->datetime $_constante_medicale->_id"]['comments'][$_name] = $_comment->comment;
                    }

                    $alert = self::checkAlert($_name, $_constante_medicale, $host);
                    if ($alert) {
                        $grid["$_constante_medicale->datetime $_constante_medicale->_id"]['alerts'][$_name] = $alert;
                    }

                    if (!in_array($_name, $names)) {
                        $names[] = $_name;
                    }
                }
            }
        }

        foreach ($cumuls_day as $_name => &$_days) {
            $_params = CConstantesMedicales::$list_constantes[$_name];

            if ($_name == '__bilan_hydrique_cumul') {
                ksort($_days);
                $prev_value = 0;
            }

            foreach ($_days as &$_values) {
                if ($_name == '__bilan_hydrique_cumul') {
                    $_value           = $_values['value'] - $prev_value;
                    $prev_value       = $_values['value'];
                    $_values['value'] = $_value;
                }

                $_color           = CConstantesMedicales::getColor($_values["value"], $_params, null);
                $_values["color"] = $_color;

                $grid[$_values["datetime"] . " " . $_values["id"]]["values"][$_name] = $_values;
            }
        }

        $names = self::sortConstNames($names, $host);

        return [
            $names,
            "names" => $names,
            $grid,
            "grid"  => $grid,
        ];
    }

    /**
     * Build constantes grid
     *
     * @param self      $constante       The CConstantesMedicales object containing the latest values
     * @param array     $dates           An array containing the date of the
     * @param bool      $full            Display the full list of constantes
     * @param bool      $only_with_value Only display not null values
     * @param CMbObject $host            The host for getting the configurations
     *
     * @return array
     */
    static function buildGridLatest($constante, $dates, $full = true, $only_with_value = false, $host = null)
    {
        $dates = CMbArray::flip($dates);
        if (array_key_exists('', $dates)) {
            unset($dates['']);
        }

        $grid        = [];
        $selection   = array_keys(CConstantesMedicales::$list_constantes);
        $cumuls_day  = [];
        $reset_hours = [];
        $cumul_names = [];

        if (!$full) {
            $conf_constantes = array_filter(CConstantesMedicales::getRanksFor('form', $host));
            $selection       = array_keys($conf_constantes);

            foreach (CConstantesMedicales::$list_constantes as $_name => $_params) {
                if ($constante->$_name != '' && !empty($_params["cumul_in"])) {
                    $selection   = array_merge($selection, $_params["cumul_in"]);
                    $cumul_names = array_merge($selection, $_params["cumul_in"]);
                }
            }

            $selection = array_unique($selection);
        }

        if ($only_with_value) {
            $selection = [];
        }

        $names = $selection;

        foreach ($dates as $_date => $_constants) {
            if (!isset($grid["$_date"])) {
                $grid["$_date"] = [
                    'comment'        => '',
                    'displayComment' => 0,
                    'values'         => [],
                    'comments'       => [],
                    'alerts'         => [],
                    'object'         => [
                        'id'      => $constante->_id,
                        'context' => "$constante->context_class-$constante->context_id",
                    ],
                    'author'         => 0,
                    'creation_date'  => 0,
                ];
            }

            foreach ($_constants as $_name) {
                $_params = CConstantesMedicales::$list_constantes[$_name];
                if (in_array($_name, $selection) || in_array($_name, $cumul_names)
                    || ($constante->$_name !== '' && $constante->$_name !== null)
                ) {
                    $value = null;
                    if (isset($_params["cumul_for"]) || isset($_params["formula"])) {
                        // cumul
                        if (!isset($reset_hours[$_name])) {
                            $reset_hours[$_name] = self::getResetHour($_name, $host);
                        }
                        $reset_hour = $reset_hours[$_name];

                        $day_24h = CMbDT::transform("-$reset_hour hours", $_date, '%y-%m-%d');

                        if (!isset($cumuls_day[$_name][$day_24h])) {
                            $cumuls_day[$_name][$day_24h] = [
                                "id"       => $constante->_id,
                                "datetime" => $_date,
                                "value"    => null,
                                "span"     => 0,
                                "pair"     => (@count($cumuls_day[$_name]) % 2 ? "odd" : "even"),
                                "day"      => CMbDT::transform($day_24h, null, "%a"),
                            ];
                        }

                        if (isset($_params["cumul_for"])) {
                            // cumul simple sur le meme champ
                            $cumul_for = $_params["cumul_for"];

                            if ($constante->$cumul_for !== null) {
                                $cumuls_day[$_name][$day_24h]["value"] += $constante->$cumul_for;
                            }
                        } else {
                            // cumul de plusieurs champs (avec formule)
                            $formula = $_params["formula"];

                            foreach ($formula as $_field => $_sign) {
                                $_value = $constante->$_field;

                                if ($constante->$_field !== null) {
                                    if ($_sign === "+") {
                                        $cumuls_day[$_name][$day_24h]["value"] += $_value;
                                    } else {
                                        $cumuls_day[$_name][$day_24h]["value"] -= $_value;
                                    }
                                }
                            }
                        }

                        $cumuls_day[$_name][$day_24h]["span"]++;

                        $value = "__empty__";
                    } else {
                        // valeur normale
                        $spec  = self::$list_constantes[$_name];
                        $value = $constante->$_name;

                        if (isset($spec["formfields"])) {
                            $arr = [];
                            foreach ($spec["formfields"] as $ff) {
                                if ($constante->$ff != "") {
                                    $arr[] = $constante->$ff;
                                }
                            }
                            $value = implode(" / ", $arr);
                        }
                    }

                    $grid["$_date"]["values"][$_name] = $value;

                    if (!in_array($_name, $names)) {
                        $names[] = $_name;
                    }
                }
            }
        }

        foreach ($cumuls_day as $_name => &$_days) {
            $_params = CConstantesMedicales::$list_constantes[$_name];

            foreach ($_days as &$_values) {
                $_color           = CConstantesMedicales::getColor($_values["value"], $_params, null);
                $_values["color"] = $_color;

                $grid[$_values["datetime"] . " " . $_values["id"]]["values"][$_name] = $_values;
            }
        }

        $names = self::sortConstNames($names, $host);
        krsort($grid);

        return [
            $names,
            "names" => $names,
            $grid,
            "grid"  => $grid,
        ];
    }

    /**
     * Sort constant names
     *
     * @param string[]  $names Constants to sort
     * @param CMbObject $host  Host object (to get the configuration from)
     *
     * @return array
     */
    static function sortConstNames($names, CMbObject $host = null)
    {
        $new_names = [];
        $constants = self::getConstantsByRank(false, false, $host);
        foreach ($constants["all"] as $_constants) {
            foreach ($_constants as $_constant) {
                if (in_array($_constant, $names)) {
                    $new_names[] = $_constant;
                    if (isset(self::$list_constantes[$_constant]["cumul_in"])) {
                        /* Don't add the formula cumul, because it can put the cumul in the order */
                        foreach (self::$list_constantes[$_constant]["cumul_in"] as $_cumul) {
                            if (!isset(self::$list_constantes[$_cumul]['formula'])) {
                                $new_names[] = $_cumul;
                            }
                        }
                    }
                }
            }
        }

        return array_unique($new_names);
    }

    /**
     * Get related constant values
     *
     * @param string[]  $selection Constants selection
     * @param CPatient  $patient   Related patient
     * @param CMbObject $context   Related context
     * @param null      $date_min  Minimal date
     * @param null      $date_max  Maximal date
     * @param null      $limit     Limit count
     *
     * @return self[]
     */
    static function getRelated(
        $selection,
        CPatient $patient,
        CMbObject $context = null,
        $date_min = null,
        $date_max = null,
        $limit = null
    ) {
        $where = [
            "patient_id" => " = '$patient->_id'",
        ];

        if ($context) {
            $where["context_class"] = " = '$context->_class'";
            $where["context_id"]    = " = '$context->_id'";
        }

        $whereOr = [];
        foreach ($selection as $name) {
            if ($name[0] === "_") {
                continue;
            }

            $whereOr[] = "`$name` IS NOT NULL";
        }
        $where[] = implode(" OR ", $whereOr);

        if ($date_min) {
            $where[] = "datetime >= '$date_min'";
        }

        if ($date_max) {
            $where[] = "datetime <= '$date_max'";
        }

        $constantes = new self;

        return array_reverse($constantes->loadList($where, "datetime DESC", $limit), true);
    }

    /**
     * Intialize params
     *
     * @return void
     */
    static function initParams()
    {
        // make a copy of the array as it will be modified
        $list_constantes = CConstantesMedicales::$list_constantes;

        foreach ($list_constantes as $_constant => &$_params) {
            self::$list_constantes_type[$_params["type"]][$_constant] = &$_params;

            // Champs de cumuls
            if (isset($_params["cumul_reset_config"])) {
                if (!isset(CConstantesMedicales::$list_constantes[$_constant]["cumul_in"])) {
                    CConstantesMedicales::$list_constantes[$_constant]["cumul_in"] = [];
                }

                if (empty($_params["formula"])) {
                    CMbArray::insertAfterKey(
                        CConstantesMedicales::$list_constantes,
                        $_constant,
                        "_{$_constant}_cumul",
                        [
                            "cumul_for" => $_constant,
                            "unit"      => $_params["unit"],
                        ]
                    );

                    CConstantesMedicales::$list_constantes[$_constant]["cumul_in"][] = "_{$_constant}_cumul";
                } else {
                    foreach ($_params["formula"] as $_const => $_sign) {
                        CConstantesMedicales::$list_constantes[$_const]["cumul_in"][] = $_constant;
                    }
                }
            }
        }
    }

    /**
     * Get the config from a host
     *
     * @param string                                          $name The config name
     * @param CMbObject|CGroups|CService|CConsultation|string $host The host object
     *
     * @return mixed
     */
    static function getHostConfig($name, $host)
    {
        $host = self::guessHost($host);

        if (in_array($name, ['selection', 'alerts', 'comment', 'show_cat_tabs', 'stacked_graphs'])
            && ($host instanceof CFunctions || $host instanceof CBlocOperatoire)
        ) {
            if ($name === 'selection') {
                if ($host instanceof CFunctions) {
                    $name = 'selection_cabinet';
                } elseif ($host instanceof CBlocOperatoire) {
                    $name = 'selection_bloc';
                }
            } elseif ($name == 'alerts') {
                if ($host instanceof CFunctions) {
                    $name = 'alerts_cabinet';
                } elseif ($host instanceof CBlocOperatoire) {
                    $name = 'alerts_bloc';
                }
            } elseif ($name == 'comment') {
                if ($host instanceof CFunctions) {
                    $name = 'comment_cabinet';
                } elseif ($host instanceof CBlocOperatoire) {
                    $name = 'comment_bloc';
                }
            }

            return CAppUI::conf("dPpatients CConstantesMedicales $name", $host);
        }

        $group_id    = null;
        $service_id  = null;
        $function_id = null;

        // Etablissement
        if ($host instanceof CGroups) {
            $group_id = $host->_id;
        }

        // Service
        if ($host instanceof CService) {
            $service_id = $host->_id;
            $group_id   = $host->group_id;
        }

        // Cabinet
        if ($host instanceof CFunctions) {
            $function_id = $host->_id;
            $group_id    = $host->group_id;
        }

        return self::getConfig($name, $group_id, $service_id, $function_id);
    }

    /**
     * Find the host from a context object
     *
     * @param CMbObject|string $context The context (séjour, rpu, service, etablissement)
     *
     * @return CGroups|CService|CFunctions|string
     */
    static function guessHost($context)
    {
        if ($context === "global") {
            return "global";
        }

        // Etablissement, service ou cabinet (deja un HOST)
        if (
            $context instanceof CGroups ||
            $context instanceof CService ||
            $context instanceof CFunctions ||
            $context instanceof CBlocOperatoire
        ) {
            return $context;
        }

        // Séjour d'urgence
        if ($context instanceof CSejour && in_array($context->type, CSejour::getTypesSejoursUrgence($context->praticien_id))) {
            $rpu = $context->loadRefRPU();
            if ($rpu && $rpu->_id) {
                $context = $rpu;
            }
        }

        // Sejour
        if ($context instanceof CSejour) {
            $affectation = $context->loadRefCurrAffectation();
            if (!$affectation->_id) {
                $context->loadRefsAffectations();
                $affectation = $context->_ref_last_affectation;
            }

            if ($affectation->_id) {
                return $affectation->loadRefService();
            } elseif ($context->service_id) {
                return $context->loadRefService();
            }
        }

        // Urgences
        if ($context instanceof CRPU) {
            /** @var CService $service */
            $service = null;

            if ($context->box_id) {
                return $context->loadRefBox()->loadRefService();
            }

            $sejour      = $context->loadRefSejour();
            $affectation = $sejour->loadRefCurrAffectation();
            if (!$affectation->_id) {
                $sejour->loadRefsAffectations();
                $affectation = $sejour->_ref_last_affectation;
            }

            $service = $affectation->loadRefService();

            if ($service && $service->_id) {
                return $service;
            }

            // Recherche du premier service d'urgences actif
            $group_id = CGroups::loadCurrent()->_id;
            $where    = [
                "group_id"  => "= '$group_id'",
                "urgence"   => "= '1'",
                "cancelled" => "= '0'",
            ];
            $service  = new CService();
            $service->loadObject($where, "nom");

            return $service;
        }

        // Utiliser le contexte de la consultation dans la cas des dossiers d'anesth
        if ($context instanceof CConsultAnesth) {
            $context = $context->loadRefConsultation();
        }

        // Utiliser le contexte du cabinet dans le cas des consultations
        if ($context instanceof CConsultation) {
            return $context->loadRefPlageConsult()->loadRefChir()->loadRefFunction();
        }

        return CGroups::loadCurrent();
    }

    /**
     * Get service or group specific configuration value
     *
     * @param string $name        Configuration name
     * @param int    $group_id    Group ID
     * @param int    $service_id  Service ID
     * @param int    $function_id Function ID
     *
     * @return mixed
     */
    static function getConfig($name, $group_id = null, $service_id = null, $function_id = null)
    {
        if (!$service_id) {
            if (isset($_SESSION["soins"]["service_id"])) {
                $service_id = $_SESSION["soins"]["service_id"];
            } elseif (isset($_SESSION["ecap"]["service_id"])) {
                $service_id = $_SESSION["ecap"]["service_id"];
            }
        }

        $guid = "global";
        if ($service_id && is_numeric($service_id)) {
            $guid = "CService-$service_id";
        } elseif ($function_id && is_numeric($function_id)) {
            $guid = "CFunctions-$function_id";
        } elseif ($group_id && is_numeric($group_id)) {
            $guid = "CGroups-$group_id";
        }

        return CAppUI::conf("dPpatients CConstantesMedicales $name", $guid);
    }

    /**
     * Get the constants's ranks, for the graphs or the form
     *
     * @param string           $type graph or form
     * @param CMbObject|string $host Host from which we'll get the configuration
     *
     * @return array
     */
    static function getRanksFor($type = 'form', $host = null)
    {
        if ($host) {
            $configs = CConstantesMedicales::getHostConfig('selection', $host);
        } else {
            $configs = CConstantesMedicales::getConfig('selection');
        }

        $id = $type == 'graph' ? 1 : 0;
        foreach ($configs as $_constant => $_config) {
            $_config             = explode('|', $_config);
            $configs[$_constant] = $_config[$id];
        }

        return $configs;
    }

    /**
     * Get the alerts from the configs by service, bloc or function
     *
     * @param CMbObject|string $host Host from which we'll get the configuration
     *
     * @return array
     */
    public static function getAlertsFor($host = null)
    {
        if ($host) {
            $configs = CConstantesMedicales::getHostConfig('alerts', $host);
        } else {
            $configs = CConstantesMedicales::getConfig('alerts');
        }

        foreach ($configs as $_constant => $_config) {
            $_config             = explode('|', $_config);
            $configs[$_constant] = [
                'lower_threshold' => $_config[0],
                'upper_threshold' => $_config[1],
                'lower_text'      => $_config[2],
                'upper_text'      => $_config[3],
            ];
        }

        return $configs;
    }

    /**
     * Check if the given constant raise an alert. Return false if not, and the alert's otherwise
     *
     * @param string               $name     The name of the constant
     * @param CConstantesMedicales $constant The constant object
     * @param CMbObject|string     $host     The host for getting the configs
     *
     * @return string
     * @throws \Exception
     */
    public static function checkAlert($name, $constant, $host)
    {
        $result = false;
        $alerts = CConstantesMedicales::getAlertsFor($host);

        /* Alerts if the weight of a baby in it's birth is lower than 90% of the birth weight */
        $constant->loadRefContext();
        if ($constant->_ref_context && $constant->_ref_context->_class == 'CSejour') {
            /** @var CSejour $sejour */
            $sejour = $constant->_ref_context;

            // Birth bug cache
            if (isset(self::$cache_naissance[$sejour->_id])) {
                $naissance = self::$cache_naissance[$sejour->_id];
            } else {
                $naissance = self::$cache_naissance[$sejour->_id] = $sejour->loadRefNaissance();
            }

            if ($naissance && $naissance->_id) {
                [$value, $datetime, $context] = self::getFor($sejour->patient_id, null, ['poids'], $sejour, false, null, 'ASC');
                if ($value && $value->poids) {
                    $alerts['_poids_g']['lower_threshold'] = round($value->poids * 900, 2);
                    $alerts['_poids_g']['upper_threshold'] = '';
                    $alerts['_poids_g']['lower_text']      = CAppUI::tr('CConstantesMedicales-msg-weight_inferior_to_90%_of_the_birth_weight');
                    $alerts['_poids_g']['upper_text']      = '';
                }
            }
        }

        if (!is_null($constant->$name) && $constant->$name != '' && array_key_exists($name, $alerts)
            && ($alerts[$name]['lower_threshold'] != '' || $alerts[$name]['upper_threshold'] != '')
        ) {
            $alert = $alerts[$name];

            if (array_key_exists('formfields', self::$list_constantes[$name])) {
                $lower_threshold = explode('/', $alert['lower_threshold']);
                $upper_threshold = explode('/', $alert['upper_threshold']);

                foreach (self::$list_constantes[$name]['formfields'] as $_index => $_field) {
                    if (array_key_exists($_index, $lower_threshold) && $lower_threshold[$_index] != ''
                        && $constant->$_field < $lower_threshold[$_index]
                    ) {
                        $result = CAppUI::tr("CConstantesMedicales-$name") . ' '
                            . CAppUI::tr("CConstantesMedicales-$_field") . " < {$lower_threshold[$_index]} :";

                        if ($alert['lower_text'] != '') {
                            $result .= '<br/>' . CMbString::makeUrlHyperlinks($alert['lower_text']);
                        }
                        break;
                    } elseif (array_key_exists($_index, $upper_threshold) && $upper_threshold[$_index] != ''
                        && $constant->$_field > $upper_threshold[$_index]
                    ) {
                        $result = CAppUI::tr("CConstantesMedicales-$name") . ' '
                            . CAppUI::tr("CConstantesMedicales-$_field") . " > {$upper_threshold[$_index]} :";

                        if ($alert['upper_text'] != '') {
                            $result .= '<br/>' . CMbString::makeUrlHyperlinks($alert['upper_text']);
                        }
                        break;
                    }
                }
            } else {
                if ($alert['lower_threshold'] != '' && $constant->$name < $alert['lower_threshold']) {
                    $result = CAppUI::tr("CConstantesMedicales-$name") . " < {$alert['lower_threshold']} :";

                    if ($alert['lower_text'] != '') {
                        $result .= '<br/>' . CMbString::makeUrlHyperlinks($alert['lower_text']);
                    }
                } elseif ($alert['upper_threshold'] != '' && $constant->$name > $alert['upper_threshold']) {
                    $result = CAppUI::tr("CConstantesMedicales-$name") . " > {$alert['upper_threshold']} :";

                    if ($alert['upper_text'] != '') {
                        $result .= '<br/>' . CMbString::makeUrlHyperlinks($alert['upper_text']);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Return the constants, ordered by rank
     *
     * @param string           $type           'form' or 'graph'
     * @param boolean          $order_by_types If false, the constants won't be ordered by types,
     *                                         even if the config show_cat_tabs is set to true
     * @param CMbObject|string $host           Host from which we'll get the configuration
     * @param boolean          $show_disabled  Include the constants with a rank of -1
     *
     * @return array
     */
    static function getConstantsByRank($type = 'form', $order_by_types = true, $host = null, $show_disabled = false)
    {
        $selection = self::getRanksFor($type, $host);

        $list_constants = CConstantesMedicales::$list_constantes;

        // Keep only valid constant names
        $selection = array_intersect_key($selection, $list_constants);

        $selection = CMbArray::flip($selection);
        ksort($selection);

        $result = [];
        if ($order_by_types) {
            foreach ($selection as $_rank => $_constants) {
                foreach ($_constants as $_constant) {
                    $_type = $list_constants[$_constant]["type"];

                    if (!array_key_exists($_type, $result)) {
                        $result["$_type"] = [];
                    }
                    if (!array_key_exists($_rank, $result["$_type"])) {
                        $result["$_type"][$_rank] = [];
                    }

                    $result["$_type"][$_rank][] = $_constant;
                }
            }
        } else {
            $result["all"] = $selection;
        }

        foreach ($result as $_type => $_ranks) {
            if (array_key_exists(0, $result[$_type])) {
                $unselected_constants = $result[$_type][0];
                unset($result[$_type][0]);
                $result[$_type]["hidden"] = $unselected_constants;
            }

            if (array_key_exists(-1, $result[$_type])) {
                if ($show_disabled) {
                    $disabled_constants         = $result[$_type][-1];
                    $result[$_type]["disabled"] = $disabled_constants;
                }
                unset($result[$_type][-1]);
            }
        }

        return $result;
    }

    /**
     * Return the selected constants in an formatted array (see getConstantsByRank to see the format)
     *
     * @param array            $selection The constant you want to select
     * @param string           $type      'form' or 'graph'
     * @param CMbObject|string $host      Host from which we'll get the configuration
     *
     * @return array
     */
    static function selectConstants($selection, $type = 'form', $host = null)
    {
        if ($host) {
            $show_cat_tabs = CConstantesMedicales::getHostConfig("show_cat_tabs", $host);
        } else {
            $show_cat_tabs = CConstantesMedicales::getConfig("show_cat_tabs");
        }
        $constants_by_rank = self::getRanksFor($type, $host);
        $list_constants    = CConstantesMedicales::$list_constantes;

        // Keep only valid constant names
        $constants_by_rank = array_intersect_key($constants_by_rank, $list_constants);

        $constants_by_rank = CMbArray::flip($constants_by_rank);
        ksort($constants_by_rank);

        $result = [];
        foreach ($constants_by_rank as $_rank => $_constants) {
            if ($_rank === -1) {
                continue;
            }
            foreach ($_constants as $_constant) {
                if (strpos($_constant, "_") === 0 && $_constant != '_poids_g') {
                    continue;
                }

                if ($show_cat_tabs) {
                    $_type = $list_constants[$_constant]["type"];

                    if (!array_key_exists($_type, $result)) {
                        $result[$_type] = [];
                    }

                    if (!in_array($_constant, $selection)) {
                        $rank = -1;
                    } else {
                        $rank = $_rank;
                    }
                    if (!array_key_exists($rank, $result[$_type])) {
                        $result[$_type][$rank] = [];
                    }

                    $result[$_type][$rank][] = $_constant;
                } else {
                    if (!array_key_exists('all', $result)) {
                        $result['all'] = [];
                    }

                    if (!in_array($_constant, $selection)) {
                        $rank = -1;
                    } else {
                        $rank = $_rank;
                    }

                    if (!array_key_exists($rank, $result['all'])) {
                        $result['all'][$rank] = [];
                    }

                    $result['all'][$rank][] = $_constant;
                }
            }
        }
        foreach ($result as $_type => $_ranks) {
            if (array_key_exists(-1, $result[$_type])) {
                $unselected_constants = $result[$_type][-1];
                unset($result[$_type][-1]);
                $result[$_type]["hidden"] = $unselected_constants;
            }

            if (array_key_exists(-1, $result[$_type])) {
                unset($result[$_type][-1]);
            }
        }

        return $result;
    }

    /**
     * Get reset hour
     *
     * @param string    $name Reset name
     * @param CMbObject $host The host of the configs
     *
     * @return mixed
     */
    static function getResetHour($name, $host = null)
    {
        $list = CConstantesMedicales::$list_constantes;

        if (isset($list[$name]["cumul_reset_config"])) {
            $confname = $list[$name]["cumul_reset_config"];
        } else {
            $confname = $list[$list[$name]["cumul_for"]]["cumul_reset_config"];
        }

        return self::getHostConfig($confname, $host);
    }

    static function getValeursHydriques(CMbObject $context, $datetime_min = null, $datetime_max = null)
    {
        if (!$context->_id) {
            return [];
        }

        $cst = new CConstantesMedicales();
        $ds  = $cst->getDS();

        $value_fields = [
            "entree_hydrique"    => +1,
            "entree_lavage"      => +1,
            "drain_jejunostomie" => +1,
            "drain_gastrostomie" => +1,
            "sng"                => +1,
            "selles_volume"      => -1,
            "vac"                => -1,
            'reliquat_perf'      => -1,
            "drain_mediastinal"  => -1,
            "drain_dve"          => -1,
            "drain_kher"         => -1,
            "drain_ileostomie"   => -1,
            "sonde_rectale"      => -1,
            "sortie_lavage"      => -1,
            "redon"              => -1,
            "redon_2"            => -1,
            "redon_3"            => -1,
            "redon_4"            => -1,
            "redon_5"            => -1,
            "redon_6"            => -1,
            "redon_7"            => -1,
            "redon_8"            => -1,
            "redon_9"            => -1,
            "redon_10"           => -1,
            "redon_11"           => -1,
            "redon_12"           => -1,
            "redon_accordeon_1"  => -1,
            "redon_accordeon_2"  => -1,
            "redon_accordeon_3"  => -1,
            "redon_accordeon_4"  => -1,
            "redon_accordeon_5"  => -1,
            "redon_accordeon_6"  => -1,
            "lame_1"             => -1,
            "lame_2"             => -1,
            "lame_3"             => -1,
            "drain_1"            => -1,
            "drain_2"            => -1,
            "drain_3"            => -1,
            "drain_thoracique_1" => -1,
            "drain_thoracique_2" => -1,
            "drain_thoracique_3" => -1,
            "drain_thoracique_4" => -1,
            "drain_pleural_1"    => -1,
            "drain_pleural_2"    => -1,
            "drain_pleural_3"    => -1,
            "drain_pleural_4"    => -1,
            "drain_orifice_1"    => -1,
            "drain_orifice_2"    => -1,
            "drain_orifice_3"    => -1,
            "drain_orifice_4"    => -1,
            "ponction_ascite"    => -1,
            "ponction_pleurale"  => -1,
            "drain_colostomie"   => -1,
            "vomiting"           => -1,
            "scurasil_1"         => -1,
            "scurasil_2"         => -1,
            "psl"                => +1,
            "perspiration"       => -1,
            "liquide_gastrique"  => -1,
        ];

        foreach (self::$list_constantes["_diurese"]["formula"] as $_cste => $_signe) {
            $value_fields[$_cste] = $_signe == "+" ? -1 : +1;
        }

        $where = [
            "context_id"    => "= '$context->_id'",
            "context_class" => "= '$context->_class'",
        ];

        if ($datetime_min) {
            if ($datetime_max) {
                $where[] = "datetime >= '$datetime_min' AND datetime < '$datetime_max'";
            } else {
                $where["datetime"] = ">= '$datetime_max'";
            }
        }

        if ($datetime_max && !$datetime_min) {
            $where["datetime"] = "< '$datetime_max'";
        }

        $where_fields = [];
        foreach ($value_fields as $_field => $_sign) {
            $where_fields[] = "$_field IS NOT NULL";
        }
        $where[] = implode(" OR ", $where_fields);

        $fields  = [
            "datetime",
            "constantes_medicales_id",
        ];
        $request = new CRequest();
        $request->addWhere($where);
        $request->addOrder($fields);
        $request->addSelect(array_merge($fields, array_keys($value_fields)));
        $query = $request->makeSelect($cst);

        $constantes = $ds->loadList($query);

        $values = [];
        foreach ($constantes as $_cst) {
            $_total = 0;

            $detail = [];

            foreach ($value_fields as $_field => $_sign) {
                if ($_cst[$_field] != null) {
                    $detail[$_field] = $_cst[$_field] * $_sign;
                }
                $_total += $_cst[$_field] * $_sign;
            }

            if (isset($values[$_cst['datetime']])) {
                $values[$_cst['datetime']]["value"] += $_total;
                $values[$_cst['datetime']]["detail"] = array_merge($values[$_cst['datetime']]["detail"], $detail);
            } else {
                $values[$_cst['datetime']] = [
                    "id"     => $_cst["constantes_medicales_id"],
                    "value"  => $_total,
                    "detail" => $detail,
                ];
            }
        }

        return $values;
    }

    static function calculBilanHydrique($constantes, $perfusions)
    {
        $bilan = [];
        $cumul = 0;

        foreach ($constantes as $_dt_cst => $_cst) {
            $_cumul_perf = 0;
            foreach ($perfusions as $_dt_perf => $_perf_value) {
                if ($_dt_perf <= $_dt_cst) {
                    $_cumul_perf += $_perf_value;
                    unset($perfusions[$_dt_perf]);
                } else {
                    break;
                }
            }

            $cumul += $_cumul_perf + $_cst["value"];

            $bilan[$_dt_cst] = [
                "id"    => $_cst["id"],
                "cumul" => $cumul,
            ];
        }

        foreach ($perfusions as $datetime_perf => $_perf) {
            $cumul                 += $_perf;
            $bilan[$datetime_perf] = [
                "id"    => null,
                "cumul" => $cumul,
            ];
        }

        return $bilan;
    }

    /**
     * Compute the early warning signs constant
     *
     * @return void
     */
    public function computeEarlyWarningSigns()
    {
        $diurese = self::computeDiurese(
            $this->patient_id,
            CMbDT::dateTime('-4 hour', $this->datetime),
            $this->datetime,
            $this->context_class,
            $this->context_id
        );

        $this->updatePlainFields();

        if (($this->frequence_respiratoire != null || $this->ta != null || $this->spo2 != null
                || $this->pouls != null || $this->conscience != null || $diurese != null) && $this->patient_id
        ) {
            $this->early_warning_signs = 0;

            if (($this->frequence_respiratoire < 5 || $this->frequence_respiratoire > 25) && $this->frequence_respiratoire != null) {
                $this->early_warning_signs += 3;
            } elseif (($this->frequence_respiratoire >= 5 && $this->frequence_respiratoire <= 8)
                || ($this->frequence_respiratoire >= 21 && $this->frequence_respiratoire <= 25)
            ) {
                $this->early_warning_signs += 2;
            }

            if (($this->spo2 < 91 && $this->spo2 != null)
            ) {
                $this->early_warning_signs += 3;
            }

            $ta_systole = null;

            if ($this->ta != null && $this->ta != '') {
                [$ta_systole, $ta_diastole] = explode('|', $this->ta);
            }

            if ($ta_systole < 8 && $ta_systole != null) {
                $this->early_warning_signs += 3;
            } elseif (($ta_systole >= 8 && $ta_systole <= 8.9) || $ta_systole > 18) {
                $this->early_warning_signs += 2;
            } elseif (($ta_systole >= 9 && $ta_systole <= 9.9)) {
                $this->early_warning_signs += 1;
            }

            if (($this->pouls < 40 || $this->pouls > 130) && $this->pouls != null) {
                $this->early_warning_signs += 3;
            } elseif (($this->pouls >= 40 && $this->pouls <= 49) || ($this->pouls >= 111 && $this->pouls <= 130)) {
                $this->early_warning_signs += 2;
            } elseif (($this->pouls >= 101 && $this->pouls <= 110)) {
                $this->early_warning_signs += 1;
            }

            if ($this->conscience == 3) {
                $this->early_warning_signs += 3;
            } elseif ($this->conscience == 2) {
                $this->early_warning_signs += 2;
            } elseif ($this->conscience == 1) {
                $this->early_warning_signs += 1;
            }

            if ($diurese < 80 && $diurese !== null) {
                $this->early_warning_signs += 3;
            } elseif ($diurese >= 80 && $diurese <= 120) {
                $this->early_warning_signs += 2;
            }
        }
    }

    /**
     * Load the constants for the given context and between the given dates, and compute the diurese
     *
     * @param integer $patient_id    The patient id
     * @param string  $date_min      The minimum datetim
     * @param string  $date_max      The maximum datetime
     * @param string  $context_class The context class
     * @param integer $context_id    The context id
     *
     * @return int|null Null if no constants corresponds to the given parameters
     * @throws \Exception
     */
    public static function computeDiurese($patient_id, $date_min, $date_max, $context_class = null, $context_id = null)
    {
        $diurese = null;
        $query   = new CRequest();
        $where   = [
            'patient_id' => " = $patient_id",
            'datetime'   => " BETWEEN '$date_min' AND '$date_max'",
        ];

        if ($context_class && $context_id) {
            $where['context_class'] = " = '$context_class'";
            $where['context_id']    = " = $context_id";
        }

        $columns = [];
        $whereOr = [];

        foreach (array_keys(self::$list_constantes['_diurese']['formula']) as $_field) {
            $columns[] = $_field;
            $whereOr[] = " `$_field` IS NOT NULL";
        }

        $where[] = implode(' OR ', $whereOr);

        $query->addTable('constantes_medicales');
        $query->addSelect($columns);
        $query->addWhere($where);

        /** @var CConstantesMedicales[] $constants */
        $ds = CSQLDataSource::get('std');

        $result = $ds->exec($query->makeSelect());
        if ($result) {
            while ($row = $ds->fetchAssoc($result)) {
                foreach (self::$list_constantes['_diurese']['formula'] as $_field => $_sign) {
                    if ($row[$_field] !== null) {
                        if ($diurese === null) {
                            $diurese = 0;
                        }
                        $diurese += $row[$_field] * ($_sign == '+' ? 1 : -1);
                    }
                }
            }
        }

        return $diurese;
    }

    static function getMoreConstantes($initial_constantes, $sejour_id, $date_min, $date_max)
    {
        $constante = new CConstantesMedicales();
        $where     = [
            "context_class" => "= 'CSejour'",
            "context_id"    => "= '$sejour_id'",
        ];
        if ($date_min && $date_max) {
            $where[] = "datetime BETWEEN '$date_min' AND '$date_max'";
        }
        $other_constantes = $constante->loadList($where);
        $more_constantes  = [];
        foreach ($other_constantes as $_other_constante) {
            foreach (array_keys(CConstantesMedicales::$list_constantes) as $_constante) {
                if ($_other_constante->$_constante && !in_array($_constante, $initial_constantes)) {
                    $more_constantes[] = $constantes[] = $_constante;
                }
            }
        }
        array_unique($more_constantes);

        return $more_constantes;
    }

    /**
     * Charge la dernière valeur de la constante donnée selon un référentiel de date donné
     *
     * @param integer  $patient_id Patient
     * @param array    $constantes Constantes à récupérer
     * @param dateTime $datetime   Date et heure des constantes
     *
     * @return integer
     * @throws \Exception
     */
    static function getFastConstantes($patient_id, array $constantes, $datetime = null)
    {
        if (!$patient_id) {
            return null;
        }

        $constantes = array_unique($constantes);

        $result = [];
        foreach ($constantes as $_constante) {
            $result[$_constante] = CConstantesMedicales::getFastConstante($patient_id, $_constante, $datetime);
        }

        return $result;
    }

    /**
     * Charge la dernière valeur de la constante donnée selon un référentiel de date donné
     *
     * @param integer  $patient_id Patient
     * @param string   $constante  Constante à récupérer
     * @param dateTime $datetime   Date et heure des constantes
     *
     * @return integer
     * @throws \Exception
     */
    static function getFastConstante($patient_id, $constante, $datetime = null)
    {
        if (!$patient_id || $constante[0] == '_' || !array_key_exists($constante, self::$list_constantes)) {
            return null;
        }

        $ds = CSQLDataSource::get('std');

        $datetime = ($datetime) ?: CMbDT::dateTime();
        $request  = new CRequest();
        $where    = [
            'patient_id' => $ds->prepare('= ?', $patient_id),
            'datetime'   => $ds->prepare('<= ?', $datetime),
            $constante   => 'IS NOT NULL',
        ];

        $request->addSelect($constante);
        $request->addTable('constantes_medicales');
        $request->addWhere($where);
        $request->addOrder('datetime DESC');
        $request->setLimit(1);

        return $ds->loadResult($request->makeSelect());
    }

    /**
     * Ajout des constantes médicales dans les champs de modèles
     *
     * @param CSejour|CConsultation $object   Contexte de récupération des constantes
     * @param CTemplateManager      $template Le template où sont ajoutées les constantes
     * @param string                $prefix   Nom de la section
     * @param string                $field    Nom de la sous-section
     *
     * @throws \Exception
     */
    static function fillLiteLimitedTemplate($object, &$template, $prefix = "Sejour", $field = "Constantes")
    {
        $object->loadListConstantesMedicales();
        $consts = [];

        if (!empty($object->_list_constantes_medicales)) {
            $consts[] = reset($object->_list_constantes_medicales);

            if (count($object->_list_constantes_medicales) >= 2) {
                $consts[] = end($object->_list_constantes_medicales);
                reset($object->_list_constantes_medicales);
            }
        }

        $grid_complet = CConstantesMedicales::buildGrid($consts, true);
        $grid_minimal = CConstantesMedicales::buildGrid($consts, false);
        $grid_valued  = CConstantesMedicales::buildGrid($consts, false, true);

        self::addConstantesTemplate($template, $grid_complet, $grid_minimal, $grid_valued, $prefix, $field);
    }

    static function addConstantesTemplate(&$template, $grid_complet, $grid_minimal, $grid_valued, $prefix = "Sejour", $field = "Constantes")
    {
        $smarty = new CSmartyDP("modules/dPpatients");

        $cstes = [];

        // Horizontal
        $smarty->assign("constantes_medicales_grid", $grid_complet);
        $smarty->assign('view', 'document');
        $cstes["Full horizontal mode"] = $smarty->fetch("print_constantes.tpl");

        $smarty->assign("constantes_medicales_grid", $grid_minimal);
        $smarty->assign('view', 'document');
        $cstes["Horizontal minimal mode"] = $smarty->fetch("print_constantes.tpl");

        $smarty->assign("constantes_medicales_grid", $grid_valued);
        $smarty->assign('view', 'document');
        $cstes["Mode with horizontal value|pl"] = $smarty->fetch("print_constantes.tpl");

        // Vertical
        $smarty->assign("constantes_medicales_grid", $grid_complet);
        $smarty->assign('view', 'document');
        $cstes["Vertical full mode"] = $smarty->fetch("print_constantes_vert.tpl");

        $smarty->assign("constantes_medicales_grid", $grid_minimal);
        $smarty->assign('view', 'document');
        $cstes["Vertical minimal mode"] = $smarty->fetch("print_constantes_vert.tpl");

        $smarty->assign("constantes_medicales_grid", $grid_valued);
        $smarty->assign('view', 'document');
        $cstes["Mode with vertical value|pl"] = $smarty->fetch("print_constantes_vert.tpl");

        foreach ($cstes as $_mode => $_constantes) {
            $_constantes = preg_replace('`([\\n\\r])`', '', $_constantes);
            $_constantes = preg_replace('/<br>/', '<br />', $_constantes);
            $template->addProperty("$prefix - $field - " . CAppUI::tr("CConstantesMedicales-$_mode"), $_constantes, '', false);
        }
    }

    static function fillLiteLimitedTemplate2($constantes, &$template, $first = true, $prefix = "Sejour", $nth = false)
    {
        $prefix = "$prefix - " .
            CAppUI::tr('CPatient-back-constantes') .
            " (" .
            ($first ?
                CAppUI::tr('common-First') :
                ($nth ? CAppUI::tr('CConstantesMedicales-last 3 statements') : CAppUI::tr('common-Last'))) .
            ($nth ? "" : (" " . CAppUI::tr('CConstantesMedicales-statement'))) .
            ")";

        if (!is_array($constantes)) {
            $constantes = [$constantes];
        }

        foreach (array_keys(self::$list_constantes) as $_cste) {
            if ($_cste[0] == "_") {
                continue;
            }

            $values = [];
            $unit   = self::$list_constantes[$_cste]["unit"];

            foreach ($constantes as $_constantes) {
                $value = $_constantes->$_cste;
                if (array_key_exists('formfields', self::$list_constantes[$_cste])) {
                    $value = '';
                    for ($i = 0; $i < count(self::$list_constantes[$_cste]['formfields']); $i++) {
                        if ($i) {
                            $value .= ' / ';
                        }

                        $_field = self::$list_constantes[$_cste]['formfields'][$i];
                        $value  .= $_constantes->$_field;
                    }
                }

                if ($value !== null) {
                    $values[] = $value . " " . $unit;
                }
            }

            $template->addProperty("$prefix - " . CAppUI::tr("CConstantesMedicales-$_cste"), implode(", ", $values));
        }
    }

    /*
   * Vérification selon pref et conf si l'on peut modifier un relevé de constantes
   *
   * @param CCanDo     $perms         Patient
   * @param CConstante $constantes    Constante
   * @param int        $modif_timeout Timeout
   * @param str        $context_guid  Contexte
   * @param bool       $can_edit      Edition
   *
   * @return array
   */
    function getEditReleve($perms, $constantes, $modif_timeout, $context_guid, $can_edit)
    {
        $user               = CMediusers::get();
        $disable_edit_motif = '';
        /* Vérification de la préférence fonctionnelle permettant de modifier une constante si l'utilisateur n'en est pas l'auteur */
        if ($perms->edit && $constantes->_id
            && ($constantes->user_id != $user->_id && CAppUI::pref('edit_constant_when_not_creator') == '0')) {
            $can_edit           = 0;
            $disable_edit_motif = 'not_creator';
        } /* Vérification de la configuration sur le délai de modification des constantes */
        elseif ($perms->edit && $constantes->_id
            && ($modif_timeout > 0 && (time() - strtotime($constantes->datetime)) > ($modif_timeout * 3600))) {
            $can_edit           = 0;
            $disable_edit_motif = 'timeout';
        } else {
            $modif_timeout = 0;
        }

        /* Gestion des droits d'edition sur les constantes */
        if (is_null($can_edit)) {
            /* Impossible d'éditer si on est pas dans le contexte actuel */
            if ($constantes->_id && $context_guid != $constantes->_ref_context->_guid) {
                $can_edit = 0;
            } else {
                $can_edit = $perms->edit;
            }
        }

        return [$can_edit, $disable_edit_motif, $modif_timeout];
    }

    /**
     * Calculate the IGS score using the values from the past 24 hours
     *
     * @param CPatient  $patient       the patient involved
     * @param CMbDT     $date          the date that is taken into account (without the 24h)
     * @param bool      $return_values set to true if you want to return the values (BP, CF, Glasgow...)
     * @param CMbObject $context       the context in which we are looking the constants
     *
     * @return CExamIgs|array The IGS object or an array of values
     * @throws Exception
     */
    function calculateIGSScore($patient, $date, CMbObject $context, $return_values = false)
    {
        $date      = ($date) ?: CMbDT::dateTime();
        $yesterday = CMbDT::dateTime("-24 HOURS", $date);

        // Use constants only if patient in USC
        $in_usc = false;

        if ($context instanceof CSejour) {
            $curr_aff = $context->loadRefCurrAffectation($date);

            if ($curr_aff->loadRefService()->usc) {
                $in_usc = true;
            }
        }


        $ds    = CSQLDataSource::get("std");
        $where = [
            "patient_id" => $ds->prepare("= ?", $patient->_id),
            "datetime"   => $ds->prepare("> ?", $yesterday),
        ];

        $constants = $in_usc ? (new CConstantesMedicales())->loadList($where) : [];

        $constant_diurese = $in_usc ? CConstantesMedicales::getFor($patient, $date, null, $context) : null;

        $worse_constants = [];
        $exam_igs        = new CExamIgs();

        // Age
        $exam_igs->age = '18'; // Else
        $age           = $patient->_annees;

        if ($age < 40) {
            $exam_igs->age = '0';
        } elseif ($age <= 59) {
            $exam_igs->age = '7';
        } elseif ($age <= 69) {
            $exam_igs->age = '12';
        } elseif ($age <= 74) {
            $exam_igs->age = '15';
        } elseif ($age <= 79) {
            $exam_igs->age = '16';
        }

        // Diuresis
        // Just in case ... :)
        if (is_array($constant_diurese) && isset($constant_diurese[0]) && $constant_diurese[0] instanceof CConstantesMedicales) {
            if (trim($constant_diurese[0]->_diurese)) {
                $diuresis     = $constant_diurese[0]->_diurese;
                $igs_diuresis = '0'; // Else

                if ($diuresis < 500) {
                    $igs_diuresis = '11';
                } elseif ($diuresis < 1000) {
                    $igs_diuresis = '4';
                }

                if ($igs_diuresis > $exam_igs->diurese) {
                    $exam_igs->diurese           = $igs_diuresis;
                    $worse_constants["_diurese"] = $diuresis;
                }
            }
        }


        // Constants
        foreach ($constants as $_constant) {
            // Pulse (Cardiac Frequency)
            if ($_constant->pouls) {
                $igs_cf = '7'; // Else
                $pulse  = $_constant->pouls;

                if ($pulse < 40) {
                    $igs_cf = '11';
                } elseif ($pulse <= 69) {
                    $igs_cf = '2';
                } elseif ($pulse <= 119) {
                    $igs_cf = '0';
                } elseif ($pulse <= 159) {
                    $igs_cf = '4';
                }

                $exam_igs_fc = ($exam_igs->FC) ?: 0;
                if ($igs_cf >= $exam_igs_fc) {
                    $exam_igs->FC          = $igs_cf;
                    $worse_constants["cf"] = $pulse;
                }
            }

            // Blood pressure (Systolic only)
            if ($_constant->ta) {
                $igs_bp     = '2'; // Else
                $bp_explode = explode("|", $_constant->ta);
                $bp         = $bp_explode[0] * 10; // Systolic

                if ($bp < 70) {
                    $igs_bp = '13';
                } elseif ($bp <= 99) {
                    $igs_bp = '5';
                } elseif ($bp <= 199) {
                    $igs_bp = '0';
                }

                $exam_igs_ta = ($exam_igs->TA) ?: 0;
                if ($igs_bp >= $exam_igs_ta) {
                    $exam_igs->TA          = $igs_bp;
                    $worse_constants["bp"] = "$bp";
                }
            }

            // Temperature
            if ($_constant->temperature) {
                $igs_temperature = $_constant->temperature < 39 ? '0' : '3';

                $exam_igs_temperature = ($exam_igs->temperature) ?: 0;
                if ($igs_temperature >= $exam_igs_temperature) {
                    $exam_igs->temperature          = $igs_temperature;
                    $worse_constants["temperature"] = $_constant->temperature;
                }
            }

            // Glasgow score
            if ($_constant->glasgow) {
                $glasgow     = $_constant->glasgow;
                $igs_glasgow = '0'; // Else

                if ($glasgow < 6) {
                    $igs_glasgow = '26';
                } elseif ($glasgow <= 8) {
                    $igs_glasgow = '13';
                } elseif ($glasgow <= 10) {
                    $igs_glasgow = '7';
                } elseif ($glasgow <= 13) {
                    $igs_glasgow = '5';
                }

                $exam_igs_glasgow = ($exam_igs->glasgow) ?: 0;
                if ($igs_glasgow >= $exam_igs_glasgow) {
                    $exam_igs->glasgow          = $igs_glasgow;
                    $worse_constants["glasgow"] = $glasgow;
                }
            }
        }

        if ($return_values) {
            return $worse_constants;
        }

        return $exam_igs;
    }

    public function loadRefReleveRedons()
    {
        return $this->_ref_releve_redons = $this->loadBackRefs("releve_redon");
    }

    public function getVariationPoidsNaissance()
    {
        $ds      = CSQLDataSource::get("std");
        $request = new CRequest();
        $request->addSelect("poids");
        $request->addTable("constantes_medicales");
        $request->addWhere("patient_id = '$this->patient_id'");
        $request->addWhere("datetime < '$this->datetime'");
        $request->addWhere("constantes_medicales_id != '$this->constantes_medicales_id'");
        $request->addOrder('datetime ASC');
        $poids_initial = $ds->loadResult($request->makeSelect());

        if ($poids_initial && $this->_poids_g) {
            $this->_poids_initial_g = $poids_initial * 1000;
        } elseif (!$poids_initial && $this->_poids_g) {
            $this->_poids_initial_g = $this->_poids_g;
        }

        if ($this->_poids_initial_g && $this->_poids_g) {
            $this->_variation_poids_naissance_g           = round($this->_poids_g - $this->_poids_initial_g, 3);
            $this->_variation_poids_naissance_g           = $this->_variation_poids_naissance_g > 0 ?
                "+$this->_variation_poids_naissance_g" : $this->_variation_poids_naissance_g;
            $this->_variation_poids_naissance_pourcentage = round(
                (($this->_poids_g - $this->_poids_initial_g) / $this->_poids_initial_g) * 100,
                2
            );
        }
    }

    /**
     * Get the last unit Glycemie value selected
     *
     * @param array|CConstantesMedicales $list_constantes
     * @param bool  $array_reverse
     */
    static function getlastUnitGlycemie($list_constantes, bool $array_reverse = false): ?string
    {
        $last_constante = $array_reverse ? end($list_constantes) : reset($list_constantes);
        $last_unit      = null;

        if ($last_constante && $last_constante->_id) {
            $last_unit = $last_constante->unite_glycemie;
        }

        return $last_unit;
    }

    /**
     * Get convert quantity from the last Glycemie unit selected
     *
     * @param array|CConstantesMedicales $list_constantes
     * @param bool                       $array_reverse
     * @param bool                       $use_last_unit_as_ref
     *
     * @return array|CConstantesMedicales
     */
    static function getConvertUnitGlycemie($list_constantes, bool $array_reverse = false, bool $use_last_unit_as_ref = false)
    {
        $ref_unit_glycemie = $use_last_unit_as_ref ? self::getlastUnitGlycemie($list_constantes, $array_reverse) : CAppUI::gconf(
            'dPpatients CConstantesMedicales unite_glycemie'
        );

        $constantes = is_array($list_constantes) ? $list_constantes : [$list_constantes];
        foreach ($constantes as $key => $_constante) {
            if ($_constante->glycemie && ($_constante->unite_glycemie != $ref_unit_glycemie)) {
                $conv                  = self::getConv("glycemie", $ref_unit_glycemie);
                $_constante->_glycemie = $_constante->glycemie = round($_constante->glycemie * $conv, self::CONV_ROUND_DOWN);
            }
        }

        return is_array($list_constantes) ? $constantes : $constantes[0];
    }

    /**
     * Permet d'espacer les créations de constantes pour la hauteur utérine de 60 secondes
     *
     * @throws Exception
     */
    public function checkLastConstanteTimeHauteurUterine(): void
    {
        $patient = (new CPatient())->load($this->patient_id);
        $context = (new $this->context_class())->load($this->context_id);

        $last_constantes         = self::getLatestFor($patient, CMbDT::dateTime(), ["hauteur_uterine"], $context);
        $last_constante_datetime = $last_constantes[1]["hauteur_uterine"];

        //Loadmatching ne fonctionne pas ici, on fait un loadlist
        $last_constante = new self();
        $ds             = $last_constante->getDS();

        $where = [
            "patient_id"    => $ds->prepare("= ?", $this->patient_id),
            "datetime"      => $ds->prepare("= ?", $last_constante_datetime),
            "context_class" => $ds->prepare("= ?", $this->context_class),
            "context_id"    => $ds->prepare("= ?", $this->context_id),
        ];

        $last_constante = $last_constante->loadList($where, null, 1);
        $last_constante = array_shift($last_constante);

        //Si la dernière constante a été mise à jour il y a moins d'une minute, on mets à jour cette constante
        if ($last_constante && $last_constante->_id && CMbDT::durationSecond(
                CMbDT::time($last_constante_datetime),
                CMbDT::time()
            ) <= 60) {
            $this->_id = $last_constante->_id;
        }
    }

    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchConstante($this);
    }

    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }
}

if (PHP_SAPI !== 'cli') {
    CConstantesMedicales::initParams();
}
