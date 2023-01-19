<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Import\MovieDb;

use Ox\Core\CMbException;

/**
 * Configurations for images.
 */
class MovieDbImageConfiguration
{
    private const PREFERED_PROFILE_SIZE = 'h632';
    private const PREFERED_POSTER_SIZE  = 'w780';

    /** @var string */
    private $base_url;

    /** @var string */
    private $profile_size;

    /** @var string */
    private $poster_size;

    /**
     * @throws CMbException
     */
    public function __construct(array $config)
    {
        $config = $config['images'];

        if (!isset($config['base_url']) || !isset($config['profile_sizes']) || !isset($config['poster_sizes'])) {
            throw new CMbException('MovieDbImageConfiguration-Error-The-request-does-not-contains-base_url');
        }

        $this->base_url = $config['base_url'];

        $this->profile_size = in_array(self::PREFERED_PROFILE_SIZE, $config['profile_sizes'])
            ? self::PREFERED_PROFILE_SIZE
            : end($config['profile_sizes']);

        $this->poster_size = in_array(self::PREFERED_POSTER_SIZE, $config['poster_sizes'])
            ? self::PREFERED_POSTER_SIZE
            : end($config['poster_sizes']);
    }

    public function getProfilePrefix(): string
    {
        return $this->base_url . $this->profile_size;
    }

    public function getPosterPrefix(): string
    {
        return $this->base_url . $this->poster_size;
    }
}
