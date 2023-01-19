{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cerfa script=Cerfa register=true}}

<div class="small-warning" id="groupe_seance_limit">
  <span>{{$msg_alert|smarty:nodefaults}}</span> <br />
  <span id="list_cerfa_entente">
  </span>
</div>

<table class="main tbl">
  <tr>
    <th class="title" colspan="3">{{tr}}CConsultationCategorie-Cerfa request for prior agreement{{/tr}}</th>
  </tr>
  <tr>
    <th class="narrow">{{tr}}Cerfa-Cerfa number{{/tr}}</th>
    <th>{{tr}}Cerfa-Cerfa label{{/tr}}</th>
    <th class="narrow">{{tr}}common-Action{{/tr}}</th>
  </tr>
  {{foreach from=$list_cerfa key=key_cerfa item=cerfa_name}}
    <tr>
      <td>{{$key_cerfa}}</td>
      <td class="text">{{$cerfa_name}}</td>
      <td class="button">
        <button type="button" onclick="Cerfa.editCerfa('{{$key_cerfa}}', '{{$consultation->_class}}', '{{$consultation->_id}}');">
          <i class="fas fa-check" style="color: forestgreen;"></i> {{tr}}common-action-Select{{/tr}}
        </button>
      </td>
    </tr>
  {{/foreach}}
</table>
