{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=ExamAudio ajax=1}}

{{assign var=frequences value='Ox\Mediboard\Cabinet\CExamAudio'|static:frequences}}
{{assign var=pressions value='Ox\Mediboard\Cabinet\CExamAudio'|static:pressions}}

{{math equation="x + 1" x='Ox\Mediboard\Cabinet\CExamAudio'|static:"frequences"|@count assign=colspan}}

<script>
  {{if !$exam_audio->_can->edit}}
    App.readonly = true;
  {{/if}}

  Main.add(function() {
    new PairEffect("dataTonal");
    new PairEffect("dataVocal");
  });

  window.onunload = function () {
    window.opener.ExamDialog.reload("{{$exam_audio->_ref_consult->_id}}");
  };

</script>

<form name="editFrm" id="editFrm" action="?m=cabinet&a=exam_audio&dialog=1" method="post">
  <input type="hidden" name="m" value="cabinet"/>
  <input type="hidden" name="dosql" value="do_exam_audio_aed"/>
  <input type="hidden" name="del" value="0"/>
  {{mb_key object=$exam_audio}}
  {{mb_field object=$exam_audio field=consultation_id hidden=1}}

  <table class="main" id="weber">
    <tr>
      <th id="title_exam_audio" class="title modify not-printable" colspan="2">
        {{assign var=consultation value=$exam_audio->_ref_consult}}
        Consultation de {{$consultation->_ref_patient}}
        le {{$consultation->_date|date_format:$conf.longdate}}
        par {{if $consultation->_ref_chir->isPraticien()}}le Dr{{/if}} {{$consultation->_ref_chir}}
      </th>
    </tr>
    <tr>
      <th id="title_print" class="title modify" style="display:none" colspan="2">
          {{assign var=consultation value=$exam_audio->_ref_consult}}
        Consultation du {{$consultation->_date|date_format:$conf.longdate}},
        de {{$consultation->_ref_patient}}
        né(e) le {{$consultation->_ref_patient->naissance|date_format:$conf.date}},
        par {{if $consultation->_ref_chir->isPraticien()}}le Dr{{/if}} {{$consultation->_ref_chir}}
      </th>
    </tr>
    <tr>
      <td style="text-align: right">
        <select name="consultation"  class="not-printable" onchange="ExamAudio.addOldExamaudio(this.value, '{{$consultation_id}}', '{{$_conduction}}', '{{$_oreille}}')">
          <option value="">{{tr}}Choose{{/tr}} {{tr}}common-old|f{{/tr}} {{tr}}CConsultation{{/tr}}</option>
          {{foreach from=$consultations_anciennes item=_consultation}}
            <option value="{{$_consultation->_id}}" {{if $old_consultation_id == $_consultation->_id}} selected="selected" {{/if}}>
              {{tr}}CConsultation{{/tr}} : {{$_consultation->_date|date_format:$conf.longdate}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th class="title" colspan="2">{{tr}}dPcabinet-audiometrie-tonale{{/tr}} ({{tr}}dPcabinet-test-weber{{/tr}})</th>
    </tr>

    <tr>
      <td>
        <table class="main layout">
          <tr>
            <td>
              {{if $old_consultation_id}}
                {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_audiometrie_tonale graph=$graphs.audiometrie_tonale.droite graph_old=$graphs_old.audiometrie_tonale.droite legend_container="audiometrie-tonale-legende"}}
              {{else}}
                {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_audiometrie_tonale graph=$graphs.audiometrie_tonale.droite legend_container="audiometrie-tonale-legende"}}
              {{/if}}
            </td>
            <td id="audiometrie-tonale-legende"></td>
            <td>
              {{if $old_consultation_id}}
                {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_audiometrie_tonale graph=$graphs.audiometrie_tonale.gauche graph_old=$graphs_old.audiometrie_tonale.gauche }}
              {{else}}
                {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_audiometrie_tonale graph=$graphs.audiometrie_tonale.gauche}}
              {{/if}}
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td class="radiointeractive" colspan="2">
        <input type="radio" name="_conduction" value="aerien" {{if $_conduction == "aerien"}}checked{{/if}} />
        <label for="_conduction_aerien" title="Conduction aérienne pour la saisie intéractive">{{tr}}dPcabinet-conduction-aerienne{{/tr}}</label>
        <input type="radio" name="_conduction" value="osseux" {{if $_conduction == "osseux"}}checked{{/if}} />
        <label for="_conduction_osseux" title="Conduction osseuse pour la saisie intéractive">{{tr}}dPcabinet-conduction-osseuse{{/tr}}</label>
        <input type="radio" name="_conduction" value="ipslat" {{if $_conduction == "ipslat"}}checked{{/if}} />
        <label for="_conduction_ipslat" title="Stapédien ipsilatéral pour la saisie intéractive">{{tr}}dPcabinet-stapedien-ipsilateral{{/tr}}</label>
        <input type="radio" name="_conduction" value="conlat" {{if $_conduction == "conlat"}}checked{{/if}} />
        <label for="_conduction_conlat" title="Stapédien controlatéral pour la saisie intéractive">{{tr}}dPcabinet-stapedien-controlateral{{/tr}}</label>
        <input type="radio" name="_conduction" value="osseux_pasrep" {{if $_conduction == "osseux_pasrep"}}checked{{/if}} />
        <label for="_conduction_osseux_pasrep" title="Pas de réponse pour la saisie intéractive">{{tr}}dPcabinet-no-response-osseux{{/tr}}</label>
        <input type="radio" name="_conduction" value="aerien_pasrep" {{if $_conduction == "aerien_pasrep"}}checked{{/if}} />
        <label for="_conduction_aerien_pasrep" title="Pas de réponse pour la saisie intéractive">{{tr}}dPcabinet-no-response-aerien{{/tr}}</label>

      </td>
    </tr>
    <tr>
      <td colspan="2">
        <table class="form" id="allvalues">
          <tr id="dataTonal-trigger">
            <th class="category" colspan="{{$colspan}}">Toutes les valeurs</th>
          </tr>
          <tbody id="dataTonal">
          <tr>
            <th class="category" colspan="{{$colspan}}">{{tr}}common-Ear{{/tr}} {{tr}}common-Right{{/tr}}</th>
          </tr>
          <tr>
            <th>{{tr}}dPcabinet-conduction-aerienne{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td>
                <input type="text" name="_droite_aerien[{{$index}}]" class="num min|-10 max|120"
                       value="{{$exam_audio->_droite_aerien.$index}}" tabindex="{{$index+110}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-conduction-aerienne{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="old_droite_aerien[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_droite_aerien.$index}}" tabindex="{{$index+110}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-conduction-osseuse{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td><input type="text" name="_droite_osseux[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_droite_osseux.$index}}" tabindex="{{$index+120}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-conduction-osseuse{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="old_droite_osseux[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_droite_osseux.$index}}" tabindex="{{$index+120}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-stapedien-ipsilateral{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td><input type="text" name="_droite_ipslat[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_droite_ipslat.$index}}" tabindex="{{$index+130}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-stapedien-ipsilateral{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="old_droite_ipslat[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_droite_ipslat.$index}}" tabindex="{{$index+130}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-stapedien-controlateral{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td><input type="text" name="_droite_conlat[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_droite_conlat.$index}}" tabindex="{{$index+140}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-stapedien-controlateral{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="old_droite_conlat[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_droite_conlat.$index}}" tabindex="{{$index+140}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}
          <tr>
            <th>{{tr}}dPcabinet-no-response-aerien{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_droite_aerien_pasrep[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_droite_aerien_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
              {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-no-response-aerien{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
                {{foreach from=$frequences key=index item=frequence}}
                  <td><input type="text" name="old_droite_aerien_pasrep[{{$index}}]" class="num min|-10 max|120" disabled
                             value="{{$old_exam_audio->_droite_aerien_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
                {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-no-response-osseux{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_droite_osseux_pasrep[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_droite_osseux_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
              {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-no-response-osseux{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
                {{foreach from=$frequences key=index item=frequence}}
                  <td><input type="text" name="old_droite_osseux_pasrep[{{$index}}]" class="num min|-10 max|120" disabled
                             value="{{$old_exam_audio->_droite_osseux_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
                {{/foreach}}
            </tr>
          {{/if}}
          {{if $old_consultation_id}}
              {{if $old_exam_audio->_droite_pasrep|@count }}
                <tr>
                  <th class="old_consultation">{{tr}}dPcabinet-no-response{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
                    {{foreach from=$frequences key=index item=frequence}}
                      <td><input type="text" name="old_droite_pasrep[{{$index}}]" class="num min|-10 max|120" disabled
                                 value="{{$old_exam_audio->_droite_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
                    {{/foreach}}
                </tr>
              {{/if}}
          {{/if}}


          <tr>
            <th class="category">{{tr}}common-frequencies{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <th class="category">
                {{$frequence}}
              </th>
            {{/foreach}}
          </tr>
          <tr>
            <th class="category" colspan="{{$colspan}}">{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}}</th>
          </tr>
          <tr>
            <th>{{tr}}dPcabinet-conduction-aerienne{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td><input type="text" name="_gauche_aerien[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_gauche_aerien.$index}}" tabindex="{{$index+10}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-conduction-aerienne{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="old_gauche_aerien[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_gauche_aerien.$index}}" tabindex="{{$index+10}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-conduction-osseuse{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td><input type="text" name="_gauche_osseux[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_gauche_osseux.$index}}" tabindex="{{$index+20}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation"> {{tr}}dPcabinet-conduction-osseuse{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="old_gauche_osseux[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_gauche_osseux.$index}}" tabindex="{{$index+20}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-stapedien-ipsilateral{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td><input type="text" name="_gauche_ipslat[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_gauche_ipslat.$index}}" tabindex="{{$index+30}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-stapedien-ipsilateral{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="old_gauche_ipslat[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_gauche_ipslat.$index}}" tabindex="{{$index+30}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-stapedien-controlateral{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td><input type="text" name="_gauche_conlat[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_gauche_conlat.$index}}" tabindex="{{$index+40}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-stapedien-controlateral{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="old_gauche_conlat[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_gauche_conlat.$index}}" tabindex="{{$index+40}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-no-response-aerien{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_gauche_aerien_pasrep[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_gauche_aerien_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
              {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-no-response-aerien{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
                {{foreach from=$frequences key=index item=frequence}}
                  <td><input type="text" name="old_gauche_aerien_pasrep[{{$index}}]" class="num min|-10 max|120" disabled
                             value="{{$old_exam_audio->_gauche_aerien_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
                {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}dPcabinet-no-response-osseux{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_gauche_osseux_pasrep[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_gauche_osseux_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
              {{/foreach}}
          </tr>
          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}dPcabinet-no-response-osseux{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
                {{foreach from=$frequences key=index item=frequence}}
                  <td><input type="text" name="old_gauche_osseux_pasrep[{{$index}}]" class="num min|-10 max|120" disabled
                             value="{{$old_exam_audio->_gauche_osseux_pasrep.$index}}" tabindex="{{$index+150}}" size="4" maxlength="4"/></td>
                {{/foreach}}
            </tr>
          {{/if}}
          {{if $old_consultation_id}}
            {{if $old_exam_audio->_gauche_pasrep|@count }}
              <tr>
                <th class="old_consultation">{{tr}}dPcabinet-no-response{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
                  {{foreach from=$frequences key=index item=frequence}}
                    <td><input type="text" name="old_gauche_pasrep[{{$index}}]" class="num min|-10 max|120" disabled
                               value="{{$old_exam_audio->_gauche_pasrep.$index}}" tabindex="{{$index+50}}" size="4" maxlength="4"/></td>
                  {{/foreach}}
              </tr>
            {{/if}}
          {{/if}}
          {{if $exam_audio->_can->edit}}
            <tr>
              <td class="button" colspan="{{$colspan}}">
                <button class="submit not-printable" type="button" onclick="onSubmitFormAjax(this.form, function(){
                  ExamAudio.updateAudiometrieTonale('{{if $old_consultation_id}}{{$old_exam_audio->_id}}{{/if}}')})">
                  {{tr}}Save{{/tr}}
                </button>
              </td>
            </tr>
          {{/if}}
          </tbody>
        </table>
      </td>
    </tr>

    <tr>
      <th class="title" colspan="2">Bilan comparé</th>
    </tr>

    <tr>
      <td colspan="2" id="td_bilan">
        {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_bilan}}
      </td>
    </tr>

    <tr style="page-break-inside: avoid; page-break-after: always">
      <td colspan="2">
        <table style="width: 100%">
          <tr>
            <th class="title"><a name="vocal"></a>{{tr}}dPcabinet-audiometrie-vocale{{/tr}}</th>
            <th class="title"><a name="tympan"></a>{{tr}}dPcabinet-tympanometrie{{/tr}}</th>
          </tr>
          <tr>
            <td>
              {{if $old_consultation_id}}
                {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_audiometrie_vocale graph=$graphs.audiometrie_vocale graph_old=$graphs_old.audiometrie_vocale}}
              {{else}}
                {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_audiometrie_vocale graph=$graphs.audiometrie_vocale}}
              {{/if}}
            </td>
            <td rowspan="2">
              <table style="width: 100%">
                <tr>
                  <td>
                    {{if $old_consultation_id}}
                      {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_tympanometrie graph=$graphs.tympanometrie.droite graph_old=$graphs_old.tympanometrie.droite}}
                    {{else}}
                      {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_tympanometrie graph=$graphs.tympanometrie.droite}}
                    {{/if}}
                  </td>
                </tr>
                <tr>
                  <td>
                    {{if $old_consultation_id}}
                      {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_tympanometrie graph=$graphs.tympanometrie.gauche graph_old=$graphs_old.tympanometrie.gauche}}
                    {{else}}
                      {{mb_include module=cabinet template=inc_exam_audio/inc_examaudio_tympanometrie graph=$graphs.tympanometrie.gauche}}
                    {{/if}}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td class="radiointeractive">
              <input type="radio" name="_oreille" value="gauche" {{if $_oreille == "gauche"}}checked{{/if}} />
              <label for="_oreille_gauche" title="Oreille gauche pour la saisie intéractive">{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}}</label>
              <input type="radio" name="_oreille" value="droite" {{if $_oreille == "droite"}}checked{{/if}} />
              <label for="_oreille_droite" title="Oreille gauche pour la saisie intéractive">{{tr}}common-Ear{{/tr}} {{tr}}common-Right{{/tr}}</label>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td colspan="2" id="td_class_to_remove"  class="radiointeractive">
        <table class="form" id="allvocales">
          <tr id="dataVocal-trigger">
            <th class="category" colspan="{{$colspan}}">Toutes les valeurs</th>
          </tr>
          <tbody id="dataVocal">
          <tr>
            <th class="category">{{tr}}dPcabinet-audiometrie-vocale{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <th class="category">
                Point #{{$index}}<br/>dB / %
              </th>
            {{/foreach}}
          </tr>
          <tr>
            <th>{{tr}}common-Ear{{/tr}} {{tr}}common-Right{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td>
                <input type="text" name="_droite_vocale[{{$index}}][0]" class="num min|0 max|120"
                       value="{{$exam_audio->_droite_vocale.$index.0}}" tabindex="{{$index*2+220}}" size="1" maxlength="3"/>
                <input type="text" name="_droite_vocale[{{$index}}][1]" class="num min|0 max|100"
                       value="{{$exam_audio->_droite_vocale.$index.1}}" tabindex="{{$index*2+221}}" size="1" maxlength="3"/>
              </td>
            {{/foreach}}
          </tr>

          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}common-Ear{{/tr}} {{tr}}common-Right{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td>
                  <input type="text" name="old_droite_vocale[{{$index}}][0]" class="num min|0 max|120" disabled
                         value="{{$old_exam_audio->_droite_vocale.$index.0}}" tabindex="{{$index*2+220}}" size="1" maxlength="3"/>
                  <input type="text" name="old_droite_vocale[{{$index}}][1]" class="num min|0 max|100" disabled
                         value="{{$old_exam_audio->_droite_vocale.$index.1}}" tabindex="{{$index*2+221}}" size="1" maxlength="3"/>
                </td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}}</th>
            {{foreach from=$frequences key=index item=frequence}}
              <td>
                <input type="text" name="_gauche_vocale[{{$index}}][0]" class="num min|0 max|120"
                       value="{{$exam_audio->_gauche_vocale.$index.0}}" tabindex="{{$index*2+200}}" size="1" maxlength="3"/>
                <input type="text" name="_gauche_vocale[{{$index}}][1]" class="num min|0 max|100"
                       value="{{$exam_audio->_gauche_vocale.$index.1}}" tabindex="{{$index*2+201}}" size="1" maxlength="3"/>
              </td>
            {{/foreach}}
          </tr>

          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td>
                  <input type="text" name="old_gauche_vocale[{{$index}}][0]" class="num min|0 max|120" disabled
                         value="{{$old_exam_audio->_gauche_vocale.$index.0}}" tabindex="{{$index*2+200}}" size="1" maxlength="3"/>
                  <input type="text" name="old_gauche_vocale[{{$index}}][1]" class="num min|0 max|100" disabled
                         value="{{$old_exam_audio->_gauche_vocale.$index.1}}" tabindex="{{$index*2+201}}" size="1" maxlength="3"/>
                </td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th class="category">{{tr}}dPcabinet-tympanometrie{{/tr}}</th>
            {{foreach from=$pressions item=pression}}
              <th class="category">
                {{$pression}} mm H²O
              </th>
            {{/foreach}}
          </tr>
          <tr>
            <th>{{tr}}common-Ear{{/tr}} {{tr}}common-Right{{/tr}}</th>
            {{foreach from=$pressions key=index item=pression}}
              <td><input type="text" name="_droite_tympan[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_droite_tympan.$index}}" tabindex="{{$index+310}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>

          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}common-Ear{{/tr}} {{tr}}common-Right{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$pressions key=index item=pression}}
                <td><input type="text" name="old_droite_tympan[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_droite_tympan.$index}}" tabindex="{{$index+310}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}

          <tr>
            <th>{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}}</th>
            {{foreach from=$pressions key=index item=pression}}
              <td><input type="text" name="_gauche_tympan[{{$index}}]" class="num min|-10 max|120"
                         value="{{$exam_audio->_gauche_tympan.$index}}" tabindex="{{$index+300}}" size="4" maxlength="4"/></td>
            {{/foreach}}
          </tr>

          {{if $old_consultation_id}}
            <tr>
              <th class="old_consultation">{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
              {{foreach from=$pressions key=index item=pression}}
                <td><input type="text" name="old_gauche_tympan[{{$index}}]" class="num min|-10 max|120" disabled
                           value="{{$old_exam_audio->_gauche_tympan.$index}}" tabindex="{{$index+300}}" size="4" maxlength="4"/></td>
              {{/foreach}}
            </tr>
          {{/if}}
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <table class="form">
          <tr>
            <th class="category">{{tr}}Remarques{{/tr}}</th>
          </tr>
          <tr>
            <td style="text-align:left;">
                {{mb_field object=$exam_audio field=remarques rows=2 form="editFrm" aidesaisie="validateOnBlur: 0"}}
            </td>
          </tr>
            {{if $exam_audio->_can->edit}}
              <tr>
                <td class="button radiointeractive">
                  <button type="button" class="submit" onclick="return onSubmitFormAjax(this.form, function(){
                    ExamAudio.updateAll(0 ,'{{if $old_consultation_id}}{{$old_exam_audio->_id}}{{/if}}')})">
                    {{tr}}Save{{/tr}}
                  </button>
                  <button type="button" class="print" onclick="return onSubmitFormAjax(this.form, function(){
                    ExamAudio.updateAll(1 ,'{{if $old_consultation_id}}{{$old_exam_audio->_id}}{{/if}}')})">{{tr}}CExamAudio-action save and print{{/tr}}</button>
                </td>
              </tr>
          {{/if}}
        </table>
      </td>
    </tr>
    {{if $old_consultation_id}}
      <tr>
        <td colspan="2">
          <table class="form">
            <tr>
              <th class="category">{{tr}}Remarques{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}</th>
            </tr>
            <tr>
              <td style="text-align:left;">
                <textarea id="test" name="old_remarques" disabled>{{$old_exam_audio->remarques}}</textarea>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    {{/if}}
  </table>
</form>
