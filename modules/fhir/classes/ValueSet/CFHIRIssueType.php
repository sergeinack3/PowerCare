<?php

/**
 * @package Mediboard\Fhir\ValueSet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\ValueSet;

/**
 * Class CFHIRIssueType
 *
 * @see http://hl7.org/fhir/valueset-issue-type.html
 * @package Ox\Interop\FHIR\ValueSet
 */
class CFHIRIssueType
{
    /** @var string */
    public const TYPE_EXCEPTION = 'exception';

    /** @var string */
    public const TYPE_NOT_FOUND = 'not-found';

    /** @var string */
    public const TYPE_INFORMATIONAL = 'informational';

    /** @var string */
    public const TYPE_FORBIDDEN = 'forbidden';

    /** @var string */
    public const TYPE_NOT_SUPPORTED = 'not-supported';

    /** @var string */
    public const TYPE_REQUIRED = 'required';

    /** @var string */
    public const TYPE_INVALID_VALUE = 'value';
}
