{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{if "dPmedicament"|module_active}}
      <tr>
        <th class="category" colspan="2">{{tr}}config-{{$m}}{{/tr}}</th>
      </tr>
      {{mb_include module=system template=inc_config_bool var=inLivretTherapeutique}}
    {{else}}
      {{mb_include module=system template=inc_config_bool var=AntiCoagulantList}}
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{if !"dPmedicament"|module_active}}
  <div class="big-info">
    {{tr}}config-bloodSalvage-Anticoagulant-desc{{/tr}}
  </div>
{{/if}}
