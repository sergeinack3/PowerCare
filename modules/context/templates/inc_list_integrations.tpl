{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="6">
      {{tr}}CContextualIntegration{{/tr}}
      <button class="new me-float-right" onclick="ContextualIntegration.create()">{{tr}}CContextIntegration-title-create{{/tr}}</button>
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CContextualIntegration field=title}}</th>
    <th>{{mb_title class=CContextualIntegration field=url}}</th>
    <th>{{mb_title class=CContextualIntegration field=description}}</th>
    <th>{{mb_title class=CContextualIntegration field=display_mode}}</th>
    <th class="narrow">{{tr}}CContextualIntegrationLocation.action{{/tr}}</th>
  </tr>
  {{foreach from=$list_integrations item=_integration}}
    <tr id="row-{{$_integration->_guid}}" {{if !$_integration->active}} class="opacity-50" {{/if}}>
      <td style="font-size: 14px; text-align: center;">
        {{mb_include module=context template=inc_integration_icon integration=$_integration}}
      </td>
      <td>
        <a href="#1" onclick="ContextualIntegration.edit({{$_integration->_id}}); return false;">
          {{mb_value object=$_integration field=title}}
        </a>
      </td>
      <td class="compact">{{$_integration->url|spancate:50}}</td>
      <td class="compact">{{mb_value object=$_integration field=description}}</td>
      <td>{{mb_value object=$_integration field=display_mode}}</td>
      <td class="me-text-align-center">
        <button class="edit notext compact" onclick="ContextualIntegration.edit({{$_integration->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CContextualIntegration.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
