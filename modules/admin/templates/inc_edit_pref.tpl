{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-{{$preference->_guid}}" action="" method="post" onsubmit="return Preferences.onSubmit(this);">

<input type="hidden" name="m" value="admin" />
{{mb_class object=$preference}}
{{mb_key   object=$preference}}

<table class="form">
  {{mb_include module=system template=inc_form_table_header object=$preference}}
  
  <tr>
    <th>{{mb_label object=$preference field=user_id}}</th>
    <td>{{mb_value object=$preference field=user_id}}</td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$preference field=key}}</th>
    <td>{{mb_value object=$preference field=key}}</td>
  </tr>
    
  <tr>
    <th>{{mb_label object=$preference field=value}}</th>
    <td>{{mb_field object=$preference field=value}}</td>
  </tr>
    
  <tr>
    <td class="button" colspan="2">
      {{if $preference->_id}}
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        
        <button type="button" class="trash" onclick="Preferences.confirmDeletion(this.form);">
          {{tr}}Delete{{/tr}}
        </button>
      {{else}}
        <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
      {{/if}}
    </td>
  </tr>
</table>
