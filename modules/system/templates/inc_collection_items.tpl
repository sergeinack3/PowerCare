{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=back_spec value=$object_select->_backSpecs.$back_name}}

<table class="main tbl" id="table_backs_{{$back_name}}">
<tr>
  <td colspan="3">
    {{mb_include module=system template=inc_pagination total=$counts.$back_name current=$start step=50
      change_page="ObjectNavigation.changePage" change_page_arg=$change_page_arg}}
  </td>
</tr>

<!-- Entête des résultats -->
<tr>
  <th class="category" colspan="3">
    {{tr}}{{$back_spec->_initiator}}-back-{{$back_name}}{{/tr}}
    {{if $object_select->_count.$back_name}}
      ( x {{$object_select->_count.$back_name}})
    {{/if}}
  </th>
</tr>

{{if !$object_select->_backSpecs[$back_name]->_notNull}}
  <tr>
    <td></td>
    <td></td>
    <td>
      <button class="tick"
              onclick="$('table_backs_{{$back_name}}').select('button.nav_button_cancel').invoke('enable');">
        {{tr}}mod-system-active-delete{{/tr}}
      </button>
    </td>
  </tr>
{{/if}}

<!-- Résultats -->
{{foreach from=$object_select->_back.$back_name key=_key_test item=back_ref}}
  <tr>
    <td class="text" style="vertical-align: top;">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$back_ref->_guid}}')">
        {{$back_ref->_view}}
      </span>
      <br/>
    </td>

    <td class="narrow">
      <button type="button" class="link"
              onclick="ObjectNavigation.openModalObject('{{$back_ref->_class}}', '{{$back_ref->_id}}')">
        {{tr}}mod-system-object-nav-object-link{{/tr}}
      </button>
    </td>

    {{if !$object_select->_backSpecs[$back_name]->_notNull}}
      <td class="narrow">
        <form name="delete_back_ref_{{$back_ref->_class}}_{{$back_ref->_id}}" method="post"
              onsubmit="return onSubmitFormAjax(this, function() {ObjectNavigation.url_active.refreshModal();})">
          <input class="hidden" name="m" value="{{$m}}"/>
          <input class="hidden" name="dosql" value="do_nav_delete_back_ref"/>
          <input class="hidden" name="back_ref_field" value="{{$object_select->_backSpecs[$back_name]->field}}"/>
          <input class="hidden" name="back_ref_id" value="{{$back_ref->_id}}"/>
          <input class="hidden" name="back_ref_class" value="{{$back_ref->_class}}"/>

          <button type="submit" class="cancel nav_button_cancel" disabled>
            {{tr}}Delete{{/tr}}
          </button>
        </form>
      </td>
    {{/if}}

  </tr>
{{/foreach}}
</table>
