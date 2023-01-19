{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <table class="main form">
    <tr>
      <th class="title narrow"><strong>{{tr}}mod-system-object-nav-label{{/tr}}</strong></th>
      <th class="title narrow"><strong>{{tr}}mod-system-object-nav-value{{/tr}}</strong></th>
      <th class="title" colspan="2"><strong>{{tr}}mod-system-object-nav-value-brut{{/tr}}</strong></th>
    </tr>

    {{foreach from=$grouped_fields key=_group item=fields}}
      {{if $_group !== 'none' && ($fields.form.refs|@count > 0 || $fields.form.fields|@count > 0)}}
        <tr>
          <th colspan="3" class="title" title="Fieldset" style="text-align: center">{{$_group|ucfirst}}</th>
        </tr>
      {{/if}}

      {{if $fields.form.refs|@count > 0}}
        {{foreach from=$fields.form.refs key=_key item=_field}}
          <tr>
            <td class="text">{{mb_label object=$object_select field=$_key}}</td>
            <td
              class="narrow text">{{if $object_select->$_key}}{{mb_value object=$object_select field=$_key}}{{/if}}</td>
            <td class="narrow text">{{$object_select->$_key}}</td>
            {{if $object_select->$_key}}
              <td>
                <button type="button" class="link narrow"
                        onclick="ObjectNavigation.openModalObject('{{$_field}}', {{$object_select->$_key}})">
                  {{tr}}mod-system-object-nav-object-link{{/tr}}
                </button>
              </td>
            {{/if}}
          </tr>
        {{/foreach}}
      {{/if}}

      {{foreach from=$fields.form.fields key=_key item=_field}}
        <tr>
          <td class="narrow text"><strong>{{mb_label object=$object_select field=$_key}}</strong></td>
          <td class="narrow text">
            {{if $object_select->$_key}}
              {{mb_value object=$object_select field=$_key}}
            {{/if}}
          </td>

          <td class="narrow text">
            {{if $object_select->$_key}}
              {{$object_select->$_key}}
            {{/if}}
          </td>
        </tr>
      {{/foreach}}


    {{/foreach}}


  </table>
</div>
