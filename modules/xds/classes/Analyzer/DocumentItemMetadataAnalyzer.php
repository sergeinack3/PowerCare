<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Analyzer;

use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Mediboard\Files\CDocumentItem;

class DocumentItemMetadataAnalyzer implements MetadataAnalyzerInterface
{

    public function generateMetadata(CDocumentItem $document_item): CXDSDocumentEntry
    {
        return new CXDSDocumentEntry();
    }

    public function isSupported(CDocumentItem $document_item): bool
    {
        return false;
    }
}
