<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Transformer;

use DateTime;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Import\Framework\Entity\ExternalReferenceStash;

/**
 * Description
 */
abstract class AbstractTransformer implements TransformerVisitorInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    private const DATETIME_FORMAT = 'Y-m-d H:i:s';
    private const DATE_FORMAT     = 'Y-m-d';
    private const TIME_FORMAT     = 'H:i:s';

    /** @var ExternalReferenceStash|null */
    private $reference_stash;

    /**
     * @param ExternalReferenceStash|null $reference_stash
     */
    public function setReferenceStash(?ExternalReferenceStash $reference_stash = null): void
    {
        $this->reference_stash = $reference_stash;
    }

    /**
     * @return ExternalReferenceStash|null
     */
    public function getReferenceStash(): ?ExternalReferenceStash
    {
        return $this->reference_stash;
    }

    /**
     * @param string|null $tel
     *
     * @return string|null
     */
    protected function sanitizeTel(?string $tel): ?string
    {
        return preg_replace('/\D+/', '', $tel ?? '');
    }

    /**
     * @param DateTime|null $datetime
     *
     * @return string|null
     */
    protected function formatDateTimeToStr(?DateTime $datetime): ?string
    {
        return $this->formatDateTime($datetime, self::DATETIME_FORMAT);
    }

    /**
     * @param DateTime|null $datetime
     *
     * @return string|null
     */
    protected function formatDateTimeToStrDate(?DateTime $datetime): ?string
    {
        return $this->formatDateTime($datetime, self::DATE_FORMAT);
    }

    /**
     * @param DateTime|null $datetime
     *
     * @return string|null
     */
    protected function formatDateTimeToStrTime(?DateTime $datetime): ?string
    {
        return $this->formatDateTime($datetime, self::TIME_FORMAT);
    }

    /**
     * @param DateTime|null $datetime
     * @param string        $format
     *
     * @return string|null
     */
    protected function formatDateTime(?DateTime $datetime, string $format): ?string
    {
        if ($datetime === null) {
            return null;
        }

        return $datetime->format($format);
    }
}
