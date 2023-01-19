{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector"}}

<script>
  changePage = function(page) {
    $V(getForm('filterFrm').page, page);
  }

  Main.add(function() {
    getForm('filterMovements').onsubmit();
  });
</script>

<form name="filterMovements" action="?" method="get"
      onsubmit="return onSubmitFormAjax(this, null, 'list_movements')">
  <input type="hidden" name="m" value="hl7" />
  <input type="hidden" name="a" value="ajax_list_movements" />
  <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()"/>

  <table class="main layout">
    <tr>
      <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

      <td>
        <table class="form">
          <tr>
            <th>{{mb_label object=$movement field=start_of_movement}}</th>
            <td class="text">
              {{mb_field object=$movement field=_date_min register=true form="filterMovements"
              prop=dateTime onchange="\$V(this.form.elements.start, 0)"}}
              <b>&raquo;</b>
              {{mb_field object=$movement field=_date_max register=true form="filterMovements"
              prop=dateTime onchange="\$V(this.form.elements.start, 0)"}}
            </td>
            <th>
            </th>
            <td></td>
          </tr>
          <tr>
            <th class="narrow">{{mb_label object=$movement field="sejour_id"}}</th>
            <td>
              <input name="object_id" class="ref" value="{{$movement->sejour_id}}" />
              <input type="hidden" name="object_class" value="CSejour" />
              <button class="search notext button" type="button" onclick="ObjectSelector.initFilter()">{{tr}}Search{{/tr}}</button>
              <script>
                ObjectSelector.initFilter = function(){
                  this.sForm     = "filterMovements";
                  this.sId       = "object_id";
                  this.sClass    = "object_class";
                  this.onlyclass = "false";
                  this.pop();
                }
              </script>
            </td>

            <th class="narrow">{{mb_label object=$movement field="movement_type"}}</th>
            <td>
              <select class="str" name="movement_type">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>

                {{assign var=values value='|'|explode:$movement->_specs.movement_type->list}}
                {{foreach from=$values item=_value}}
                  <option value="{{$_value}}" {{if $movement_type == $_value}}selected{{/if}}>
                    {{tr}}CMovement.movement_type.{{$_value}}{{/tr}} ({{$_value}})
                  </option>
                {{/foreach}}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan="4">
              <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>

<div id="list_movements"> </div>