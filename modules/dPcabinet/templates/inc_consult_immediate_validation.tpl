{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=consultations value=false}}

{{if $consultations && $consultations|@count}}
  <div style="text-align:center; white-space: initial" class="small-warning">
    {{tr var1=$selected_praticien var2=$selected_date|date_format:$conf.date}}CConsultation.There are similar consultations{{/tr}}
    <hr>
    {{foreach from=$consultations item=_consultation}}
      {{mb_ternary var=icon_class test=$_consultation->_ref_consult_anesth->_id
      value="fa-eye-dropper " other="fa-stethoscope"}}
      <div>
        <i class="fa {{$icon_class}} event-icon" style="background-color: steelblue;" title="{{tr}}CConsultation{{/tr}}"></i>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consultation->_guid}}');">
        {{$_consultation}}
      </span>
        <button type="button" class="right notext"
                onclick="Control.Modal.close(); Consultation.editModal('{{$_consultation->_id}}', null, '')">
          {{tr}}CConsultation{{/tr}}
        </button>
      </div>
    {{/foreach}}
  </div>
  <button type="button" class="new"
          onclick="if (confirm($T('CConsultation.Create a new consultation'))) { this.form.onsubmit();}">
    {{tr}}CConsultation-action-Consult{{/tr}}
  </button>
{{else}}
  <button type="submit" class="new">{{tr}}CConsultation-action-Consult{{/tr}}</button>
{{/if}}
