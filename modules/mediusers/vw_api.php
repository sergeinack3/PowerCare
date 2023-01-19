<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\Api\CAPITools;

CCanDo::checkAdmin();

$api = array(
  'api_get_functions'  => array(
    'method' => 'get',
    'fields' => array(),
    'forms'  => null
  ),
  'api_get_profiles'   => array(
    'method' => 'get',
    'fields' => array(),
    'forms'  => null
  ),
  'api_get_user_types' => array(
    'method' => 'get',
    'fields' => array(),
    'forms'  => null
  ),
  'api_create_user'    => array(
    'method' => 'post',
    'fields' => array(),
    'form'   => array(
      'login'                   => CAPITools::makeAPIfield('text', true),
      'ldap_guid'               => CAPITools::makeAPIfield('text'),
      'nom'                     => CAPITools::makeAPIfield('text', true),
      'prenom'                  => CAPITools::makeAPIfield('text'),
      'type'                    => CAPITools::makeAPIfield('select', true, '13', array_keys(CUser::$types)),
      'fonction_id'             => CAPITools::makeAPIfield('num', true),
      'profil_id'               => CAPITools::makeAPIfield('num'),
      'actif'                   => CAPITools::makeAPIfield('bool', true),
      'forcer_changmt_mdp'      => CAPITools::makeAPIfield('bool'),
      'autoriser_changmt_mdp'   => CAPITools::makeAPIfield('bool'),
      'mot_de_passe'            => CAPITools::makeAPIfield('password'),
      'acces_local'             => CAPITools::makeAPIfield('bool'),
      'deb_activite'            => CAPITools::makeAPIfield('date'),
      'fin_activite'            => CAPITools::makeAPIfield('date'),
      'adresse'                 => CAPITools::makeAPIfield('text'),
      'cp'                      => CAPITools::makeAPIfield('text'),
      'ville'                   => CAPITools::makeAPIfield('text'),
      'tel'                     => CAPITools::makeAPIfield('text'),
      'email'                   => CAPITools::makeAPIfield('text'),
      'adeli'                   => CAPITools::makeAPIfield('text'),
      'rpps'                    => CAPITools::makeAPIfield('text'),
      'inami'                   => CAPITools::makeAPIfield('text'),
      'cps'                     => CAPITools::makeAPIfield('text'),
      'titres'                  => CAPITools::makeAPIfield('text'),
      'initiales'               => CAPITools::makeAPIfield('text'),
      'couleur'                 => CAPITools::makeAPIfield('text'),
      'bris_de_glace'           => CAPITools::makeAPIfield('bool'),
      'commentaires'            => CAPITools::makeAPIfield('text'),
      'rib'                     => CAPITools::makeAPIfield('text'),
      'code_intervenant_ssr'    => CAPITools::makeAPIfield('text'),
      'secteur'                 => CAPITools::makeAPIfield('select', false, '1', array('1', '2')),
      'pratique_tarifaire'      => CAPITools::makeAPIfield('select', false, 'none', array('none', 'optam', 'optamco')),
      'mode_tp_acs'             => CAPITools::makeAPIfield('select', false, 'tp_coordonne', array('tp_coordonne', 'amc_standard')),
      'cab'                     => CAPITools::makeAPIfield('text'),
      'conv'                    => CAPITools::makeAPIfield('text'),
      'zisd'                    => CAPITools::makeAPIfield('text'),
      'ik'                      => CAPITools::makeAPIfield('text'),
      'ean'                     => CAPITools::makeAPIfield('text'),
      'ean_facturation'         => CAPITools::makeAPIfield('text'),
      'num_ofac'                => CAPITools::makeAPIfield('text'),
      'rcc'                     => CAPITools::makeAPIfield('text'),
      'adherent'                => CAPITools::makeAPIfield('text'),
      'debut_bvr'               => CAPITools::makeAPIfield('text'),
      'texte_relance'           => CAPITools::makeAPIfield('text'),
      'mail_apicrypt'           => CAPITools::makeAPIfield('text'),
      'adresse_mssante'         => CAPITools::makeAPIfield('text'),
      'deleguer_compta'         => CAPITools::makeAPIfield('bool'),
    )
  )
);

$smarty = new CSmartyDP();
$smarty->assign('api', $api);
$smarty->display('vw_api.tpl');
