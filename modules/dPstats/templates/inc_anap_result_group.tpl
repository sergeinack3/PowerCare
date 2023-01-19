{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if array_key_exists('context_place', $results)}}
  {{assign var=colspan value=4}}
{{else}}
  {{assign var=colspan value=3}}
{{/if}}

<tr>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: center;"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    <i id="spinner_{{$group}}" class="fa fa-chevron-circle-right"></i>
  </td>
  <td style="cursor: pointer; font-weight: bold; text-align: center;" onclick="toggleVacations('{{$group}}', this.up('tr'));"
      colspan="{{$colspan}}">
    {{if $line.element->_class == 'CMediusers'}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$line.element}}
    {{else}}
      {{$line.element}}
    {{/if}}
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.tvo}}
  </td>

  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.tpos}}
  </td>

  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.tros}}
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.trov}}
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.txoc}}%
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.pot}}
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.deb}}
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.txdeb}}%
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.txper}}%
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));" title="{{$line.beg}}">
    {{$line.txbeg}}%
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));" title="{{$line.end}}">
    {{$line.txend}}%
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.txurg}}%
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.txpot}}%
  </td>
  <td class="narrow" style="cursor: pointer; font-weight: bold; text-align: right"
      onclick="toggleVacations('{{$group}}', this.up('tr'));">
    {{$line.evtvo}}%
  </td>
</tr>