<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

class CXDSAssociation
{
    /** @var string */
    public const TYPE_HAS_MEMBER = 'hasMember';
    /** @var string */
    public const TYPE_UPDATE_AVAILABILITY_STATUS = 'updateAvailabilityStatus';
    /** @var string */
    public const TYPE_REPLACE = 'replace';
    /** @var string */
    public const TYPE_SIGN = 'sign';

    /** @var string hasMember|... */
    public $type;

    /** @var string */
    public $id;

    /** @var string */
    public $status;

    /** @var string */
    public $from;

    /** @var string */
    public $to;

    /** @var string */
    public $submissionSetStatus;

    /** @var string|int */
    public $previousVersion;

    /** @var string */
    public $availabilityStatus;

    /** @var string */
    public $new_availabilityStatus;

    /**
     * @param XDSElementInterface $from
     * @param XDSElementInterface $to
     * @param string              $type
     *
     * @return self
     */
    public function associate(
        XDSElementInterface $from,
        XDSElementInterface $to,
        string $type = self::TYPE_HAS_MEMBER
    ): self {
        $this->from = $from;
        $this->to   = $to;
        $this->type = $type;

        return $this;
    }
}
