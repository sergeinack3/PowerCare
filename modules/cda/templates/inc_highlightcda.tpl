{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("message-tab-cda", true);
    var tree = new TreeView("message-cda-tree");
    tree.collapseAll();
  });
</script>

<div id="message-cda">
  <br/>
  <ul class="control_tabs" id="message-tab-cda">
    <li><a href="#message-cda-tree">{{tr}}tree{{/tr}}</a></li>
    <li><a href="#message-cda-xml">XML</a></li>
    <li><a href="#message-cda-html">HTML</a></li>
    <li>
      <a href="#message-cda-errors" {{if $treecda.validate|@count == 0}}class="special"{{else}} class="wrong" {{/if}}>
        {{tr}}validation{{/tr}} XSD
      </a>
    </li>
    <li>
      <a href="#message-cda-errors-schematron" {{if $treecda.validateSchematron}}class="wrong"{{else}} class="special" {{/if}}>
        {{tr}}validation{{/tr}} schematron
      </a>
    </li>
  </ul>

  <div id="message-cda-tree">
    <ul class="hl7-tree">
      {{mb_include template=inc_tree_cda}}
    </ul>
  </div>

  <div id="message-cda-xml" style="display: none;">
  </div>

  <div id="message-cda-html" style="display: none;">
    <iframe id="cda-html" src="data:text/html;charset=utf-8, {{$html|smarty:nodefaults}}"></iframe>
  </div>

  <div id="message-cda-errors" style="display: none;" class="me-no-align">
    {{mb_include template="inc_highlightcda_validate"}}
  </div>

  <div id="message-cda-errors-schematron" style="display: none;" class="me-no-align">
    {{mb_include template="inc_highlightcda_validate_schematron"}}
  </div>
</div>