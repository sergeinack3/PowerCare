<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDecimal;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;

/**
 * Class CFHIRDataTypeBundleSearch
 * @package Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle
 */
class CFHIRDataTypeBundleSearch extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Bundle.entry.search';

    /** @var CFHIRDataTypeCode */
    public $mode;

    /** @var CFHIRDataTypeDecimal */
    public $score;
}

