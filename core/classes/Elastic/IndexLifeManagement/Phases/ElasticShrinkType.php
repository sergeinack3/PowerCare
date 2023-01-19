<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\IndexLifeManagement\Phases;

use MyCLabs\Enum\Enum;

/**
 * Shrink type for Elasticsearch
 * Todo: Replace by Enum in PHP 8.1
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/ilm-shrink.html Elasticsearch's Documentation
 *
 * @method static static SHARD_COUNT()
 * @method static static SHARD_SIZE()
 */
class ElasticShrinkType extends Enum
{
    private const SHARD_COUNT = 'count';
    private const SHARD_SIZE  = 'size';
}
