<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Transformer\TransformerVisitorInterface;
use Ox\Import\Framework\Validator\ValidatorVisitorInterface;

/**
 * Description
 */
class Correspondant extends AbstractEntity
{
    protected const EXTERNAL_CLASS = 'CORR';

    /** @var string */
    protected $medecin_id;

    /** @var string */
    protected $patient_id;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateCorrespondant($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformCorrespondant($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getExternalClass()
    {
        return static::EXTERNAL_CLASS;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::MEDECIN, $this->medecin_id),
            ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),

        ];
    }

   public function getMedecinId()
   {
       return $this->medecin_id;
   }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }
}
