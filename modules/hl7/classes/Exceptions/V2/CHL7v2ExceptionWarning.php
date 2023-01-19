<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Exceptions\V2;

use Ox\Interop\Hl7\CHL7v2Exception;

class CHL7v2ExceptionWarning extends CHL7v2Exception
{
    /** @var string */
    protected $code_warning;
    /** @var string */
    protected $comments;
    /** @var string */
    protected $position;
    /** @var string */
    protected $severity;

    /**
     * CHL7v2ExceptionWarning constructor.
     *
     * @param string $code_warning
     * @param string $severity
     */
    public function __construct(string $code_warning, string $severity = 'W')
    {
        parent::__construct(self::INVALID_ACK_APPLICATION_WARNING, 0);

        if (!in_array($severity, ['I', 'W', 'E'])) {
            $severity = 'W';
        }

        $this->severity     = $severity;
        $this->code_warning = $code_warning;
    }

    /**
     * @return string|array
     */
    public function getWarning()
    {
        $comments = $this->getComments();
        if ($this->position) {
            $comments = ($comments ? "$comments " : '') . $this->position;
        }

        return [
            'code'     => $this->code_warning,
            'comments' => $comments,
            'type'     => $this->severity,
        ];
    }

    public function setPosition(string $position): self
    {
        $this->position = 'from : [' . $position . ']';

        return $this;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return string
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }
}
