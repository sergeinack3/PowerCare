{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=_conduction value='aerien'}}
{{mb_default var=_oreille value='gauche'}}
{{mb_default var=consultations_anciennes value=null}}
{{mb_default var=old_consultation_id value=null}}
{{mb_script module=cabinet script=ExamAudio ajax=1}}

{{assign var=frequences value='Ox\Mediboard\Cabinet\CExamAudio'|static:frequences}}
{{assign var=pressions value='Ox\Mediboard\Cabinet\CExamAudio'|static:pressions}}

{{math equation="x + 1" x='Ox\Mediboard\Cabinet\CExamAudio'|static:"frequences"|@count assign=colspan}}

<form name="editFrm" id="editFrm" method="post">
  <input type="hidden" name="m" value="cabinet"/>
  <input type="hidden" name="dosql" value="do_exam_audio_aed"/>
  <input type="hidden" name="del" value="0"/>
    {{mb_key object=$exam_audio}}
    {{mb_field object=$exam_audio field=consultation_id hidden=1}}

  <table class="print">
    <tr>
      <th class="title modify" colspan="2">
          {{assign var=consultation value=$exam_audio->_ref_consult}}
        Consultation de {{$consultation->_ref_patient}}
        le {{$consultation->_date|date_format:$conf.longdate}}
        par {{if $consultation->_ref_chir->isPraticien()}}le Dr{{/if}} {{$consultation->_ref_chir}}
      </th>
    </tr>

    <tr>
      <th class="title" colspan="2">{{tr}}dPcabinet-audiometrie-tonale{{/tr}} ({{tr}}dPcabinet-test-weber{{/tr}})</th>
    </tr>

    <tr>
      <td colspan="2">
        <input type="radio" name="_conduction" value="aerien" {{if $_conduction == "aerien"}}checked="1"{{/if}} />
        <label for="_conduction_aerien"
               title="Conduction aérienne pour la saisie intéractive">{{tr}}dPcabinet-conduction-aerienne{{/tr}}</label>
        <input type="radio" name="_conduction" value="osseux" {{if $_conduction == "osseux"}}checked="1"{{/if}} />
        <label for="_conduction_osseux"
               title="Conduction osseuse pour la saisie intéractive">{{tr}}dPcabinet-conduction-osseuse{{/tr}}</label>
        <input type="radio" name="_conduction" value="ipslat" {{if $_conduction == "ipslat"}}checked="1"{{/if}} />
        <label for="_conduction_ipslat"
               title="Stapédien ipsilatéral pour la saisie intéractive">{{tr}}dPcabinet-stapedien-ipsilateral{{/tr}}</label>
        <input type="radio" name="_conduction" value="conlat" {{if $_conduction == "conlat"}}checked="1"{{/if}} />
        <label for="_conduction_conlat"
               title="Stapédien controlatéral pour la saisie intéractive">{{tr}}dPcabinet-stapedien-controlateral{{/tr}}</label>
        <input type="radio" name="_conduction" value="osseux_pasrep"
               {{if $_conduction == "osseux_pasrep"}}checked="1"{{/if}} />
        <label for="_conduction_osseux_pasrep"
               title="Pas de réponse pour la saisie intéractive">{{tr}}dPcabinet-no-response-osseux{{/tr}}</label>
        <input type="radio" name="_conduction" value="aerien_pasrep"
               {{if $_conduction == "aerien_pasrep"}}checked="1"{{/if}} />
        <label for="_conduction_aerien_pasrep"
               title="Pas de réponse pour la saisie intéractive">{{tr}}dPcabinet-no-response-aerien{{/tr}}</label>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <table>
          <tr>
            <th class="category" colspan="{{$colspan}}">Toutes les valeurs</th>
          </tr>
          <tbody id="dataTonal">
          <tr>
            <th class="category" colspan="{{$colspan}}">{{tr}}common-Ear{{/tr}} {{tr}}common-Right{{/tr}}</th>
          </tr>
          <tr>
            <th>{{tr}}dPcabinet-conduction-aerienne{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_droite_aerien[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_droite_aerien.$index}}" tabindex="{{$index+110}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-conduction-osseuse{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_droite_osseux[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_droite_osseux.$index}}" tabindex="{{$index+120}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-stapedien-controlateral{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_droite_conlat[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_droite_conlat.$index}}" tabindex="{{$index+140}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-no-response-aerien{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_droite_aerien_pasrep[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_droite_aerien_pasrep.$index}}" tabindex="{{$index+150}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-no-response-osseux{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_droite_osseux_pasrep[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_droite_osseux_pasrep.$index}}" tabindex="{{$index+150}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

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
                           value="{{$exam_audio->_gauche_aerien.$index}}" tabindex="{{$index+10}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-conduction-osseuse{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_gauche_osseux[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_gauche_osseux.$index}}" tabindex="{{$index+20}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-stapedien-ipsilateral{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_gauche_ipslat[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_gauche_ipslat.$index}}" tabindex="{{$index+30}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-stapedien-controlateral{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_gauche_conlat[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_gauche_conlat.$index}}" tabindex="{{$index+40}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-no-response-aerien{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_gauche_aerien_pasrep[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_gauche_aerien_pasrep.$index}}" tabindex="{{$index+150}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}dPcabinet-no-response-osseux{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td><input type="text" name="_gauche_osseux_pasrep[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_gauche_osseux_pasrep.$index}}" tabindex="{{$index+150}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>
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

    <tr>
      <td colspan="2">
        <table style="width: 100%">
          <tr>
            <th class="title">{{tr}}dPcabinet-audiometrie-vocale{{/tr}}</th>
            <th class="title">{{tr}}dPcabinet-tympanometrie{{/tr}}</th>
          </tr>
          <tr>
            <td>
              <input type="radio" name="_oreille" value="gauche" {{if $_oreille == "gauche"}}checked="1"{{/if}} />
              <label for="_oreille_gauche"
                     title="Oreille gauche pour la saisie intéractive">{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}}</label>
              <input type="radio" name="_oreille" value="droite" {{if $_oreille == "droite"}}checked="1"{{/if}} />
              <label for="_oreille_droite"
                     title="Oreille gauche pour la saisie intéractive">{{tr}}common-Ear{{/tr}} {{tr}}common-Right{{/tr}}</label>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <table>
          <tr>
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
                         value="{{$exam_audio->_droite_vocale.$index.0}}" tabindex="{{$index*2+220}}" size="4"
                         maxlength="3"/>
                  <input type="text" name="_droite_vocale[{{$index}}][1]" class="num min|0 max|100"
                         value="{{$exam_audio->_droite_vocale.$index.1}}" tabindex="{{$index*2+221}}" size="4"
                         maxlength="3"/>
                </td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}}</th>
              {{foreach from=$frequences key=index item=frequence}}
                <td>
                  <input type="text" name="_gauche_vocale[{{$index}}][0]" class="num min|0 max|120"
                         value="{{$exam_audio->_gauche_vocale.$index.0}}" tabindex="{{$index*2+200}}" size="4"
                         maxlength="3"/>
                  <input type="text" name="_gauche_vocale[{{$index}}][1]" class="num min|0 max|100"
                         value="{{$exam_audio->_gauche_vocale.$index.1}}" tabindex="{{$index*2+201}}" size="4"
                         maxlength="3"/>
                </td>
              {{/foreach}}
          </tr>

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
                           value="{{$exam_audio->_droite_tympan.$index}}" tabindex="{{$index+310}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>

          <tr>
            <th>{{tr}}common-Ear{{/tr}} {{tr}}common-Left{{/tr}}</th>
              {{foreach from=$pressions key=index item=pression}}
                <td><input type="text" name="_gauche_tympan[{{$index}}]" class="num min|-10 max|120"
                           value="{{$exam_audio->_gauche_tympan.$index}}" tabindex="{{$index+300}}" size="4"
                           maxlength="4"/></td>
              {{/foreach}}
          </tr>
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <table>
          <tr>
            <th class="category">{{tr}}Remarques{{/tr}}</th>
          </tr>
          <tr>
            <td style="text-align:left;">
                {{mb_value object=$exam_audio field=remarques}}
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
