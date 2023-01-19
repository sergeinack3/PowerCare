{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table id="help" class="tbl">
  {{foreach from=$styles key=style item=_examples}}
    <tr>
      <th colspan="3" class="category">{{tr}}{{$style}}{{/tr}}</th>
    </tr>
    {{if $_examples|@is_array}}
      {{foreach from=$_examples item=_example}}
        <tr>
          <td style="white-space: pre">{{$_example}}</td>
          <td>{{$_example|smarty:nodefaults|markdown}}</td>
          <td style="white-space: pre"><pre>{{$_example|smarty:nodefaults|markdown|htmlentities:$smarty.const.ENT_COMPAT}}</pre></td>
        </tr>
      {{/foreach}}
    {{else}}
      <tr>
        <td style="white-space: pre">{{$_examples}}</td>
        <td style="white-space: pre">{{$_examples|smarty:nodefaults|markdown}}</td>
        <td style="white-space: pre"><pre>{{$_examples|smarty:nodefaults|markdown|htmlentities:$smarty.const.ENT_COMPAT}}</pre></td>
      </tr>
    {{/if}}

  {{/foreach}}
</table>
