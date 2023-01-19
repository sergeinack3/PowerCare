<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;

/**
 * Examens complémentaires prévus pendant la consultation, en particulier pour un anesthésiste
 */
class CExamComp extends CMbObject
{
    /** @var int */
    public $exam_id;

    // DB References
    /** @var int */
    public $consultation_id;

    // DB fields
    /** @var string */
    public $examen;
    /** @var string */
    public $realisation;
    /** @var int */
    public $fait;
    /** @var string */
    public $date_bilan;
    /** @var string */
    public $labo;

    /** @var CConsultation */
    public $_ref_consult;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'exams_comp';
        $spec->key   = 'exam_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props                    = parent::getProps();
        $props["consultation_id"] = "ref notNull class|CConsultation back|examcomp";
        $props["examen"]          = "text helped";
        $props["realisation"]     = "enum notNull list|avant|pendant";
        $props["fait"]            = "num min|0 max|1";
        $props["date_bilan"]      = "date";
        $props["labo"]            = "text helped";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->_view = $this->examen;
    }

    /**
     * Charge la consultation associée
     *
     * @return CStoredObject|CConsultation
     * @throws Exception
     */
    public function loadRefConsult(): CConsultation
    {
        return $this->_ref_consult = $this->loadFwdRef("consultation_id", true);
    }

    /**
     * @throws Exception
     * @see parent::getPerm()
     */
    public function getPerm($permType): bool
    {
        return $this->loadRefConsult()->getPerm($permType);
    }
}
