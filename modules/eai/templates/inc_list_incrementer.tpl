{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl main">
  <tr>
    <th class="category narrow"></th>
    <th class="category">{{mb_label object=$incrementer field="_view"}}</th>
    <th class="category">{{mb_label object=$incrementer field="pattern"}}</th>
    <th class="category">{{mb_label object=$incrementer field="range_min"}}</th>
    <th class="category">{{mb_label object=$incrementer field="range_max"}}</th>
  </tr>

  <tr>
    <td>
      <form name="editIncrementer" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
        <input type="hidden" name="m" value="dPsante400" />
        <input type="hidden" name="dosql" value="do_incrementer_aed" />
        <input type="hidden" name="incrementer_id" value="{{$incrementer->_id}}" />
        <input type="hidden" name="del" value="0" />

        <button type="button" class="edit notext"
                onclick="Domain.editIncrementer('{{$incrementer->_id}}', '{{$domain->_id}}')">{{tr}}Edit{{/tr}}</button>
        <button class="trash notext" type="button" onclick="confirmDeletion(this.form, {
          ajax:1,
          typeName:&quot;{{tr}}{{$incrementer->_class}}.one{{/tr}}&quot;,
          objName:&quot;{{$incrementer->_view|smarty:nodefaults|JSAttribute}}&quot;},
          { onComplete: function() {
          Domain.refreshListIncrementerActor('{{$domain->_id}}');
          Domain.refreshListDomains();
          Domain.refreshSuppressionIncrementerDomain('{{$domain->_id}}');
          }})">
          {{tr}}Delete{{/tr}}
        </button>
      </form>
    </td>
    <td>{{mb_value object=$incrementer field="_view"}}</td>
    <td>{{mb_value object=$incrementer field="pattern"}}</td>
    <td>{{mb_value object=$incrementer field="range_min"}}</td>
    <td>{{mb_value object=$incrementer field="range_max"}}</td>
  </tr>
</table>