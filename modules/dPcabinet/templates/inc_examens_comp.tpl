{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}CExamCom.fiche|pl{{/tr}}</legend>
  <form name="newExamen" action="?m=dPcabinet" method="get" class="not-printable">
    <select name="type_examen" onchange="ExamDialog.init(this.value)">
      <option value="">&mdash; {{tr}}CExamCom.choose_type{{/tr}}</option>
      {{if $consult->_is_anesth}}
        <option value="exam_possum">{{tr}}CExamPossum{{/tr}}</option>
        <option value="exam_nyha">{{tr}}CExamNyha-long{{/tr}}</option>
      {{else}}
        <option value="exam_audio">{{tr}}CExamAudio-long{{/tr}}</option>
      {{/if}}
    </select>
    <input type="hidden" name="consultation_id" value="{{$consult->_id}}" />
    {{if $consult->_is_anesth}}
      <input type="hidden" name="dossier_anesth_id" value="{{$dossier_anesth_id}}" />
    {{/if}}
    <script>
       ExamDialog.init = function(type_exam){
         this.sForm      = "newExamen";
         this.sConsultId = "consultation_id";
         {{if $consult->_is_anesth}}
           this.sDossierAnesthId = "dossier_anesth_id";
         {{/if}}
         this.pop(type_exam);
       }
    </script>
  </form>

  <ul>
    {{if !$consult->_count_fiches_examen}}
    <li class="empty">
      {{tr}}CExamComp.none_fiche{{/tr}}
    </li>
    {{/if}}
  	{{assign var=examaudio value=$consult->_ref_examaudio}}
    {{if $examaudio->_id}}
    <li>    
      <form name="Delete-{{$examaudio->_guid}}" action="?m=dPcabinet" enctype="multipart/form-data" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="dPcabinet" />
        <input type="hidden" name="dosql" value="do_exam_audio_aed" />
        <input type="hidden" name="del" value="1" />
        {{mb_key   object=$examaudio}}
        {{mb_field object=$examaudio field="_view" hidden=1}}
        <input type="hidden" name="_conduction" value="" />
        <input type="hidden" name="_oreille" value="" />
        <button class="trash notext me-tertiary" type="button" onclick="ExamDialog.remove(this,'{{$consult->_id}}')">{{tr}}Delete{{/tr}}</button>
      </form>
      <a href="#nothing" onclick="ExamDialog.init('exam_audio');">
        {{tr}}CExamAudio-long{{/tr}}
      </a>
    </li>
    {{/if}}
    {{if $consult->_ref_exampossum->_id}}
    <li>   
      <a href="#nothing" onclick="ExamDialog.init('exam_possum');">
        {{$consult->_ref_exampossum->_view}}
      </a>
    </li>
    {{/if}}
    {{if $consult->_ref_examnyha->_id}}
    <li>
      <a href="#nothing" onclick="ExamDialog.init('exam_nyha');">
        {{$consult->_ref_examnyha->_view}}
      </a>
    </li>
    {{/if}}
  </ul>
</fieldset>