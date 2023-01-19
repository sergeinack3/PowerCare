{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry("tabs-contenu", true));
</script>

<table class="form">
  <tr>
    <th class="title">
      {{tr}}{{$exchange->_class}}{{/tr}} - #{{$exchange->_id}}
      <br />
      - {{mb_value object=$exchange field="function_name"}} -
    </th>
  </tr>
  <tr>
    <td>
      <ul id="tabs-contenu" class="control_tabs">
        <li><a href="#input">{{mb_title object=$exchange field="input"}}</a></li>
        <li><a href="#output">{{mb_title object=$exchange field="output"}}</a></li>

        {{if $exchange|instanceof:'Ox\Interop\Webservices\CEchangeSOAP' && $exchange->trace}}
          <li><a href="#lastRequestHeaders">{{mb_title object=$exchange field="last_request_headers"}}</a></li>
          <li><a href="#lastRequest">{{mb_title object=$exchange field="last_request"}}</a></li>
          <li><a href="#lastResponseHeaders">{{mb_title object=$exchange field="last_response_headers"}}</a></li>
          <li><a href="#lastResponse">{{mb_title object=$exchange field="last_response"}}</a></li>
        {{/if}}
      </ul>

      <div id="input" style="display: none; height: 410px;" class="highlight-fill">
        {{mb_value object=$exchange field="input" export=true}}
      </div>

      <div id="output" style="display: none; height: 410px;" class="highlight-fill">
        {{mb_value object=$exchange field="output" export=true}}
      </div>

      {{if $exchange|instanceof:'Ox\Interop\Webservices\CEchangeSOAP' && $exchange->trace}}
        <div id="lastRequestHeaders" style="display: none;">
          {{mb_value object=$exchange field="last_request_headers"}}
        </div>
        <div id="lastRequest" style="display: none;">
          {{mb_value object=$exchange field="last_request"}}
        </div>
        <div id="lastResponseHeaders" style="display: none;">
          {{mb_value object=$exchange field="last_response_headers"}}
        </div>
        <div id="lastResponse" style="display: none;">
          {{mb_value object=$exchange field="last_response"}}
        </div>
      {{/if}}
    </td>
  </tr>
</table>
