{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h1>
  <small style="float: right">[{{$header.0}}]</small>
  Résultats de laboratoire &ndash; {{$header.9}}
</h1>

{{mb_include template="inc_hprim_header"}}

<h1>Courrier</h1>
<pre>{{$text}}</pre>

<h1>Résultats</h1>
<table class="main tbl">
  <tr>
    <th>Libellé</th>
  {{*<th>Code</th>*}}
  {{*<th>Type de résultat</th>*}}
    <th>Résultat</th>
    <th>Unité</th>
    <th>Val. normale inf.</th>
    <th>Val. normale sup.</th>
    <th>Anorm.</th>
    <th>Statut</th>
    <th>Résultat 2</th>
    <th>Unité 2</th>
    <th>Val. norm. inf. 2</th>
    <th>Val. norm. sup. 2</th>
  </tr>
{{foreach from=$results item=_result}}
  <tr>
    <td>{{$_result.label}}</td>
  {{*<td>{{$_result.code}}</td>*}}
  {{*<td>{{$_result.type}}</td>*}}
    <td class="{{$_result.anormal_class}}">
      {{$_result.value}}
    </td>
    <td>{{$_result.unit}}</td>
    <td>{{$_result.min}}</td>
    <td>{{$_result.max}}</td>
    <td>
      {{if $_result.anormal != "N"}}
          {{$_result.anormal_text}}
        {{/if}}
    </td>
    <td>{{$_result.status}}</td>
    <td>{{$_result.value2}}</td>
    <td>{{$_result.unit2}}</td>
    <td>{{$_result.min2}}</td>
    <td>{{$_result.max2}}</td>
  </tr>
{{/foreach}}
</table>