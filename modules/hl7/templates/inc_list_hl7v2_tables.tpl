{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hl7 script=tables_hl7v2 ajax=true}}
{{mb_include module=system template=inc_pagination total=$total_tables current=$page change_page='Tables_hl7v2.changePage' step='25' narrow=true}}

<table class="tbl me-striped">
  <tr>
    <th class="narrow button"></th>
    <th class="narrow">{{mb_title object=$table_description field=number}}</th>
    <th>{{mb_title object=$table_description field=description}}</th>
    <th class="narrow">{{mb_title object=$table_description field=_count_entries}}</th>
  </tr>

  {{foreach from=$tables item=_table}}
    <tr {{if $_table->number == $table_entry->number}}class="selected"{{/if}}>
      <td>
        <button class="edit notext" onclick="Tables_hl7v2.editTableDescription('{{$_table->_id}}', this);" title="{{tr}}Edit{{/tr}}">
          {{tr}}Edit{{/tr}}
        </button>
        <button class="lookup notext" onclick="Tables_hl7v2.loadEntries('{{$_table->number}}', this);" title="{{tr}}View{{/tr}}">
          {{tr}}View{{/tr}}
        </button>
        <a target="_blank" href="?m=hl7&a=download_table_svs&table_id={{$_table->_id}}&dialog=1&suppressHeaders=1&message=1"
           class="button modify notext"></a>

        {{if $_table->user}}
          <form name="delHL7TabDescription-{{$_table->_id}}" action="?m=hl7" method="post" onsubmit="return onSubmitFormAjax(this, Tables_hl7v2.loadTables);"
            <input type="hidden" name="m" value="hl7" />
            <input type="hidden" name="@class" value="{{$_table->_class}}" />
            <input type="hidden" name="del" value="1" />
            {{mb_key object=$_table}}
            <button type="submit" class="trash notext">{{tr}}Delete{{/tr}}</button>
          </form>
        {{/if}}
      </td>
      <td {{if !$_table->user}}class="disabled"{{/if}}>
        {{$_table->number|str_pad:4:'0':$smarty.const.STR_PAD_LEFT}}
      </td>
      <td class="text {{if !$_table->user}}disabled{{/if}}">{{$_table->description}}</td>
      <td {{if !$_table->user}}class="disabled"{{/if}}>{{$_table->_count_entries}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="4">{{tr}}CHL7v2TableDescription.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>