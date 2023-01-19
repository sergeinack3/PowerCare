<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport;

use Ox\Core\CMbObject;

/**
 * Description
 */
class CImportFile extends CMbObject
{
    /** @var int */
    public $import_file_id;

    /** @var int */
    public $import_campaign_id;

    /** @var string */
    public $file_name;

    /** @var string */
    public $entity_type;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                           = parent::getSpec();
        $spec->table                    = "import_file";
        $spec->key                      = "import_file_id";
        $spec->uniques['campaign_type'] = ['import_campaign_id', 'entity_type'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props['import_campaign_id'] = 'ref class|CImportCampaign notNull back|import_files';
        $props['file_name']          = 'str notNull';
        $props['entity_type']        = 'enum list|' . implode('|', GenericImport::AVAILABLE_TYPES);

        return $props;
    }
}
