<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Ox\Core\Cache;
use Ox\Core\CMbString;
use Ox\Core\CModelObject;

/**
 * The CDiscipline Class
 */
class CSpecCPAM extends CModelObject
{

    /** @var string */
    public const RESOURCE_TYPE = 'specialty';

    /** @var array The list of specialities */
    protected static $specialities = [
        1  => "Médecine générale",
        2  => "Anesthesie - Réanimation",
        3  => "Cardiologie",
        4  => "Chirurgie générale",
        5  => "Dermatologie et Vénérologie",
        6  => "Radiologie",
        7  => "Gynécologie obstétrique",
        8  => "Gastro-Entérologie et Hépatologie",
        9  => "Médecine interne",
        10 => "Neuro-Chirurgie",
        11 => "Oto-Rhino-Laryngologie",
        12 => "Pédiatrie",
        13 => "Pneumologie",
        14 => "Rhumatologie",
        15 => "Ophtalmologie",
        16 => "Chirurgie urologique",
        17 => "Neuro-Psychiatrie",
        18 => "Stomatologie",
        19 => "Chirurgie dentaire",
        20 => "Réanimation médicale",
        21 => "Sage-femme",
        22 => "Spécialiste en médecin générale (Diplômé)",
        23 => "Spécialiste en médecin générale (Ordre)",
        24 => "Infirmier",
        26 => "Masseur Kinésithérapeute",
        27 => "Pedicure Podologue",
        28 => "Orthophoniste",
        29 => "Orthoptiste",
        30 => "Laboratoire d'analyses médicales",
        31 => "Rééducation Réadaption fonctionnelle",
        32 => "Neurologie",
        33 => "Psychiatrie",
        34 => "Gériatrie",
        35 => "Néphrologie",
        36 => "Chirurgie dentaire (spé. O.D.F.)",
        37 => "Anatomo Cyto-Pathologie",
        38 => "Médecin biologiste",
        39 => "Laboratoire polyvalent",
        40 => "Laboratoire anatomo-cyto-pathologie",
        41 => "Chirurgie orthopédique et Traumatologie",
        42 => "Endocrinologie et Métabolisme",
        43 => "Chirurgie infantile",
        44 => "Chirurgie maxillo-faciale",
        45 => "Chirurgie maxillo-faciale et Stomatologie",
        46 => "Chirurgie Plastique reconstructrice",
        47 => "Chirurgie thoracique et cardio-vasculaire",
        48 => "Chirurgie vasculaire",
        49 => "Chirurgie viscérale et digestive",
        50 => "Pharmacien",
        51 => "Pharmacien mutualiste",
        53 => "Chirurgie dentaire (spé. C.O.)",
        54 => "Chirurgie dentaire (spé. M.B.D.)",
        60 => "Prestataire de type société",
        61 => "Prestataire artisan",
        62 => "Prestataire de type association",
        63 => "Orthésiste",
        64 => "Opticien",
        65 => "Audioprothésiste",
        66 => "Epithèsiste Oculariste",
        67 => "Podo-orthésiste",
        68 => "Orthoprothésiste",
        69 => "Chirurgie orale",
        70 => "Gynécologie médicale",
        71 => "Hématologie",
        72 => "Médecine nucléaire",
        73 => "Oncologie médicale",
        74 => "Oncologie radiothérapique",
        75 => "Psychiatrie de l'enfant et de l'adolescent",
        76 => "Radiothérapie",
        77 => "Obstétrique",
        78 => "Génétique médicale",
        79 => "Obstétrique et Gynécologie médicale",
        80 => "Santé publique et médecine sociale",
        81 => "Médecine des maladies infectieuses et tropicales",
        82 => "Médecin légale et expertises médicales",
        83 => "Médecine d'urgence",
        84 => "Médecin vasculaire",
        85 => "Allergologie",
        86 => "Infirmier exerçant en pratiques avancées (IPA)",
    ];

    /** @var array The map between RPPS and CPAM */
    protected static $mapRPPStoCPAM = [
        'SM25'  => 1,
        'SM54'  => 1,
        'SM02'  => 2,
        'SM04'  => 3,
        'SM05'  => 4,
        'SM15'  => 5,
        'SM44'  => 6,
        'SM20'  => 7,
        'SM51'  => 7,
        'SM24'  => 8,
        'SM27'  => 9,
        'SM72'  => 9,
        'SM31'  => 10,
        'SM34'  => 11,
        'SM39'  => 11,
        'SM40'  => 12,
        'SM41'  => 13,
        'SM48'  => 14,
        'SM38'  => 15,
        'SM12'  => 16,
        'CEX64' => 16,
        'SM33'  => 17,
        'SM50'  => 18,
        40      => 19,
        'SM46'  => 20,
        50      => 21,
        'SM53'  => 22,
        'SM26'  => 23,
        60      => 24,
        70      => 26,
        80      => 27,
        91      => 28,
        92      => 29,
        30      => "Laboratoire d'analyses médicales",
        'SM29'  => 31,
        'SM32'  => 32,
        'SM42'  => 33,
        'SM18'  => 34,
        'SM30'  => 35,
        'SCD01' => 36,
        'SM01'  => 37,
        'SM03'  => 38,
        39      => "Laboratoire polyvalent",
        400     => "Laboratoire anatomo-cyto-pathologie",
        'SM08'  => 41,
        'SM16'  => 42,
        'SM09'  => 43,
        'SM06'  => 44,
        'SM07'  => 45,
        'SM10'  => 46,
        'SM11'  => 47,
        'SM13'  => 48,
        'SM14'  => 49,
        21      => 50,
        51      => "Pharmacien mutualiste",
        'SCD02' => 53,
        'SCD03' => 54,
        600     => "Prestataire de type société",
        61      => "Prestataire artisan",
        62      => "Prestataire de type association",
        83      => 63,
        28      => 64,
        26      => 65,
        85      => 66,
        82      => 67,
        81      => 68,
        'SM56'  => 69,
        'SM19'  => 70,
        'SM21'  => 71,
        'SM22'  => 71,
        'SM23'  => 71,
        'SM71'  => 71,
        'SM28'  => 72,
        'SM35'  => 73,
        'SM36'  => 73,
        'SM37'  => 74,
        'SM43'  => 75,
        'SM45'  => 76,
        'CEX22' => 77,
        'CEX24' => 77,
        'CEX26' => 77,
        'SM17'  => 78,
        'SM52'  => 79,
        'SM49'  => 80,
        'SM58'  => 81,
        'SM60'  => 82,
        'SM59'  => 83,
        'SM61'  => 84,
        'SM57'  => 85,
        'SI01'  => 86,
        'SI02'  => 86,
        'SI03'  => 86,
        'SI04'  => 86,
    ];

