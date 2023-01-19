<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Services;

use Ox\Core\CMbDT;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientState;
use Ox\Mediboard\Patients\CSourceIdentite;

/**
 * Identity qualification service
 */
class PatientQualifierService
{
    private ?CPatient $patient;

    private array $traits_insi = [];

    private ?string $message = '';

    public const TRAIT_STRICT_NOM = 'nom_jeune_fille';

    public const TRAIT_STRICT_PRENOM = 'prenom';

    public const TRAIT_STRICT_SEXE = 'sexe';

    public const TRAIT_STRICT_NAISSANCE = 'naissance';

    public const TRAIT_STRICT_LIEU_NAISSANCE = 'commune_naissance_insee';

    public const TRAIT_STRICT_NOM_SOURCE = '_source_nom_jeune_fille';

    public const TRAIT_STRICT_PRENOM_SOURCE = '_source_prenom';

    public const TRAIT_STRICT_SEXE_SOURCE = '_source_sexe';

    public const TRAIT_STRICT_NAISSANCE_SOURCE = '_source_naissance';

    public const TRAIT_STRICT_CP_NAISSANCE_SOURCE = '_source_cp_naissance';

    public const TRAIT_STRICT_LIEU_NAISSANCE_SOURCE = '_source_commune_naissance_insee';

    public const TRAITS_STRICTS = [
        self::TRAIT_STRICT_NOM            => self::TRAIT_STRICT_NOM_SOURCE,
        self::TRAIT_STRICT_PRENOM         => self::TRAIT_STRICT_PRENOM_SOURCE,
        self::TRAIT_STRICT_SEXE           => self::TRAIT_STRICT_SEXE_SOURCE,
        self::TRAIT_STRICT_NAISSANCE      => self::TRAIT_STRICT_NAISSANCE_SOURCE,
        self::TRAIT_STRICT_LIEU_NAISSANCE => self::TRAIT_STRICT_LIEU_NAISSANCE_SOURCE,
    ];

    public function __construct(CPatient $patient, array $traits_insi)
    {
        $this->patient     = $patient;
        $this->traits_insi = $traits_insi;
    }

    public function qualify(): bool
    {
        if (!$this->canQualify()) {
            return false;
        }

        foreach ($this->traits_insi as $trait_insi => $value) {
            $this->patient->$trait_insi = $value;
        }

        $this->patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;
        $this->message                  = $this->patient->store();

        if ($this->message || ($this->patient->status !== CPatientState::STATE_QUAL)) {
            return false;
        }

        return true;
    }

    public function canQualify(): bool
    {
        if ($this->patient->status !== CPatientState::STATE_VALI) {
            return false;
        }

        foreach (self::TRAITS_STRICTS as $trait_strict => $trait_strict_source) {
            // Tolérance sur l'absence du lieu de naissance dans la réponse
            if ($trait_strict === self::TRAIT_STRICT_LIEU_NAISSANCE && !$this->traits_insi[$trait_strict_source]) {
                continue;
            }

            $trait_strict_from_patient   = $this->patient->$trait_strict;
            $trait_strict_from_from_insi = $this->traits_insi[$trait_strict_source];

            if ($trait_strict === self::TRAIT_STRICT_NAISSANCE) {
                $trait_strict_from_from_insi = CMbDT::dateFromLocale($trait_strict_from_from_insi);
            }

            if ($trait_strict_from_patient !== $trait_strict_from_from_insi) {
                return false;
            }
        }

        return true;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
