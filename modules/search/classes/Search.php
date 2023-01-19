<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;


use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Elastic\ElasticObjectMappings;
use Ox\Core\Elastic\ElasticObjectSettings;

/**
 * TODO: Implement the ODM
 */
class Search extends ElasticObject
{
    public const DATASOURCE_NAME = "search";

    public function setSettings(): ElasticObjectSettings
    {
        return new ElasticObjectSettings(self::DATASOURCE_NAME);
    }

    public function setMappings(): ElasticObjectMappings
    {
        return new ElasticObjectMappings(false);
    }
}
