{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=object_indexer ajax=1}}

<script>
  Main.add(function() {
    ViewPort.SetAvlHeight("container_keys", 1);
    ViewPort.SetAvlHeight("container_objects", 1);
  });
</script>

<table class="main layout">
  <thead>
    <tr>
      <th>{{tr}}CObjectIndexer-keys{{/tr}}</th>
      <th>{{tr}}CObjectIndexer-objects{{/tr}}</th>
    </tr>
  </thead>

  <tr>
    <td style="width: 200px;">
      <div id="container_keys">
        <table class="tbl">
          {{foreach from=$keys key=_key item=_count}}
            <tr>
              <td class="narrow">
                <button type="button" class="notext lookup" onclick="ObjectIndexer.displayObjects('{{$index_name}}', '{{$_key}}')"></button>
              </td>
              <td><span style="float: left;">{{$_key|emphasize:$tokens:'strong'}}</span> <span style="float: right;"><em>{{$_count}}</em></span></td>
            </tr>
          {{foreachelse}}
            <tr>
              <td class="empty">{{tr}}CObjectIndexer-keys.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
      </div>
    </td>
    <td>
      <div id="container_objects">
        {{mb_include module=system template=inc_list_indexer_objects}}
      </div>
    </td>
  </tr>
</table>