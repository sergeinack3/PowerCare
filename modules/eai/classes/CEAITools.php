<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Mediboard\System\CSourceSMTP;
use Ox\Mediboard\Urgences\CExtractPassages;

/**
 * Class CEAITools
 * EAI tools
 */
class CEAITools implements IShortNameAutoloadable
{
    /** @var string */
    public static $email_to = "mediboard.ito@openxtrem.com";

    /**
     * Notify new actor created
     *
     * @param CInteropActor $actor Actor
     *
     * @return void
     */
    public static function notifyNewActor(CInteropActor $actor): void
    {
        if (!$actor->_id) {
            return;
        }

        $source       = new CSourceSMTP();
        $source->name = 'system-message';
        $source->loadMatchingObject();
        if (!$source->_id) {
            return;
        }

        $log = $actor->loadLastLog();
        if ($log->type != "create") {
            return;
        }

        $mail = self::getActorEmail($actor);

        self::sendEmail($mail['subject'], $mail['body']);
    }

    /**
     * @return CSourceSMTP|null
     * @throws Exception
     */
    protected function getSourceSMTP(): ?CSourceSMTP
    {
        $source       = new CSourceSMTP();
        $source->name = 'system-message';
        $source->loadMatchingObject();
        if (!$source->_id || !$source->active) {
            return null;
        }

        return $source;
    }

    /**
     * @param CExtractPassages $passage
     *
     * @return bool
     * @throws Exception
     */
    public function notifyRPUError(CExtractPassages $passage): bool
    {
        if (!$passage->_id) {
            return false;
        }

        if (!$this->getSourceSMTP()) {
            return false;
        }

        $mail = $this->getRPUEmail($passage);

        return self::sendEmail($mail['subject'], $mail['body']);
    }

    /**
     * Get e-mail rpu
     *
     * @param CInteropActor $actor Actor
     *
     * @return array
     * @throws \Exception
     */
    public static function getRPUEmail(CExtractPassages $passage): ?array
    {
        if (!$passage->_id) {
            return null;
        }

        $group = $passage->loadRefGroup();

        $mail = array(
            'subject' => null,
            'body'    => null,
        );

        $title = CAppUI::tr("CExtractPassages-msg-error for rpu type {$passage->type}");

        $content = "<h3>{$title}</h3>";
        $content .= "<p><ul><li><strong>" . CAppUI::tr('CExtractPassages-date_extract')
            . "</strong> : $passage->date_extract </li>";
        $content .= "<li><strong>" . CAppUI::tr('CExtractPassages-group_id') . "</strong> : $group->_view </li>";
        $content .= "<li><strong>" . CAppUI::tr('CExtractPassages-type') . "</strong> : $passage->type </li>";
        $content .= "<li><strong>" . CAppUI::tr('CExtractPassages-debut_selection')
            . "</strong> : $passage->debut_selection </li>";
        $content .= "<li><strong>" . CAppUI::tr('CExtractPassages-fin_selection')
            . "</strong> : $passage->fin_selection </li>";
        $content .= "</ul></p>";

        $url = CAppUI::conf('base_url');
        $content .= "<h4><a href='{$url}?m=ror&tab=vw_extract_passages'>"
            . CAppUI::tr('CExtractPassages-msg-Access to extract') . "</a></h4>";

        $mail['subject'] = CAppUI::tr('CExtractPassages-msg-error for rpu') . " ($group->_view)";
        $mail['body']    = $content;

        return $mail;
    }


    /**
     * Get e-mail new actor
     *
     * @param CInteropActor $actor Actor
     *
     * @return array
     */
    public static function getActorEmail(CInteropActor $actor): ?array
    {
        if (!$actor->_id) {
            return null;
        }

        $group = $actor->loadRefGroup();
        $user  = $actor->_ref_last_log->loadRefUser();

        $mail = array(
            'subject' => null,
            'body'    => null,
        );

        $title = CAppUI::tr("{$actor->_parent_class}-msg-The interop actor have been created.");

        $content = "<h3>{$title}</h3>";
        $content .= "<p><ul><li><strong>" . CAppUI::tr("{$actor->_class}-nom") . "</strong> : $actor->nom </li>";
        $content .= "<li><strong>" . CAppUI::tr("{$actor->_class}-group_id") . "</strong> : $group->_view </li>";
        $content .= "<li><strong>" . CAppUI::tr("{$actor->_class}-actif") . "</strong> : $actor->actif </li></ul></p>";
        $content .= "<li><strong>" . CAppUI::tr("CUser-user_first_name") . CAppUI::tr("CUser-user_last_name")
            . "</strong> : $user->user_first_name $user->user_last_name </li></ul></p>";

        $url     = CAppUI::conf('base_url');
        $content .= "<h4><a href='{$url}?m=eai&tab=vw_idx_interop_actors#interop_actor_guid={$actor->_guid}'>{$actor->_view}</a></h4>";

        $mail['subject'] = "$actor->_class ($group->_view)";
        $mail['body']    = $content;

        return $mail;
    }

    /**
     * Send email at mediboard.ito@openxtrem.com
     *
     * @param string $subject Mail subject
     * @param string $body    Mail body
     *
     * @return bool Send status
     */
    public static function sendEmail(string $subject, string $body): bool
    {
        return CApp::sendEmail($subject, $body, array(), array(), array(), self::$email_to);
    }
}
