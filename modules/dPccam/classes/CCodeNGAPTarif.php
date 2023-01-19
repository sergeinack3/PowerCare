<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\CRequest;

/**
 * Represent a NGAP tarif (NGAP price and majoration for a speciality and geographic zone)
 */
class CCodeNGAPTarif extends CNGAP
{
    /** @var integer The tarif id */
    public $id;

    /** @var string The NGAP code */
    public $code;

    /** @var string The zone (metro, antilles, guyane-reunion, mayotte) */
    public $zone;

    /** @var float The price */
    public $tarif;

    /** @var float The price of night majoration */
    public $maj_nuit;

    /** @var float The price of the holiday majoration */
    public $maj_ferie;

    /** @var boolean Indicate if the night majoration is possible */
    public $complement_nuit;

    /** @var boolean Indicate if the holiday majoration is possible */
    public $complement_ferie;

    /** @var boolean Indicate if the emergency majoration is possible */
    public $complement_urgence;

    /** @var bool Indicate the obligation */
    public $coefficient;

    /** @var float The minimum possible value for the coefficient */
    public $coef_min;

    /** @var float The maximum possible value for the coefficient */
    public $coef_max;

    /** @var boolean Indicate if a DEP is necessary */
    public $entente_prealable;

    /** @var string The begin date */
    public $debut;

    /** @var string The end date */
    public $fin;

    /**
     * Set the properties from the given array
     *
     * @param array $data The data
     *
     * @return void
     */
    public function map($data): void
    {
        if (array_key_exists('tarif_ngap_id', $data)) {
            $this->id = $data['tarif_ngap_id'];
        }

        foreach ($data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->$field = $value;
            }
        }
    }

    /**
     * Load all the tarif for the given code in the given zone
     *
     * @param string $code The NGAP code
     *
     * @return CCodeNGAPTarif[]
     */
    public static function loadFor(string $code): array
    {
        $tarifs = [];

        foreach (CCodeNGAP::$zones as $zone) {
            $tarifs[$zone] = [];
        }

        $ds = self::getSpec()->ds;

        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable('tarif_ngap');
        $query->addWhere("code = '$code'");
        $query->addOrder('tarif_ngap_id, debut DESC');

        $results = $ds->loadList($query->makeSelect());
        if ($results) {
            foreach ($results as $result) {
                $_tarif = new self();
                $_tarif->map($result);

                $tarifs[$_tarif->zone][$_tarif->id] = $_tarif;
            }
        }

        return $tarifs;
    }
}
