{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  function reloadChambres() {
    var oForm = document.FrmSelectService;
    var url = new Url;
    url.setModuleAction("dPrepas", "httpreq_vw_repas");
    url.addParam("service_id", oForm.service_id.value);
    url.addParam("date", "{{$date}}");
    url.addParam("type", oForm.type.value);
    url.requestUpdate('vwService');
  }

  Main.add(function () {
    Calendar.regField(getForm("changeDate").date, null, {noView: true});
    {{if $service_id}}
    reloadChambres();
    {{/if}}
  });
</script>

<table class="main">
  <tr>
    <th colspan="2" class="button">
      <form name="FrmSelectService" action="?m={{$m}}" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        
        <label for="service_id" title="Veuillez sélectionner un service">Service</label>
        <select name="service_id" onchange="reloadChambres();">
          <option value="">&mdash; Veuillez sélectionner un service</option>
          {{foreach from=$services item=curr_service}}
            {{assign var="validation" value=$curr_service->_ref_validrepas.$date.$type}}
            {{if $type && $validation->validationrepas_id && !$validation->modif}}
              {{assign var="classStyle" value="validation"}}
            {{elseif $type && $validation->validationrepas_id}}
              {{assign var="classStyle" value="modification"}}
            {{else}}
              {{assign var="classStyle" value=""}}
            {{/if}}
            <option class="{{$classStyle}}" value="{{$curr_service->service_id}}"
                    {{if $curr_service->service_id == $service_id}}selected="selected"{{/if}}>
              {{$curr_service->nom}}
            </option>
          {{/foreach}}
        </select>
        <label for="type" title="Veuillez sélectionner un type de repas">Type de repas</label>
        <select name="type" onchange="this.form.submit();">
          <option value="">&mdash; Veuillez sélectionner un type de repas</option>
          {{foreach from=$listTypeRepas item=curr_type}}
            <option value="{{$curr_type->typerepas_id}}" {{if $curr_type->typerepas_id == $type}}selected="selected"{{/if}}>
              {{$curr_type->nom}}
            </option>
          {{/foreach}}
        </select>
        pour le {{$date|date_format:$conf.longdate}}
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
      <br />
    </th>
  </tr>
  <tr>
    <td class="halfPane" id="vwService"></td>
    <td class="halfPane">
      <table class="tbl">
        <tr>
          <th colspan="4">Repas</th>
        </tr>
        
        {{foreach from=$listMenu item=curr_repas}}
          {{assign var="nbrows" value=0}}
          
          {{foreach from=$plats->_specs.type->_list item=curr_typePlat}}
            {{if $curr_repas.obj->$curr_typePlat}}
              {{assign var="nbrows" value=$nbrows+1}}
            {{/if}}
          {{/foreach}}
          
          {{if $nbrows}}
            <tr>
            <td rowspan="{{$nbrows}}">{{$curr_repas.total}}</td>
            <td class="text" rowspan="{{$nbrows}}">{{$curr_repas.obj->_view}}</td>
            {{assign var="nbligne" value=0}}
            {{foreach name="plat" from=$plats->_specs.type->_list item=curr_typePlat}}
              {{if $curr_repas.obj->$curr_typePlat}}
                {{if $nbligne!=0}}<tr>{{/if}}
                <td class="text">{{$curr_repas.obj->$curr_typePlat}}</td>
                <td>{{$curr_repas.detail.$curr_typePlat}}</td>
                {{if $nbligne==0 && !($nbrows!=$nbligne)}}</tr>{{/if}}
                
                {{assign var="nbligne" value=1}}
              {{/if}}
            {{/foreach}}
            </tr>
          {{/if}}
        {{/foreach}}
        
        {{if $listRemplacement|@count}}
          <tr>
            <th colspan="4">Remplacements</th>
          </tr>
          {{foreach from=$listRemplacement key=keyType item=curr_type}}
            <tr>
            <td rowspan="{{$curr_type|@count}}">{{$keyType}}</td>
            {{foreach from=$curr_type name="plat" item=curr_plat}}
              {{if !$smarty.foreach.plat.first}}<tr>{{/if}}
              <td colspan="2" class="text">{{$curr_plat.obj->nom}}</td>
              <td colspan="2" class="text">{{$curr_plat.nb}}</td>
              {{if !$smarty.foreach.plat.first && !$smarty.foreach.plat.last}}</tr>{{/if}}
            {{/foreach}}
            </tr>
          {{/foreach}}
        {{/if}}
      </table>
    </td>
  </tr>
</table>