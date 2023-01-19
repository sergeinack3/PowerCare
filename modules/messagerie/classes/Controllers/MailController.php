<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CController;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Medimail\CMedimailAccount;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\MailCounter;
use Ox\Mediboard\Mssante\CMSSanteUserAccount;
use Ox\Mediboard\System\CSourcePOP;
use Symfony\Component\HttpFoundation\Response;

class MailController extends CController
{

    private const COUNT_PARAMETER   = 'count';
    private const USER_ID_PARAMETER = 'user_id';

    /**
     * @throws ApiException
     * @throws Exception
     * @api
     */
    public function getMails(RequestApi $request_api): Response
    {
        $user_id    = $request_api->getRequest()->get(self::USER_ID_PARAMETER);
        $count      = $request_api->getRequest()->get(self::COUNT_PARAMETER, false);
        $collection = [];

        $user       = $user_id ? CMediusers::findOrFail($user_id) : null;

        $mailboxes = $user ? $this->getUsedMailBoxes($user) : MailCounter::MAILS;

        if ($count) {
            $mail_counters = $this->getMailCounters($mailboxes, $user);
            $collection    = Collection::createFromRequest($request_api, $mail_counters);
        }

        return $this->renderApiResponse($collection);
    }

    /**
     * @param string[]   $mailboxes
     * @param CMediusers $user
     *
     * @return MailCounter[]
     * @throws Exception
     */
    private function getMailCounters(array $mailboxes, ?CMediusers $user = null): array
    {
        $counter_collection = [];
        foreach ($mailboxes as $mailbox) {
            $mail_counter = new MailCounter($mailbox, $user);
            $mail_counter->computeUnread();
            $counter_collection[] = $mail_counter;
        }

        return $counter_collection;
    }


    private function getUsedMailBoxes(CMediusers $user): array
    {
        $mailboxes = [];

        if (CSourcePOP::getAccountsFor($user)) {
            $mailboxes[] = MailCounter::USER_MAIL;
        }

        if (CModule::getActive("apicrypt")) {
            if (isset((CSourcePOP::getApicryptAccountFor($user))->_id)) {
                $mailboxes[] = MailCounter::APICRYPT;
            }
        }

        if (CModule::getActive("medimail")) {
            if (isset((CMedimailAccount::getAccountFor($user))->_id)) {
                $mailboxes[] = MailCounter::MEDIMAIL;
            }
        }

        if (CModule::getActive("mssante")) {
            if (isset((CMSSanteUserAccount::getAccountFor($user))->_id)) {
                $mailboxes[] = MailCounter::MAILIZ;
            }
        }

        return $mailboxes;
    }
}
