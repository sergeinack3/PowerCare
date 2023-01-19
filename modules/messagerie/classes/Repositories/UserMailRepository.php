<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Repositories;

use Exception;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\System\CSourcePOP;

/**
 * Repository to fetch CSampleMovie objects.
 */
class UserMailRepository extends AbstractMailRepository
{
    /**
     * Count the user's mails using the repository parameters.
     *
     * @throws Exception
     */
    public function countUnreadMails(?CMediusers $user = null): int
    {
        $count = 0;

        if ($user) {
            $accounts = CSourcePOP::getAccountsFor($user);
            foreach ($accounts as $account) {
                $count += CUserMail::countUnread($account->_id);
            }
        } else {
            $source = new CSourcePOP();
            $ds     = $source->getDS();

            $where                    = [
                'object_class'                => " = 'CMediusers'",
                'name'                        => " NOT LIKE '%apicrypt'",
                'users_mediboard.function_id' => $ds->prepare('= ?', CMediusers::get()->function_id),
            ];
            $ljoin = ['users_mediboard' => 'source_pop.object_id = users_mediboard.user_id'];

            $source_ids = $source->loadIds($where, null, null, null, $ljoin);

            $where = [
                'account_id'    => CSQLDataSource::prepareIn($source_ids),
                'account_class' => "= 'CSourcePOP'",
                'archived'      => "= '0'",
                'sent'          => "= '0'",
                'date_read'     => 'IS NULL',
                'draft'         => "= '0'",
                'folder_id'     => 'IS NULL',
            ];

            $mail  = new CUserMail();
            $count = $mail->countList($where);
        }

        return $count;
    }
}
