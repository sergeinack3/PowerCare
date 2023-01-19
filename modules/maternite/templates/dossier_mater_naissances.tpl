{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=naissance ajax=1}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=patient value=$grossesse->_ref_parturiente}}

{{if !$dossier->admission_id}}
  {{mb_include module=maternite template=inc_dossier_mater_admission_choix_sejour}}

  {{mb_return}}
{{/if}}

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<table class="tbl">
  <tr>
    <th class="title me-no-title me-category" colspan="5">
      <button type="button" class="add not-printable" style="float: left;" {{if !$grossesse->active}}disabled{{/if}}
              onclick="Naissance.edit(0, null, '{{$dossier->admission_id}}')">{{tr}}CNaissance{{/tr}}</button>

      <form name="closeGrossesse" method="post"
            onsubmit="return onSubmitFormAjax(this, DossierMater.refresh);">
        {{mb_class object=$grossesse}}
        {{mb_key   object=$grossesse}}
        {{if $grossesse->active}}
          <input type="hidden" name="active" value="0" />
          <button type="button" class="tick me-primary" onclick="Modal.open('date_cloture', {width: 300, showClose: true} );"
                  style="float: right;">{{tr}}CGrossesse-stop_grossesse{{/tr}}</button>
          <div id="date_cloture" style="display: none;">
            <table class="form">
              <tr>
                <th class="title">{{mb_title object=$grossesse field=datetime_cloture}}</th>
              </tr>
              <tr>
                <td class="button">{{mb_field object=$grossesse field=datetime_cloture register=true
                  form="closeGrossesse" style="width: 10em;" class="notNull"}}</td>
              </tr>
              <tr>
                <td class="button">
                  <button type="button" class="edit" onclick="Control.Modal.close(); this.form.onsubmit();">{{tr}}Edit{{/tr}}</button>
                </td>
              </tr>
            </table>
          </div>
        {{else}}
          <input type="hidden" name="active" value="1" />
          <input type="hidden" name="datetime_cloture" value="" />
          <button type="button" class="cancel"
                  title="{{tr}}CGrossesse-datetime_cloture{{/tr}} {{tr var1=$grossesse->datetime_cloture|date_format:$conf.date var2=$grossesse->datetime_cloture|date_format:$conf.time}}common-the %s at %s{{/tr}}"
                  onclick="this.form.onsubmit()"
                  style="float: right;">{{tr}}CGrossesse-reactive_grossesse{{/tr}}</button>
        {{/if}}
      </form>

      {{tr}}CNaissance{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="category narrow"></th>
    <th class="category">{{mb_label class=CNaissance field=rang}} / {{mb_label class=CNaissance field=date_time}}</th>
    <th class="category">{{tr}}CPatient{{/tr}}</th>
    <th class="category">{{tr}}CSejour{{/tr}}</th>
  </tr>
  {{foreach from=$grossesse->_ref_naissances item=_naissance}}
    {{assign var=sejour_enfant value=$_naissance->_ref_sejour_enfant}}
    {{assign var=enfant value=$sejour_enfant->_ref_patient}}
    <tr>
      <td>
        <button type="button" class="edit notext not-printable"
                onclick="Naissance.edit('{{$_naissance->_id}}')">{{tr}}Edit{{/tr}}</button>
        <button type="button" class="edit not-printable"
                title="{{tr}}CNaissance-File of the newborn in the delivery room{{/tr}}"
                onclick="Naissance.editSalleNaissance('{{$_naissance->_id}}');">
          {{tr}}CAntecedent-dossier_medical_id-court{{/tr}}
        </button>
        <button type="button" class="trash notext not-printable"
                onclick="Naissance.cancelNaissance('{{$_naissance->_id}}');">{{tr}}Delete{{/tr}}</button>
      </td>
      <td>
        {{if $_naissance->date_time}}
          Le {{$_naissance->date_time|date_format:$conf.date}} à {{$_naissance->date_time|date_format:$conf.time}}
        {{else}}
          {{$_naissance}}
        {{/if}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$enfant->_guid}}')">{{$enfant}}</span>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour_enfant->_guid}}')">{{$sejour_enfant->_shortview}}</span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}CNaissance.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>

