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
class File extends AbstractEntity
{
    protected const EXTERNAL_CLASS = 'FILE';

    protected $file_name;
    protected $file_date;
    protected $file_type;
    protected $file_content;
    protected $file_path;
    protected $author_id;
    protected $consultation_id;
    protected $sejour_id;
    protected $patient_id;
    protected $evenement_id;
    protected $file_cat_name;

    protected $default_refs = true;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateFile($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformFile($this, $reference_stash, $campaign);
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
            ExternalReference::getNotMandatoryFor(ExternalReference::UTILISATEUR, $this->author_id),
            ExternalReference::getNotMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
            ExternalReference::getNotMandatoryFor(
                $this->default_refs ? ExternalReference::CONSULTATION : ExternalReference::CONSULTATION_AUTRE,
                $this->consultation_id
            ),
            ExternalReference::getNotMandatoryFor(ExternalReference::SEJOUR, $this->sejour_id),
            ExternalReference::getNotMandatoryFor(ExternalReference::EVENEMENT, $this->evenement_id),
        ];
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @return mixed
     */
    public function getFileDate()
    {
        return $this->file_date;
    }

    /**
     * @return mixed
     */
    public function getFileType()
    {
        return $this->file_type;
    }

    /**
     * @return mixed
     */
    public function getFileContent()
    {
        return $this->file_content;
    }

    /**
     * @return mixed
     */
    public function getAuthorId()
    {
        return $this->author_id;
    }

    /**
     * @return mixed
     */
    public function getConsultationId()
    {
        return $this->consultation_id;
    }

    /**
     * @return mixed
     */
    public function getSejourId()
    {
        return $this->sejour_id;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    public function getDefaultRefs(): bool
    {
        return $this->default_refs;
    }

    public function setDefaultRefs(bool $default_refs): void
    {
        $this->default_refs = $default_refs;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    /**
     * @return mixed
     */
    public function getEvenementId()
    {
        return $this->evenement_id;
    }

    public function getFileCatName()
    {
        return $this->file_cat_name;
    }
}
