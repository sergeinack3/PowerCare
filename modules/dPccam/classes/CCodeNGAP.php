<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use SQLiteDatabase;

/**
 * Represents a NGAP code
 */
class CCodeNGAP extends CNGAP
{
    /** @var int */
    public static $cache_layers = Cache::INNER_OUTER;

    /** @var string The NGAP code */
    public $code;

    /** @var boolean Indicate if the act is a main act or a complement */
    public $lettre_cle;

    /** @var string The full name of the act */
    public $libelle;

    /** @var array A list of tarifs by speciality */
    public $tarifs = [];

    /** @var array */
    public $specialites = [];

    /** @var array A list of possible associations */
    public $associations;

    /** @var CCodeNGAPTarif The tarif, set by the fonction getTarif */
    public $_tarif;

    /** @var boolean If true, the code is unknown */
    public $_unknown = false;

    /** @var boolean If true, the code is deprecated */
    public $_deprecated = false;

    /** @var array An array containing all the geographic zones */
    public static $zones = ['metro', 'antilles', 'guyane-reunion', 'mayotte'];

    /** @var array An array linking the postal_codes to zones */
    public static $postal_codes_to_zone = [
        971 => 'antilles',
        972 => 'antilles',
        973 => 'guyane-reunion',
        974 => 'guyane-reunion',
        976 => 'mayotte',
    ];

    /**
     * CCodeNGAP constructor.
     *
     * @param string $code The NGAP code
     */
    public function __construct(string $code = null)
    {
        $this->code = strtoupper($code);
    }

    /**
     * Load the data from the database
     *
     * @return void
     */
    protected function load(): void
    {
        $ds = self::getSpec()->ds;

        $query = new CRequest();
        $query->addSelect(['code', 'libelle', 'lettre_cle', 'tarif']);
        $query->addTable('codes_ngap');
        $query->addWhere("code = '$this->code'");

        $result = $ds->loadHash($query->makeSelect());

        if ($result) {
            $this->lettre_cle = $result['lettre_cle'] ? true : false;
            $this->libelle    = $result['libelle'];

            $this->loadAssociations();
            $this->loadTarifs();
            $this->loadSpecialites();
        } else {
            $this->_unknown = true;
        }
    }

    /**
     * Load the possible associations for the code
     *
     * @return void
     */
    protected function loadAssociations(): void
    {
        $ds = self::getSpec()->ds;

        $query = new CRequest();
        $query->addSelect(['associations']);
        $query->addTable('associations_ngap');
        $query->addWhere("code = '$this->code'");

        $data = $ds->loadHash($query->makeSelect());

        if ($data) {
            $this->associations = explode('|', $data['associations']);
        }
    }

    /**
     * Load the tarifs for the code
     *
     * @return void
     */
    protected function loadTarifs(): void
    {
        $this->tarifs = CCodeNGAPTarif::loadFor($this->code);

        $count = 0;
        foreach ($this->tarifs as $zone => $tarifs) {
            if (count($tarifs)) {
                $count = count($tarifs);
                break;
            }
        }

        if (!$count) {
            $this->_deprecated = true;
        }
    }

    /**
     * Load the link between specialities and tarifs
     *
     * @return void
     */
    protected function loadSpecialites(): void
    {
        $ds = self::getSpec()->ds;

        foreach (self::$zones as $zone) {
            $this->specialites[$zone] = [];
        }

        $query = new CRequest();
        $query->addSelect(['s.tarif_id', 's.specialite', 't.zone']);
        $query->addTable('specialite_to_tarif_ngap AS s');
        $query->addLJoin('tarif_ngap AS t ON t.tarif_ngap_id = s.tarif_id');
        $query->addWhere(["t.code = '$this->code'"]);
        $query->addOrder('specialite ASC');

        $results = $ds->loadList($query->makeSelect());

        if ($results) {
            foreach ($results as $result) {
                $zone       = $result['zone'];
                $tarif_id   = $result['tarif_id'];
                $speciality = $result['specialite'];

                if (!array_key_exists($speciality, $this->specialites[$zone])) {
                    $this->specialites[$zone][$speciality] = [];
                }

                $this->specialites[$zone][$speciality][] = $tarif_id;
            }
        }
    }

