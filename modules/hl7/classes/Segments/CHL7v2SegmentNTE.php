<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Server\CEvenementMedical;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Cabinet\CConsultation;

class CHL7v2SegmentNTE extends CHL7v2Segment
{
    /** @var string */
    public $name = 'NTE';

    /** @var int */
    public $set_id;

    /** @var CConsultation|CEvenementMedical */
    public $appointment;

    /** @var string */
    public $comment;

    /** @var string */
    public $comment_type;

    /**
     * Build ACC segement
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return void
     * @throws \Ox\Interop\Hl7\CHL7v2Exception
     */
    public function build(CHEvent $event, $name = null): void
    {
        parent::build($event);

        $appointment = $this->appointment;

        // NTE-1 : Set ID (SI)
        $data[] = $this->set_id;

        // NTE-2 : Source of Comment (ID)
        // Table 105
        // L - Ancillary (filler) department is source of comment
        // O - Other system is source of comment
        // P - Orderer (placer) is source of comment
        $data[] = 'L';

        // NTE-3 : Comment (FT)
        $data[] = str_replace(array("\r\n", "\n"), "\\.br\\", $this->comment);

        // NTE-4 : Comment Type (CE)
        // Table 364
        // 1R - Primary Reason
        // 2R - Secondary Reason
        // AI - Ancillary Instructions
        // DR - Duplicate/Interaction Reason
        // GI - General Instructions
        // GR - General Reason
        // PI - Patient Instructions
        // RE - Remark
        // WITHDRAWAL - Appointment to move forward if withdrawal
        // HD - History of the disease
        // CE - Clinical examination
        //
        if ($this->comment_type) {
            $data[] = [
                [
                    $this->comment_type,
                    CHL7v2TableEntry::getDescription(364, $this->comment_type),
                ],
            ];
        }

        $this->fill($data);
    }
}
