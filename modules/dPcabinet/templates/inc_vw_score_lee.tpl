{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=cabinet script=exam_lee ajax=1}}

<form name="editScoreLee" method="post"
      onsubmit="return onSubmitFormAjax(this, ExamLee.refreshScoreLee.curry('{{$consult_anesth->_id}}'));">
  {{mb_key   object=$score_lee}}
  {{mb_class object=$score_lee}}

  {{mb_field object=$score_lee field=consultation_anesth_id value=$consult_anesth->_id hidden=true}}

  <div style="height: 90%;">
    {{mb_field object=$score_lee field=chirurgie_risque typeEnum=checkbox onchange="ExamLee.updateScore(this.form);"}}
    {{mb_label object=$score_lee field=chirurgie_risque}} <br/>
    {{mb_field object=$score_lee field=coronaropathie typeEnum=checkbox onchange="ExamLee.updateScore(this.form);"}}
    {{mb_label object=$score_lee field=coronaropathie}} <br/>
    {{mb_field object=$score_lee field=insuffisance_cardiaque typeEnum=checkbox onchange="ExamLee.updateScore(this.form);"}}
    {{mb_label object=$score_lee field=insuffisance_cardiaque}} <br/>
    {{mb_field object=$score_lee field=antecedent_avc typeEnum=checkbox onchange="ExamLee.updateScore(this.form);"}}
    {{mb_label object=$score_lee field=antecedent_avc}} <br/>
    {{mb_field object=$score_lee field=diabete typeEnum=checkbox onchange="ExamLee.updateScore(this.form);"}}
    {{mb_label object=$score_lee field=diabete}} <br/>
    {{mb_field object=$score_lee field=clairance_creatinine typeEnum=checkbox onchange="ExamLee.updateScore(this.form);"}}
    {{mb_label object=$score_lee field=clairance_creatinine}} <br/>
  </div>

  <div style="text-align: right; padding: 5px;">
    <strong>
      {{tr}}common-Score{{/tr}}: <span id="resutl_score_lee">{{$score_lee->_score_lee}}</span>
    </strong>
  </div>
</form>
