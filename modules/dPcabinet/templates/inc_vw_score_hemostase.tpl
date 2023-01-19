{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=cabinet script=exam_hemostase ajax=1}}

<form name="editScoreHemostase" method="post"
      onsubmit="return onSubmitFormAjax(this, ExamHemostase.refreshScoreHemostase.curry('{{$consult_anesth->_id}}'));">
  {{mb_key   object=$score_hemostase}}
  {{mb_class object=$score_hemostase}}

  {{mb_field object=$score_hemostase field=consultation_anesth_id value=$consult_anesth->_id hidden=true}}

  <div style="height: 90%;">
    {{mb_field object=$score_hemostase field=coupure_minime typeEnum=checkbox onchange="ExamHemostase.updateScore(this.form);"}}
    {{mb_label object=$score_hemostase field=coupure_minime}}
    <br/>
    {{mb_field object=$score_hemostase field=soin_dentaire typeEnum=checkbox onchange="ExamHemostase.updateScore(this.form);"}}
    {{mb_label object=$score_hemostase field=soin_dentaire}}
    <br/>
    {{mb_field object=$score_hemostase field=apres_chirurgie typeEnum=checkbox onchange="ExamHemostase.updateScore(this.form);"}}
    {{mb_label object=$score_hemostase field=apres_chirurgie}}
    <br/>
    {{mb_field object=$score_hemostase field=hematomes_spontanes typeEnum=checkbox onchange="ExamHemostase.updateScore(this.form);"}}
    {{mb_label object=$score_hemostase field=hematomes_spontanes}}
    <br/>
    {{mb_field object=$score_hemostase field=hemostase_famille typeEnum=checkbox onchange="ExamHemostase.updateScore(this.form);"}}
    {{mb_label object=$score_hemostase field=hemostase_famille}}
    <br/>
    {{if $consult_anesth->_ref_consultation->_ref_patient->sexe === 'f'}}
      {{mb_field object=$score_hemostase field=apres_accouchement typeEnum=checkbox onchange="ExamHemostase.updateScore(this.form);"}}
      {{mb_label object=$score_hemostase field=apres_accouchement}}
      <br/>
      {{mb_field object=$score_hemostase field=menometrorragie typeEnum=checkbox onchange="ExamHemostase.updateScore(this.form);"}}
      {{mb_label object=$score_hemostase field=menometrorragie}}
      <br/>
    {{/if}}
  </div>

  <div style="text-align: right; padding: 5px;">
    <strong>
      {{tr}}common-Score{{/tr}}: <span id="result_score_hemostase">{{$score_hemostase->_score_hemostase}}</span>
    </strong>
  </div>
</form>
