{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="div_modify_non_active_{{$form_uid}}">
  <table class="main form">
    <tr>
      <td colspan="4"></td>
    </tr>
    {{foreach from=$grouped_fields key=_group item=fields}}
      {{if $_group !== 'none' && ($fields.plain.refs|@count > 0 || $fields.plain.fields|@count > 0) }}
        <tr>
          <th colspan="4" class="title" title="Fieldset" style="text-align: center">{{$_group|ucfirst}}</th>
        </tr>
      {{/if}}
      {{foreach from=$fields.plain.refs key=_key item=_field}}
        <tr>
          <th>
            <strong>{{mb_label object=$object_select field=$_key}}</strong>
          </th>
          <td colspan="2">
            <tt>{{mb_value object=$object_select field=$_key}}</tt>
          </td>
          <td class="narrow">
            {{if $object_select->$_key && $_key != $object_select->_spec->key}}
              <button type="button" class="link"
                      onclick="ObjectNavigation.openModalObject('{{$_field}}', '{{$object_select->$_key}}')">
                {{tr}}mod-system-object-nav-object-link{{/tr}}
              </button>
            {{/if}}
          </td>
        </tr>
      {{/foreach}}

      {{foreach from=$fields.plain.fields key=_key item=_field}}
        <tr>
          <th>{{mb_label object=$object_select field=$_key}}</th>
          <td
            colspan="2">{{mb_value object=$object_select field=$_key emptyLabel="&nbsp;"}}</td>
          <td></td>
        </tr>
      {{/foreach}}

    {{/foreach}}
  </table>
</div>
