<?php

/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Context\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Context\CContextualIntegration;
use Ox\Mediboard\Context\Token\Tokenizer;
use Ox\Mediboard\Mediusers\CMediusers;

class CContextuelIntegrationController extends CLegacyController
{
    public function tokenize(): void
    {
        $this->checkPermEdit();

        // Call parameters, all parameters except token_username HAVE to be present in the call view as well
        $ipp                     = CView::get('ipp', 'str');
        $nda                     = CView::get('nda', 'str');
        $nom                     = CView::get('name', 'str');
        $prenom                  = CView::get('firstname', 'str');
        $date_naiss              = CView::get('birthdate', 'str');
        $date_sejour             = CView::get('admit_date', 'str');
        $group_tag               = CView::get('group_tag', 'str');
        $group_idex              = CView::get('group_idex', 'str');
        $sejour_tag              = CView::get('sejour_tag', 'str');
        $sejour_idex             = CView::get('sejour_idex', 'str');
        $view                    = CView::get('view', 'str notNull default|none');
        $show_menu               = CView::get('show_menu', 'bool default|0');
        $token_username          = CView::get('token_username', 'str');
        $retourURL               = CView::get('RetourURL', 'str');
        $rpps                    = CView::get('rpps', 'str');
        $cabinet_id              = CView::get('cabinet_id', 'str');
        $ext_patient_id          = CView::get('ext_patient_id', 'str');
        $context_guid            = CView::get('context_guid', 'str');
        $g                       = CView::get('g', 'str');
        $consultation_id         = CView::get('consultation_id', 'ref class|CConsultation');
        $consultation_patient_id = CView::get('patient_id', 'ref class|CPatient');
        $rpps_praticien          = CView::get('rpps_praticien', 'str');
        $numero_finess           = CView::get('numero_finess', 'str');
        $tabs                    = CView::get('tabs', 'str');

        CView::checkin();

        $json = [
            'token'     => null,
            'code'      => 0,
            "message"   => null,
            'url_token' => null,
        ];

        if (!$token_username && !$rpps) {
            $json["message"] = CAppUI::tr('common-error-Missing parameter: %s', 'token_username');
            CApp::json($json);
        }

        // Token user
        $user = new CUser();
        if ($token_username) {
            $user->user_username = $token_username;
            $user->loadMatchingObjectEsc();
        }

        if (!$token_username && $rpps) {
            $mediuser        = new CMediusers();
            $mediuser->actif = "1";
            $mediuser->rpps  = $rpps;
            $mediuser->loadMatchingObjectEsc();
            if (!$mediuser->_id) {
                $json["message"] = CAppUI::tr('CContext-rpps-unavailable', $rpps);
                CApp::json($json);
            }
            $user = $mediuser->loadRefUser();
        }

        if (!$user || !$user->_id) {
            $json["message"] = CAppUI::tr('CContext-user_undefined', $token_username);
            CApp::json($json);
        }

        if (!($mediuser = $user->loadRefMediuser()) || !$mediuser->_id || !$mediuser->canDo()->read) {
            $json["message"] = CAppUI::tr('common-error-No permission on this object');
            CApp::json($json);
        }

        $params = [];
        foreach (CView::$params as $name => $value) {
            if ($name !== 'token_username') {
                $params[$name] = $value;
            }
        }

        $msg = $token = null;
        try {
            $token = (new Tokenizer())->tokenize($user, $params, (int)CAppUI::conf('context token_lifetime'));
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        $json = [
            'token'     => $msg ? null : $token->hash,
            'code'      => $msg ? 0 : 1,
            'message'   => trim(strip_tags($msg)),
            'url_token' => $msg ? null : $token->getUrl(),
        ];

        CApp::json($json);
    }

    public function ajax_edit_integration(): void
    {
        $this->checkPermRead();

        $integration_id = CView::get("integration_id", "ref class|CContextualIntegration");

        CView::checkIn();

        $integration = CContextualIntegration::findOrNew($integration_id);
        $integration->loadRefsLocations();

        $this->renderSmarty(
            "inc_edit_integration",
            [
                'integration' => $integration,
            ]
        );
    }

    /**
     * Autocomplete des icônes FontAwesome
     * Cf. ContextualIntegration.iconAutocomplete
     *
     * @throw Exception
     */
    public function icon_autocomplete(): void
    {
        $this->checkPermRead();

        $keywords = CView::request("keywords", "str");
        $list_icon = CContextualIntegration::iconList();

        CView::checkIn();

        if ($keywords) {
            foreach ($list_icon as $_key => $_list) {
                if (strpos(strtolower($_key), strtolower($keywords)) === false) {
                    unset($list_icon[$_key]);
                }
            }
        }

        $this->renderSmarty(
            "inc_icon_autocomplete",
            [
                'list_icon' => $list_icon,
            ]
        );
    }
}
