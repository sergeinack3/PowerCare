{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(() => {
    let span_count = $('counter_{{$uid_unread}}');

    if (span_count) {
      span_count.update('{{$documents|@count}}');
    }
  });
</script>

<table class="tbl me-width-min-content">
    <tr>
        <th class="title narrow" colspan="2">
            {{tr}}List-of-file{{/tr}} ({{$documents|@count}})
        </th>
    </tr>
    {{foreach from=$documents item=_document}}
        <tr>
            <td>
                <div class="icon_fileview" onmouseover="ObjectTooltip.createEx(this, '{{$_document->_guid}}')" onclick="popFile('{{$object_class}}', '{{$object_id}}', '{{$_document->_class}}', '{{$_document->_id}}')">
                    {{$_document->_view}}
                </div>
            </td>
            <td>
                <button class="tick" title="{{tr}}Treat{{/tr}}"
                        onclick="File.readFile('{{$_document->_id}}', '{{$_document->_class}}', '{{$object_id}}', '{{$object_class}}', '{{$uid_unread}}')"></button>
            </td>
        </tr>
    {{foreachelse}}
        <tr>
            <td class="empty">{{tr}}CDocumentItem.none{{/tr}}</td>
        </tr>
    {{/foreach}}
</table>
