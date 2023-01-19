<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Ox\Core\CStoredObject;
use Ox\Import\Framework\Exception\TransformationException;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Transformer\TransformerVisitorInterface;

/**
 * External object representation
 */
interface EntityInterface
{
    /**
     * Get the external Id
     *
     * @return mixed
     */
    public function getExternalId();

    /**
     * @return string
     */
    public function getExternalClass();

    /**
     * Create an entity from an array of data
     *
     * @param array $data
     *
     * @return static
     */
    public static function fromState(array $data): self;

    /**
     * Get the ref objects needed to create this
     *
     * @return array An array of ExternalReference
     */
    public function getRefEntities(): array;

    public function getCollections(): array;

    /**
     * @param TransformerVisitorInterface $transformer
     * @param ExternalReferenceStash|null $reference_stash
     * @param CImportCampaign|null        $campaign
     *
     * @return ImportableInterface
     * @throws TransformationException
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface;

    public function setMbObject(CStoredObject $mb_object): void;

    public function getMbObject(): ?CStoredObject;
}
