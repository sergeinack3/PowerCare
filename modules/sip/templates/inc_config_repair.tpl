{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigRepair" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{assign var="mod" value="sip"}}
    <tr>
      <th class="title" colspan="10">{{tr}}config-{{$mod}}-repair{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_str var=repair_segment}}

    {{mb_include module=system template=inc_config_str var=repair_date_min}}

    {{mb_include module=system template=inc_config_str var=repair_date_max}}

    {{mb_include module=system template=inc_config_bool var=verify_repair}}

    <tr>
      <td class="button" colspan="10">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="tbl">
  <tr>
    <th class="title" colspan="3">{{tr}}sip-repair-classes{{/tr}}</th>
  </tr>

  <tr>
    <td>
      {{tr}}sip-repair-class{{/tr}} '{{tr}}CSejour{{/tr}}'
    </td>
    <td>
      <button type="button" class="new" onclick="SIP.repair('start', 'sejour')">
        {{tr}}Start{{/tr}}
      </button>
      <button type="button" class="change" onclick="SIP.repair('retry', 'sejour')">
        {{tr}}Retry{{/tr}}
      </button>
      <button type="button" class="tick" onclick="SIP.repair('continue', 'sejour')">
        {{tr}}Continue{{/tr}}
      </button>
    </td>
    <td id="repair"></td>
  </tr>
</table>

{{if $conf.sip.verify_repair}}
<div class="small-info">Vous êtes en mode vérification des réparations à effectuer.</div>
{{/if}}