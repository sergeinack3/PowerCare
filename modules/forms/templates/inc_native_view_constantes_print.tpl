{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style type="text/css">
table.tbl.constantes td {
  white-space: nowrap;
}
</style>

{{assign var=cst value=$object->loadListConstantesMedicales()}}
{{assign var=grid value='Ox\Mediboard\Patients\CConstantesMedicales::buildGrid'|static_call:$object->_list_constantes_medicales:false:true}}

{{mb_include module=patients template=print_constantes constantes_medicales_grid=$grid}}
