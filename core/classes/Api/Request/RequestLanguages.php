<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request;

use Symfony\Component\HttpFoundation\Request;

class RequestLanguages implements IRequestParameter
{
    /** @var string */
    public const HEADER_KEY_WORD = 'Accept-Language';

    /** @var string */
    public const SHORT_TAG_FR = 'fr';

    /** @var string */
    public const LONG_TAG_FR = 'fr-FR';

    /** @var string */
    public const SHORT_TAG_EN = 'en';

    /** @var string */
    public const LONG_TAG_EN = 'en-US';

    /** @var array */
    private $languages;

    /** @var array */
    private $languages_weighting;


    /**
     * CResourceLinks constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $languages       = $request->headers->get(static::HEADER_KEY_WORD, static::SHORT_TAG_FR);
        $this->languages = explode(',', $languages);

        foreach ($this->languages as $language) {
            $result    = explode(';', $language);
            $result[1] = $result[1] ?? null;
            [$tag, $factor] = $result;
            $this->languages_weighting[$tag] = $factor ? substr($factor, 2) : $factor;
        }

        arsort($this->languages_weighting);
    }

    /**
     * @return array
     */
    public function getLanguage(): array
    {
        return $this->languages;
    }

    /**
     * @return array
     */
    public function getWeithtingLanguages(): array
    {
        return $this->languages_weighting;
    }

    /**
     * @return string|null
     */
    public function getExpected(): ?string
    {
        $keys = array_keys($this->languages_weighting);

        return reset($keys);
    }
}
