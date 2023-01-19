<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeId;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeMeta;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
trait CStoredObjectResourceTrait
{
    /** @var CStoredObject */
    protected $object;

    protected CFHIRResource $resource;

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->object   = $object;
        $this->resource = $resource;
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CStoredObject && $object->_id;
    }

    /**
     * Map property Id
     *
     * @return CFHIRDataTypeString|null
     * @throws Exception
     */
    public function mapId(): ?CFHIRDataTypeString
    {
        if ($this->object && $this->object->_id) {
            return new CFHIRDataTypeString($this->getInternalId($this->object));
        }

        return null;
    }

    /**
     * Get internal id of object or use idex when receiver is present
     *
     * @param CStoredObject $object
     *
     * @return string
     * @throws Exception
     */
    private function getInternalId(CStoredObject $object): string
    {
        $receiver = $this->resource && $this->resource->getReceiver() ? $this->resource->getReceiver() : null;

        // If we send a resource (POST | PUT)
        if ($receiver) {
            $idex = CIdSante400::getMatch($object->_class, $receiver->_tag_fhir, null, $object->_id);
            if ($idex && $idex->_id) {
                return $idex->id400;
            }
        }

        return $object->getUuid();
    }

    /**
     * Map property Meta
     *
     * @return CFHIRDataTypeMeta|null
     * @throws Exception
     */
    public function mapMeta(): ?CFHIRDataTypeMeta
    {
        // meta
        $meta = new CFHIRDataTypeMeta();

        // meta / versionID|lastUpdated
        if (!$this->resource->isContained()) {
            if ($this->object && $this->object->_id) {
                $last_log          = $this->object->loadLastLog();
                $meta->versionId   = new CFHIRDataTypeId($last_log->_id);
                $meta->lastUpdated = new CFHIRDataTypeInstant($last_log->date);
            } else {
                $meta->lastUpdated = new CFHIRDataTypeInstant(CMbDT::dateTime());
            }
        }

        // meta / profile
        if (!$this->resource->isFHIRResource()) {
            $profiles          = [new CFHIRDataTypeString($this->resource->getProfile())];
            $existing_profiles = $this->resource->getMeta() ? $this->resource->getMeta()->profile : [];
            $meta->profile     = array_merge($existing_profiles, $profiles);
        }

        return $meta;
    }

    /**
     * Map property ImplicitRules
     *
     * @return CFHIRDataTypeUri|null
     */
    public function mapImplicitRules(): ?CFHIRDataTypeUri
    {
        return null;
    }

    /**
     * Map property Language
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapLanguage(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode('fr-FR');
    }
}
