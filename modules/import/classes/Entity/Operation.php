<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use DateTime;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Transformer\TransformerVisitorInterface;
use Ox\Import\Framework\Validator\ValidatorVisitorInterface;

/**
 * External operation representation
 */
class Operation extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'OPRT';

    /** @var string */
    protected $sejour_id;

    /** @var mixed */
    protected $chir_id;

    /** @var string */
    protected $cote;

    /** @var string */
    protected $date_time;

    /** @var string */
    protected $libelle;

    /** @var string */
    protected $examen;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateOperation($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformOperation($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::SEJOUR, $this->sejour_id),
            ExternalReference::getMandatoryFor(ExternalReference::UTILISATEUR, $this->chir_id),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getExternalClass()
    {
        return static::EXTERNAL_CLASS;
    }

    /**
     * @return string
     */
    public function getSejourId(): string
    {
        return $this->sejour_id;
    }

    /**
     * @return mixed
     */
    public function getChirId()
    {
        return $this->chir_id;
    }

    /**
     * @return string
     */
    public function getCote(): string
    {
        return $this->cote;
    }

    /**
     * @return null|string
     */
    public function getDateTime(): ?string
    {
        return $this->date_time;
    }

    /**
     * @return null|string
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @return null|string
     */
    public function getExamen(): ?string
    {
        return $this->examen;
    }
}
