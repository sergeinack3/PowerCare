{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="11" class="title">{{tr}}CExamComp.all{{/tr}}</th>
  </tr>
  <tr>
    <th>{{mb_label class=CConsultation field=patient_id}}</th>
    <th class="narrow">{{mb_label class=CPatient field=naissance}}</th>
    <th>{{mb_label class=CPatient field=tel}}</th>
    <th>{{mb_label class=CPlageconsult field=chir_id}} de la consultation</th>
    <th>{{mb_label class=CSejour field=praticien_id}} du séjour</th>
    <th>{{mb_label class=CSejour field=entree}} du séjour</th>
    <th>{{tr}}Examen{{/tr}}</th>
    <th>{{mb_label class=CExamComp field=date_bilan}}</th>
    <th>{{mb_label class=CExamComp field=labo}}</th>
    <th>{{tr}}common-Results{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
    {{foreach from=$examens item=_examen}}
        {{assign var=consult value=$_examen->_ref_consult}}
        {{if $consult->patient_id}}
            {{assign var=patient value=$consult->_ref_patient}}
        {{else}}
            {{assign var=patient value=$consult->_ref_sejour->_ref_patient}}
        {{/if}}
        {{assign var=consult_anesth value=$consult->_ref_consult_anesth}}
        {{assign var=consult_anesth_id value=$consult_anesth->_id}}
        {{assign var=examen_guid value=$_examen->_id}}
      <tr>
        <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient}}
        </span>
        </td>
        <td>{{mb_value object=$patient field=naissance}}</td>
        <td>{{mb_value object=$patient field=tel}}</td>
        <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult->_ref_plageconsult->_ref_chir}}</td>
        <td>
            {{if $consult_anesth->sejour_id}}
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult_anesth->_ref_sejour->_ref_executant}}
            {{elseif $consult->sejour_id}}
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult->_ref_sejour->_ref_executant}}
            {{/if}}
        </td>
        <td>{{mb_value object=$consult_anesth->_ref_sejour field=entree}}</td>
        <td>
            {{mb_value object=$_examen field=examen}}
        </td>
        <td>
            {{if $_examen->fait}}
                {{mb_value object=$_examen field=date_bilan}}
            {{else}}
              <form name="date_bilan-{{$examen_guid}}" action="?" method="post">
                  {{mb_key   object=$_examen}}
                  {{mb_class object=$_examen}}
                  {{mb_field object=$_examen field=date_bilan form="date_bilan-$examen_guid" register=true onchange="return onSubmitFormAjax(this.form);"}}
              </form>
            {{/if}}
        </td>
        <td>
            {{if $_examen->fait}}
                {{mb_value object=$_examen field=labo}}
            {{else}}
              <form name="labo-{{$examen_guid}}" action="?" method="post">
                  {{mb_key   object=$_examen}}
                  {{mb_class object=$_examen}}
                  {{mb_field object=$_examen field="labo" rows="1" form="labo-$examen_guid" aidesaisie="validateOnBlur: 0" onchange="return onSubmitFormAjax(this.form);"}}
              </form>
            {{/if}}
        </td>
        <td>
            {{if $consult_anesth_id}}
              <form name="editExamCompFrm_{{$consult_anesth_id}}" method="post">
                  {{mb_key   object=$consult_anesth}}
                  {{mb_class object=$consult_anesth}}
                  {{mb_field object=$consult_anesth field="result_autre" rows="4" onblur="return onSubmitFormAjax(this.form);" form="editExamCompFrm_$consult_anesth_id" aidesaisie="validateOnBlur: 0"}}
              </form>
            {{/if}}
        </td>
        <td>
          
          <form name="edit-{{$_examen->_guid}}" action="?m=dPcabinet" method="post"
                onsubmit="return onSubmitFormAjax(this, { onComplete: function() { searchExams(getForm('filters-exams_comp')); } } );">
            
            <input type="hidden" name="m" value="dPcabinet"/>
            <input type="hidden" name="del" value="0"/>
            <input type="hidden" name="dosql" value="do_examcomp_aed"/>
              {{mb_key object=$_examen}}
              {{mb_field object=$_examen field=fait hidden=1}}

              {{if !$_examen->fait}}
                <button class="tick" type="button"
                        onclick="$V(this.form.fait, '1'); this.form.onsubmit();">{{tr}}Done{{/tr}}</button>
              {{else}}
                <button class="cancel notext" type="button"
                        onclick="$V(this.form.fait, '0'); this.form.onsubmit();">{{tr}}Cancel{{/tr}}</button>
              {{/if}}
          </form>
        </td>
      </tr>
        {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CExamComp.none{{/tr}}</td>
      </tr>
    {{/foreach}}
</table>
