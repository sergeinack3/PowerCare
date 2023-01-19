<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\CarePath;

use Ox\Core\CMbString;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Exceptions\CarePath\CarePathMappingException;

final class CarePathDoctor extends AbstractEntity
{
    /** @var string */
    protected $last_name;

    /** @var string */
    protected $first_name;

    /** @var string */
    protected $invoicing_id;

    protected function setInvoicingId(string $id): void
    {
        if ($id !== '') {
            if (!is_numeric($id)) {
                throw CarePathMappingException::invoicingIdMustBeNumeric();
            }
            if (strlen($id) !== 9) {
                throw CarePathMappingException::wrongInvoicingIdSize();
            }
        }

        $this->invoicing_id = $id;
    }

    private function sanitizeName(string $name): string
    {
        return substr(CMbString::removeDiacritics(str_replace(["'", '"', '-'], ' ', $name)), 0, 25);
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $this->sanitizeName($last_name);

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $this->sanitizeName($first_name);

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getInvoicingId(): ?int
    {
        return $this->invoicing_id;
    }
}
