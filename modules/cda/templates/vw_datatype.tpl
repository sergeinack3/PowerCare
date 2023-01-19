{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cda script=ccda}}

<script>
  Main.add(function(){
    var tree = new TreeView("datatype-list");
    tree.collapseAll();
  });
</script>

<table class="main">
  <tr>
    <td style="width: 20%" id="datatype-list">
      <ul style="font-family: monospace;">
        {{foreach from=$listTypes item=_type}}
          <li>
            <a href="#1" onclick="Ccda.showxml('{{$_type}}')">{{$_type}}</a><br/>
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      <div id="xmltype-view" class="me-align-auto">
      </div>
    </td>
  </tr>
</table>