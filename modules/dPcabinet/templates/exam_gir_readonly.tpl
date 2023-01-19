{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editScoreGIR" method="post" action="?">
  <div id="table_form_exam_gir">
    <table class="tbl me-no-align">
      {{foreach from=$exam_gir|const:'VARIABLES_ACTIVITES' key=type item=variables_activites}}
        <tr>
          <th colspan="2" class="me-border-right me-border-top">
            {{tr}}CExamGir-{{$type}}-desc{{/tr}}
          </th>
          <th class="me-valign-top me-border-right me-border-top">
            <div
              class="me-padding-0 me-text-align-center error me-valign-top me-padding-top-8">{{tr}}CExamGir.c{{/tr}}</div>
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
              <td class="text">
                <div class="exam-gir-titre">
                  {{tr}}CExamGir-{{$_var}}{{/tr}}
                </div>
                <div class="exam-gir-desc">
                  <i>{{tr}}CExamGir-{{$_var}}-desc{{/tr}}</i>
                </div>
              </td>
              <td class="me-border-right"></td>
              <td class="narrow me-border-right">
                <input type="checkbox" class="set-checkbox-c"
                       {{if $exam_gir->$_var === null }}checked{{/if}} disabled="disabled">
              </td>
              <td class="me-border-right me-text-align-center">
                <fieldset class="fs_exam_gir me-no-bg no-sub-var me-padding-0 me-no-box-shadow set-checkbox-b-container"
                          name="{{$_var}}_fs">
                  {{assign var=codes_b value="|"|explode:$exam_gir->$_var}}

                  <label>
                    <input type="checkbox" name="_{{$_var}}_s" value="s"
                           class="set-checkbox" disabled=""
                           {{if in_array("s", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                  </label>
                  <label>
                    <input type="checkbox" name="_{{$_var}}_t" value="t"
                           class="set-checkbox" disabled=""
                           {{if in_array("t", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                  </label>
                  <label>
                    <input type="checkbox" name="_{{$_var}}_c" value="c"
                           class="set-checkbox" disabled=""
                           {{if in_array("c", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                  </label>
                  <label>
                    <input type="checkbox" name="_{{$_var}}_h" value="h"
                           class="set-checkbox" disabled=""
                           {{if in_array("h", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                  </label>
                </fieldset>
              </td>
              <td class="narrow me-border-right">
                <input type="checkbox" class="set-checkbox-a"
                       {{if $exam_gir->$_var === 's|t|c|h'}}checked="checked"{{/if}} disabled="disabled">
              </td>
              <td class="narrow me-text-align-center" id="result_codage_{{$_var}}" colspan="2">
                {{assign var=code_field value="_final_code_$_var"}}
                <input type="text" style="border-color: #FF0000;" readonly class="me-text-align-center" size="1"
                       id="codage_{{$_var}}"
                       name="codage_{{$_var}}" value="{{$exam_gir->$code_field}}">
              </td>
            </tr>
          {{else}}
            <tr class="set-checkbox-container">
              <td class="text" style="width: 40%;" rowspan="{{$_ss_var|@count}}">
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
                    <input type="checkbox" class="set-checkbox-c"
                           {{if $exam_gir->$_ss_variable === null }}checked{{/if}} disabled="disabled">
                  </td>
                  <td class="me-text-align-center me-border-right" style="width: 12%">
                    <fieldset class="fs_exam_gir me-no-bg me-padding-0 me-no-box-shadow set-checkbox-b-container"
                              name="{{$_ss_variable}}_fs">
                      {{assign var=codes_b value="|"|explode:$exam_gir->$_ss_variable}}

                      <label>
                        <input type="checkbox" name="_{{$_ss_variable}}_s" value="s"
                               class="set-checkbox" disabled=""
                               {{if in_array("s", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                      </label>
                      <label>
                        <input type="checkbox" name="_{{$_ss_variable}}_t" value="t"
                               class="set-checkbox" disabled=""
                               {{if in_array("t", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                      </label>
                      <label>
                        <input type="checkbox" name="_{{$_ss_variable}}_c" value="c"
                               class="set-checkbox" disabled=""
                               {{if in_array("c", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                      </label>
                      <label>
                        <input type="checkbox" name="_{{$_ss_variable}}_h" value="h"
                               class="set-checkbox" disabled=""
                               {{if in_array("h", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                      </label>
                    </fieldset>
                  </td>
                  <td class="narrow me-border-right">
                    <input type="checkbox" class="set-checkbox-a"
                           {{if $exam_gir->$_ss_variable === 's|t|c|h'}}checked="checked"{{/if}}
                      disabled="disabled">
                  </td>
                  <td class="narrow ss_codage_{{$_var}}">
                    {{assign var=code_field value="_code_$_ss_variable"}}
                    <input type="text" style="border-color: #0000FF;" readonly class="me-text-align-center ss_codage"
                           size="1"
                           id="codage_{{$_ss_variable}}"
                           name="codage_{{$_ss_variable}}" value="{{$exam_gir->$code_field}}">
                  </td>
                  <td class="narrow" id="result_codage_{{$_var}}" rowspan="{{$_ss_var|@count}}">
                    {{assign var=code_field value="_final_code_$_var"}}
                    <input type="text" style="border-color: #FF0000;" readonly class="me-text-align-center" size="1"
                           id="codage_{{$_var}}" name="codage_{{$_var}}" value="{{$exam_gir->$code_field}}">
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
                    <input type="checkbox" class="set-checkbox-c"
                           {{if $exam_gir->$_ss_variable === null }}checked{{/if}} disabled="disabled">
                  </td>
                  <td class="me-text-align-center me-border-right" style="width: 12%">
                    <fieldset class="fs_exam_gir me-no-bg me-padding-0 me-no-box-shadow set-checkbox-b-container"
                              name="{{$_ss_variable}}_fs">

                      {{assign var=codes_b value="|"|explode:$exam_gir->$_ss_variable}}

                      <label>
                        <input type="checkbox" name="_{{$_ss_variable}}_s" value="s"
                               class="set-checkbox" disabled=""
                               {{if in_array("s", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                      </label>
                      <label>
                        <input type="checkbox" name="_{{$_ss_variable}}_t" value="t"
                               class="set-checkbox" disabled=""
                               {{if in_array("t", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                      </label>
                      <label>
                        <input type="checkbox" name="_{{$_ss_variable}}_c" value="c"
                               class="set-checkbox" disabled=""
                               {{if in_array("c", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                      </label>
                      <label>
                        <input type="checkbox" name="_{{$_ss_variable}}_h" value="h"
                               class="set-checkbox" disabled=""
                               {{if in_array("h", $codes_b) && $codes_b|@count < 4}}checked="checked"{{/if}}>
                      </label>
                    </fieldset>
                  </td>
                  <td class="narrow me-border-right">
                    <input type="checkbox" class="set-checkbox-a"
                           {{if $exam_gir->$_ss_variable === 's|t|c|h'}}checked="checked"{{/if}}
                      disabled="disabled">
                  </td>
                  <td class="narrow ss_codage_{{$_var}}">
                    {{assign var=code_field value="_code_$_ss_variable"}}
                    <input type="text" style="border-color: #0000FF;" readonly class="me-text-align-center ss_codage"
                           size="1"
                           id="codage_{{$_ss_variable}}"
                           name="codage_{{$_ss_variable}}" value="{{$exam_gir->$code_field}}">
                  </td>
                </tr>
              {{/if}}
            {{/foreach}}
          {{/if}}
          </tbody>
        {{/foreach}}
      {{/foreach}}
      <thead>
      <tr>
        <th class="category" colspan="">
          {{mb_label object=$exam_gir field="score_gir"}}
          {{mb_field object=$exam_gir field="score_gir" readonly=true style="font-weight: bold; text-align: center; font-size: 1.2em;"}}
        </th>
        <th colspan="6">
          {{mb_label object=$exam_gir field=date form=editScoreGIR}}
          {{mb_value object=$exam_gir field=date form=editScoreGIR readonly=true}}
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
