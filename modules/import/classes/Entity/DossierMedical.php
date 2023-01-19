<?php

/**
 * @package Mediboard\Import
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
 * Representation of a CDossierMedical for importation
 */
class DossierMedical extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'DMED';

    /** @var string */
    protected $patient_id;

    /** @var string */
    protected $group_sanguin;

    /** @var string */
    protected $rhesus;

    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
        ];
    }

    public function getExternalClass()
    {
        return self::EXTERNAL_CLASS;
    }

    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformDossierMedical($this, $reference_stash, $campaign);
    }

    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateDossierMedical($this);
    }

    public function getPatientId(): string
    {
        return $this->patient_id;
    }

    public function getGroupSanguin(): string
    {
        return $this->group_sanguin;
    }

    public function getRhesus(): string
    {
        return $this->rhesus;
    }
}
