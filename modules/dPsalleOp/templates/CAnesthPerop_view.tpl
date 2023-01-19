{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="main tbl">
  <tr>
    <th class="title">
      {{mb_include module=system template=inc_object_idsante400 object=$object}}
      {{mb_include module=system template=inc_object_history    object=$object}}
      {{tr}}{{$object->_class}}{{/tr}}
    </th>
  </tr>
  <tr>
    <td>
      {{foreach from=$object->_specs key=prop item=spec}}
        {{mb_include module=system template=inc_field_view}}
      {{/foreach}}
    </td>
  </tr>
  <tr style="display: none;">
    <td class="button">
      <form name="gestesPerop{{$object->_guid}}" method="post">
        {{mb_key object=$object}}
        {{mb_class object=$object}}

        <button type="button" class="edit" onclick="SurveillancePerop.editEvenementPerop('{{$object->_guid}}', '{{$object->operation_id}}');">
          {{tr}}Edit{{/tr}}
        </button>

        <button type="button" class="trash" onclick="confirmDeletion(this.form, {
          typeName: 'l\'événement peroperatoire',
          objName: '{{$object->_shortview|smarty:nodefaults|JSAttribute}}',
          ajax: true},
          {onComplete: function() {
            if (window.reloadSurveillance) {
              var container    = $$('.surveillance-timeline-container')[0];
              var element_main = $$('div[data-graphguid=supervision-timeline-geste]')[0];
              container.retrieve('timeline').updateChildrenSelected(null, element_main);
            }
          }});">
          {{tr}}Delete{{/tr}}
        </button>
      </form>
    </td>
  </tr>
</table>
