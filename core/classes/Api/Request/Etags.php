<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Symfony\Component\HttpFoundation\Request;

class Etags implements \Countable
{
    private $etags = [];

    public static function createFromRequest(Request $request): Etags
    {
        return new self($request->getETags());
    }

    public function __construct(array $etags)
    {
        foreach ($etags as $etag) {
            $this->etags[] = $this->sanitize($etag);
        }
    }

    public function getEtags(): array
    {
        return $this->etags;
    }

    public function hasEtag(string $etag): bool
    {
        return in_array($this->sanitize($etag), $this->etags);
    }

    private function sanitize(string $etag): string
    {
        return str_replace('"', '', $etag);
    }

    public function count(): int
    {
        return count($this->etags);
    }
}
