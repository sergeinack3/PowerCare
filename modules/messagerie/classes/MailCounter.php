<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Exception;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Medimail\CMedimailAccount;
use Ox\Mediboard\Medimail\Repositories\MedimailRepository;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\Repositories\AbstractMailRepository;
use Ox\Mediboard\Messagerie\Repositories\ApicryptRepository;
use Ox\Mediboard\Messagerie\Repositories\UserMailRepository;
use Ox\Mediboard\Mssante\CMSSanteUserAccount;
use Ox\Mediboard\Mssante\Repositories\MssanteRepository;
use Ox\Mediboard\System\CSourcePOP;

/**
 * My class
 */
class MailCounter
{
    /** @var string */
    public const RESOURCE_TYPE = 'mail_counter';

    public const APICRYPT  = "apicrypt";
    public const USER_MAIL = "usermail";
    public const MEDIMAIL  = "medimail";
    public const MAILIZ    = "mailiz";

    public const MAILS = [
        self::MEDIMAIL,
        self::APICRYPT,
        self::MAILIZ,
        self::USER_MAIL,
    ];

    public int                     $unread;
    public string                  $mailbox;
    private ?CMediusers            $user;
    private AbstractMailRepository $repository;

    /**
     * @throws Exception
     */
    public function __construct(string $mailbox, ?CMediusers $user = null)
    {
        if (!in_array($mailbox, self::MAILS)) {
            throw new Exception("Invalid mailbox");
        }

        $this->mailbox    = $mailbox;
        $this->user       = $user;
        $this->repository = $this->getRepository();
    }


    /**
     * get Repository containing all queries needed mails
     * @throws Exception
     */
    public function getRepository(): AbstractMailRepository
    {
        switch ($this->mailbox) {
            case self::APICRYPT:
                return new ApicryptRepository();
            case self::MEDIMAIL:
                return new MedimailRepository();
            case self::MAILIZ:
                return new MssanteRepository();
            case self::USER_MAIL:
                return new UserMailRepository();
            default:
                throw new Exception("Invalid mailbox");
        }
    }

    /**
     * Count unread mails
     * @throws Exception
     */
    public function computeUnread(): void
    {
        $this->unread = $this->repository->countUnreadMails($this->user);
    }

    /**
     * Get the source POP for each mail account
     * @throws Exception
     */
    static function getAccounts(): array
    {
        $user = CMediusers::get();

        $accounts = [];

        foreach (self::MAILS as $mail_box) {
            switch ($mail_box) {
                case self::USER_MAIL:
                    if ($usermail_accounts = CSourcePOP::getAccountsFor($user)) {
                        $accounts[$mail_box] = $usermail_accounts;
                    }
                    break;
                case self::APICRYPT:
                    if (CModule::getActive("apicrypt")) {
                        if (($account = CSourcePOP::getApicryptAccountFor($user)) && isset($account->_id)) {
                            $accounts[$mail_box] = $account;
                        }
                    }
                    break;
                case self::MEDIMAIL:
                    if (CModule::getActive("medimail")) {
                        if (($account = CMedimailAccount::getAccountFor($user)) && isset($account->_id)) {
                            $accounts[$mail_box] = $account;
                        }
                    }
                    break;
                case self::MAILIZ:
                    if (CModule::getActive("mssante")) {
                        if (($account = CMSSanteUserAccount::getAccountFor($user)) && isset($account->_id)) {
                            $accounts[$mail_box] = $account;
                        }
                    }
                    break;
                default:
            }
        }

        return $accounts;
    }
}
