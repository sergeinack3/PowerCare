<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;

/**
 * Périodes d'allaitement
 */
class CAllaitement extends CMbObject
{
    /**
     * @var integer Primary key
     */
    public $allaitement_id;

    // DB Fields
    public $patient_id;
    public $grossesse_id;
    public $date_debut;
    public $date_fin;
    public $antecedent_id;

    /** @var CPatient */
    public $_ref_patient;
    /** @var CGrossesse */
    public $_ref_grossesse;
    /** @var CAntecedent */
    public $_ref_antecedent;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "allaitement";
        $spec->key   = "allaitement_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                  = parent::getProps();
        $props["patient_id"]    = "ref notNull class|CPatient back|allaitements";
        $props["grossesse_id"]  = "ref class|CGrossesse back|allaitements";
        $props["date_debut"]    = "dateTime notNull";
        $props["date_fin"]      = "dateTime moreEquals|date_debut";
        $props["antecedent_id"] = "ref class|CAntecedent back|allaitements";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = "Allaitement du " . CMbDT::transform(
                $this->date_debut,
                null,
                CAppUI::conf("date")
            ) . " à " . CMbDT::transform($this->date_debut, null, CAppUI::conf("time"));

        if ($this->date_fin) {
            $this->_view .= " au " . CMbDT::transform(
                    $this->date_fin,
                    null,
                    CAppUI::conf("date")
                ) . " à " . CMbDT::transform($this->date_fin, null, CAppUI::conf("time"));
        }
    }

    /**
     * @inheritdoc
     */
    public function store(): ?string
    {
        $this->completeField("antecedent_id");
        $this->loadRefAntecedent();
        $this->manageAntecedent();

        return parent::store();
    }

    /**
     * @inheritdoc
     */
    public function delete(): ?string {
        $this->completeField("antecedent_id");
        if ($this->antecedent_id) {
            $antecedent = $this->loadRefAntecedent();
            if ($msg = $antecedent->delete()) {
                CAppUI::setMsg($msg, UI_MSG_WARNING);
            }
        }

        return parent::delete();
    }

    /**
     * Load the patient
     *
     * @return CPatient
     */
    public function loadRefPatient(): CPatient
    {
        return $this->_ref_patient = $this->loadFwdRef('patient_id', true);
    }

    /**
     * Load the pregnancy
     *
     * @return CGrossesse
     * @throws Exception
     */
    public function loadRefGrossesse(): CGrossesse
    {
        return $this->_ref_grossesse = $this->loadFwdRef("grossesse_id", true);
    }

    /**
     * Load the pregnancy
     *
     * @return CAntecedent
     * @throws Exception
     */
    public function loadRefAntecedent(): CAntecedent
    {
        return $this->_ref_antecedent = $this->loadFwdRef("antecedent_id", true);
    }

    /**
     * Create an antecedent when a breastfeeding is created
     */
    public function manageAntecedent(): void
    {
        $patient         = $this->loadRefPatient();
        $dossier_medical = $patient->loadRefDossierMedical();

        if (!$this->_ref_antecedent) {
            $this->loadRefAntecedent();
        }

        $antecedent = $this->_ref_antecedent;

        if (!$antecedent->_id) {
            $antecedent->type               = "med";
            $antecedent->appareil           = "gyneco_obstetrique";
            $antecedent->dossier_medical_id = $dossier_medical->_id;
            $antecedent->rques              = CAppUI::tr("CGrossesse-allaitement_maternel");
        }

        $antecedent->date               = CMbDT::date($this->date_debut);
        $antecedent->date_fin           = CMbDT::date($this->date_fin);


        if ($msg = $antecedent->store()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
        }

        $this->antecedent_id = $antecedent->_id;
    }
}
