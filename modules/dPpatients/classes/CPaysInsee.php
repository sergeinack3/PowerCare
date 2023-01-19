<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\OpenData\CCommuneFrance;

/**
 * Pays Insee
 */
class CPaysInsee extends CMbObject
{
    // DB Fields
    public $numerique;
    public $alpha_2;
    public $alpha_3;
    public $nom_fr;
    public $code_insee;

    public const NUMERIC_FRANCE    = 250;
    public const NUMERIC_SUISSE    = 756;
    public const NUMERIC_ALLEMAGNE = 276;
    public const NUMERIC_ESPAGNE   = 724;
    public const NUMERIC_PORTUGAL  = 620;
    public const NUMERIC_GB        = 826;
    public const NUMERIC_BELGIQUE  = 56;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->dsn         = 'INSEE';
        $spec->incremented = false;
        $spec->table       = 'pays';
        $spec->key         = 'numerique';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $specs               = parent::getProps();
        $specs["numerique"]  = "numchar length|3";
        $specs["alpha_2"]    = "str length|2";
        $specs["alpha_3"]    = "str length|3";
        $specs["nom_fr"]     = "str";
        $specs["code_insee"] = "str length|5";

        return $specs;
    }

    /**
     * @param string $insee_code Code INSEE du pays
     *
     * @return CCommuneFrance
     */
    public function loadByInsee(string $insee_code): self
    {
        $this->code_insee = $insee_code;
        $this->loadMatchingObject();

        return $this;
    }

    /**
     * Retourne le code Alpha-2 du pays
     *
     * @param int $numerique Numero de pays
     *
     * @return string
     */
    static function getAlpha2($numerique)
    {
        $pays = new self;
        $pays->load($numerique);

        return $pays->alpha_2;
    }

    /**
     * Retourne le code Alpha-3 du pays
     *
     * @param int $numerique Numero de pays
     *
     * @return string
     */
    static function getAlpha3($numerique)
    {
        $pays = new self;
        $pays->load($numerique);

        return $pays->alpha_3;
    }

    /**
     * Retourne le pays à partir du code numérique
     *
     * @param int $numerique Numero de pays
     *
     * @return CPaysInsee
     */
    static function getPaysByNumerique($numerique)
    {
        $pays            = new self;
        $pays->numerique = $numerique;
        $pays->loadMatchingObject();

        return $pays;
    }

    /**
     * Retourne le pays à partir du code Alpha-3
     *
     * @param string $alpha_3 Valeur du pays en alpha-3
     *
     * @return CPaysInsee
     */
    static function getPaysByAlpha($alpha_3)
    {
        $pays          = new self;
        $pays->alpha_3 = $alpha_3;
        $pays->loadMatchingObject();

        return $pays;
    }

    /**
     * Retourne le nom français du pays
     *
     * @param string $numerique Numéro de pays
     *
     * @return mixed
     */
    static function getNomFR($numerique)
    {
        static $noms = [];

        if (array_key_exists($numerique, $noms)) {
            return $noms[$numerique];
        }

        $pays            = new self;
        $pays->numerique = $numerique;
        $pays->loadMatchingObject();

        return $noms[$numerique] = $pays->nom_fr;
    }

    public static function getPaysNumByNomFR(string $nom_pays): string
    {
        $ds = CSQLDataSource::get('std');

        $pays         = new static();
        $pays->nom_fr = $ds->escape($nom_pays);
        $pays->loadMatchingObject();

        if (!$pays->_id) {
            return '000';
        }

        return $pays->numerique;
    }

    /**
     * Search INSEE Country by INSEE Code
     *
     * @param string $keyword
     * @param int    $max
     *
     * @return CPaysInsee[]
     * @throws Exception
     */
    public function match(string $keyword, int $max): array
    {
        return $this->loadList(
            [
                "code_insee" => $this->getDS()->prepareLike("$keyword%")
            ],
            "nom_fr, code_insee",
            $max
        );
    }
}
