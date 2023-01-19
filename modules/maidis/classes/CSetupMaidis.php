<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupMaidis extends CSetup
{
    /**
    * @inheritDoc
    */
    function __construct() {
    parent::__construct();

    $this->mod_name = "maidis";
    $this->makeRevision("0.0");

    $this->addDependency('import', '0.03');

    $this->setModuleCategory("import", "autre");

    $this->mod_version = "0.01";
    }
}
