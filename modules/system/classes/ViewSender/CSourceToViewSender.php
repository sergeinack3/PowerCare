<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\ViewSender;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;

class CSourceToViewSender extends CMbObject
{
    // DB Table key
    public $source_to_view_sender_id;

    // DB fields
    public $source_id;
    public $sender_id;
    public $last_datetime;
    public $last_duration;
    public $last_size;
    public $last_status;
    public $last_count;

    // Form fields
    public $_last_age;
    public $_source_name;

    public const RESOURCE_TYPE = "sourceViewSender";

    /** @var CViewSenderSource */
    public $_ref_sender_source;

    /** @var CViewSender */
    public $_ref_sender;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "source_to_view_sender";
        $spec->key   = "source_to_view_sender_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                  = parent::getProps();
        $props["sender_id"]     = "ref class|CViewSender notNull autocomplete|name back|sources_link";
        $props["source_id"]     = "ref class|CViewSenderSource notNull autocomplete|name back|senders_link";
        $props["last_datetime"] = "dateTime loggable|0";
        $props["last_duration"] = "float loggable|0 fieldset|default";
        $props["last_size"]     = "num min|0 loggable|0 fieldset|default";
        $props["last_status"]   = "enum list|triggered|uploaded|checked loggable|0 fieldset|default";
        $props["last_count"]    = "num min|0 loggable|0 fieldset|default";

        $props["_last_age"]    = "num fieldset|default";
        $props["_source_name"] = "str fieldset|default";

        return $props;
    }

    /**
     * Charge le sender
     *
     * @return CViewSender
     * @throws Exception
     */
    public function loadRefSender(): CViewSender
    {
        $sender          = $this->loadFwdRef("sender_id", true);
        $this->_last_age = CMbDT::minutesRelative($this->last_datetime, CMbDT::dateTime());

        return $this->_ref_sender = $sender;
    }

    /**
     * Charge la source
     *
     * @return CViewSenderSource
     * @throws Exception
     */
    public function loadRefSenderSource(): CViewSenderSource
    {
        $this->_ref_sender_source = $this->loadFwdRef("source_id", true);
        $this->_source_name       = $this->_ref_sender_source->name;

        return $this->_ref_sender_source;
    }

    public function resetValues(): void
    {
        $this->last_duration = '';
        $this->last_size     = '';
        $this->last_status   = '';

        $this->store();
    }
}
