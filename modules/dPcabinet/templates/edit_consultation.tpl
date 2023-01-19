{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $consult->_id && !$consult->sejour_id}}
  {{assign var=pref_prat value='Ox\Mediboard\System\CPreferences::getAllPrefs'|static_call:$consult->_ref_praticien->_id}}
  {{if $pref_prat.allowed_new_consultation == 0}}
    <div class="small-info">{{tr}}pref.allowed_new_consultation.0{{/tr}} {{$consult->_ref_praticien}}</div>
    {{if !$app->_ref_user->isAdmin()}}{{mb_return}}{{/if}}
  {{/if}}
{{/if}}

{{mb_script module="cabinet" script="dossier_medical"}}
{{assign var=auto_refresh_frequency value="dPcabinet CConsultation auto_refresh_frequency"|gconf}}
{{mb_default var=synthese_rpu value=0}}

{{if $current_m == "dPurgences"}}
  {{mb_script module="planningOp" script="sejour"}}
{{/if}}

{{if 'teleconsultation'|module_active}}
    {{mb_script module=teleconsultation script=teleconsultation ajax=true}}
{{/if}}

{{mb_script module="patients" script="patient" ajax=true}}

<script>
  Main.add(function() {
    ListConsults.init("{{$consult->_id}}", "{{$userSel->_id}}", "{{$date}}", "{{$vue}}", "{{$current_m}}", "{{$auto_refresh_frequency}}");
    $("listConsult").fixedTableHeaders(1.0);
    {{if $launchTeleconsultation}}Teleconsultation.checkRoomActive('{{$consult->_id}}');{{/if}}
  })
</script>

{{if $consult->_id && $consult->_ref_consult_anesth->_id}}
  {{assign var=operation value=$consult->_ref_consult_anesth->_ref_operation}}
  {{if $operation->_id}}
    <form name="addOpFrm" action="?m={{$m}}" method="post">
      <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="m" value="dPcabinet" />
      <input type="hidden" name="sejour_id" value="{{$operation->sejour_id}}" />
      <input type="hidden" name="operation_id" value="{{$operation->_id}}" />
    </form>
  {{/if}}
{{/if}}

{{if $current_m == "dPurgences"}}
  {{mb_include module=soins template=inc_common_forms sejour=$consult->_ref_sejour}}
{{/if}}

<table class="main">
  <tr>
    <td style="width: 240px; {{if $synthese_rpu}}display: none;{{/if}}" id="listConsultToggle">
      <div id="listConsult"></div>
    </td>
    <td>{{mb_include module=cabinet template=inc_full_consult}}</td>
  </tr>
</table>
