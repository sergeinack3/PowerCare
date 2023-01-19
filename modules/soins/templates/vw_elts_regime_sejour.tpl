{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{if $prescription->_ref_lines_regime}}
  {{assign var=lines value=$prescription->_ref_lines_regime}}
{{elseif $prescription->_ref_lines_jeun}}
  {{assign var=lines value=$prescription->_ref_lines_jeun}}
{{else}}
  {{mb_return}}
{{/if}}

<table class="main tbl">
  <tr>
    <th>
      {{tr}}CUserSejour-title-regime{{/tr}}: <br/>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_ref_object->_guid}}')">
        {{$prescription->_ref_object->_view}}
      </span>({{$lines|@count}})
    </th>
  </tr>
  {{foreach from=$lines item=_line_regime}}
    <tr>
      <td>
        {{mb_include module=prescription template=inc_print_element elt=$_line_regime nodebug=true}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty">{{tr}}CPrescriptionLine.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>