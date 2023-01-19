{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="narrow">
      <button type="button" class="new notext"
              onclick="Salutation.editSalutation(null, {onClose: function() { Salutation.reloadList(this.form); }});">
        {{tr}}CSalutation-action-Create{{/tr}}
      </button>
    </th>

    <th class="narrow">
      {{if $owner_id}}
        {{mb_label class=CSalutation field=object_class}}
      {{else}}
        {{mb_label class=CSalutation field=owner_id}}
      {{/if}}
      <input type="search" onkeyup="Salutation.filterContent(this, '._salutation');"
             onsearch="Salutation.onFilterContent(this, '._salutation');" />
    </th>

    <th>{{mb_label class=CSalutation field=starting_formula}}</th>
    <th>{{mb_label class=CSalutation field=closing_formula}}</th>
  </tr>

  {{foreach from=$salutations key=_function_id item=_salutations}}
    <tr>
      <th colspan="4" class="section">
        {{mb_include module=mediusers template=inc_vw_function function=$functions[$_function_id]}}
      </th>
    </tr>
    {{foreach from=$_salutations item=_salutation}}
      <tr class="_salutation">
        <td style="text-align: center;">
          <button type="button" class="edit notext"
                  onclick="Salutation.editSalutation('{{$_salutation->_id}}', {onClose: function() { Salutation.reloadList(); }});">
            {{tr}}Edit{{/tr}}
          </button>
        </td>

        <td>
          {{if $owner_id}}
            {{mb_value object=$_salutation field=object_id tooltip=true}}
          {{else}}
            {{mb_value object=$_salutation field=owner_id tooltip=true}}
          {{/if}}
        </td>

        <td class="text compact">
          {{mb_value object=$_salutation field=starting_formula}}
        </td>

        <td class="text compact">
          {{mb_value object=$_salutation field=closing_formula}}
        </td>
      </tr>
    {{/foreach}}
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">{{tr}}CSalutation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
