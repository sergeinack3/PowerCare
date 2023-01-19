<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;

class CPatientHandicap extends CStoredObject
{
    /** @var int */
    public $patient_handicap_id;

    /** @var string */
    public $handicap;

    /** @var int */
    public $patient_id;

    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = "patient_handicap";
        $spec->key   = "patient_handicap_id";

        return $spec;
    }

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["handicap"]   = 'enum list|moteur|psychique|autonome|fauteuil|besoin_aidant|mal_entendant|mal_voyant';
        $props["patient_id"] = "ref class|CPatient back|patient_handicaps";

        return $props;
    }

    public function updateFormFields(): void
    {
        parent::updateFormFields();
        self::loadView();
    }

    public function loadView(): void
    {
        parent::loadView();

        $this->_view = CAppUI::tr(CClassMap::getSN($this) . '.handicap.' . $this->handicap);
    }

    /**
     * Check if a handicap is in a a list of handicaps
     *
     * @param array  $handicaps - list of handicaps
     * @param string $handicap  - the handicap
     *
     * @return bool
     */
    public static function hasHandicap(array $handicaps, string $handicap): bool
    {
        $filtered = array_filter(
            $handicaps,
            function (CPatientHandicap $patient_handicap) use ($handicap) {
                return $patient_handicap->handicap === $handicap;
            }
        );

        return count($filtered) > 0;
    }
}
