<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Controllers\Legacy;

use Ox\Import\Framework\CFwImport;
use Ox\Import\Framework\Controllers\Legacy\CImportFwLegacyController;
use Ox\Import\Maidis\MaidisImport;

/**
 * Description
 */
class CMaidisLegacyController extends CImportFwLegacyController
{

    protected function getModName(): string
    {
        return 'maidis';
    }

    protected function getUsersTable(): string
    {
        return 'utilisateur';
    }

    protected function getImportInstance(?string $type = null): CFwImport
    {
        return new MaidisImport();
    }

    public function configure()
    {
        $this->checkPermAdmin();

        $this->renderSmarty('configure');
    }
}
