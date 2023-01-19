{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

function setClose(selClass,keywords,key,val){
  var oObject = {
    objClass : selClass,
    id : key,
    view : val,
    keywords : keywords
  }
  
  var oSelector = window.opener.ObjectSelector;
  
  if (oSelector) {
    oSelector.set(oObject);
  }
  else {
    window.opener.setObject(oObject);
  }
  window.close();
}
</script>

<form action="?" name="frmSelector" method="get" onsubmit="return checkForm(this)">

<input type="hidden" name="m" value="system" />
<input type="hidden" name="a" value="object_selector" />
<input type="hidden" name="dialog" value="1" />
<input type="hidden" name="onlyclass" value="{{$onlyclass}}" />
<input type="hidden" name="replacevalue" value="{{$replacevalue}}" />
{{if $onlyclass=='true'}}
<input type="hidden" name="selClass" value="{{$selClass}}" />
{{/if}}
<table class="form">
  <tr>
    <th class="title" colspan="2">{{tr}}common-Selection criteria{{/tr}}</th>
  </tr>
  <tr>
    <th><label for="selClass">Type d'objet</label></th>
    <td colspan="2">
    	{{if $onlyclass == 'true'}}
			  <strong>{{tr}}{{$selClass}}{{/tr}}</strong>
			{{else}}
	      <select class="notNull str" name="selClass">
	        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
	        {{foreach from=$classes key=_class item=_fields}}
	        <option value="{{$_class}}" 
	        	{{if $selClass == $_class}} selected="selected" {{/if}}
	        	{{if !$_fields|@count}} style="opacity: .6" {{/if}}
	        >	
	        	{{tr}}{{$_class}}{{/tr}}
	        </option>
	        {{/foreach}}
	       </select>
			 {{/if}}
    </td>
  </tr>

  <tr>
    <th>
    	<label for="keywords" title="Veuillez saisir un ou plusieurs mot clé">Mots Clés</label>
    </th>
    <td>
    	<input class="str" type="text" name="keywords" value="{{$keywords|stripslashes}}" />
    </td>
  </tr>

	{{if $selClass}}
  {{assign var=fields value=$classes.$selClass}}
  <tr>
    <td colspan="2" class="text">
      {{if $fields|@count}}
	      <div class="small-info">
	        Mots clés recherchés dans les champs suivants :
	        {{foreach from=$fields item=_field name=field}}
						{{mb_label class=$selClass field=$_field}}{{$smarty.foreach.field.last|ternary:'.':','}}
					{{/foreach}}
	      </div>
			{{else}}
	      <div class="small-warning">
	        <strong>Recherche par mot clés impossible</strong> : 
	        aucun champ de recherche pour ce type d'objet.
	        <br/>
	        Utilisez l'identifiant interne ci-dessous.
	      </div>
			{{/if}}
    </td>
  </tr>
	{{/if}}
  
  <tr>
    <th>
    	<label for="object_id" title="Identifiant interne de l'objet">Identifiant</label>
    </th>
    <td>
    	<input class="ref" type="text" name="object_id" value="{{$object_id}}" />
    </td>
  </tr>

  <tr>
    <td class="button" colspan="2">
    	<button class="search" type="submit">{{tr}}Search{{/tr}}</button>
    </td>
  </tr>
</table>
</form>

{{if $selClass}}
<table class="tbl">
  <tr>
    <th class="title" colspan="2">{{tr}}Results{{/tr}}</th>
  </tr>
  
  {{foreach from=$list item=_object}}
    <tr>
      <td>
      	<label onmouseover="ObjectTooltip.createEx(this, '{{$_object->_guid}}');">{{$_object}}</label>
      </td>     
      <td class="button narrow">
      	<button type="button" class="tick" onclick="setClose('{{$selClass}}', '{{$keywords|stripslashes|smarty:nodefaults|JSAttribute}}', {{$_object->_id}}, '{{$_object->_view|smarty:nodefaults|JSAttribute}}')">
      	  {{tr}}Select{{/tr}}
      	</button>
      </td>
    </tr>
	{{foreachelse}}
	  <tr>
	  	<td colspan="2" class="empty">
	  		{{tr}}{{$selClass}}.none{{/tr}}
	  	</td>
	  </tr>
  {{/foreach}}
</table>
{{/if}}