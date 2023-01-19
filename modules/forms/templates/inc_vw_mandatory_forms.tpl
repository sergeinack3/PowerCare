{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>{{tr}}CSejour{{/tr}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$objects item=_object}}
    {{assign var=_object_guid value=$_object->_guid}}
    <tr>
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_object->_guid}}');">
          {{$_object}}
        </span>
      </td>

      <td style="text-align: center;">
        <button type="button" class="search notext compact"
                onclick="ExObject.showMandatoryExClasses('{{$_object->_class}}', '{{$_object->_id}}', {onClose: function() { getForm('search-mandatory-forms').onsubmit(); }});">
          {{tr}}CExClass-Mandatory object|pl{{/tr}}
        </button>

        {{mb_include module=system template=inc_vw_counter_tip count=$ex_events.$_object_guid|@count}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">
        {{tr}}CSejour.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>