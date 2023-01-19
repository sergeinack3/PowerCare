{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="groupdomain{{$group_domain->_guid}}" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_group_domain_aed" />
  <input type="hidden" name="group_domain_id" value="{{$group_domain->_id}}" />
  <input type="hidden" name="domain_id" value="{{$domain->_id}}" />
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$group_domain}}
    
    <tr>
      <th>{{mb_label object=$group_domain field="group_id"}}</th>
      <td>{{mb_field object=$group_domain field="group_id" form="groupdomain`$group_domain->_guid`" autocomplete="true,1,50,true,true"}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$group_domain field="object_class"}}</th>
      <td>{{mb_field object=$group_domain field="object_class" typeEnum="radio"}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$group_domain field="master"}}</th>
      <td>{{mb_field object=$group_domain field="master"}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $group_domain->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        {{else}}
           <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr> 
  </table>
</form>