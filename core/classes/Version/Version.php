<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Version;

/**
 * Application version definition
 */
final class Version
{
    /** @var int */
    private $major;

    /** @var int */
    private $minor;

    /** @var int */
    private $patch;

    /** @var int */
    private $build;

    /** @var string */
    private $datetime_build;

    /** @var string|null */
    private $title;

    /** @var string|null */
    private $title_short;

    /** @var string|null */
    private $update_date;

    /** @var string|null */
    private $release_date;

    /** @var string|null */
    private $complete_date;

    /** @var array|null */
    private $relative;

    /** @var string|null */
    private $code;

    /** @var string|null */
    private $revision;

    /**
     * @param array|null  $data
     */
    public function __construct(array $version_data = null)
    {
        $this->major          = $version_data[Builder::KEY_MAJOR] ?? 0;
        $this->minor          = $version_data[Builder::KEY_MINOR] ?? 0;
        $this->patch          = $version_data[Builder::KEY_PATCH] ?? 0;
        $this->build          = $version_data[Builder::KEY_BUILD] ?? 0;
        $this->datetime_build = $version_data[Builder::KEY_DATETIME_BUILD] ?? null;
        $this->title          = $version_data[Builder::KEY_TITLE] ?? null;
        $this->title_short    = $version_data[Builder::KEY_TITLE_SHORT] ?? null;
        $this->update_date    = $version_data[Builder::KEY_UPDATE_DATE] ?? null;
        $this->release_date   = $version_data[Builder::KEY_RELEASE_DATE] ?? null;
        $this->complete_date  = $version_data[Builder::KEY_COMPLETE_DATE] ?? null;
        $this->relative       = $version_data[Builder::KEY_RELATIVE_DATE] ?? null;
        $this->code           = $version_data[Builder::KEY_CODE] ?? null;
        $this->revision       = $version_data[Builder::KEY_REVISION] ?? null;
    }

    public function toArray(): array
    {
        return [
            'major'               => $this->major,
            'minor'               => $this->minor,
            'patch'               => $this->patch,
            'build'               => $this->build,
            'datetime_build'      => $this->datetime_build,
            'string'              => (string)$this,
            'version'             => sprintf('%d.%d.%d', $this->major, $this->minor, $this->patch),
            'date'                => $this->update_date,
            'relative'            => $this->relative,
            'releaseCode'         => $this->code,
            'releaseDate'         => $this->release_date,
            'releaseDateComplete' => $this->complete_date,
            'releaseRev'          => $this->revision,
            'releaseTitle'        => $this->title_short,
            'title'               => $this->title,
        ];
    }

    public function __toString(): string
    {
        return sprintf('%d.%d.%d.%d', $this->major, $this->minor, $this->patch, $this->build);
    }


    /**
     * @return string
     */
    public function getKey(): string
    {
        $branch  = $this->getCode();
        $version = $this->getBuild();

        return $branch ? "$branch-$version" : $version;
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getMinor(): int
    {
        return $this->minor;
    }

    public function getPatch(): int
    {
        return $this->patch;
    }

    public function getBuild(): int
    {
        return $this->build;
    }

    public function getDatetimeBuild(): ?string
    {
        return $this->datetime_build;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getUpdateDate(): ?string
    {
        return $this->update_date;
    }

    public function getReleaseDate(): ?string
    {
        return $this->release_date;
    }

    public function getCompleteDate(): ?string
    {
        return $this->complete_date;
    }

    public function getDateRelative(): ?array
    {
        return $this->relative;
    }

    public function getRevision(): ?string
    {
        return $this->revision;
    }

    public function getReleaseTitle(): ?string
    {
        return $this->title_short;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