    /**
     * Get the tarif corresponding to the speciality of the given user, and valid at the given date
     *
     * @param CMediusers $user The user
     * @param string     $date The date
     * @param CSpecCPAM  $spec Tge speciality
     * @param string     $zone The zone
     *
     * @return CCodeNGAPTarif
     */
    public function getTarifFor(
        CMediusers $user = null,
        string $date = null,
        CSpecCPAM $spec = null,
        string $zone = 'metro'
    ): ?CCodeNGAPTarif {
        if ($this->_unknown) {
            return null;
        }

        if (!$date) {
            $date = CMbDT::date();
        }

        if ($user) {
            $speciality = self::getSpeciality($user);
            $zone       = self::getZone($user);
        } elseif ($spec) {
            $speciality = $spec->spec_cpam_id;
        } else {
            $speciality = (new CSpecCPAM(1))->spec_cpam_id;
        }

        $this->_tarif = null;
        if (array_key_exists($speciality, $this->specialites[$zone])) {
            foreach ($this->specialites[$zone][$speciality] as $_tarif_id) {
                if ($_tarif_id && array_key_exists($_tarif_id, $this->tarifs[$zone])) {
                    /** @var CCodeNGAPTarif */
                    $_tarif = $this->tarifs[$zone][$_tarif_id];

                    /* Check the tarifs dates */
                    if ((!$_tarif->debut || $_tarif->debut <= $date) && (!$_tarif->fin || $_tarif->fin >= $date)) {
                        $this->_tarif = $_tarif;
                        break;
                    }
                }
            }
        }

        return $this->_tarif;
    }

    /**
     * Get the given code from the cache or load it
     *
     * @param string $code The NGAP code to get
     *
     * @return CCodeNGAP
     */
    public static function get(string $code): self
    {
        $cache = Cache::getCache(self::$cache_layers)->withCompressor();
        $ngap = $cache->get("CCodeNGAP.get-{$code}");
        if (!$ngap) {
            $ngap = new CCodeNGAP($code);
            $ngap->load();
            $cache->set("CCodeNGAP.get-{$code}", $ngap);
        }

        return $ngap;
    }

    /**
     * @param string     $keywords The keyword to search
     * @param CMediusers $user     The user
     * @param string     $date     The date
     * @param boolean    $main     If true, only main acts will be searched
     * @param boolean    $codable  If true, only the code that are codable by the user will be returned
     *                                (no DDT, DHT, DLT, MTX, MCX, CCX, CCE)
     * @param boolean    $urgences If true, the codes only usable for emergency services will be added
     *
     * @return CCodeNGAP[]
     */
    public static function search(
        string $keywords,
        CMediusers $user = null,
        string $date = null,
        bool $main = false,
        bool $codable = true,
        bool $urgences = false
    ): array {
        $keywords   = addslashes($keywords);
        $codes      = [];
        $speciality = self::getSpeciality($user);
        $zone       = self::getZone($user);

        if (!$date) {
            $date = CMbDT::date();
        }

        $ds = self::getSpec()->ds;

        $query = new CRequest();
        $query->addSelect('c.code');
        $query->addTable('codes_ngap AS c');
        $query->addLJoin(
            [
                'tarif_ngap AS t ON t.code = c.code',
                'specialite_to_tarif_ngap AS s ON t.tarif_ngap_id = s.tarif_id',
            ]
        );

        $where = [
            "t.code LIKE '$keywords%'",
            "t.zone = '$zone'",
            "s.specialite = $speciality",
            "t.debut IS NULL OR t.debut <= '$date'",
            "t.fin IS NULL OR t.fin >= '$date'",
        ];

        if ($main) {
            $where[] = "c.lettre_cle = '1'";
        }

        if (!$urgences) {
            $where[] = "c.type != 'urgence'";
        }

        $complex_anonym_acts = ['DDT', 'DHT', 'DLT', 'MTX', 'MCX', 'CCX', 'CCE'];
        $complex_acts        = [
            'CGP',
            'EPH',
            'CSM',
            'CSO',
            'MCA',
            'MCT',
            'MMF',
            'MPS',
            'MPT',
            'MSP',
            'PEG',
            'POG',
            'PPN',
            'PPR',
            'PTG',
            'SGE',
            'SLA',
            'TCA',
            'CPM',
            'IGR',
            'MAV',
            'MIS',
            'MMM',
            'MPB',
            'PIV',
            'CBX',
        ];
        /* The codes automatically coded are not included, except when the SESAM-Vitale version is below 1.40.13 */
        if (
            $codable && (CModule::getActive('oxPyxvital') || CModule::getActive('pyxVital')
                || CModule::getActive('jfse'))
        ) {
            if (
                (CAppUI::pref('LogicielFSE') == 'oxPyxvital' && CAppUI::pref('agrement_sesam_vitale') == '1.40.13')
                || CAppUI::pref('LogicielFSE') == 'jfse'
            ) {
                $where[] = "c.code " . CSQLDataSource::prepareNotIn($complex_anonym_acts);
            } elseif (
                (CAppUI::pref('LogicielFSE') == 'oxPyxvital'
                    && CAppUI::pref('agrement_sesam_vitale') != '1.40.13')
                || CAppUI::pref('LogicielFSE') == 'pv'
            ) {
                $where[] = "c.code " . CSQLDataSource::prepareNotIn($complex_acts);
            }
        }

        $query->addWhere($where);

        $results = $ds->loadList($query->makeSelect());

        if ($results) {
            foreach ($results as $result) {
                $codes[] = self::get($result['code']);
            }
        }

        return $codes;
    }

