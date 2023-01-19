{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{math equation="x + 2" x='Ox\Mediboard\Cabinet\CExamAudio'|static:"frequences"|@count assign=colspan}}

<table class="tbl">
  <tr>
    <th class="text">Fréquences</th>
    {{foreach from=$bilan key=frequence item=pertes}}
      <th>{{$frequence}}</th>
    {{/foreach}}
  </tr>
  <tr>
    <th></th>
    <th colspan="{{$colspan}}">{{tr}}dPcabinet-conduction-aerienne{{/tr}}</th>
  </tr>
  <tr class="moyenne">
    <th class="text">
      {{tr}}common-Average{{/tr}} {{tr}}common-Right{{/tr}}
    </th>
    <td class="aerien" colspan="10">{{$exam_audio->_moyenne_droite_aerien}}dB</td>
  </tr>

  {{if $old_consultation_id}}
    <tr class="moyenne old_consultation">
      <th class="text">
        {{tr}}common-Average{{/tr}} {{tr}}common-Right{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}
      </th>
      <td class="aerien" colspan="10">{{$old_exam_audio->_moyenne_droite_aerien}}dB</td>
    </tr>
  {{/if}}

  <tr class="moyenne">
    <th class="text">
      {{tr}}common-Average{{/tr}} {{tr}}common-Left{{/tr}}
    </th>
    <td class="aerien" colspan="10">{{$exam_audio->_moyenne_gauche_aerien}}dB</td>
  </tr>

  {{if $old_consultation_id}}
    <tr class="moyenne old_consultation">
      <th class="text">
        {{tr}}common-Average{{/tr}} {{tr}}common-Left{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}
      </th>
      <td class="aerien" colspan="10">{{$old_exam_audio->_moyenne_gauche_aerien}}dB</td>
    </tr>
  {{/if}}

  <tr>
    <th class="text">
      {{tr}}common-Comparison{{/tr}}<br/>
      ({{tr}}common-Right{{/tr}} / {{tr}}common-Left{{/tr}})
    </th>
    {{foreach from=$bilan item=pertes}}
      <td>
        {{$pertes.aerien.droite}}dB / {{$pertes.aerien.gauche}}dB<br/>
        {{assign var="delta" value=$pertes.aerien.delta}}
        {{if $delta lt -20}}&lt;&lt;
        {{elseif $delta lt 0}}&lt;=
        {{elseif $delta eq 0}}==
        {{elseif $delta lt 20}}=&gt;
        {{else}}&gt;&gt;
        {{/if}}
      </td>
    {{/foreach}}
  </tr>

  {{if $old_consultation_id}}
    <tr class="old_consultation">
      <th class="text">
        {{tr}}common-Comparison{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}<br/>
        ({{tr}}common-Right{{/tr}} / {{tr}}common-Left{{/tr}})
      </th>
      {{foreach from=$old_bilan item=pertes}}
        <td>
          {{$pertes.aerien.droite}}dB / {{$pertes.aerien.gauche}}dB<br/>
          {{assign var="delta" value=$pertes.aerien.delta}}
          {{if $delta lt -20}}&lt;&lt;
          {{elseif $delta lt 0}}&lt;=
          {{elseif $delta eq 0}}==
          {{elseif $delta lt 20}}=&gt;
          {{else}}&gt;&gt;
          {{/if}}
        </td>
      {{/foreach}}
    </tr>
  {{/if}}

  <tr>
    <th></th>
    <th colspan="{{$colspan}}">{{tr}}dPcabinet-conduction-osseuse{{/tr}}</th>
  </tr>
  <tr class="moyenne">
    <th class="text">
      {{tr}}common-Average{{/tr}} {{tr}}common-Right{{/tr}}
    </th>
    <td class="osseux" colspan="10">{{$exam_audio->_moyenne_droite_osseux}}dB</td>
  </tr>

  {{if $old_consultation_id}}
    <tr class="moyenne old_consultation">
      <th class="text">
        {{tr}}common-Average{{/tr}} {{tr}}common-Right{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}
      </th>
      <td class="osseux" colspan="10">{{$old_exam_audio->_moyenne_droite_osseux}}dB</td>
    </tr>
  {{/if}}

  <tr class="moyenne">
    <th class="text">
      {{tr}}common-Average{{/tr}} {{tr}}common-Left{{/tr}}
    </th>
    <td class="osseux" colspan="10">{{$exam_audio->_moyenne_gauche_osseux}}dB</td>
  </tr>

  {{if $old_consultation_id}}
    <tr class="moyenne old_consultation">
      <th class="text">
        {{tr}}common-Average{{/tr}} {{tr}}common-Left{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}
      </th>
      <td class="osseux" colspan="10">{{$old_exam_audio->_moyenne_gauche_osseux}}dB</td>
    </tr>
  {{/if}}

  <tr>
    <th class="text">
      {{tr}}common-Comparison{{/tr}}<br/>
      ({{tr}}common-Right{{/tr}} / {{tr}}common-Left{{/tr}})
    </th>
    {{foreach from=$bilan item=pertes}}
      <td>
        {{$pertes.osseux.droite}}dB / {{$pertes.osseux.gauche}}dB<br/>
        {{assign var="delta" value=$pertes.osseux.delta}}
        {{if $delta lt -20}}&lt;&lt;
        {{elseif $delta lt 0}}&lt;=
        {{elseif $delta eq 0}}==
        {{elseif $delta lt 20}}=&gt;
        {{else}}&gt;&gt;
        {{/if}}
      </td>
    {{/foreach}}
  </tr>

  {{if $old_consultation_id}}
    <tr class="old_consultation">
      <th class="text">
        {{tr}}common-Comparison{{/tr}} - {{$old_consultation->_date|date_format:$conf.longdate}}<br/>
        ({{tr}}common-Right{{/tr}} / {{tr}}common-Left{{/tr}})
      </th>
      {{foreach from=$bilan item=pertes}}
        <td>
          {{$pertes.osseux.droite}}dB / {{$pertes.osseux.gauche}}dB<br/>
          {{assign var="delta" value=$pertes.osseux.delta}}
          {{if $delta lt -20}}&lt;&lt;
          {{elseif $delta lt 0}}&lt;=
          {{elseif $delta eq 0}}==
          {{elseif $delta lt 20}}=&gt;
          {{else}}&gt;&gt;
          {{/if}}
        </td>
      {{/foreach}}
    </tr>
  {{/if}}
</table>
