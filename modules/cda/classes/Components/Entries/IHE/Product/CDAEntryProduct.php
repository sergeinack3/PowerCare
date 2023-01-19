<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\IHE\Product;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\CDAEntryRole;
use Ox\Interop\Cda\Rim\CCDARIMRole;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ManufacturedProduct;

/**
 * Class CDAEntryProduct
 *
 * @package Ox\Interop\Cda\Components\Entries\IHE\Product
 */
class CDAEntryProduct extends CDAEntryRole
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.4.7.2';

    /**
     * CDAEntryProduct constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        // Conformity CCD
        $this->addTemplateIds('2.16.840.1.113883.10.20.1.53');

        $this->entry_content = new CCDAPOCD_MT000040_ManufacturedProduct();
    }

    /**
     * @param CCDARIMRole $entry_content
     */
    protected function buildContent(CCDAClasseCda $entry_content): void
    {
        // not implemented
    }
}
