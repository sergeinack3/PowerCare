{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  createUsage = function(ressource_materielle_id) {
    var form = getForm("createUsageForm");
    $V(form.ressource_materielle_id, ressource_materielle_id);
    onSubmitFormAjax(form, function() {
      window.parent.Control.Modal.close();
      window.parent.reloadModal();
    });
  };
  
  Main.add(function() {
    var form = getForm("filterDate");
    Calendar.regField(form.date, null, {noView: true});
    // Redimensionnement du cadre noir représentant l'intervention courante
    var curr_interv = $("current_interv_planning");
    curr_interv.setStyle({"height": (parseInt(curr_interv.up("table").getHeight()) - 56)+ "px"});
  });
  
  redirectPlanning = function(date) {
    var url = new Url("bloc", "ajax_vw_planning_ressources");
    url.addParam("operation_id", '{{$operation->_id}}');
    url.addParam("besoin_ressource_id", '{{$besoin_ressource_id}}');
    url.addParam("usage_ressource_id", '{{$usage_ressource_id}}');
    url.addParam("type_ressource_id", '{{$type_ressource_id}}');
    url.addParam("dialog", 1);
    url.addParam("date", date);
    url.redirect();
  }
</script>

{{* Légende du planning *}}
<div id="legend" style="display: none">
  <table class="tbl">
    <tr>
      <th colspan="3">
        Légende
      </th>
    </tr>
    <tr>
      <td class="button">
        <div class="planning_ressource interv" style="width: 100%; height: 1.5em;margin: 0 !important;">Intervention</div>
      </td>
    </tr>
    <tr>
      <td class="button">
        <div class="planning_ressource besoin" style="width: 100%; margin: 0 !important;">Besoin</div>
      </td>
    </tr>
    <tr>
      <td class="button">
        <div class="planning_ressource usage" style="width: 100%; margin: 0 !important;">Ressource assignée</div>
      </td>
    </tr>
    <tr>
      <td class="button">
        <div class="planning_ressource usage_selected" style="width: 100%; margin: 0 !important;">Ressource assignée à l'intervention courante</div>
      </td>
    </tr>
    <tr>
      <td class="button">
        <div class="planning_ressource indispo" style="width: 100%; margin: 0 !important;">Ressource indisponible</div>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="3">
        <button type="button" class="close" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>

<form name="createUsageForm" method="post">
  <input type="hidden" name="m" value="bloc" />
  <input type="hidden" name="dosql" value="do_usage_ressource_aed" />
  <input type="hidden" name="usage_ressource_id" value="" />
  <input type="hidden" name="besoin_ressource_id" value="{{$besoin_ressource_id}}" />
  <input type="hidden" name="ressource_materielle_id" value="" />
</form>

{{if $ua->getCodeName() == "firefox" || $ua->getCodeName() == "msie"}}
  {{math equation=x/y x=90 y=$hours|@count assign=td_width}}
{{else}}
  {{math equation=x/y x=89 y=$hours|@count assign=td_width}}
{{/if}}


<div class="small-warning" id="tooltip_alert_indispo" style="display: none">
  Attention, cette ressource a un temps de réhabilitation qui va provoquer une possible indisponibilité pour l'intervention suivante.
</div>


<table class="tbl" style="table-layout: fixed;">
  <col style="width: 10%" />
  {{if !$operation->_id}}
    <tr>
      <td colspan="{{math equation=x+1 x=$hours|@count}}">
        <div class="small-warning">Attention, veuillez selectionner la date de l'intervention dans le calendrier</div>
      </td>
    </tr>
  {{/if}}
  <tr>
    <th colspan="{{math equation=x+1 x=$hours|@count}}" class="title">
      <button type="button" class="search" style="float: right;" onclick="Modal.open('legend')">Légende</button>
      <span>
      <a href="#1" onclick="redirectPlanning('{{$date_before}}')" style="display: inline;">&lt;&lt;&lt;</a>
      {{$date|date_format:$conf.longdate}}
      <form name="filterDate" method="get">
        <input type="hidden" name="m" value="bloc" />
        <input type="hidden" name="a" value="ajax_vw_planning_ressources" />
        <input type="hidden" name="dialog" value="1" />
        <input type="hidden" name="operation_id" value="{{$operation->_id}}" />
        <input type="hidden" name="type_ressource_id" value="{{$type_ressource_id}}" />
        <input type="hidden" name="besoin_ressource_id" value="{{$besoin_ressource_id}}" />
        <input type="hidden" name="date" class="date notNull" value="{{$date}}" onchange="this.form.submit()"/>
      </form>
      <a href="#1" onclick="redirectPlanning('{{$date_after}}')" style="display: inline;">&gt;&gt;&gt;</a>
      </span>
    </th>
  </tr>
  <tr>
    <th></th>
    {{foreach from=$hours item=_hour}}
      <th>{{$_hour|date_format:"%H"}}h</th>
    {{/foreach}}
  </tr>
  {{* Ligne des besoins non validés (pas d'usage) *}}
  <tr>
    {{assign var="div_height" value=10}}
    {{math equation=(x+8)*y x=$div_height y=$besoins|@count assign=td_height}}
    {{if $td_height == 0}}
      {{assign var=td_height value=18}}
    {{/if}}
    <th style="height: {{$td_height}}px !important">
      Non validés
    </th>

    {{foreach from=$hours item=_hour name=hours name=hours}}
      <td style="vertical-align: top">
        {{if $smarty.foreach.hours.first}}
          {{math equation=x*y x=$operation->_debut_offset y=$td_width assign=offset}}
          {{math equation=x*y x=$operation->_width y=$td_width assign=width}}

          {{if $width > 0}}
            <div class="planning_ressource interv" id="current_interv_planning"
              style="position: absolute; left: {{$offset}}%; width: {{$width}}%; margin-top: 3px"></div>
          {{/if}}

          {{assign var=margin_top value=5}}
          {{foreach from=$besoins item=_besoin key=ressource_id name=ressources}}
            {{math equation=x*y x=$_besoin->_debut_offset y=$td_width assign=offset}}
            {{math equation=x*y x=$_besoin->_width y=$td_width assign=width}}
            <div class="planning_ressource besoin" onmouseover="ObjectTooltip.createEx(this, 'COperation-{{$_besoin->operation_id}}')"
                 style="position: absolute; height: {{$div_height}}px; left: {{$offset}}%; width: {{$width}}%; margin-top: {{$margin_top}}px">
            </div>
            {{math equation=x+3+y x=$div_height y=$margin_top assign=margin_top}}
          {{/foreach}}
        {{/if}}
      </td>
    {{/foreach}}
  </tr>
  {{foreach from=$ressources item=_ressource key=ressource_id name=ressources}}
    {{assign var=height_th value=23}}
    {{if $ua->getCodeName() == "firefox"}}
      {{assign var=height_th value=28}}
    {{/if}}
    {{assign var=height_ressource value=$height_th}}
    {{if isset($usages_by_ressource.$ressource_id|smarty:nodefaults)}}
      {{math equation=x*y x=$usages_by_ressource.$ressource_id|@count y=$height_th assign=height_ressource}}
    {{/if}}
    <tr>
      <th style="height: {{$height_ressource}}px;" class="text">
        {{if isset($display_alert.$ressource_id|smarty:nodefaults)}}
          {{me_img src="warning.png" icon="warning" class="me-warning" style="float:left" onmouseover="ObjectTooltip.createDOM(this, 'tooltip_alert_indispo')"}}
        {{/if}}
        {{if $usage && !$usage_ressource_id}}
          <button type="button" class="tick notext" style="float: right;" onclick="createUsage('{{$ressource_id}}')"></button>
        {{/if}}
        {{$_ressource}}
      </th>
      {{foreach from=$hours item=_hour name=hours}}
        <td style="vertical-align: top;">
          {{if $smarty.foreach.hours.first}}
            {{*
              Dans la première case, on place les usages et les indisponibilités 
             *}}
            {{if isset($usages_by_ressource.$ressource_id|smarty:nodefaults)}}
              {{foreach from=$usages_by_ressource.$ressource_id item=_usage name=usage}}
                {{math equation=x*y x=$_usage->_debut_offset y=$td_width assign=offset}}
                {{math equation=x*y x=$_usage->_width y=$td_width assign=width}}
                {{math equation=x*y x=$smarty.foreach.usage.index y=2 assign=top}}
                <div class="planning_ressource usage{{if $_usage->_id == $usage_ressource_id}}_selected{{/if}}"
                     onmouseover="ObjectTooltip.createEx(this, 'COperation-{{$_usage->_ref_besoin->operation_id}}')"
                     style="position: absolute; left: {{$offset}}%; width: {{$width}}%; margin-top: {{$top}}em;"></div>
                {{math equation=x*y x=$_usage->_debut_offset_retablissement y=$td_width assign=offset_retablissement}}
                {{math equation=x*y x=$_usage->_width_retablissement y=$td_width assign=width_retablissement}}
                <div class="planning_ressource indispo"
                     style="position: absolute; left: {{$offset_retablissement}}%; width: {{$width_retablissement}}%; margin-top: {{$top}}em;"></div>
              {{/foreach}}
            {{/if}}            
            {{if isset($indispos_by_ressource.$ressource_id|smarty:nodefaults)}}
              {{foreach from=$indispos_by_ressource.$ressource_id item=_indispo}}
                {{math equation=x*y x=$_indispo->_debut_offset y=$td_width assign=offset}}
                {{math equation=x*y x=$_indispo->_width y=$td_width assign=width}}
                <div class="planning_ressource indispo"
                  style="position: absolute; left: {{$offset}}%; width: {{$width}}%;"></div>
              {{/foreach}}
            {{/if}}
          {{/if}}
        </td>
      {{/foreach}}
    </tr>
  {{/foreach}}
</table>