<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Csrf;

use Ox\Core\CMbDT;
use Stringable;

/**
 * Description
 */
class AntiCsrfToken implements Stringable
{
    /** @var string */
    private const HMAC_ALGO = 'sha256';

    /** @var string */
    private $token;

    /** @var array */
    private $parameters;

    /** @var int */
    private $ttl;

    /** @var string */
    private $expiration_date;

    /**
     * AntiCsrfToken constructor.
     *
     * @param string $token
     * @param array  $parameters
     * @param int    $ttl
     */
    private function __construct(string $token, array $parameters, int $ttl)
    {
        $this->token      = $token;
        $this->ttl        = $ttl;
        $this->parameters = $parameters;

        $this->expiration_date = CMbDT::dateTime("+{$ttl} seconds");
    }

    /**
     * Token generator.
     *
     * @param string $secret
     * @param array  $parameters
     * @param int    $ttl
     *
     * @return static
     */
    public static function generate(string $secret, array $parameters, int $ttl): self
    {
        $hotp = hash_hmac(self::HMAC_ALGO, uniqid('', true), $secret, false);

        return new self($hotp, $parameters, $ttl);
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    public function __sleep(): array
    {
        return [
            'token',
            'parameters',
            'ttl',
            'expiration_date',
        ];
    }

    /**
     * @return bool
     */
    public function hasExpired(): bool
    {
        return (CMbDT::dateTime() >= $this->expiration_date);
    }

    /**
     * @return int
     */
    public function getTTL(): int
    {
        return $this->ttl;
    }

    /**
     * @param string $candidate
     *
     * @return bool
     */
    public function match(string $candidate): bool
    {
        /**
         * hash_equals() prevents from temporal attacks.
         * HMAC can be protected by such attacks by design, but depending on chosen ALGO.
         */
        return hash_equals($this->token, $candidate);
    }

    /**
     * @param array $parameters
     *
     * @return bool
     */
    public function isValid(array $parameters): bool
    {
        // No parameters means that we do not check them
        if (empty($this->parameters)) {
            return true;
        }

        // Additional parameters are forbidden, but we allow to not submit all ones
        $diff = array_diff_key($parameters, $this->parameters);

        // If diff is not empty, there is additional (forbidden) parameters
        if (!empty($diff)) {
            return false;
        }

        foreach ($this->getEnforcedParameters(false) as $_field => $_value) {
            // If enforced value is an array, we consider it as an enumeration
            $_is_enum = is_array($_value);

            if (!array_key_exists($_field, $parameters)) {
                if ($_is_enum) {
                    // If enum, user must submit the parameter
                    return false;
                }

                // Allowed parameter has not been submitted, allowed
                continue;
            }

            if ($_is_enum) {
                if (!in_array($parameters[$_field], $_value, false)) {
                    return false;
                }
            } elseif ($parameters[$_field] != $_value) {
                // Checking that given values are equals.
                // Loose comparison between token parameters and given ones because of HTTP serialization.

                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getEnforcedParameters(bool $only_scalars = false): array
    {
        return array_filter(
            $this->parameters,
            function ($v) use ($only_scalars) {
                return ($only_scalars) ? (($v !== null) && is_scalar($v)) : ($v !== null);
            }
        );
    }

    public function __toString()
    {
        return (string) $this->token;
    }
}
