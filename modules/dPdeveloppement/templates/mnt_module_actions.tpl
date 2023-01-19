{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

Documentation = {
  check: function(page) {
	  var url = new Url("dPdeveloppement", "ajax_check_documentation");
	  url.addParam("page", page);
	  url.requestUpdate(page);
  },
  checkAll: function() {
  	$$("td.page").each(function(element) { 
  		Documentation.check(element.id);
  	} );
  }
}


</script>

<button class="change" onclick="Documentation.checkAll()" >
  Check all documentation
</button>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>Locale</th>
    <th colspan="2">Documentation</th>
  </tr>

  {{foreach from=$modules item=module}}
  <tr>
    <th class="category" colspan="2">mod-{{$module->mod_name}}</th>
	  <td class="page" id="mod-{{$module->mod_name}}" />
	  <td class="narrow">
	  	<a class="button search notext" href="http://www.mediboard.org/public/mod-{{$module->mod_name}}">
	  		{{tr}}Link{{/tr}}
	  	</a>
	  </td>
  </tr>

	  {{foreach from=$module->_tabs item=tab}}
	  {{assign var=_tabs_info value=$tabs.$tab}}
		<tr>
		  <td>{{$tab}}</td>
		  <td class="{{mb_ternary test=$_tabs_info.locale value=ok other=warning}}">
		    {{$_tabs_info.locale|default:$_tabs_info.name}}
		  </td>
		  <td class="page" id="{{$_tabs_info.name}}" />
		  <td class="narrow">
		  	<a class="button search notext" href="http://www.mediboard.org/public/{{$_tabs_info.name}}">
		  		{{tr}}Link{{/tr}}
		  	</a>
		  </td>
		</tr>	
		{{/foreach}}
	{{/foreach}}
</table>

