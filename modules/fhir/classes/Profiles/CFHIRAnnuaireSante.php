<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Profiles;

use Ox\Core\Autoload\IShortNameAutoloadable;

class CFHIRAnnuaireSante extends CFHIR implements IShortNameAutoloadable
{
    /** @var string */
    public const DOMAIN_NAME = 'ANS';
    /** @var string  */
    public const DOMAIN_TYPE = 'AnnuaireSante';

    /** @var string */
    public const BASE_PROFILE = 'https://apifhir.annuaire.sante.fr/ws-sync/exposed/structuredefinition/';
}
