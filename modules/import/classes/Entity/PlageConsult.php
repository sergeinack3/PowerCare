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
 * External plageconsult representation
 */
class PlageConsult extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'PLGC';

    /** @var DateTime */
    protected $date;

    /** @var DateTime */
    protected $freq;

    /** @var DateTime */
    protected $debut;

    /** @var DateTime */
    protected $fin;

    /** @var string */
    protected $libelle;

    /** @var mixed */
    protected $chir_id;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validatePlageConsult($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformPlageConsult($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
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
     * @return DateTime
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @return DateTime
     */
    public function getFreq(): ?DateTime
    {
        return $this->freq;
    }

    /**
     * @return DateTime
     */
    public function getDebut(): ?DateTime
    {
        return $this->debut;
    }

    /**
     * @return DateTime
     */
    public function getFin(): ?DateTIme
    {
        return $this->fin;
    }

    /**
     * @return string
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @return mixed
     */
    public function getChirId()
    {
        return $this->chir_id;
    }
}
