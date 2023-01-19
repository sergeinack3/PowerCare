{{*
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig-treatment" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}
  <table class="form">

    {{assign var=hprimsante_versions value='Ox\Interop\Hprimsante\CHPrimSanteMessage::getVersions'|static_call:null}}
    {{assign var=list_hprimsante_versions value='|'|implode:$hprimsante_versions}}
    {{mb_include module=system template=inc_config_enum var=default_version values=$list_hprimsante_versions}}

    {{mb_include module=system template=inc_config_bool var=mandatory_num_dos_ipp_adm}}

    {{mb_include module=system template=inc_config_str var=tag}}
    {{mb_include module=system template=inc_config_str var=sending_application}}
    {{mb_include module=system template=inc_config_str var=importFunctionName}}
    {{mb_include module=system template=inc_config_bool var=doctorActif}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>