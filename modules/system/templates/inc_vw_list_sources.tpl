{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback_source value="ExchangeSource.refreshUserSources"}}

<table class="main tbl me-no-align me-no-box-shadow">
  <tr>
    <th class="section" style="width: 25%">{{tr}}CExchangeSource-libelle{{/tr}}</th>
    <th class="section" style="width: 25%">{{tr}}CExchangeSource-host{{/tr}}</th>
    <th class="section" style="width: 25%">{{tr}}CExchangeSource-user{{/tr}}</th>
    <th class="section" style="width: 10%">{{tr}}CExchangeSource-active{{/tr}}</th>
    <th class="section" style="width: 15%">{{tr}}Actions{{/tr}}</th>
  </tr>
  {{foreach from=$sources item=_source}}
    {{if $_source->_id}}
      <tr {{if !$_source->active}}class="hatching"{{/if}}>
        <td class="compact">
          <button type="button" class="edit notext compact"
                  onclick="ExchangeSource.editSource('{{$_source->_guid}}', true, '{{$_source->name}}',
                    '{{$_source->_wanted_type}}', null, {{$callback_source}})">
            {{tr}}Edit{{/tr}}
          </button>
          {{mb_value object=$_source field=libelle}}
        </td>
        <td>{{mb_value object=$_source field=host}}</td>
        <td>{{mb_value object=$_source field=user}}</td>
        <td>{{mb_value object=$_source field=active}}</td>
        <td class="button">
          {{mb_include module="`$_source->_ref_module->mod_name`" template="`$_source->_class`_tools_inc"}}
        </td>
      </tr>
    {{else}}
      <tr>
        <td colspan="5" class="empty">{{tr}}CExchangeSource.none{{/tr}}</td>
      </tr>
    {{/if}}
  {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">{{tr}}CExchangeSource.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
