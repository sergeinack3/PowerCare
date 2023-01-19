{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>

Main.add(function(){
  Calendar.regField(getForm("updateChargeSoins").datetime);
}); 

</script>

<table class="main tbl me-no-hover">
  <tr>
    <th class="title" colspan="{{math equation=(2*x)+3 x=$datetimes|@count}}">
      <form name="updateChargeSoins" action="?" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="{{$actionType}}" value="{{$action}}" />

        <span style="float: right;">
          <label>
            <input type="checkbox" name="show_cost_view" {{if $show_cost}}checked="checked"{{/if}}/ onchange="$V(this.form.show_cost, this.checked ? 1 : 0); this.form.submit()"> Coût
            <input type="hidden" name="show_cost" value="{{$show_cost}}" />
          </label> 
        </span>
				        
        {{tr}}soins-charge en soins title{{/tr}}
        <select name="service_id" onchange="this.form.submit();">
          <option value="">&mdash; {{tr}}CAffectation-service_id-court{{/tr}}</option>
          {{foreach from=$services item=_service name=list_services}}
            <option value="{{$_service->_id}}" {{if $_service->_id == $service_id}}selected{{/if}}>{{$_service}}</option>
          {{/foreach}}
        </select>
          {{tr}}date.From_long{{/tr}}
        <input type="hidden" name="datetime" class="dateTime" value="{{$datetime}}" onchange="this.form.submit()" />
        {{tr}}while{{/tr}}
        <input type="text" name="nb_periods" class="num" size="2" value="{{$nb_periods}}" onchange="this.form.submit()" />
        
        <select name="period" onchange="this.form.submit()" >
          <option value="hour" {{if $period == "hour"}} selected="true" {{/if}}>{{tr}}Hour{{/tr}}s</option>
          <option value="day"  {{if $period == "day" }} selected="true" {{/if}}>{{tr}}Day{{/tr}}s</option>
          <option value="week" {{if $period == "week"}} selected="true" {{/if}}>{{tr}}Week{{/tr}}s</option>
        </select>

      </form>
    </th>
  </tr>

  <tr>
    <td rowspan="2" class="me-border-right"></td>

    {{foreach from=$datetimes item=_datetime}}
    {{mb_include template=inc_period_table_cell}}
    {{/foreach}}

    <th class="me-text-align-center" colspan="2">{{tr}}Total{{/tr}}</th>
  </tr>
  <tr>
    {{foreach from=$datetimes item=_datetime}}
      <td class="narrow me-border-right me-text-align-center">{{tr}}soins-planned-charge{{/tr}}</td>
      <td class="narrow me-border-right me-text-align-center">{{tr}}soins-actual-charge{{/tr}}</td>
    {{/foreach}}
      <td class="narrow me-border-right me-text-align-center">{{tr}}soins-planned-charge{{/tr}}</td>
      <td class="narrow me-border-right me-text-align-center">{{tr}}soins-actual-charge{{/tr}}</td>
  </tr>
  {{if $service_id !== null}}

  {{foreach from=$charge key=_sejour_id item=_indices_by_datetime}}
	  {{assign var=sejour value=$sejours.$_sejour_id}}
	  <tr>
	  	<td class="text me-border-right narrow">
        <strong onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
          {{$sejour->_ref_patient}}  
        </strong>
      </td>
  		{{foreach from=$_indices_by_datetime key=_datetime item=_ressources}}
  		  <td class="{{if $sejour->entree > $_datetime || $_datetime > $sejour->sortie}}arretee {{/if}}me-border-right" >
  			  {{mb_include template=inc_detail_ressources list_ressources=$_ressources total=0}}
  			</td>
        <td class="{{if $sejour->entree > $_datetime || $_datetime > $sejour->sortie}}arretee {{/if}}realisee me-border-right" >
  			  {{mb_include template=inc_detail_ressources list_ressources=$charge_realisee.$_sejour_id.$_datetime total=0}}
        </td>
  	  {{/foreach}}
      <th class="me-text-align-right me-border-right">
        {{mb_include template=inc_detail_ressources list_ressources=$total_sejour.$_sejour_id total=1}}
      </th>
      <th class="me-text-align-right realisee">
        {{mb_include template=inc_detail_ressources list_ressources=$total_sejour_realisee.$_sejour_id total=1}}
      </th>
		</tr>
	{{foreachelse}}
	<tr>
		<td class="empty" colspan="{{math equation=x+1 x=$datetimes|@count}}">
			{{tr}}CSejour.none{{/tr}}
		</td>
	</tr>
	{{/foreach}}
  {{else}}
    <tr>
      <td class="empty" colspan="{{math equation=x+1 x=$datetimes|@count}}">
        {{tr}}CSejour.none{{/tr}}
      </td>
    </tr>
  {{/if}}

  <tr>
    <td></td>

    {{foreach from=$datetimes item=_datetime}}
    {{mb_include template=inc_period_table_cell}}
    {{/foreach}}

    <th class="me-text-align-center" colspan="2">{{tr}}Total{{/tr}}</th>
  </tr>

  {{if $service_id !== null}}
  <tr>
    <th class="me-border-right">{{tr}}Total{{/tr}}</th>
  {{foreach from=$total_datetime key=_date_time item=_total}}
    <th class="me-border-right">
      {{mb_include module=soins template=inc_detail_ressources list_ressources=$_total total=0}}
    </th>
    <th class="realisee me-border-right">
      {{mb_include module=soins template=inc_detail_ressources list_ressources=$total_datetime_realisee.$_date_time total=0}}
    </th>
  {{/foreach}}
    <th class="title me-border-right">
        {{mb_include module=soins template=inc_detail_ressources list_ressources=$total total=1}}
    </th>
    <th class="realisee title me-border-right">
        {{mb_include module=soins template=inc_detail_ressources list_ressources=$total_realisee total=1}}
    </th>
  </tr>
  {{/if}}
</table>  