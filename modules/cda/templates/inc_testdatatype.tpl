{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>
      {{tr}}Description{{/tr}}
    </th>
    <th>
      {{tr}}ResultPlanned{{/tr}}
    </th>
    <th>
      {{tr}}Result{{/tr}}
    </th>
  </tr>
  {{foreach from=$result key=name item=_test}}
    <tr>
      <th colspan="3" class="section">
        <A NAME="{{$name}}">{{$name}}</A>
      </th>
    </tr>
    {{foreach from=$_test item=_ligne}}
      <tr>
        <td>{{$_ligne.description}}</td>
        <td>{{$_ligne.resultatAttendu}}</td>
        <td {{if $_ligne.resultatAttendu == $_ligne.resultat}}class="ok"{{else}}class="error"{{/if}}>{{$_ligne.resultat}}</td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>
<br/>