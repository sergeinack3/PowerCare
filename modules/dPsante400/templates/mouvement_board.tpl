{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="text-align: center;">
  <tr>
    <th class="title">Type</th>
    <th class="title" colspan="7">Triggers</th>
    <th class="title" colspan="7">Marques</th>
  </tr>

  <tr>
    <th></th>
    <th colspan="2">Plus ancien</th>
    <th colspan="2">Plus récent</th>
    <th colspan="3">Totaux</th>
    <th colspan="2">Plus ancien</th>
    <th colspan="2">Plus récent</th>
    <th colspan="3">Totaux</th>
  </tr>

  <tr>
    <th class="section"></th>
    <th class="section">numéro</th>
    <th class="section">horodatage</th>
    <th class="section">numéro</th>
    <th class="section">horodatage</th>
    <th class="section">disponibles</th>
    <th class="section">à traiter</th>
    <th class="section">en erreur</th>
    <th class="section">numéro</th>
    <th class="section">horodatage</th>
    <th class="section">numéro</th>
    <th class="section">horodatage</th>
    <th class="section">traités</th>
    <th class="section">purgeables</th>
    <th class="section">obsolètes</th>
  </tr>

  {{foreach from=$report key=_type item=_report}}
    <tr>
      <th>{{$_type}}</th>

      {{assign var=triggers value=$_report.triggers}}
      <td>{{$triggers.oldest->rec}}</td>
      <td>{{$triggers.oldest->when|date_format:$conf.datetime}}</td>
      <td>{{$triggers.latest->rec}}</td>
      <td>{{$triggers.latest->when|date_format:$conf.datetime}}</td>
      <td>{{$triggers.available}}</td>
      <td>{{$triggers.marked.0}}</td>
      <td>{{$triggers.marked.1}}</td>

      {{assign var=marks value=$_report.marks}}
      <td>{{$marks.oldest->trigger_number}}</td>
      <td>{{$marks.oldest->when|date_format:$conf.datetime}}</td>
      <td>{{$marks.latest->trigger_number}}</td>
      <td>{{$marks.latest->when|date_format:$conf.datetime}}</td>
      <td>{{$marks.all      }}</td>
      <td>
        <button class="change singleclick" onclick="Moves.boardAction('purge', '{{$_type}}')">
          {{$marks.purgeable}}
        </button>
      </td>
      <td>
        <button class="change singleclick" onclick="Moves.boardAction('obsolete', '{{$_type}}')">
          {{$marks.obsolete }}
        </button>
      </td>

    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty">No type to report</td>
    </tr>
  {{/foreach}}

</table>
