<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CAppUI;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Admin\CUser;

/**
 * Interoperability Sender HTTP
 */
class CSenderHTTP extends CInteropSender
{
    // DB Table key
    public $sender_http_id;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'sender_http';
        $spec->key   = 'sender_http_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|senders_http";
        $props["user_id"]  .= " back|expediteur_http";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function loadRefsExchangesSources(bool $put_all_sources = false): array
    {
        $source_http = CExchangeSource::get(
            "$this->_guid",
            CSourceHTTP::TYPE,
            true,
            $this->_type_echange,
            false,
            $put_all_sources
        );
        $this->_ref_exchanges_sources[$source_http->_guid] = $source_http;

        return $this->_ref_exchanges_sources;
    }

    /**
     * @param CUser $user
     *
     * @return $this|null
     * @throws Exception
     */
    public static function loadFromUser(CUser $user): ?CSenderHTTP
    {
        if (!$user->_id) {
            return null;
        }

        $sender = new CSenderHTTP();

        $ds    = $sender->getDS();
        $where = [
            'actif'   => $ds->prepare('= ?', 1),
            'role'    => $ds->prepare('= ?', CAppUI::conf("instance_role")),
            'user_id' => $ds->prepare('= ?', $user->_id),
        ];

        $sender->loadObject($where);

        return $sender->_id ? $sender : null;
    }
}
