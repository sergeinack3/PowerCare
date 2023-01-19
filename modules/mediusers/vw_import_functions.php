<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();


$import_specs = array(
  'intitule'         => 'str',
  'sous-titre'       => 'str',
  'type'             => 'str',
  'couleur'          => 'str',
  'initiales'        => 'str',
  'adresse'          => 'str',
  'cp'               => 'str',
  'ville'            => 'str',
  'tel'              => 'str',
  'fax'              => 'str',
  'mail'             => 'str',
  'siret'            => 'str',
  'quotas'           => 'str',
  'actif'            => 'str',
  'compta_partage'   => 'str',
  'consult_partage'  => 'str',
  'adm_auto'         => 'str',
  'facturable'       => 'str',
  'creation_sejours' => 'str',
  'ufs'              => 'str',
  'ufs_secondaires'  => 'str',
);

$smarty = new CSmartyDP();
$smarty->assign('import_specs', $import_specs);
$smarty->display('vw_import_functions');