    /**
     * Get the speciality from the given user. If not set, return the speciality 1
     *
     * @param CMediusers $user The user
     *
     * @return int
     */
    public static function getSpeciality(?CMediusers $user): int
    {
        $speciality = 1;

        if ($user && $user->spec_cpam_id) {
            $speciality = $user->spec_cpam_id;
        }

        return $speciality;
    }

    /**
     * Guess the zone based on the function or the group postal code
     *
     * @param CMediusers $user The user
     *
     * @return string
     */
    public static function getZone(?CMediusers $user): string
    {
        $zone = 'metro';

        $postal_code = CGroups::loadCurrent()->cp;
        if ($user && $user->_id) {
            $user->loadRefFunction();

            if ($user->_ref_function->cp) {
                $postal_code = $user->_ref_function->cp;
            }
        }

        $postal_code = intval($postal_code / 100);

        if (array_key_exists($postal_code, self::$postal_codes_to_zone)) {
            $zone = self::$postal_codes_to_zone[$postal_code];
        }

        return $zone;
    }

    /**
     * Return all the codes available at the given date, for the given speciality
     *
     * @param CSpecCPAM $spec    The speciality
     * @param string    $date    The date
     * @param string    $zone    The zone (metro, antilles, reunion, etc)
     * @param string    $urgence Include the emergency codes or not
     *
     * @return CCodeNGAP[]
     */
    public static function getForSpeciality(
        CSpecCPAM $spec = null,
        string $date = null,
        string $zone = 'metro',
        bool $urgence = false
    ): array {
        if (!$spec) {
            $spec = CSpecCPAM::get(1);
        }

        if (!$date) {
            $date = CMbDT::date();
        }

        $codes = [];

        $ds = self::getSpec()->ds;

        $query = new CRequest();
        $query->addSelect('c.code');
        $query->addTable('codes_ngap AS c');
        $query->addLJoin(
            [
                'tarif_ngap AS t ON t.code = c.code',
                'specialite_to_tarif_ngap AS s ON t.tarif_ngap_id = s.tarif_id',
            ]
        );

        $where = [
            "t.zone = '$zone'",
            "s.specialite = $spec->spec_cpam_id",
            "t.debut IS NULL OR t.debut <= '$date'",
            "t.fin IS NULL OR t.fin >= '$date'",
        ];

        if (!$urgence) {
            $where[] = "type != 'urgence'";
        }

        $query->addWhere($where);
        $query->addOrder('c.code');

        $results = $ds->loadList($query->makeSelect());

        if ($results) {
            foreach ($results as $result) {
                $code = self::get($result['code']);
                $code->getTarifFor(null, $date, $spec, $zone);
                $codes[] = $code;
            }
        }

        return $codes;
    }
}
