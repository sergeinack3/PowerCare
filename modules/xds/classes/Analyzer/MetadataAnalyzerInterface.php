<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Analyzer;

use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Mediboard\Files\CDocumentItem;

/**
 * Description
 */
interface MetadataAnalyzerInterface
{
    /**
     * Generate CDocumentEntry for a CDocumentItem
     *
     * @param CDocumentItem $document_item
     *
     * @return CXDSDocumentEntry
     */
    public function generateMetadata(CDocumentItem $document_item): CXDSDocumentEntry;

    /**
     * Know if analyzer support it's document
     *
     * @param CDocumentItem $document_item
     *
     * @return bool
     */
    public function isSupported(CDocumentItem $document_item): bool;
}
