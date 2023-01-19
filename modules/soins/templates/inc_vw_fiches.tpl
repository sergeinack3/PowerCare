{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=score_id}}

<script>
  openScoreIGS = function (igs_id, date) {
    var url = new Url("dPcabinet", "exam_igs");
    url.addParam("sejour_id", "{{$sejour->_id}}");
    url.addParam("exam_igs_id", igs_id);
    url.addParam('digest', '{{$digest}}');
    if (date) {
      url.addParam("date", date);
    }
    url.requestModal();
  };

  openChungScore = function (id) {
    var url = new Url('soins', 'ajax_edit_score_chung');
    url.addParam('sejour_id', '{{$sejour->_id}}');
    url.addParam('digest', '{{$digest}}');
    if (id) {
      url.addParam('chung_score_id', id);
    }
    url.requestModal();
  };

  openExamGir = function (gir_id, user_id) {
    var url = new Url('dPcabinet', 'ajax_edit_exam_gir')
      .addParam('sejour_id', '{{$sejour->_id}}')
      .addParam('exam_gir_id', gir_id)
      .addParam('creator_id', user_id)
      .addParam('digest', '{{$digest}}')
      .requestModal('750px', '100%');
  };

  Main.add(function () {
    var tabs = Control.Tabs.create('{{$score_id}}_tab-fiches');
    tabs.setActiveTab('{{$score_id}}_{{$selected_tab}}');
  });
</script>

<ul id="{{$score_id}}_tab-fiches" class="control_tabs small">
  <li><a href="#{{$score_id}}_chung_score">{{tr}}CChungScore{{/tr}}</a></li>
  <li><a href="#{{$score_id}}_score_igs">{{tr}}CExamIgs{{/tr}}</a></li>
  <li><a href="#{{$score_id}}_score_gir">{{tr}}CExamGir{{/tr}}</a></li>
</ul>

<table class="tbl me-no-align" id="{{$score_id}}_score_igs" style="display: none;">
  <tr>
    <th class="title" colspan="19">
      <button id="btn_new_igs_score" type="button" style="float: right" class="add" onclick="openScoreIGS()">
        {{tr}}CExamIgs-open-score-igs{{/tr}}
      </button>
      {{tr}}CExamIgs{{/tr}}
    </th>
  </tr>

  <tr>
    <th style="font-weight: bold; text-align: center;" class="text">{{mb_label class="CExamIgs" field="scoreIGS"}}</th>
    <th style="text-align: center;" class="text">{{mb_label class="CExamIgs" field=simplified_igs}}</th>
    <th class="text">Date</th>
    {{foreach from='Ox\Mediboard\Cabinet\CExamIGS'|static:fields item=_field}}
      <th class="text">{{mb_label class="CExamIgs" field=$_field}}</th>
    {{/foreach}}
    <th class="narrow"></th>
  </tr>

  {{foreach from=$sejour->_ref_exams_igs item=_igs}}
    <tr>
      <td style="font-weight: bold; font-size: 1.3em; text-align: center;">
        {{mb_value object=$_igs field="scoreIGS"}}
      </td>
      <td style="font-size: 1.2em; text-align: center;">
        {{mb_value object=$_igs field=simplified_igs}}
      </td>
      <td class="text" style="text-align: center;">
        {{mb_value object=$_igs field=date}}
      </td>
      {{foreach from='Ox\Mediboard\Cabinet\CExamIGS'|static:fields item=_field}}
        <td class="text {{if $_igs->$_field == ''}}empty{{/if}}"
            style="text-align: center;">{{mb_value object=$_igs field=$_field}}</td>
      {{/foreach}}
      <td>
        <button type="button" class="edit notext" onclick="openScoreIGS('{{$_igs->_id}}')"></button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="18">
        {{tr}}CExamIgs.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>

<table id="{{$score_id}}_chung_score" style="display: none;" class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th class="title" colspan="8">
      <button id="btn_new_chung_score" type="button" style="float: right" class="add" onclick="openChungScore()">
        {{tr}}CChungScore-action-add{{/tr}}
      </button>
      {{tr}}CChungScore{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="">{{mb_title class=CChungScore field="total"}}</th>
    {{foreach from='Ox\Mediboard\Soins\CChungScore'|static:fields item=_field}}
      <th class="">{{mb_title class=CChungScore field=$_field}}</th>
    {{/foreach}}
    <th class=" narrow"></th>
  </tr>

  {{foreach from=$sejour->_ref_chung_scores item=_chung_score}}
    <tr>
      <td style="font-weight: bold; font-size: 1.3em; text-align: center;">{{mb_value object=$_chung_score field="total"}}</td>
      {{foreach from='Ox\Mediboard\Soins\CChungScore'|static:fields item=_field}}
        <td style="text-align: center;" class="me-text-align-left me-ws-wrap">{{mb_value object=$_chung_score field=$_field}}</td>
      {{/foreach}}
      <td>
        <button class="edit notext" type="button" onclick="openChungScore('{{$_chung_score->_id}}')">{{tr}}Edit{{/tr}}</button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" style="text-align: center;" colspan="8">
        {{tr}}CChungScore.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
{{assign var=current_user value='Ox\Mediboard\Mediusers\CMediusers::get'|static_call:null}}
<table id="{{$score_id}}_score_gir" class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th class="title" colspan="18">
      <button id="btn_new_exam_gir" type="button" style="float: right" class="add" onclick="openExamGir(0,{{$current_user->_id}})">
        {{tr}}CExamGir-action-add{{/tr}}
      </button>
      {{tr}}CExamGir{{/tr}}
    </th>
  </tr>
  <tr>
    <th style="font-weight: bold; font-size: 1.3em; text-align: center;"
        class="text">
      {{mb_label class="CExamGir" field="score_gir"}}
    </th>
    <th class="text me-text-align-center">
      {{mb_label class="CExamGir" field="date"}}
    </th>
    <th class=" narrow"></th>
  </tr>
  {{foreach from=$sejour->_ref_exams_gir item=_exam_gir}}
    <tr>
      <td class="me-text-align-center">
        {{mb_value object=$_exam_gir field=score_gir}}
      </td>
      <td class="me-text-align-center">{{mb_value object=$_exam_gir field=date}}</td>
      <td>
        {{if $_exam_gir->creator_id === $current_user->_id}}
          <button class="edit notext" type="button"
                  onclick="openExamGir({{$_exam_gir->_id}},{{$current_user->_id}})">{{tr}}Edit{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="me-text-align-center" colspan="3">
        {{tr}}CExamGir.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
