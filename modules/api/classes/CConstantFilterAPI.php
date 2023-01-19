<?php

/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\CMbException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\Constants\CConstantFilter;
use Ox\Mediboard\Patients\Constants\CConstantReleve;

class CConstantFilterAPI extends CConstantFilter
{

    /**
     * @param string[]|string $sources
     *
     * @return CConstantFilter
     * @throws CAPITiersException
     * @throws CMbException
     */
    public function addSources($sources): CConstantFilter
    {
        if (!is_array($sources)) {
            $sources = [$sources];
        }

        foreach ($sources as $source) {
            $source_names = null;
            switch ($source) {
                case CConstantReleve::SOURCE_FITBIT:
                    $user_id      = CFitbitAPI::getCronID();
                    $source_names = [CConstantReleve::FROM_API, CConstantReleve::FROM_DEVICE];
                    break;
                case CConstantReleve::SOURCE_WITHINGS:
                    $user_id      = CWithingsAPI::getCronID();
                    $source_names = [CConstantReleve::FROM_API, CConstantReleve::FROM_DEVICE];
                    break;
                case CConstantReleve::SOURCE_MANUAL:
                    $user_id      = CUser::get()->_id;
                    $source_names = [CConstantReleve::SOURCE_MANUAL];
                    break;

                default:
                    return parent::addSources($sources);
            }

            $this->sources = array_unique(array_merge($this->sources, $source_names));

            if ($user_id && !in_array($user_id, $this->user_ids)) {
                $this->user_ids[] = $user_id;
            }
        }

        return $this;
    }
}
