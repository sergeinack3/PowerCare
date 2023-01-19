{{*
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("message-tab-hprimxml", true);
    var tree = new TreeView("message-hprimxml-tree");
    tree.collapseAll();
  });
</script>

<div id="message-hprimxml">
  <br/>
  <ul class="control_tabs" id="message-tab-hprimxml">
    <li><a href="#message-hprimxml-xml">XML</a></li>
    <li>
      <a href="#message-hprimxml-errors" {{if $treehprimxml.validate|@count == 0}}class="special"{{else}} class="wrong" {{/if}}>
        {{tr}}validation{{/tr}} XSD
      </a>
    </li>
  </ul>

  <div id="message-hprimxml-xml" style="display: none; height: 410px;" class="highlight-fill">
    {{$message|highlight:xml}}
  </div>

  <div id="message-hprimxml-errors" style="display: none;" class="me-no-align">
    <table class="tbl me-no-align me-no-box-shadow">
      <tr>
        <th>{{tr}}Result{{/tr}}</th>
      </tr>
      {{foreach from=$treehprimxml.validate item=_error}}
        {{if $_error !== "1"}}
          <tr>
            <td>
              {{$_error}}
            </td>
          </tr>
        {{/if}}
        {{foreachelse}}
        <tr>
          <td>
            {{tr}}Document valide{{/tr}}
          </td>
        </tr>
      {{/foreach}}
    </table>
  </div>
</div>
