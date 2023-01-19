<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeId;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;

/**
 * Each resource contains an element "meta", of type "Meta", which is a set of metadata that provides technical and
 * workflow context to the resource
 */
class CFHIRDataTypeMeta extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Meta';

    /** @var CFHIRDataTypeId */
    public $versionId;

    /** @var CFHIRDataTypeInstant */
    public $lastUpdated;

    /** @var CFHIRDataTypeUri */
    public $source;

    /** @var CFHIRDataTypeString[] */
    public $profile;

    /** @var CFHIRDataTypeCoding[] */
    public $security;

    /** @var CFHIRDataTypeCoding[] */
    public $tag;
}
