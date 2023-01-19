<?php

/**
 * @package Mediboard\Fhir\ValueSet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ValueSet;

/**
 * Class CFHIRIssueSeverity
 *
 * @see http://hl7.org/fhir/valueset-issue-severity.html
 * @package Ox\Interop\Fhir\ValueSet
 */
class CFHIRIssueSeverity
{
    /** @var string */
    public const SEVERITY_FATAL = 'fatal';

    /** @var string */
    public const SEVERITY_ERROR = 'error';

    /** @var string */
    public const SEVERITY_WARNING = 'warning';

    /** @var string */
    public const SEVERITY_INFORMATION = 'information';
}
