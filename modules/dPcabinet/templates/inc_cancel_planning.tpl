{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=meeting_id value=$meeting_id|default:""}}

<script>
  CancelAction = {
    action: null,
    form: null,

    checkAll: function(input) {
      $$('input.consult').each(function(element) {
        element.checked = input.checked;
      });
    },

    confirm: function(button, action) {
      $('cell_motif_annulation').hide();
      $$('div.confirm').invoke('hide');
      $$('div.'+action).invoke('show');

      if (action == 'cancel-1') {
        $('cell_motif_annulation').show();
        $V(button.form.motif_annulation, 'not_arrived');
        button.form._rques.value = button.form.rques.value;
      }

      Modal.open('following_consultations');
      this.action = action;
      this.form = button.form;
    },

    submit: function() {
      var consultation_ids = $$('input.consult:checked').pluck('value');
      consultation_ids.push($V(this.form.consultation_id));
      consultation_ids = consultation_ids.join('-');
      $V(this.form.consultation_ids, consultation_ids);

      switch (this.action) {
        case 'cancel-0': $V(this.form.annule, '0'); break;
        case 'cancel-1': $V(this.form.annule, '1'); break;
        case 'deletion': $V(this.form.del   , '1'); break;
      }

      if (checkForm(this.form)) {
        {{if $dialog && $modal}}
          Control.Modal.close();
          this.form.onsubmit();
        {{else}}
          this.form.submit();
        {{/if}}
      }
    },

    deleteMeeting: function () {
      if (confirm($T('CReunion-confirm-delete'))) {
        onSubmitFormAjax(getForm('deleteMeeting'), {onComplete: window.parent.Control.Modal.close});
      }
    }
  }
</script>

{{if $consult->annule}}
  <button class="change me-secondary" type="button" onclick="CancelAction.confirm(this, 'cancel-0')">
    {{tr}}Restore{{/tr}}
  </button>
{{else}}
  <button class="cancel me-secondary" type="button" onclick="CancelAction.confirm(this, 'cancel-1')">
    {{tr}}Cancel{{/tr}}
  </button>
{{/if}}

{{if $can->admin || !$consult->patient_id}}
<button class="trash me-tertiary" type="button" onclick="CancelAction.confirm(this, 'deletion');">
  {{tr}}Delete{{/tr}}
</button>
   {{if $meeting_id}}
    <button class="trash me-tertiary" type="button" onclick="CancelAction.deleteMeeting()">
      {{tr}}Delete-Meeting{{/tr}}
    </button>
  {{/if}}
{{/if}}

<div id="following_consultations" style="display: none; width: 500px; max-height: 600px; overflow-y: auto;">
  <table class="tbl">
    {{if count($following_consultations)}}
      <tr>
        <td colspan="4" class="text">
          <div class="big-warning">
            <div class="confirm cancel-0"><strong>{{tr}}CConsultation-confirm-cancel-0{{/tr}}</strong></div>
            <div class="confirm cancel-1"><strong>{{tr}}CConsultation-confirm-cancel-1{{/tr}}</strong></div>
            <div class="confirm deletion"><strong>{{tr}}CConsultation-confirm-deletion{{/tr}}</strong></div>
            <div>{{tr}}CConsultation-propose-selection-1{{/tr}}</div>
            <div>{{tr}}CConsultation-propose-selection-2{{/tr}}</div>
          </div>
        </td>
      </tr>

      <tr>
        <th colspan="4" class="title">Rendez-vous suivants</th>
      </tr>
      <tr>
        <th class="narrow">
          <input type="checkbox" value="" onclick="CancelAction.checkAll(this);" />
        </th>
        <th style="text-align: center;">{{mb_label class=CConsultation field=_datetime}}</th>
        <th style="text-align: center;">{{mb_label class=CConsultation field=_praticien_id}}</th>
        <th style="text-align: center;">{{mb_label class=CConsultation field=_etat}}</th>
      </tr>


      {{foreach from=$following_consultations item=_consultation}}
      <tr>
        <td class="narrow">
          <input class="consult" type="checkbox" value="{{$_consultation->_id}}" />
        </td>
        <td>{{mb_value object=$_consultation field=_datetime}}</td>
        <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consultation->_ref_praticien}}</td>
        <td {{if $_consultation->annule}} class="cancelled" {{/if}}>
          {{mb_value object=$_consultation field=_etat}}
        </td>
      </tr>
      {{/foreach}}

    {{else}}
      <div class="small-warning">
        <div class="confirm cancel-0">{{tr}}CConsultation-confirm-cancel-0{{/tr}}</div>
        <div class="confirm cancel-1">{{tr}}CConsultation-confirm-cancel-1{{/tr}}</div>
        <div class="confirm deletion">{{tr}}CConsultation-confirm-deletion{{/tr}}</div>
      </div>
    {{/if}}

    <tbody id="cell_motif_annulation" style="display: none;">
      <tr>
        <td style="text-align: right" colspan="2"><strong>{{mb_title object=$consult field=motif_annulation}}</strong></td>
        <td colspan="2">
          <input type="radio" name="_motif_annulation" value="not_arrived" checked="checked" onclick="CancelAction.form.motif_annulation.value = this.value;">
          <label for="editFrm__motif_annulation_not_arrived" id="labelFor_editFrm__motif_annulation_not_arrived" class="">{{tr}}CConsultation.motif_annulation.not_arrived{{/tr}}</label> <br/>

          <input type="radio" name="_motif_annulation" value="by_patient" onclick="CancelAction.form.motif_annulation.value = this.value;">
          <label for="editFrm__motif_annulation_by_patient" id="labelFor_editFrm__motif_annulation_by_patient" class="">{{tr}}CConsultation.motif_annulation.by_patient{{/tr}}</label> <br/>

          <input type="radio" name="_motif_annulation" value="other" onclick="CancelAction.form.motif_annulation.value = this.value;">
          <label for="editFrm__motif_annulation_other" id="labelFor_editFrm__motif_annulation_other" class="">{{tr}}CConsultation.motif_annulation.other{{/tr}}</label>
        </td>
      </tr>
      <tr >
        <td style="text-align: right" colspan="2"><strong>{{mb_label object=$consult field=rques}}</strong></td>
        <td colspan="2">
          <textarea name="_rques" value="" onchange="CancelAction.form.rques.value = this.value;"></textarea>
        </td>
      </tr>
      {{if $consult->sejour_id}}
        <tr>
          <td class="narrow">
            <strong>{{tr}}CConsultation-sejour_id-cancel{{/tr}}</strong><br/>
            <span onmouseover="ObjectTooltip.createEx(this, 'CSejour-{{$consult->sejour_id}}');">
              {{mb_value object=$consult field=sejour_id}}
            </span>
          </td>
          <td colspan="2">
            <input type="radio" name="__cancel_sejour" value="1" onclick="$V(CancelAction.form._cancel_sejour, 1);">
            <label for="editFrm___cancel_sejour_1" id="labelFor_editFrm___cancel_sejour_1" class="">{{tr}}Yes{{/tr}}</label>
            <input type="radio" name="__cancel_sejour" value="0" checked="checked" onclick="$V(CancelAction.form._cancel_sejour, 0);">
            <label for="editFrm___cancel_sejour_0" id="labelFor_editFrm___cancel_sejour_0" class="">{{tr}}No{{/tr}}</label>
          </td>
        </tr>
      {{/if}}
    </tbody>

    <tr>
      <td colspan="4" class="button">
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        <button type="button" class="tick me-primary"   onclick="CancelAction.submit();">{{tr}}Validate{{/tr}}</button>
      </td>
    </tr>

  </table>
</div>
