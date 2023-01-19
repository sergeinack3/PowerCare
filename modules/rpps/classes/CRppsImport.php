<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps;

use Ox\Core\Import\CExternalDataSourceImport;

class CRppsImport extends CExternalDataSourceImport
{
    public const SOURCE_NAME = 'rpps_import';

    public function __construct()
    {
        parent::__construct(
            self::SOURCE_NAME,
            null,
            null
        );
    }

    public function importDatabase(?array $types = []): bool
    {
        parent::importDatabase($types);

        return (new CExternalMedecinBulkImport())->createSchema();

        /* @todo Always triggers SQL Error: LOAD DATA LOCAL INFILE is forbidden, check mysqli.allow_local_infile
        $downloader = new CRppsFileDownloader();
        if ($downloader->downloadRppsFile(CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL)) {
            $this->addMessage(['CRppsFileDownloader-msg-Info-RPPS file downloaded and extracted', UI_MSG_OK]);
        }
        if ($downloader->downloadRppsFile(CRppsFileDownloader::DOWNLOAD_MSSANTE_URL)) {
            $this->addMessage(['CRppsFileDownloader-msg-Info-MSSante file downloaded and extracted', UI_MSG_OK]);
        }

        $messages = (new CExternalMedecinBulkImport())->bulkImport();

        foreach ($messages as $_msg) {
            $this->addMessage([array_shift($_msg), UI_MSG_OK, ...$_msg]);
        }

        return true;
        */
    }
}
