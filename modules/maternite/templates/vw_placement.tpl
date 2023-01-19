{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=operation}}

<script>
  Main.add(function () {
    Calendar.regField(getForm("changeDate").date, null, {noView: true});
  });

  EditCheckList = {
    url:  null,
    edit: function (salle_id, multi_ouverture) {
      var url = new Url('salleOp', 'ajax_edit_checklist');
      url.addParam('date', '{{$date}}');
      url.addParam('salle_id', salle_id);
      url.addParam('bloc_id', 0);
      url.addParam('type', 'ouverture_salle');
      if (multi_ouverture) {
        url.addParam('multi_ouverture', multi_ouverture);
      }
      url.requestModal();
      url.modalObject.observe("afterClose", function () {
        location.reload();
      });
    }
  };
</script>

{{assign var=systeme_materiel          value="dPbloc CPlageOp systeme_materiel"|gconf}}
{{assign var=enable_surveillance_perop value="monitoringMaternite general active_graph_supervision"|gconf}}

<table class="tbl main">
  <tr>
    <th class="title" colspan="10">
      Accouchements du {{$date|date_format:$conf.longdate}}
      <form name="changeDate" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
    </th>
  </tr>
  <tr>
    <th>{{tr}}CSejour-patient_id{{/tr}}</th>
    <th>{{tr}}CSejour-praticien_id{{/tr}}</th>
    <th>{{tr}}COperation-anesth_id{{/tr}}</th>
    <th>{{tr}}COperation-time_operation{{/tr}}</th>
    <th>{{tr}}CSalle{{/tr}}</th>

    {{if "monitoringMaternite"|module_active && $enable_surveillance_perop}}
      <th>{{tr}}CGrossesse-datetime_debut_travail-court{{/tr}}</th>
    {{/if}}

    <th>Accouchement</th>
    <th>Remarques</th>
  </tr>
  {{foreach from=$urgences item=_op}}
    {{assign var=sejour value=$_op->_ref_sejour}}
    {{assign var=patient value=$sejour->_ref_patient}}
    {{assign var=consult_anesth value=$_op->_ref_consult_anesth}}
    {{assign var=anesth value=$_op->_ref_anesth}}
    <tr>
      <td>
        <span class="{{if !$sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $sejour->septique}}septique{{/if}}"
              onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient}}
        </span>

        {{mb_include module=patients template=inc_icon_bmr_bhre}}
      </td>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_chir}}</td>
      <td>
        <form name="editPlageFrm{{$_op->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
          <input type="hidden" name="m" value="dPplanningOp" />
          <input type="hidden" name="dosql" value="do_planning_aed" />
          <input type="hidden" name="operation_id" value="{{$_op->_id}}" />
          <select name="anesth_id" style="width: 15em;" onchange="this.form.onsubmit()">
            <option value="">&mdash; Anesthésiste</option>
            {{foreach from=$anesths item=_anesth}}
              <option value="{{$_anesth->_id}}" {{if $_anesth->_id == $anesth->_id}}selected="selected"{{/if}}>{{$_anesth}}</option>
            {{/foreach}}
          </select>
        </form>
      </td>
      {{if $_op->annulee}}
        <td colspan="{{if "monitoringMaternite"|module_active && $enable_surveillance_perop}}3{{else}}2{{/if}}" class="cancelled">
          Annulée
        </td>
      {{else}}
        <td class="text">
          {{if !$_op->annulee}}
            <form name="editTimeFrm{{$_op->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
              <input type="hidden" name="m" value="dPplanningOp" />
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="dosql" value="do_planning_aed" />
              <input type="hidden" name="operation_id" value="{{$_op->_id}}" />
              {{assign var=_op_id value=$_op->_id}}
              {{mb_field object=$_op field=time_operation form="editTimeFrm$_op_id" register=true onchange="this.form.onsubmit()"}}
            </form>
          {{else}}
            {{mb_value object=$_op field=time_operation}}
          {{/if}}
        </td>
        <td class="text">
          {{if !$_op->annulee}}
            <form name="editSalleFrm{{$_op->_id}}" action="?m={{$m}}" method="post"
                  onsubmit="return onSubmitFormAjax(this, {onComplete: function(){window.location.reload();}})">
              <input type="hidden" name="m" value="dPplanningOp" />
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="dosql" value="do_planning_aed" />
              <input type="hidden" name="operation_id" value="{{$_op->_id}}" />
              <select style="width: 15em;" name="salle_id" onchange="this.form.onsubmit()">
                <option value="">&mdash; {{tr}}CSalle.select{{/tr}}</option>
                {{foreach from=$listBlocs item=_bloc}}
                  <optgroup label="{{$_bloc}}">
                    {{foreach from=$_bloc->_ref_salles item=_salle}}
                      <option value="{{$_salle->_id}}" {{if $_salle->_id == $_op->salle_id}}selected="selected"{{/if}}>
                        {{$_salle}}
                      </option>
                      {{foreachelse}}
                      <option value="" disabled="disabled">{{tr}}CSalle.none{{/tr}}</option>
                    {{/foreach}}
                  </optgroup>
                {{/foreach}}
              </select>
            </form>
            {{assign var=salle_id value=$_op->salle_id}}
            {{if $_op->salle_id && isset($date_last_checklist.$salle_id|smarty:nodefaults)}}
              <br />
              <div class="info">
                {{tr}}CDailyCheckList.last_validation{{/tr}}:
                {{$date_last_checklist.$salle_id|date_format:$conf.datetime}}
              </div>
              {{if $date_last_checklist.$salle_id|date_format:$conf.date != $date|date_format:$conf.date}}
                <button class="checklist" type="button" onclick="EditCheckList.edit('{{$salle_id}}');">
                  {{tr}}CDailyCheckList.validation{{/tr}}
                </button>
              {{else}}
                <button class="checklist" type="button"
                        onclick="EditCheckList.edit('{{$salle_id}}', true);">{{tr}}CDailyCheckList._type.ouverture_salle{{/tr}}</button>
              {{/if}}
            {{/if}}

            {{if $_op->_alternate_plages|@count}}
              <form name="editPlageFrm{{$_op->_id}}" action="?m={{$m}}" method="post">
                <input type="hidden" name="m" value="dPplanningOp" />
                <input type="hidden" name="del" value="0" />
                <input type="hidden" name="dosql" value="do_planning_aed" />
                <input type="hidden" name="operation_id" value="{{$_op->_id}}" />
                <input type="hidden" name="date" value="" />
                <input type="hidden" name="time_op" value="" />
                <input type="hidden" name="salle_id" value="" />
                <input type="hidden" name="horaire_voulu" value="{{$_op->time_operation}}" />
                <select name="plageop_id" style="width: 15em;" onchange="this.form.submit()">
                  <option value="">&mdash; Replacer cette intervention</option>
                  {{foreach from=$_op->_alternate_plages item=_plage}}
                    <option value="{{$_plage->_id}}">{{$_plage->_ref_salle}} - {{mb_value object=$_plage field=debut}}
                      à {{mb_value object=$_plage field=fin}} - {{$_plage}}</option>
                  {{/foreach}}
                </select>
              </form>
            {{/if}}
          {{else}}
            {{mb_value object=$_op field=salle_id}}
          {{/if}}
          {{if $systeme_materiel == "expert"}}
            {{mb_include module=dPbloc template=inc_button_besoins_ressources type=operation_id usage=1 object_id=$_op->_id}}
          {{/if}}
        </td>
        {{if "monitoringMaternite"|module_active && $enable_surveillance_perop}}
          <td>
            {{mb_value object=$sejour->_ref_grossesse field=datetime_debut_travail}}
          </td>
        {{/if}}
      {{/if}}

      <td class="text">
        <button type="button" class="edit compact" style="float: right;" onclick="Operation.dossierBloc('{{$_op->_id}}')">
          Dossier accouchement
        </button>
        <span style="float: right;">
          {{mb_include module=patients template=vw_antecedents_allergies sejour_id=$sejour->_id}}
          {{if $_op->_is_urgence}}
            <img src="images/icons/attente_fourth_part.png" title="{{tr}}COperation-emergency{{/tr}}" />
          {{/if}}
        </span>
        <a href="?m=planningOp&tab=vw_edit_urgence&operation_id={{$_op->_id}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}');">
        {{if $_op->libelle}}
          <em>[{{$_op->libelle}}]</em>
          <br />
        {{/if}}
          {{foreach from=$_op->_ext_codes_ccam item=_code}}
            <strong>{{$_code->code}}</strong>
             : {{$_code->libelleLong}}
            <br />
          {{/foreach}}
        </span>
        </a>
      </td>
      <td class="text">
        <a href="?m=planningOp&tab=vw_edit_urgence&operation_id={{$_op->_id}}">
          {{$_op->rques|nl2br}}
        </a>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}COperation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>