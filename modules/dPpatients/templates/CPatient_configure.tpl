{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=class value=CPatient}}

<form name="EditConfig-{{$class}}" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  <table class="form">
    {{mb_include module=system template=inc_config_enum var=function_distinct values="0|1|2"}}

    <tr>
      <th colspan="2" class="category">{{tr}}CPatient-config-interoperability{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_str var=tag_ipp}}
    {{mb_include module=system template=inc_config_str var=tag_ipp_group_idex}}
    {{mb_include module=system template=inc_config_str var=tag_ipp_trash}}
    {{mb_include module=system template=inc_config_str var=tag_conflict_ipp}}

    {{assign var=class value=INSEE}}

    <tr>
      <th colspan="2" class="category">{{tr}}INSEE{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_bool var=france}}
    {{mb_include module=system template=inc_config_bool var=suisse}}
    {{mb_include module=system template=inc_config_bool var=allemagne}}
    {{mb_include module=system template=inc_config_bool var=espagne}}
    {{mb_include module=system template=inc_config_bool var=portugal}}
    {{mb_include module=system template=inc_config_bool var=gb}}
    {{mb_include module=system template=inc_config_bool var=belgique}}

    <tr>
      <td class="button" colspan="6">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
