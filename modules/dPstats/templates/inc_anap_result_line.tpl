{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=grouping value=0}}

<tr class="alternate{{if $grouping}} {{$grouping}}" style="visibility: collapse;{{/if}}">
  {{if array_key_exists('groupings', $results)}}
    <td class="narrow"></td>
  {{/if}}
  <td class="narrow" style="cursor: pointer; text-align: center;" onclick="getVacationDetails('{{$plage}}');">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$line.plage->_ref_chir}}
  </td>
  {{if array_key_exists('context_place', $results)}}
    <td class="narrow" style="cursor: pointer; text-align: center;" onclick="getVacationDetails('{{$plage}}');">
      {{$results.context_place->_view}}
    </td>
  {{/if}}
  <td style="cursor: pointer; text-align: center;" onclick="getVacationDetails('{{$plage}}');">
    {{$line.plage}} {{mb_value object=$line.plage field=debut}} - {{mb_value object=$line.plage field=fin}}
  </td>
  <td style="cursor: pointer; text-align: center;" onclick="getVacationDetails('{{$plage}}');">
    {{$line.plage->_ref_salle}}
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.tvo}}
  </td>

  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.tpos}}
  </td>

  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.tros}}
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.trov}}
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.txoc}}%
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.pot}}
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.deb}}
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.txdeb}}%
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.txper}}%
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');" title="{{$line.beg}}">
    {{$line.txbeg}}%
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');" title="{{$line.end}}">
    {{$line.txend}}%
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.txurg}}%
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.txpot}}%
  </td>
  <td class="narrow" style="cursor: pointer; text-align: right" onclick="getVacationDetails('{{$plage}}');">
    {{$line.evtvo}}%
  </td>
</tr>