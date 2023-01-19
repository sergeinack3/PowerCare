{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=cabinet script=exam_met ajax=1}}

<form name="editScoreMet" method="post"
      onsubmit="return onSubmitFormAjax(this, ExamMet.refreshScoreMet.curry('{{$consult_anesth->_id}}'));">
  {{mb_key   object=$score_met}}
  {{mb_class object=$score_met}}

  {{mb_field object=$score_met field=consultation_anesth_id value=$consult_anesth->_id hidden=true}}

  <div style="height: 90%;">
    {{foreach from=$score_met->_specs.aptitude_physique->_list item=_aptitude}}
      <label>
        <input type="radio" name="aptitude_physique" {{if $_aptitude == $score_met->aptitude_physique}}checked{{/if}}
               value="{{$_aptitude}}" onchange="ExamMet.updateScore(this);">
        {{tr}}CExamMet.aptitude_physique.{{$_aptitude}}{{/tr}}
      </label>
      <br/>
    {{/foreach}}
  </div>

  <div style="text-align: right; padding: 5px;">
    <strong>
      {{tr}}common-Score{{/tr}}: <span id="resutl_score_met">{{$score_met->_score_met}}</span>
    </strong>
  </div>
</form>
