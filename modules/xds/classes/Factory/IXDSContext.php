<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Factory;

/**
 * Description
 */
interface IXDSContext
{
    /**
     *
     *
     * @param CXDSFactory $xds
     */
    public function initializeXDS(CXDSFactory $xds): void;
}
