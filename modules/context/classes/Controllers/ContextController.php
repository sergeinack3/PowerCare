<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Context\Controllers;

use Exception;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CMbException;
use Ox\Core\Config\Conf;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Locales\Translator;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Context\Token\Tokenizer;
use Ox\Mediboard\Mediusers\CMediusers;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;

class ContextController extends CController
{
    private Translator $translator;

    /**
     * @api
     */
    public function tokenize(RequestApi $request, Translator $translator, Conf $conf): Response
    {
        $this->translator = $translator;

        $query = $request->getRequest()->query;

        $token_username = $query->get('token_username');
        $rpps           = $query->get('rpps');

        if (!$token_username && !$rpps) {
            throw new HttpException(400, $this->translator->tr('common-error-Missing parameter: %s', 'token_username'));
        }

        $user = null;
        if ($token_username) {
            $user = $this->getUserFromUsername($token_username);
        } elseif ($rpps) {
            $user = $this->getUserFromRpps($rpps);
        }

        if (!$user) {
            throw new HttpException(400, $this->translator->tr('CContext-user_undefined'));
        }

        try {
            $token = (new Tokenizer())
                ->tokenize($user, $this->getAllowedParams($query), (int)$conf->get('context token_lifetime'));
        } catch (Exception $e) {
            throw new HttpException(500, $e->getMessage());
        }

        $item = new Item($token);
        $item->addLinks(
            [
                'url_token' => $token->getUrl(),
            ]
        );

        return $this->renderApiResponse($item);
    }

    private function getUserFromUsername(string $username): CUser
    {
        $user                = new CUser();
        $user->user_username = $username;
        $user->loadMatchingObjectEsc();

        $mediuser = $user->loadRefMediuser();
        if (!$mediuser->_id || !$mediuser->canDo()->read) {
            throw new HttpException(403, $this->translator->tr('common-error-No permission on this object'));
        }

        return $user;
    }

    private function getUserFromRpps(string $rpps): CUser
    {
        $mediuser        = new CMediusers();
        $mediuser->actif = '1';
        $mediuser->rpps  = $rpps;
        $mediuser->loadMatchingObjectEsc();

        if (!$mediuser->_id) {
            throw new HttpException(400, $this->translator->tr('CContext-rpps-unavailable', $rpps));
        }

        if (!$mediuser->canDo()->read) {
            throw new HttpException(403, $this->translator->tr('common-error-No permission on this object'));
        }

        return $mediuser->loadRefUser();
    }

    private function getAllowedParams(InputBag $query): array
    {
        return [
            'ipp'             => $query->get('ipp'),
            'nda'             => $query->get('nda'),
            'name'            => $query->get('name'),
            'firstname'       => $query->get('firstname'),
            'birthdate'       => $query->get('birthdate'),
            'admit_date'      => $query->get('admit_date'),
            'group_tag'       => $query->get('group_tag'),
            'group_idex'      => $query->get('group_idex'),
            'sejour_tag'      => $query->get('sejour_tag'),
            'sejour_idex'     => $query->get('sejour_idex'),
            'view'            => $query->get('view', 'none'),
            'show_menu'       => $query->get('show_menu', '0'),
            'RetourURL'       => $query->get('RetourURL'),
            'rpps'            => $query->get('rpps'),
            'cabinet_id'      => $query->get('cabinet_id'),
            'ext_patient_id'  => $query->get('ext_patient_id'),
            'context_guid'    => $query->get('context_guid'),
            'g'               => $query->get('g'),
            'consultation_id' => $query->get('consultation_id'),
            'patient_id'      => $query->get('patient_id'),
            'rpps_praticien'  => $query->get('rpps_praticien'),
            'numero_finess'   => $query->get('numero_finess'),
            'tabs'            => $query->get('tabs'),
        ];
    }
}
