<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit\Handle;

use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\Cda\Tests\Unit\UnitTestCDA;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class UnitTestHandle extends UnitTestCDA
{

    /**
     * @param string $file_name
     * @param bool   $randomize
     *
     * @return CCDADomDocument|null
     */
    public static function loadDocumentCDA(string $file_name, bool $randomize = true): CCDADomDocument
    {
        if (!file_exists($file_name)) {
            throw new FileNotFoundException("Error when load '$file_name' document cda");
        }

        $content = file_get_contents($file_name);

        $document = new CCDADomDocument();
        $document->preserveWhiteSpace = false;
        $document->loadXML($content);
        $document->getContentNodes();

        // todo faire le randomize setID

        return $document;
    }
}
