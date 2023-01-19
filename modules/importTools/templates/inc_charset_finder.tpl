{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $compare}}
  <style>
    table.compare {
      font-family: 'Courier New', 'Lucida Console', Courier, monospace;"
    }

    table.compare > * > tr > td {
      border: 1px solid #ccc!important;
    }
  </style>
  <table class="main tbl me-no-align me-margin-top-8">
    <tr>
      <th>Charset</th>
      <th>Result</th>
    </tr>

    {{foreach from=$results item=_result key=_charset}}
      {{if $_result.status}}
      <tr>
        <td>{{$_charset}}</td>
        <td>
          <table class="layout compare">
            <tr>
              {{foreach from=$_result.input item=_input}}
                <td>{{$_input.0}}</td>
              {{/foreach}}
            </tr>
            <tr>
              {{foreach from=$_result.input item=_input}}
                <td>{{$_input.1}}</td>
              {{/foreach}}
            </tr>
            <tr>
              {{foreach from=$_result.output item=_output}}
                <td>{{$_output.0}}</td>
              {{/foreach}}
            </tr>
            <tr>
              {{foreach from=$_result.output item=_output}}
                <td>{{$_output.1}}</td>
              {{/foreach}}
            </tr>
          </table>
        </td>
      </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{else}}
  <table class="main tbl me-no-align me-margin-top-8">
    <tr>
      <th>Charset</th>
      <th>Result</th>
      <th>Hex</th>
    </tr>

    {{foreach from=$results item=_out key=_charset}}
      <tr {{if !$_out.status}} class="opacity-30" {{/if}}>
        <td>{{$_charset}}</td>
        <td><code>{{$_out.result}}</code></td>
        <td><code>{{$_out.hex}}</code></td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}