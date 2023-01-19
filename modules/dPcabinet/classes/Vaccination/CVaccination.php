<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Vaccination;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

/**
 * Can be several vaccines for one injection
 */
class CVaccination extends CMbObject
{
    /** @var string */
    public const RESOURCE_TYPE = 'vaccination';

    public const TYPE_VACCINATION_BCG           = "BCG";
    public const TYPE_VACCINATION_DTP           = "DTP";
    public const TYPE_VACCINATION_COQUELUCHE    = "Coqueluche";
    public const TYPE_VACCINATION_HIB           = "HIB";
    public const TYPE_VACCINATION_HB            = "HB";
    public const TYPE_VACCINATION_PNEUMOCOQUE   = "Pneumocoque";
    public const TYPE_VACCINATION_MENINGOCOQUEC = "MeningocoqueC";
    public const TYPE_VACCINATION_MENINGOCOQUEB = "MeningocoqueB";
    public const TYPE_VACCINATION_ROR           = "ROR";
    public const TYPE_VACCINATION_HPV           = "HPV";
    public const TYPE_VACCINATION_GRIPPE        = "Grippe";
    public const TYPE_VACCINATION_ZONA          = "Zona";
    public const TYPE_VACCINATION_AUTRE         = "Autre";

    public const TYPES_VACCINATIONS = [
        self::TYPE_VACCINATION_BCG,
        self::TYPE_VACCINATION_DTP,
        self::TYPE_VACCINATION_COQUELUCHE,
        self::TYPE_VACCINATION_HIB,
        self::TYPE_VACCINATION_HB,
        self::TYPE_VACCINATION_PNEUMOCOQUE,
        self::TYPE_VACCINATION_MENINGOCOQUEC,
        self::TYPE_VACCINATION_MENINGOCOQUEB,
        self::TYPE_VACCINATION_ROR,
        self::TYPE_VACCINATION_HPV,
        self::TYPE_VACCINATION_GRIPPE,
        self::TYPE_VACCINATION_ZONA,
        self::TYPE_VACCINATION_AUTRE,
    ];

    /** @var int Primary key */
    public $vaccination_id;
    public $injection_id;
    public $type;

    /** @var CVaccin */
    public $_ref_vaccine;
    /** @var CInjection */
    public $_ref_injection;

    /**
     * Check if a type of vaccine exists in a list of vaccines
     *
     * @param CVaccin   $vaccine
     * @param CVaccin[] $vaccines
     *
     * @return bool
     */
    public static function isVaccinationActive($vaccine, $vaccines)
    {
        $vaccines_type = CMbArray::pluck($vaccines, "type");

        return in_array($vaccine->type, $vaccines_type);
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "vaccination";
        $spec->key   = "vaccination_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["injection_id"] = "ref class|CInjection back|vaccinations cascade fieldset|extra";
        $props["type"]         = "enum list|" . implode("|", self::TYPES_VACCINATIONS) . " fieldset|default";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        if (!$this->_ref_vaccine) {
            $this->loadRefVaccine();
        }
        if (!$this->_ref_injection) {
            $this->loadRefInjection();
        }

        $txt = ($this->_ref_vaccine->type !== "Autre") ? $this->_ref_vaccine->longname . "<br>" : "";
        $txt .= "Produit: " . $this->_ref_injection->speciality;

        return $txt;
    }

    /**
     * @return CVaccin|null
     * @throws Exception
     */
    function loadRefVaccine()
    {
        if ($this->type !== "Autre") {
            return $this->_ref_vaccine = (new CVaccinRepository())->findByType($this->type);
        }

        return $this->_ref_vaccine = new CVaccin("Autre", "Autre", "Autre");
    }

    /**
     * @return CStoredObject|null
     * @throws Exception
     */
    function loadRefInjection()
    {
        return $this->_ref_injection = $this->loadFwdRef("injection_id");
    }
}
