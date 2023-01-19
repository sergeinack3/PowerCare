{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <tr>
        <th colspan="4">
            {{tr}}Results{{/tr}}
        </th>
    </tr>
    <tr>
        <td colspan="4">
            {{mb_include module=system template=inc_pagination total=$total current=$start change_page="changePage" step=100}}
        </td>
    </tr>
    {{assign var=count value=$codes|@count}}
    {{foreach from=$codes item=_code key=_index}}
        {{if $_index is div by 4}}
            <tr>
        {{/if}}
        <td onclick="displayCode('{{$_code->code}}');" style="width: 25%; cursor: pointer; border: 2px #aaa solid;">
            {{assign var=_text value=$_code->name|lower}}
            <span class="compact" style="float: right;">
        {{mb_value object=$_code field=prestation_type}}
      </span>
            <strong>{{mb_value object=$_code field=code}}</strong>
            <div class="text compact"
                 style="width: 100%; overflow: hidden; text-overflow: ellipsis;">{{$_code->name}}</div>
        </td>
        {{if ($_index + 1) is div by 4}}
            </tr>
        {{/if}}
        {{foreachelse}}
        <tr>
            <td colspan="4" class="empty" style="text-align: center;">
                {{tr}}CLPPCode-msg-no_result{{/tr}}
            </td>
        </tr>
    {{/foreach}}
    {{if !$codes|@count is div by 4 && $codes|@count > 4}}
        {{assign var=missing_cells value=$count%4}}
        <td colspan="{{$missing_cells}}"></td>
        </tr>
    {{/if}}
    <tr>
        <td colspan="4">
            {{mb_include module=system template=inc_pagination total=$total current=$start change_page="changePage" step=100}}
        </td>
    </tr>
</table>
