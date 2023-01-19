{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="new" onclick="editStockLocation(0)">
  {{tr}}CProductStockLocation-title-create{{/tr}}
</button>

<form name="edit_stock_location" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  {{mb_class object=$stock_location}}
  {{mb_key   object=$stock_location}}
  <input type="hidden" name="group_id" value="{{$host_group_id}}" />
  <input type="hidden" name="del" value="0" />
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$stock_location}}
    
    <tr>
      <th>{{mb_label object=$stock_location field=name}}</th>
      <td>{{mb_field object=$stock_location field=name}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$stock_location field=object_id}}</th>
      <td>
        {{if $stock_location->_id && ($stock_location->_back.group_stocks|@count || $stock_location->_back.service_stocks|@count)}}
          <div class="small-info">
            {{tr}}CProductStockLocation-stock-exist-error{{/tr}}
          </div>
        {{/if}}
        <select name="_type"
                {{if $stock_location->_id && ($stock_location->_back.group_stocks|@count || $stock_location->_back.service_stocks|@count)}}disabled="disabled"{{/if}}>
          <option value="" disabled="disabled"> &ndash; Choisir un type</option>
          {{foreach from=$types item=_type key=_label}}
            <optgroup label="{{$_label}}">
              {{foreach from=$_type item=_object}}
                <option value="{{$_object->_guid}}"
                        {{if $_object->_guid == $stock_location->_type}}selected="selected"{{/if}}>{{$_object}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock_location field=actif}}</th>
      <td>{{mb_field object=$stock_location field=actif}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock_location field=desc}}</th>
      <td>{{mb_field object=$stock_location field=desc}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock_location field=position}}</th>
      <td>{{mb_field object=$stock_location field=position increment=1 min=0 form="edit_stock_location"}}</td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        {{if $stock_location->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$stock_location->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $stock_location->_id}}
  <table class="main tbl">
    <tr>
      <th class="category" colspan="10">
        <button style="float: right;" class="print notext"
                onclick="new Url('dPstock','print_stock_location').addParam('stock_location_id','{{$stock_location->_id}}').popup()">
          {{tr}}Print{{/tr}}
        </button>
        Stocks à cet emplacement
      </th>
    </tr>
    {{foreach from=$stock_location->_back.group_stocks item=_stock}}
      <tr>
        <td>
          <strong onmouseover="ObjectTooltip.createEx(this, '{{$_stock->_guid}}')">
            {{$_stock}}
          </strong>
        </td>
        <td>{{$_stock->quantity}}</td>
        <td>{{mb_include module=stock template=inc_bargraph stock=$_stock}}</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CProductStockGroup.none{{/tr}}</td>
      </tr>
    {{/foreach}}

    {{foreach from=$stock_location->_back.service_stocks item=_stock}}
      <tr>
        <td>
          <strong onmouseover="ObjectTooltip.createEx(this, '{{$_stock->_guid}}')">
            {{$_stock}}
          </strong>
        </td>
        <td>{{$_stock->quantity}}</td>
        <td>{{mb_include module=stock template=inc_bargraph stock=$_stock}}</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CProductStockService.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}