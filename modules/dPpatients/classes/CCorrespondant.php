<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Liaison entre le médecin et le patient
 */
class CCorrespondant extends CMbObject implements ImportableInterface, IGroupRelated
{
    /** @var int */
    public $correspondant_id;

    // DB Fields
    /** @var int */
    public $medecin_id;

    /** @var int */
    public $medecin_exercice_place_id;

    /** @var int */
    public $patient_id;

    /** @var CMedecin */
    public $_ref_medecin;

    /** @var CMedecinExercicePlace */
    public $_ref_medecin_exercice_place;

    /** @var CPatient */
    public $_ref_patient;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "correspondant";
        $spec->key   = "correspondant_id";

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props                              = parent::getProps();
        $props["medecin_id"]                = "ref notNull class|CMedecin back|patients_correspondants";
        $props['medecin_exercice_place_id'] = 'ref class|CMedecinExercicePlace back|patients_correspondants';
        $props["patient_id"]                = "ref notNull class|CPatient back|correspondants";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $medecin = $this->loadRefMedecin();

        $this->_view = $medecin->_view;
    }

    /**
     * @inheritDoc
     */
    public function loadRefsFwd(): void
    {
        $this->loadRefPatient();
        $this->loadRefMedecin();
    }

    /**
     * @inheritDoc
     */
    public function store(): ?string
    {
        (new MedecinExercicePlaceService($this, 'medecin_id', 'medecin_exercice_place_id'))->applyFirstExercicePlace();

        return parent::store();
    }

    /**
     * Charge le patient
     *
     * @return CPatient
     * @throws \Exception
     */
    public function loadRefPatient(): CPatient
    {
        return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
    }

    /**
     * Charge le médecin
     *
     * @return CMedecin
     * @throws \Exception
     */
    public function loadRefMedecin(): CMedecin
    {
        return $this->_ref_medecin = $this->loadFwdRef("medecin_id", true);
    }

    public function loadRefMedecinExercicePlace(): CMedecinExercicePlace
    {
        return $this->_ref_medecin_exercice_place = $this->loadFwdRef('medecin_exercice_place_id', true);
    }

    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchCorrespondant($this);
    }

    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    public function loadRelGroup(): ?CGroups
    {
        return $this->loadRefPatient()->loadRelGroup();
    }
}
