{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=exam_gir ajax=1}}

<script>
  Main.add(function () {
    $('table_form_exam_gir').fixedTableHeaders();
    var oForm = getForm('editScoreGIR');
    Calendar.regField(oForm.date);
    var checkboxes_b = $$('.set-checkbox-b-container .set-checkbox')
    checkboxes_b.forEach(function (element) {
      element.onclick = function (element) {
        ExamGir.codageB(element.target);
      }
    })
    ExamGir.updateScoreGir();
  });
</script>
<form name="editScoreGIR" method="post" action="?"
      onsubmit="
        return onSubmitFormAjax(this, {
        onComplete: function() {
      {{if $digest}}
        window.urlScoresDigest.refreshModal();
      {{else}}
        refreshFiches('{{$sejour->_id}}');
      {{/if}}
        Control.Modal.close();
        }});">
  {{mb_key   object=$exam_gir}}
  {{mb_class object=$exam_gir}}
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="m" value="dPcabinet"/>
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}"/>
  <input type="hidden" name="creator_id" value="{{$user_id}}"/>
  <div id="table_form_exam_gir">
    <table class="tbl me-no-align">
      {{foreach from=$exam_gir|const:'VARIABLES_ACTIVITES' key=type item=variables_activites}}
        <tr>
          <th colspan="2" class="me-border-right me-border-top">
            {{tr}}CExamGir-{{$type}}-desc{{/tr}}
          </th>
          <th class="me-valign-top me-border-right me-border-top">
            <div class="me-padding-0 me-text-align-center error me-valign-top me-padding-top-8">{{tr}}CExamGir.c{{/tr}}</div>
          </th>
          <th rowspan="2" class="me-valign-top me-padding-top-6 me-text-align-center me-border-right me-border-top">
            <div class="error">{{tr}}CExamGir.b{{/tr}}</div>
            <span class="me-margin-5">{{tr}}CExamGir.s{{/tr}}</span>
            <span class="me-margin-5">{{tr}}CExamGir.t{{/tr}}</span>
            <span class="me-margin-5">{{tr}}CExamGir.c{{/tr}}</span>
            <span class="me-margin-5">{{tr}}CExamGir.h{{/tr}}</span>
          </th>
          <th class="me-valign-top me-text-align-center me-border-right me-border-top">
            <div class="me-padding-0 me-text-align-center error me-padding-top-8">{{tr}}CExamGir.a{{/tr}}</div>
          </th>
          {{if $type === 'discrim'}}
            <th class="me-text-align-center me-border-top">{{tr}}CExamGir-codage-intermediaire{{/tr}}</th>
            <th class="me-text-align-center me-border-top">{{tr}}CExamGir-codage-final{{/tr}}</th>
          {{else}}
            <th class="me-text-align-center me-border-top" colspan="2">{{tr}}CExamGir-codage-final{{/tr}}</th>
          {{/if}}
        </tr>
        {{foreach from=$variables_activites key=_var item=_ss_var name=variables_act}}
          <tbody class="hoverable">
          {{if !$_ss_var|@count}}
            <tr class="set-checkbox-container">
              <td>
                <div class="exam-gir-titre">
                  {{tr}}CExamGir-{{$_var}}{{/tr}}
                </div>
                <div class="exam-gir-desc">
                  <i>{{tr}}CExamGir-{{$_var}}-desc{{/tr}}</i>
                </div>
              </td>
              <td class="me-border-right"></td>
              <td class="narrow me-border-right">
                <input type="checkbox" class="set-checkbox-c" onchange="ExamGir.codageC(this)"
                       {{if $exam_gir->$_ss_variable === null }}checked{{/if}}>
              </td>
              <td class="me-border-right me-text-align-center">
                <fieldset class="fs_exam_gir me-no-bg no-sub-var me-padding-0 me-no-box-shadow set-checkbox-b-container"
                          name="{{$_var}}_fs">
                  {{mb_field object=$exam_gir field=$_var class="set-checkbox-b hidden-stch" typeEnum=checkbox onchange="ExamGir.updateScoreGir()"}}
                </fieldset>
              </td>
              <td class="narrow me-border-right">
                <input type="checkbox" class="set-checkbox-a" onchange="ExamGir.codageA(this)"
                       {{if $exam_gir->$_ss_variable === 's|t|c|h'}}checked{{/if}}>
              </td>
              <td class="narrow me-text-align-center" id="result_codage_{{$_var}}" colspan="2">
                <input type="text" style="border-color: #FF0000;" readonly class="me-text-align-center" size="1" id="codage_{{$_var}}"
                       name="codage_{{$_var}}">
              </td>
            </tr>
          {{else}}
            <tr class="set-checkbox-container">
              <td style="width: 40%;" rowspan="{{$_ss_var|@count}}">
                <div class="exam-gir-titre">
                  {{tr}}CExamGir-{{$_var}}{{/tr}}
                </div>
                <div class="exam-gir-desc">
                  <i>{{tr}}CExamGir-{{$_var}}-desc{{/tr}}</i>
                </div>
              </td>
              {{foreach from=$_ss_var key=_key item=_ss_variable name=_ss_variables}}
                {{if $smarty.foreach._ss_variables.iteration === '1'}}
                  <td class="me-border-right narrow">
                    {{tr}}CExamGir-{{$_ss_variable}}{{/tr}}
                  </td>
                  <td class="narrow me-border-right">
                    <input type="checkbox" class="set-checkbox-c" onchange="ExamGir.codageC(this)"
                           {{if $exam_gir->$_ss_variable === null }}checked{{/if}}>
                  </td>
                  <td class="me-text-align-center me-border-right" style="width: 12%">
                    <fieldset class="fs_exam_gir me-no-bg me-padding-0 me-no-box-shadow set-checkbox-b-container"
                              name="{{$_ss_variable}}_fs">
                      {{mb_field object=$exam_gir field=$_ss_variable class="hidden-stch" typeEnum=checkbox onchange="ExamGir.updateScoreGir();"}}
                    </fieldset>
                  </td>
                  <td class="narrow me-border-right">
                    <input type="checkbox" class="set-checkbox-a"
                           onchange="ExamGir.codageA(this)" {{if $exam_gir->$_ss_variable === 's|t|c|h'}}checked{{/if}}>
                  </td>
                  <td class="narrow ss_codage_{{$_var}}">
                    <input type="text" style="border-color: #0000FF;" readonly class="me-text-align-center ss_codage" size="1"
                           id="codage_{{$_ss_variable}}"
                           name="codage_{{$_ss_variable}}">
                  </td>
                  <td class="narrow" id="result_codage_{{$_var}}" rowspan="{{$_ss_var|@count}}">
                    <input type="text" style="border-color: #FF0000;" readonly class="me-text-align-center" size="1"
                           id="codage_{{$_var}}" name="codage_{{$_var}}">
                  </td>
                {{/if}}
              {{/foreach}}
            </tr>
            {{foreach from=$_ss_var key=_key item=_ss_variable name=_ss_variables}}
              {{if $smarty.foreach._ss_variables.iteration !== '1'}}
                <tr class="set-checkbox-container">
                  <td class="me-border-right">
                    {{tr}}CExamGir-{{$_ss_variable}}{{/tr}}
                  </td>
                  <td class="narrow me-border-right">
                    <input type="checkbox" class="set-checkbox-c" onchange="ExamGir.codageC(this)"
                           {{if $exam_gir->$_ss_variable === null }}checked{{/if}}>
                  </td>
                  <td class="me-text-align-center me-border-right" style="width: 12%">
                    <fieldset class="fs_exam_gir me-no-bg me-padding-0 me-no-box-shadow set-checkbox-b-container"
                              name="{{$_ss_variable}}_fs">
                      {{mb_field object=$exam_gir field=$_ss_variable class="hidden-stch" typeEnum=checkbox onchange="ExamGir.updateScoreGir()"}}
                    </fieldset>
                  </td>
                  <td class="narrow me-border-right">
                    <input type="checkbox" class="set-checkbox-a" onchange="ExamGir.codageA(this)"
                           {{if $exam_gir->$_ss_variable === 's|t|c|h'}}checked{{/if}}>
                  </td>
                  <td class="narrow ss_codage_{{$_var}}">
                    <input type="text" style="border-color: #0000FF;" readonly class="me-text-align-center ss_codage" size="1"
                           id="codage_{{$_ss_variable}}"
                           name="codage_{{$_ss_variable}}">
                  </td>
                </tr>
              {{/if}}
            {{/foreach}}
          {{/if}}
          </tbody>
        {{/foreach}}
      {{/foreach}}
      <tr>
        <td colspan="4" class="button">
          {{if $exam_gir->_id}}
            <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
            <button type="button" class="trash"
                    onclick="confirmDeletion(
                      this.form, {
                      ajax:true,
                      typeName: $T('CExamGir-thisGIR')
                      }, {
                      onComplete: function(){
                    {{if $digest}}
                      window.urlScoresDigest.refreshModal();
                    {{else}}
                      refreshFiches('{{$sejour->_id}}');
                    {{/if}}
                      Control.Modal.close();
                      }
                      })">
              {{tr}}Delete{{/tr}}
            </button>
          {{else}}
            <button type="submit" class="submit">{{tr}}Create{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
      <thead>
      <tr>
        <th class="title {{if $exam_gir->_id}}modify{{else}}me-th-new{{/if}}" colspan="7">
          {{if $exam_gir->_id}}
            {{mb_include module=system template=inc_object_history object=$exam_gir}}
            {{tr}}CExamGir-title-modify{{/tr}}
          {{else}}
            {{tr}}CExamGir-title-create{{/tr}}
          {{/if}}
        </th>
      </tr>
      <tr>
        <th class="category" colspan="">
          {{mb_label object=$exam_gir field="score_gir"}}
          {{mb_field object=$exam_gir field="score_gir" readonly="readonly" style="font-weight: bold; text-align: center; font-size: 1.2em;"}}
        </th>
        <th colspan="5">
          {{mb_label object=$exam_gir field=date form=editScoreGIR}}
          {{if $exam_gir->_id}}
            {{mb_value object=$exam_gir field=date form=editScoreGIR}}
          {{else}}
            {{mb_field object=$exam_gir field=date value=$date form=editScoreGIR register=true}}
          {{/if}}
        </th>
        <th class="narrow me-text-align-center">
          <span onmouseover="ObjectTooltip.createDOM(this, 'score_gir_legend')">
            <i class="me-icon me-primary help"></i>
          </span>
        </th>
      </tr>
      </thead>
    </table>
  </div>
</form>
<div>
  <table class="tbl me-no-hover" id="score_gir_legend" style="display: none;width: 200px;">
    <tr>
      <th class="title" colspan="3">{{tr}}CExamGir-help{{/tr}}</th>
    </tr>
    <tr>
      <th>
        <div class="me-text-align-center narrow error">A</div>
      </th>
      <td colspan="2">{{tr}}CExamGir-help-codage-a-desc{{/tr}}</td>
    </tr>
    <tr>
      <th class="me-no-border-bottom">
        <div class="me-text-align-center narrow error">B</div>
      </th>
      <td class="me-no-border-bottom" colspan="2">{{tr}}CExamGir-help-codage-b-desc{{/tr}}</td>
    </tr>
    <tr>
      <th rowspan="4"></th>
      <td class="me-no-border-bottom">
        <div class="me-text-align-center narrow error">S</div>
      </td>
      <td class="me-no-border-bottom" colspan="2">{{tr}}CExamGir-help-sscodage-s-desc{{/tr}}</td>
    </tr>
    <tr>
      <td class="me-no-border-bottom">
        <div class="me-text-align-center narrow error">T</div>
      </td>
      <td class="me-no-border-bottom" colspan="2">{{tr}}CExamGir-help-sscodage-t-desc{{/tr}}</td>
    </tr>
    <tr>
      <td class="me-no-border-bottom">
        <div class="me-text-align-center narrow error">C</div>
      </td>
      <td class="me-no-border-bottom" colspan="2">{{tr}}CExamGir-help-sscodage-c-desc{{/tr}}</td>
    </tr>
    <tr>
      <td>
        <div class="me-text-align-center narrow error">H</div>
      </td>
      <td colspan="2">{{tr}}CExamGir-help-sscodage-h-desc{{/tr}}</td>
    </tr>
    <tr>
      <th>
        <div class="me-text-align-center narrow error">C</div>
      </th>
      <td colspan="2">{{tr}}CExamGir-help-codage-c-desc{{/tr}}</td>
    </tr>
  </table>
</div>