    /** @var int The id of the speciality */
    public $spec_cpam_id;

    /** @var string The number of the speciality, in string format */
    public $number;

    /** @var string The name of the speciality */
    public $text;

    /**
     * CSpecCPAM constructor.
     *
     * @param int $id The id of the speciality
     */
    public function __construct($id = null)
    {
        parent::__construct();

        if ($id && array_key_exists(intval($id), self::$specialities)) {
            $this->spec_cpam_id = $this->_id = intval($id);
            $this->number       = str_pad($this->spec_cpam_id, 2, '0', STR_PAD_LEFT);
            $this->text         = self::$specialities[$this->spec_cpam_id];
            $this->_view        = "{$this->number} - {$this->text}";
            $this->_shortview   = CMbString::truncate($this->_view);
        }
    }

    /**
     * @see parent::getProps
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['number'] = 'str fieldset|default';
        $props['text']   = 'str notNull fieldset|default';

        return $props;
    }

    /**
     * Returns the CSpecCPAM for the given id
     *
     * @param int $id The id of the speciality
     *
     * @return CSpecCPAM
     */
    public static function get($id)
    {
        return new self($id);
    }

    /**
     * Returns the CSpecCPAM with the given name
     *
     * @param string $name The name of the speciality
     *
     * @return CSpecCPAM
     */
    public static function getByName($name)
    {
        $search = ['-', '.', "'", '(', ')'];

        $specialities = array_map(
            function ($v) {
                return strtolower(CMbString::removeDiacritics(str_replace(['-', '.', "'", '(', ')'], '', $v)));
            },
            self::$specialities
        );

        $name = strtolower(CMbString::removeDiacritics(str_replace(['-', '.', "'", '(', ')'], '', $name)));

        /* If the value is not found, an empty object will be returned */

        return self::get(array_search($name, $specialities));
    }

    /**
     * Returns the RPPS code of a given CPAM id
     *
     * @param int $code The medecin CPAM speciality code
     *
     * @return string
     */
    public static function getMatchingRPPSOfCPAM(int $code): string
    {
        return array_search($code, self::$mapRPPStoCPAM);
    }

    /**
     * Returns the CSpecCPAM with the given profession code or the savoir_faire code
     *
     * @param string|int $code The correspondant profession code or the savoir_faire code
     *
     * @return CSpecCPAM
     */
    public static function getMatchingCPAMSpecOfRPPS($code): CSpecCPAM
    {
        $specCPAM = new CSpecCPAM();

        if (isset(self::$mapRPPStoCPAM[$code])) {
            $specCPAM = self::get(self::$mapRPPStoCPAM[$code]);
        }

        return $specCPAM;
    }

    /**
     * @param string $name
     *
     * @return CSpecCPAM[]
     */
    public static function searchByName(string $name): array
    {
        $search       = ['-', '.', "'", '(', ')'];
        $specialities = array_map(
            function ($v) use ($search) {
                return strtolower(CMbString::removeDiacritics(str_replace($search, '', $v)));
            },
            self::$specialities
        );

        $name = strtolower(CMbString::removeDiacritics(str_replace($search, '', $name)));

        $matches = array_filter($specialities, function ($speciality) use ($name) {
            return preg_match("#$name#", $speciality);
        });

        return array_map(
            function ($speciality_id) {
                return self::get($speciality_id);
            },
            array_keys($matches)
        );
    }

    /**
     * Returns the list of all the specialities
     *
     * @param string $order The order (asc or desc)
     *
     * @return CSpecCPAM[]
     */
    public static function getList($order = 'asc')
    {
        $cache = new Cache('CSpecCPAM.getList', null, Cache::INNER);

        $specialities = [];
        if ($cache->exists()) {
            $specialities = $cache->get();
        } else {
            foreach (self::$specialities as $id => $text) {
                $specialities[$id] = new self($id);
            }

            $cache->put($specialities);
        }

        if ($order == 'asc') {
            ksort($specialities);
        } elseif ($order == 'desc') {
            krsort($specialities);
        }

        return $specialities;
    }
}
