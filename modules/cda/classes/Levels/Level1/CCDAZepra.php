<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level1;

use Exception;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\InteropResources\valueset\CValueSet;
use Ox\Mediboard\Files\CFile;

class CCDAZepra extends CCDALevel1
{
    /** @var string */
    public const TYPE = self::TYPE_ZEPRA;

    /**
     * @return CANSValueSet
     */
    protected function getFactoryValueSet(): CValueSet
    {
        return new CANSValueSet();
    }

    /**
     * @param string $content_cda
     * @param int    $file_category_id
     *
     * @return CFile
     * @throws Exception
     */
    protected function getFile(string $content_cda): CFile
    {
        $file = parent::getFile($content_cda);
        $file->type_doc_sisra = $this->mbObject->type_doc_sisra;

        return $file;
    }
}
