<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Context\Token;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;

/**
 * Create a token for a user using the parameters passed to the function tokenize.
 */
class Tokenizer
{
    public const RAW_VIEWS = [
        'documents',
        'sejour',
        'intervention',
    ];

    /**
     * @param array $parameters Parameters that will be added to the token.
     *
     * @throws CMbException|Exception
     */
    public function tokenize(CUser $user, array $parameters, ?int $token_lifetime = null): CViewAccessToken
    {
        if (!$user->_id) {
            throw new CMbException('Tokenize-Error-User must exists');
        }

        $token                 = new CViewAccessToken();
        $token->user_id        = $user->_id;
        $token->params         = $this->buildParameters($parameters);
        $token->datetime_start = CMbDT::dateTime();
        $token->datetime_end   = $this->getDatetimeEnd(CMbDT::dateTime(), $token_lifetime);
        $token->purgeable      = 1;
        $token->restricted     = 0;

        if ($msg = $token->store()) {
            throw new CMbException($msg);
        }

        return $token;
    }

    private function buildParameters(array $parameters): string
    {
        $params = [
            'm=context',
        ];

        $view = $parameters['view'] ?? 'none';

        if (in_array($view, static::RAW_VIEWS)) {
            $params[] = 'raw=call';
        } elseif ($view === 'get_infos') {
            $params = array_merge($params, ['m=planningOp', 'a=get_dhe_recently_create',]);
        } elseif ($view === 'get_docs') {
            $params = array_merge($params, ['m=planningOp', 'a=get_dhe_docs_recently_create',]);
        } else {
            $params = array_merge($params, ['a=call', 'view=' . $view]);
        }

        foreach ($parameters as $name => $value) {
            if ($name === 'tabs' && $value) {
                foreach ($value as $val) {
                    $params[] = 'tabs[]=' . $val;
                }
            } elseif ($value) {
                $params[] = $name . '=' . $value;
            }
        }

        return implode("\n", $params);
    }

    private function getDatetimeEnd(string $datetime, ?int $token_lifetime): string
    {
        if (!$token_lifetime || $token_lifetime < 0) {
            $token_lifetime = (ini_get('session.gc_maxlifetime')) ? (int)(ini_get('session.gc_maxlifetime') / 60) : 10;
        }

        $token_lifetime = max($token_lifetime, 10);

        return CMbDT::dateTime("+{$token_lifetime} minutes", $datetime);
    }
}
