{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form action="?" name="Filter" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="{{$a}}" />

	<table class="form">
		<tr>
			<th>
			  <label for="class" title="Veuillez sélectionner une classe">Choix de la classe</label>
			</th>

			<td>
			  <select name="class" onchange="submit();">
			    <option value="">&mdash; Toutes les classes</option>
					{{foreach from=$classes item=_class}}
			    <option value="{{$_class}}"{{if $class == $_class}} selected="selected"{{/if}}>
			    	{{$_class}} ({{tr}}{{$_class}}{{/tr}})
			    </option>
			    {{/foreach}}
			  </select>
			</td>

			<th>
			  <label for="show" title="Mode d'affichage">Afficher</label>
			</th>

			<td>
			  <select name="show" onchange="submit();">
          <option value="errors" {{if $show == "errors"}}selected="selected"{{/if}}>Les erreurs</option>
			    <option value="all"    {{if $show == "all"   }}selected="selected"{{/if}}>Tout</option>
			  </select>
			</td>

		</tr>
	</table>

</form>

{{if $error_count}}
<div class="small-warning">
	Attention, il reste <strong>{{$error_count}} erreur(s)</strong>
	dans la déclaration des <em>BackProps</em>, ce qui met en péril
	l'intégrité référentielle du système.
</div>
{{else}}
<div class="small-success">
  Félicitations, toutes les <em>BackProps</em> sont correctement déclarées,
	ce qui assure l'intégrité référentielle du système.
</div>
{{/if}}

<table class="tbl">
{{foreach from=$reports key=class item=_report}}
  <tr>
  	<th class="title" colspan="10">
    	{{$class}} ({{tr}}{{$class}}{{/tr}})
    </th>
  </tr>

	<tr>
	  <th>BackProp</th>
	  <th>Present</th>
	  <th>Wanted</th>
	  <th>BackName</th>
	</tr>

	{{assign var=style value="text-align: center; text-transform: uppercase; font-weight: bold;"}}
  {{foreach from=$_report key=backProp item=value}}
  <tr>
    <td>{{$backProp}}</td>
    {{if $value == "ok"}}
    <td class="ok" colspan="2" style="{{$style}}>{{$value}}">{{$value}}</td>
    {{/if}}

    {{if $value == "present"}}
    <td class="warning" style="{{$style}}">{{$value}}</td>
    <td />
    {{/if}}

    {{if $value == "wanted"}}
    <td />
    <td class="warning" style="{{$style}}">{{$value}}</td>
    {{/if}}

    <td>
    {{if $value != "wanted"}}
    {{$present.$class.$backProp}}
    {{/if}}
    </td>

  </tr>

	{{/foreach}}
{{/foreach}}
</table>
