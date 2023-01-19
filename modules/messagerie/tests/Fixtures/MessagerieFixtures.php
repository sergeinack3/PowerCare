<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Tests\Fixtures;

use Ox\Mediboard\System\CContentHTML;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Data's used for messaging
 */
class MessagerieFixtures extends Fixtures implements GroupFixturesInterface
{
    public const MESSAGING_CONTENT_TAG = 'messaging_content';

    /**
     * @inheritDoc
     * @throws FixturesException
     */
    public function load(): void
    {
        $this->createMailContent();
    }

    /**
     * @inheritDoc
     */
    public static function getGroup(): array
    {
        return ['messaging_fixtures', 100];
    }

    /**
     * Create a mail content
     *
     * @throws FixturesException
     */
    private function createMailContent(): void
    {
        $content          = new CContentHTML();
        $content->content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean eleifend est congue.';

        $this->store($content, self::MESSAGING_CONTENT_TAG);
    }
}
