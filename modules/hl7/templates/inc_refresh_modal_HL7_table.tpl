{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hl7 script=tables_hl7v2 ajax=true}}
<table class="tbl">
  <tr>
    <th class="title" colspan="6">{{$table_description->description}}</th>
  </tr>
  <tr>
    <th style="width: 20px">&nbsp;</th>
    <th class="category" style="width: 18%">{{mb_title object=$table_entry field=code_mb_from}}</th>
    <th class="category" style="width: 18%">{{mb_title object=$table_entry field=code_hl7_to}}</th>

    <th class="category" style="width: 18%">{{mb_title object=$table_entry field=code_hl7_from}}</th>
    <th class="category" style="width: 18%">{{mb_title object=$table_entry field=code_mb_to}}</th>
    <th class="category">{{mb_title object=$table_entry field=description}}</th>
  </tr>
  {{foreach from=$table_entries item=_table_entry}}
    <tr>
      <td>
        {{if $_table_entry->user}}
          <form name="delTabEntry-{{$_table_entry->_id}}" action="?m=hl7" method="post" onsubmit="return onSubmitFormAjax(this, {
              onComplete : Tables_hl7v2.refreshModalTableHL7Submit.curry('{{$_table_entry->number}}') });">
            <input type="hidden" name="m" value="hl7" />
            <input type="hidden" name="@class" value="{{$_table_entry->_class}}" />
            <input type="hidden" name="del" value="1" />
            {{mb_key object=$_table_entry}}
            <button type="submit" class="trash notext compact">{{tr}}Delete{{/tr}}</button>
          </form>
        {{/if}}
      </td>
      <td class="disabled" id="test22">
        <form name="editTabEntryMbCodeFrom-{{$_table_entry->_id}}" action="?m=hl7" method="post"
              onsubmit="return onSubmitFormAjax(this, {
                  onComplete : Tables_hl7v2.refreshModalTableHL7Submit.curry('{{$_table_entry->number}}') });">
          <input type="hidden" name="m" value="hl7" />
          <input type="hidden" name="@class" value="{{$_table_entry->_class}}" />
          {{mb_key object=$_table_entry}}
          {{mb_field object=$_table_entry field="code_mb_from" size="10"}}
          <button type="submit" class="save notext compact">{{tr}}Save{{/tr}}</button>
        </form>
      </td>
      <td class="disabled">
        <form name="editTabEntryHL7CodeTo-{{$_table_entry->_id}}" action="?m=hl7" method="post"
              onsubmit="return onSubmitFormAjax(this, {
                  onComplete : Tables_hl7v2.refreshModalTableHL7Submit.curry('{{$_table_entry->number}}') });">
          <input type="hidden" name="m" value="hl7" />
          <input type="hidden" name="@class" value="{{$_table_entry->_class}}" />
          {{mb_key object=$_table_entry}}
          {{mb_field object=$_table_entry field="code_hl7_to" size="10"}}
          <button type="submit" class="save notext compact">{{tr}}Save{{/tr}}</button>
        </form>
      </td>
      <td class="disabled button">
        {{if !$_table_entry->user}}
          {{mb_value object=$_table_entry field="code_hl7_from"}}
        {{else}}
          <form name="editTabEntryHL7CodeFrom-{{$_table_entry->_id}}" action="?m=hl7" method="post"
                onsubmit="return onSubmitFormAjax(this, {
                    onComplete : Tables_hl7v2.refreshModalTableHL7Submit.curry('{{$_table_entry->number}}') });">
            <input type="hidden" name="m" value="hl7" />
            <input type="hidden" name="@class" value="{{$_table_entry->_class}}" />
            {{mb_key object=$_table_entry}}
            {{mb_field object=$_table_entry field="code_hl7_from" size="10"}}
            <button type="submit" class="save notext compact">{{tr}}Save{{/tr}}</button>
          </form>
        {{/if}}
      </td>
      <td class="disabled">
        <form name="editTabEntryMbCodeTo-{{$_table_entry->_id}}" action="?m=hl7" method="post"
              onsubmit="return onSubmitFormAjax(this, {
                  onComplete : Tables_hl7v2.refreshModalTableHL7Submit.curry('{{$_table_entry->number}}') });">
          <input type="hidden" name="m" value="hl7" />
          <input type="hidden" name="@class" value="{{$_table_entry->_class}}" />
          {{mb_key object=$_table_entry}}
          {{mb_field object=$_table_entry field="code_mb_to" size="10"}}
          <button type="submit" class="save notext compact">{{tr}}Save{{/tr}}</button>
        </form>
      </td>
      <td class="disabled text">
        {{if !$_table_entry->user}}
          {{mb_value object=$_table_entry field="description"}}
        {{else}}
          <form name="editTabEntryHL7Description-{{$_table_entry->_id}}" action="?m=hl7" method="post"
                onsubmit="return onSubmitFormAjax(this, {
                    onComplete : Tables_hl7v2.refreshModalTableHL7Submit.curry('{{$_table_entry->number}}') });">
            <input type="hidden" name="m" value="hl7" />
            <input type="hidden" name="@class" value="{{$_table_entry->_class}}" />
            {{mb_key object=$_table_entry}}
            {{mb_field object=$_table_entry field="description" size="25"}}
            <button type="submit" class="save notext compact">{{tr}}Save{{/tr}}</button>
          </form>
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CHL7v2TableEntry.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>