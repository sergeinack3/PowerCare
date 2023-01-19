<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\PasswordSpecs;

use Exception;
use Ox\Core\CMbFieldSpecFact;
use Ox\Core\FieldSpecs\CPasswordSpec;

/**
 * Description
 */
class PasswordSpec
{
    private const TYPE_WEAK   = 'weak';
    private const TYPE_STRONG = 'strong';
    private const TYPE_LDAP   = 'ldap';
    private const TYPE_ADMIN  = 'admin';

    private const TYPES = [
        self::TYPE_WEAK,
        self::TYPE_STRONG,
        self::TYPE_LDAP,
        self::TYPE_ADMIN,
    ];

    /** @var string */
    public $type;

    /** @var string */
    public $class;

    /** @var int */
    private $min_length;

    /** @var bool */
    private $alpha_chars;

    /** @var bool */
    private $upper_chars;

    /** @var bool */
    private $num_chars;

    /** @var bool */
    private $special_chars;

    /** @var string */
    private $not_containing;

    /** @var string */
    private $not_near;

    /** @var string */
    private $prop;

    /** @var CPasswordSpec */
    private $spec;

    /**
     * PasswordSpec constructor.
     *
     * @param string      $type
     * @param string      $class
     * @param int         $min_length
     * @param bool|null   $alpha_chars
     * @param bool|null   $upper_chars
     * @param bool|null   $num_chars
     * @param bool|null   $special_chars
     * @param string|null $not_containing
     * @param string|null $not_near
     */
    private function __construct(
        string $type,
        string $class,
        int $min_length,
        ?bool $alpha_chars = false,
        ?bool $upper_chars = false,
        ?bool $num_chars = false,
        ?bool $special_chars = false,
        ?string $not_containing = '',
        ?string $not_near = ''
    ) {
        $this->type  = $type;
        $this->class = $class;

        $this->min_length     = $min_length;
        $this->alpha_chars    = $alpha_chars;
        $this->upper_chars    = $upper_chars;
        $this->num_chars      = $num_chars;
        $this->special_chars  = $special_chars;
        $this->not_containing = $not_containing;
        $this->not_near       = $not_near;

        $this->prop = "password minLength|{$min_length} randomizable";

        if ($this->not_containing) {
            $this->prop .= " notContaining|{$this->not_containing}";
        }

        if ($this->not_near) {
            $this->prop .= " notNear|{$this->not_near}";
        }

        if ($this->alpha_chars) {
            $this->prop .= ' alphaChars';
        }

        if ($this->upper_chars) {
            $this->prop .= ' alphaUpChars';
        }

        if ($this->num_chars) {
            $this->prop .= ' numChars';
        }

        if ($this->special_chars) {
            $this->prop .= ' specialChars';
        }
    }

    /**
     * @param string      $class
     * @param int         $min_length
     * @param bool|null   $alpha_chars
     * @param bool|null   $upper_chars
     * @param bool|null   $num_chars
     * @param bool|null   $special_chars
     * @param string|null $not_containing
     * @param string|null $not_near
     *
     * @return static
     */
    public static function createWeak(
        string $class,
        int $min_length,
        ?bool $alpha_chars = false,
        ?bool $upper_chars = false,
        ?bool $num_chars = false,
        ?bool $special_chars = false,
        ?string $not_containing = '',
        ?string $not_near = ''
    ): self {
        return new static(
            static::TYPE_WEAK,
            $class,
            $min_length,
            $alpha_chars,
            $upper_chars,
            $num_chars,
            $special_chars,
            $not_containing,
            $not_near
        );
    }

    /**
     * @param string      $class
     * @param int         $min_length
     * @param bool|null   $alpha_chars
     * @param bool|null   $upper_chars
     * @param bool|null   $num_chars
     * @param bool|null   $special_chars
     * @param string|null $not_containing
     * @param string|null $not_near
     *
     * @return static
     */
    public static function createStrong(
        string $class,
        int $min_length,
        ?bool $alpha_chars = false,
        ?bool $upper_chars = false,
        ?bool $num_chars = false,
        ?bool $special_chars = false,
        ?string $not_containing = '',
        ?string $not_near = ''
    ): self {
        return new static(
            static::TYPE_STRONG,
            $class,
            $min_length,
            $alpha_chars,
            $upper_chars,
            $num_chars,
            $special_chars,
            $not_containing,
            $not_near
        );
    }

    /**
     * @param string      $class
     * @param int         $min_length
     * @param bool|null   $alpha_chars
     * @param bool|null   $upper_chars
     * @param bool|null   $num_chars
     * @param bool|null   $special_chars
     * @param string|null $not_containing
     * @param string|null $not_near
     *
     * @return static
     */
    public static function createLDAP(
        string $class,
        int $min_length,
        ?bool $alpha_chars = false,
        ?bool $upper_chars = false,
        ?bool $num_chars = false,
        ?bool $special_chars = false,
        ?string $not_containing = '',
        ?string $not_near = ''
    ): self {
        return new static(
            static::TYPE_LDAP,
            $class,
            $min_length,
            $alpha_chars,
            $upper_chars,
            $num_chars,
            $special_chars,
            $not_containing,
            $not_near
        );
    }

    /**
     * @param string      $class
     * @param int         $min_length
     * @param bool|null   $alpha_chars
     * @param bool|null   $upper_chars
     * @param bool|null   $num_chars
     * @param bool|null   $special_chars
     * @param string|null $not_containing
     * @param string|null $not_near
     *
     * @return static
     */
    public static function createAdmin(
        string $class,
        int $min_length,
        ?bool $alpha_chars = false,
        ?bool $upper_chars = false,
        ?bool $num_chars = false,
        ?bool $special_chars = false,
        ?string $not_containing = '',
        ?string $not_near = ''
    ): self {
        return new static(
            static::TYPE_ADMIN,
            $class,
            $min_length,
            $alpha_chars,
            $upper_chars,
            $num_chars,
            $special_chars,
            $not_containing,
            $not_near
        );
    }

    public function getProp(): string
    {
        return $this->prop;
    }

    /**
     * @param string $password_field
     *
     * @return CPasswordSpec
     * @throws Exception
     */
    public function getSpec(string $password_field): CPasswordSpec
    {
        return CMbFieldSpecFact::getSpecWithClassName($this->class, $password_field, $this->prop);
    }

    private function hasType(string $type): bool
    {
        return ($this->type === $type);
    }

    public function isWeak(): bool
    {
        return $this->hasType(self::TYPE_WEAK);
    }

    public function isStrong(): bool
    {
        return $this->hasType(self::TYPE_STRONG);
    }

    public function isLDAP(): bool
    {
        return $this->hasType(self::TYPE_LDAP);
    }

    public function isAdmin(): bool
    {
        return $this->hasType(self::TYPE_ADMIN);
    }
}
