{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-exchange-dicom', true));
</script>

<ul id="tabs-exchange-dicom" class="control_tabs">
  <li>
    <a href="#request">
      {{tr}}Request{{/tr}}
    </a>
  </li>
  <li>
    <a href="#response">
      {{tr}}Response{{/tr}}
    </a>
  </li>
</ul>

<div id="request" style="display: none;">
  <table class="tbl">
    {{foreach from=$exchange->_requests item=_request}}
      <tr>
        <th class="category">
          {{assign var=pdvs value=$_request->getPDVs()}}
          {{foreach from=$pdvs key=_index item=_pdv}}
            {{assign var=msg value=$_pdv->getMessage()}}
            {{if $_index == 0}}
              {{$msg->type}}
            {{else}}
              | {{$msg->type}}
            {{/if}}
          {{/foreach}}
        </th>
      </tr>
      <tr>
        <td>
          {{$_request->toString()}}
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>

<div id="response" style="display: none;">
  <table class="tbl">
    {{foreach from=$exchange->_responses item=_response}}
      <tr>
        <th class="category">
          {{assign var=pdvs value=$_response->getPDVs()}}
          {{foreach from=$pdvs key=_index item=_pdv}}
            {{assign var=msg value=$_pdv->getMessage()}}
            {{if $_index == 0}}
              {{$msg->type}}
            {{else}}
              | {{$msg->type}}
            {{/if}}
          {{/foreach}}
        </th>
      </tr>
      <tr>
        <td>
          {{$_response->toString()}}
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>