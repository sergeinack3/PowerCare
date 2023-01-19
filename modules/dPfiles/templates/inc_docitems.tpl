{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  delDocItem = function(docitem_guid, context_class, tr) {
    window.docitems_guid[context_class].splice(window.docitems_guid[context_class].indexOf(docitem_guid), 1);
    tr.remove();
  };
</script>

<table class="tbl">
  {{foreach from=$docitems item=_docitems_by_class key=class}}
  <tr>
    <th>
      {{tr}}{{$class}}{{/tr}}
    </th>
  </tr>
  {{foreach from=$_docitems_by_class item=_docitem}}
  <tr>
    <td>
      <button type="button" class="trash notext" onclick="delDocItem('{{$_docitem->_guid}}', '{{$context_class}}', this.up('tr'));"></button>
      {{$_docitem}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty">
      {{tr}}{{$class}}.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
  {{/foreach}}
</table>