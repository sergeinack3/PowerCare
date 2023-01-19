{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  PlageConsultSelector.init_multi = function(form_id) {
    this.sForm            = "editConsult_"+form_id;
    this.sHeure           = "heure";
    this.sPlageconsult_id = "plageconsult_id";
    this.sDate            = "plage_date_"+form_id;
    this.sChir_id         = "chir_id";
    this.sFunction_id     = "_function_id";
    this.sDatePlanning    = "plage_date_"+form_id;
    this.sConsultId       = "consultation_id";
    this.sLineElementId   = "_line_element_id";
    this.options          = {width: -30, height: -30};
    this.modal();
  };

  saveAll = function() {
    $$(".formEditConsult").invoke("onsubmit");
    Control.Modal.close();
  };
</script>

{{math assign="number" equation="a" a=$consults|@count}}
<table class="main">
  <tr>
    <th class="title" colspan="{{$number}}">Modification des consultations à venir pour {{$consult->_ref_patient->_view}}</th>
  </tr>
  <tr>
    {{foreach from=$consults item=_consult}}
      <td>
        <form name="editConsult_{{$_consult->_id}}" method="post" class="formEditConsult" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="dosql" value="do_consultation_aed"/>
          <input type="hidden" name="tab" value="{{$tab}}"/>
          <input type="hidden" name="m" value="{{$m}}"/>
          <input type="hidden" name="del" value="0" />
          {{mb_key object=$_consult}}
          <input type="hidden" name="patient_id" value="{{$_consult->patient_id}}"/>
          <table class="form">
            <tr>
              <th>Praticien</th>
              <td>
                <select name="chir_id">
                  {{foreach from=$praticiens item=_prat}}
                    <option value="{{$_prat->_id}}" {{if $_prat->_id == $_consult->_ref_praticien->_id}}selected="selected"{{/if}}>{{$_prat}}</option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <th>Date</th>
              <td>
                <input type="hidden" name="plageconsult_id" value="{{$_consult->plageconsult_id}}"/>
                <input type="text" readonly="readonly" name="plage_date_{{$_consult->_id}}" onclick="PlageConsultSelector.init_multi('{{$_consult->_id}}')" value='{{$_consult->_ref_plageconsult->date|date_format:$conf.longdate}}'/>
                à <input type="text" readonly="readonly" name="heure" value="{{$_consult->heure}}"/>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$_consult field=annule}}</th>
              <td>{{mb_field object=$_consult field=annule}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$_consult field=motif}}</th>
              <td>{{mb_field object=$_consult field=motif}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$_consult field=rques}}</th>
              <td>{{mb_field object=$_consult field=rques}}</td>
            </tr>
            <tr>
              <td colspan="2" class="button"><button type="submit" class="submitBtnEdit save button">{{tr}}Save{{/tr}}</button></td>
            </tr>
          </table>
        </form>
      </td>
    {{foreachelse}}
        <td class="empty">{{tr}}CConsultation.none{{/tr}}</td>
    {{/foreach}}
  </tr>
  <tr>
    <td class="button" colspan="{{$number}}">
      <button type="submit" class="save button" onclick="saveAll();">Tout {{tr}}Save{{/tr}} {{tr}}and{{/tr}} {{tr}}Close{{/tr}}</button>
      <button type="button" class="cancel button" onclick="Control.Modal.close();" title="{{tr}}Close_without_saving{{/tr}}">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>