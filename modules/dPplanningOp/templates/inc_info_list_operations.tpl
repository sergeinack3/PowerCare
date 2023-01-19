{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=offline value=0}}
{{mb_default var=alert   value=0}}

{{foreach from=$sejour->_ref_operations item=_operation name=operation}}
  <tr>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}

      {{if $_operation->_ref_chir_2 && $_operation->_ref_chir_2->_id}}
        <br/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir_2}}
        <span>(<em>{{mb_label object=$_operation field=chir_2_id}}</em>)</span>
      {{/if}}
      {{if $_operation->_ref_chir_3 && $_operation->_ref_chir_3->_id}}
        <br/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir_3}}
        <span>(<em>{{mb_label object=$_operation field=chir_3_id}}</em>)</span>
      {{/if}}
      {{if $_operation->_ref_chir_4 && $_operation->_ref_chir_4->_id}}
        <br/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir_4}}
        <span>(<em>{{mb_label object=$_operation field=chir_4_id}}</em>)</span>
      {{/if}}

    </td>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_anesth}}
    </td>
    <td>{{$_operation->_datetime|date_format:$conf.date}}
      {{if $_operation->_datetime|date_format:$conf.time != "00h00"}}
        {{$_operation->_datetime|date_format:$conf.time}}
      {{/if}}
      {{if $_operation->annulee}}
    <th class="category cancelled">
      <strong onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">{{tr}}COperation-annulee{{/tr}}</strong>
    </th>
    {{else}}
    <td class="text">
      {{if $alert}}
        <span style="float: right">
        {{mb_include module=planningOp template=inc_reload_infos_interv operation=$_operation just_alert=1}}
      </span>
      {{/if}}
      {{mb_include module=planningOp template=inc_vw_operation}}
    </td>
    {{/if}}
    <td>
      {{if $conf.dPplanningOp.COperation.verif_cote && in_array($_operation->cote, array("droit","gauche"))}}
        <form name="editCoteOp{{$_operation->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
          <input type="hidden" name="m" value="planningOp"/>
          <input type="hidden" name="dosql" value="do_planning_aed"/>
          {{mb_key object=$_operation}}
          {{mb_label object=$_operation field="cote"}} :
          {{mb_field emptyLabel="Choose" object=$_operation field="cote_hospi" onchange="this.form.onsubmit();"}}
        </form>
      {{else}}
        {{mb_value object=$_operation field=cote}}
      {{/if}}
    </td>
    {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
      <td>
        <div id="brancardage-{{$_operation->_guid}}">
          {{mb_include module=brancardage template=inc_exist_brancard colonne="patientPret" object=$_operation
          brancardage_to_load="aller"}}
        </div>
      </td>
    {{/if}}
    <td class="text">
      {{if $_operation->date_visite_anesth}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_anesth_visite initials=border}}
        {{tr}}the{{/tr}} {{$_operation->date_visite_anesth|date_format:$conf.date}}
        {{if "dPsalleOp COperation use_time_vpa"|gconf && $_operation->time_visite_anesth}}
          {{tr}}to{{/tr}} {{$_operation->time_visite_anesth|date_format:$conf.time}}
        {{/if}}
      {{else}}
        {{tr}}common-Not done{{/tr}}
      {{/if}}
      {{if $app->_ref_user->isAnesth()}}
        <button type="button" class="edit notext" onclick="editVisite({{$_operation->_id}});">{{tr}}Edit{{/tr}}</button>
      {{/if}}
    </td>
    <td class="narrow button">
      <button
        class="me-tertiary me-dark {{if $_operation->_ref_consult_anesth && $_operation->_ref_consult_anesth->_id}}print{{else}}warning{{/if}}"
        type="button"
        onclick="
        {{if $offline}}
          var fiche = $('fiche_anesth_{{$_operation->_id}}');
          if (fiche) {
        Modal.open(fiche);
          }
        {{else}}
          printFicheAnesth('{{if $_operation->_ref_consult_anesth}}{{$_operation->_ref_consult_anesth->_id}}{{/if}}', '{{$_operation->_id}}');
        {{/if}}">
        {{tr}}CAnesthPerop-action-Anesthesia sheet{{/tr}}
      </button>
      <br/>
      {{if !$offline}}
        <button class="print me-tertiary me-dark" style="width: 100%; min-width: 10em;" type="button"
                onclick="printFicheBloc('{{$_operation->_id}}');">
          {{tr}}COperation-action-Block sheet{{/tr}}
        </button>
      {{/if}}
      {{mb_include module=forms template=inc_widget_ex_class_register object=$_operation event_name=liaison}}
    </td>
  </tr>
  {{if $_operation->_back && array_key_exists("check_lists", $_operation->_back)}}
    <tr>
      <td colspan="10">
        {{mb_include module=salleOp template=inc_vw_check_lists object=$_operation}}
      </td>
    </tr>
  {{/if}}
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">{{tr}}COperation.none{{/tr}}</td>
  </tr>
{{/foreach}}
