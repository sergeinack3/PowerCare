<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys;

use Ox\Core\Security\Crypt\Alg;
use Ox\Core\Security\Crypt\Mode;

/**
 * Key representation.
 * Metadata are information about algorithm, encryption mode, etc.
 */
class Key
{
    /** @var CKeyMetadata */
    private $metadata;

    /** @var string */
    private $value;

    /**
     * @param string $name
     * @param string $value
     * @param Alg    $alg
     * @param Mode   $mode
     */
    public function __construct(CKeyMetadata $metadata, string $value)
    {
        $this->metadata = $metadata;
        $this->value    = $value;
    }

    public static function createEmpty(string $name, Alg $alg, Mode $mode): self
    {
        $metadata       = new CKeyMetadata();
        $metadata->name = $name;
        $metadata->alg  = $alg->getValue();
        $metadata->mode = $mode->getValue();

        $metadata->updateFormFields();

        return new self($metadata, '');
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->metadata->name;
    }

    public function getAlg(): Alg
    {
        return $this->metadata->_alg;
    }

    public function getMode(): Mode
    {
        return $this->metadata->_mode;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->metadata->name;
    }
}
