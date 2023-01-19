<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use DateTime;
use DateTimeImmutable;
use Ox\Core\CMbString;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

class SearchResult
{
    /** @var string */
    private $title;

    /** @var CMediusers|null */
    private $author;

    /** @var CPatient|null */
    private $patient;

    /** @var string */
    private $body;

    /** @var string */
    private $guid;

    /** @var string */
    private $type;

    /** @var DateTime */
    private $date;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?CMediusers
    {
        return $this->author;
    }

    public function setAuthor(?CMediusers $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getPatient(): ?CPatient
    {
        return $this->patient;
    }

    public function setPatient(?CPatient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function getBody(int $obfuscated): string
    {
        if (!$obfuscated) {
            return $this->body;
        }


        return $this->obfuscateBody($this->body);
    }

    public function setBody(string $body): self
    {
        $body = str_replace("<em>", "<b>", $body);
        $body = str_replace("</em>", "</b>", $body);

        $this->body = $body;

        return $this;
    }

    private function obfuscateBody(string $value): string
    {
        preg_match_all("/<b>(.*)<\/b>/imU", $value, $matches, PREG_OFFSET_CAPTURE);
        $value = preg_replace("/[A-Za-z0-9]/i", "X", CMbString::removeAccents($value));

        foreach ($matches[0] as $_match) {
            $value = substr_replace($value, $_match[0], $_match[1], strlen($_match[0]));
        }

        return $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getStringDate(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }
}
