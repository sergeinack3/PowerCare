{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=details ajax=$ajax}}
{{mb_script module=files script=file ajax=$ajax}}
<script>
  Main.add( function (){
    Control.Tabs.create.curry("tabs-owner", true);
    File.searchByFactory('{{$doc_class}}');
  });
</script>

<ul id="tabs-owner" class="control_tabs">
  <li><a href="#" onclick="File.controleTab('tab-group')">{{tr}}CGroups{{/tr}}</a></li>
  <li><a href="#" onclick="File.controleTab('tab-func')">{{tr}}CFunctions{{/tr}}</a></li>
  <li><a href="#" onclick="File.controleTab('tab-user')">{{tr}}CMediusers{{/tr}}</a> </li>
  {{if $doc_class != 'CFile'}}
  <li style="margin-left: auto;"">{{mb_label class="CCompteRendu" field=factory}} :
      {{mb_field class="CCompteRendu" field="factory" emptyLabel="All" onchange="File.searchByFactory('$doc_class', this.value)"}}</li>
  {{/if}}
</ul>
<div id="results_stats"></div>
