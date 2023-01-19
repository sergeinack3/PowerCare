{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Main.add(function(){
  MbObject.list("{{$object_class}}", {{$columns|@json}}, '{{$group_id}}');

  var objectGUID = "{{$object_guid}}";
  var parts = objectGUID.split(/-/);

  if (parts[1] == "0") {
    var hash = Url.parse().fragment;
    if (hash) {
      var match = hash.match(/edit\.(.+)/);
      if (match) {
        MbObject.edit(match[1]);
        return;
      }
    }
  }

  MbObject.edit(objectGUID);
});
</script>

<table class="main layout">
  <tr>
    <td id="tag-tree" style="{{if $tree_width}} width: {{$tree_width}}; {{else}} width: 30%; max-width: 60%; {{/if}} {{if $hide_tree}} display: none; {{/if}}"> </td>
    {{if !$hide_tree}}
      <td class="separator" onclick="MbObject.toggleColumn(this, $(this).previous())"></td>
    {{/if}}
    <td id="object-editor">&nbsp;</td>
  </tr>
</table>
