{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=alert}}
<script>
  function compteurAlerte(level, prescription_guid) {
    var url = new Url("prescription", "ajax_count_alerte", "raw");
    url.addParam("prescription_guid", prescription_guid);
    url.requestJSON(function(count) {
      var span_ampoule = $('span-icon-alert-'+level+'-'+prescription_guid);
      if (count[level]) {
        span_ampoule.down('span.countertip').innerHTML = count[level];
      }
      else {
        span_ampoule.down('img').remove();
        span_ampoule.remove();
      }
    });
  }

printOffline = function(element) {
  var elements = [element];
  
  $$('.modal-view').each(function(modal){
    var id = modal.id;
    var tab = window["tab-"+id];
    var sejour_id = id.match(/(\d+)/)[1];
    var sejour_guid = 'CSejour-'+sejour_id;
    
    modal.show();
    $("planning-"+sejour_id).show();
    
    $(sejour_guid).down('.week-container').setStyle({height: '800px' });
    window['planning-'+sejour_guid].updateEventsDimensions();
    
    elements.push(
      modal.down(".modal-title"), 
      tab
    );
    
    modal.hide();
    $("planning-"+sejour_id).hide();
  });
  
  Element.print(elements);
}
</script>

<table id="sejours-ssr" class="tbl">
  <tr>
    <th class="title" colspan="11">
      {{if @$offline}}
        <button class="print not-printable" style="float: right;" onclick="printOffline($(this).up('table'))">{{tr}}Print{{/tr}}</button>
      {{/if}}
      
      <span style="text-align: left">
        ({{$sejours|@count}}) 
      </span>
      {{tr}}{{$m}}-sejour_{{$m}}|pl{{/tr}} {{$date|date_format:$conf.longdate}}
      
      {{if !$dialog && !@$offline}}
        <form name="selDate" action="?" method="get">
          <input type="hidden" name="m" value="{{$m}}" />
          <script>
            Main.add(function () {
              Calendar.regField(getForm("selDate").date, null, { noView: true } );
            });
          </script>
          <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
        </form>
      {{/if}}
    </th>
  </tr>
  <tr>
    {{assign var=url value="?m=$m&$actionType=$action&dialog=$dialog"}}
    <th style="width:  8em;">{{mb_colonne class="CAffectation" field="lit_id" order_col=$order_col order_way=$order_way url=$url}}</th>
    <th style="width: 20em;">{{mb_colonne class="CSejour" field="patient_id" order_col=$order_col order_way=$order_way url=$url}}</th>
    <th class="narrow">
      <input type="text" size="6" class="not-printable" onkeyup="SejoursSSR.filter(this, 'sejours-ssr')" id="filter-patient-name" />
    </th>
    <th style="width:  5em;">{{mb_colonne class="CSejour" field="entree"     order_col=$order_col order_way=$order_way url=$url}}</th>
    <th style="width:  5em;">{{mb_colonne class="CSejour" field="sortie"     order_col=$order_col order_way=$order_way url=$url}}</th>

    <th style="width:  5em;">
      {{mb_colonne class="CSejour" field="service_id" order_col=$order_col order_way=$order_way url=$url}}
      
      {{if !$dialog && !@$offline && $order_col != "service_id"}}
        <br />
        <select name="service_id" onchange="$V(getForm('Filter').service_id, $V(this), true);">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if $_service->_id == $filter->service_id}}selected="selected"{{/if}}>
              {{$_service}}
            </option>
          {{/foreach}}
        </select>
      {{/if}}
    </th>

    {{if $m != "psy"}}
      <th style="width: 20em;">
        {{mb_colonne class="CSejour" field="libelle" order_col=$order_col order_way=$order_way url=$url}}
      </th>
    {{/if}}

    <th style="width: 12em;">
      {{mb_colonne class="CSejour" field="praticien_id" order_col=$order_col order_way=$order_way url=$url}}
      
      {{if $order_col != "praticien_id"}}
        {{mb_title class=CSejour field=praticien_id}} /
        {{mb_title class=CBilanSSR field=_prat_demandeur_id}}
      
        {{if !$dialog && !@$offline}}
        <br />
        <select name="praticien_id" onchange="$V(getForm('Filter').praticien_id, $V(this), true);">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$praticiens selected=$filter->praticien_id}}
        </select>
        {{/if}}
      {{/if}}
    </th>

    {{if $m != "psy"}}
    <th style="width: 16em;">
      {{mb_title class=CBilanSSR field=_kine_referent_id}} /
      {{mb_title class=CBilanSSR field=_kine_journee_id}}

      {{if !$dialog && !@$offline && $order_col != "_kine_referent_id" && $order_col != "kine_journee_id"}}
        <br />
        <select name="referent_id" onchange="$V(getForm('Filter').referent_id, $V(this), true);">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$kines selected=$filter->referent_id}}
        </select>
      {{/if}}
    </th>
    {{/if}}

    <th colspan="2" class="narrow">
      <label title="{{tr}}CEvenementSSR-title_cell{{/tr}}">{{tr}}CEvenementSSR-court{{/tr}}</label>
    </th>
  </tr>
  
  {{foreach from=$sejours item=_sejour}}
  {{assign var=sejour_id value=$_sejour->_id}}
  {{assign var=ssr_class value=""}}
  {{if !$_sejour->entree_reelle}}
    {{assign var=ssr_class value=ssr-prevu}}
  {{elseif $_sejour->sortie_reelle}}
    {{assign var=ssr_class value=ssr-termine}}
  {{/if}}

  <tr class="{{$ssr_class}}">
    <td class="text">
      {{if @$offline}}
        <button class="search notext not-printable" onclick="modalwindow = Modal.open($('modal-view-{{$_sejour->_id}}'));" style="float: left;"></button>
      {{/if}}
      {{assign var=affectation value=$_sejour->_ref_curr_affectation}}
      {{if $affectation->_id}}
        {{$affectation->_ref_lit}}
      {{/if}}
    </td>
    <td colspan="2" class="text">      
      {{if $conf.ssr.CPrescription.show_dossier_soins}}
      <button type="button" class="search" onclick="showDossierSoins('{{$sejour_id}}');" style="float: right;">Dossier</button>
      {{/if}}
      
      {{mb_include module=ssr template=inc_view_patient patient=$_sejour->_ref_patient
        link="?m=$m&tab=vw_aed_sejour_ssr&sejour_id=$sejour_id"
      }}
    </td>

    {{assign var=distance_class value=ssr-far}}
    {{if $_sejour->_entree_relative == "-1"}}
      {{assign var=distance_class value=ssr-close}}
    {{elseif $_sejour->_entree_relative == "0"}}
      {{assign var=distance_class value=ssr-today}}
    {{/if}}
    <td class="{{$distance_class}}">
      {{mb_value object=$_sejour field=entree format=$conf.date}}
      <div style="text-align: left; opacity: 0.6;">{{$_sejour->_entree_relative}}j</div>
    </td>

    {{assign var=distance_class value=ssr-far}}
    {{if $_sejour->_sortie_relative == "1"}}
      {{assign var=distance_class value=ssr-close}}
    {{elseif $_sejour->_sortie_relative == "0"}}
      {{assign var=distance_class value=ssr-today}}
    {{/if}}
    <td class="{{$distance_class}}">
      {{mb_value object=$_sejour field=sortie format=$conf.date}}
      <div style="text-align: right; opacity: 0.6;">{{$_sejour->_sortie_relative}}j</div>
    </td>
    
    <td style="text-align: center;">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
       {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour}}
      </span>

      {{assign var=bilan value=$_sejour->_ref_bilan_ssr}}
      <div class="opacity-60">
        {{if $_sejour->hospit_de_jour && $bilan->_demi_journees}}
          <img style="float: right;" title="{{mb_value object=$bilan field=_demi_journees}}" src="modules/ssr/images/dj-{{$bilan->_demi_journees}}.png" />
        {{/if}}
        {{if $_sejour->_ref_curr_affectation->_id}}
          {{mb_value object=$_sejour->_ref_curr_affectation field=service_id}}
        {{elseif $_sejour->service_id}}
          {{mb_value object=$_sejour field=service_id}}
        {{/if}}
      </div>
    </td>

    {{if $m != "psy"}}
      <td class="text">
        {{mb_include module=system template=inc_get_notes_image object=$_sejour mode=view float=right}}
        {{mb_value object=$_sejour field=libelle}}
        {{assign var=libelle value=$_sejour->libelle|upper|smarty:nodefaults}}
        {{assign var=color value=$colors.$libelle}}
        {{if $color->color}}
          <div class="motif-color" style="background-color: #{{$color->color}};" />
        {{/if}}
      </td>
    {{/if}}
    
    {{if $_sejour->annule}}
      <td colspan="4" class="cancelled">
        {{tr}}CSejour-{{$_sejour->recuse|ternary:"recuse":"annule"}}{{/tr}}
      </td>

    {{else}}
      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
        {{assign var=prat_demandeur value=$bilan->_ref_prat_demandeur}}
        {{if $prat_demandeur->_id}}
        <br />{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$prat_demandeur}}
        {{/if}}
      </td>

      {{if $m != "psy"}}
        <td class="text">
          {{assign var=kine_referent value=$bilan->_ref_kine_referent}}
          {{if $kine_referent->_id}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$kine_referent}}
            {{assign var=kine_journee value=$bilan->_ref_kine_journee}}
            {{if $kine_journee->_id != $kine_referent->_id}}
            <br/>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$kine_journee}}
            {{/if}}
          {{/if}}
        </td>
      {{/if}}

      <td colspan="2" style="text-align: center;">
        {{assign var=prescription value=$_sejour->_ref_prescription_sejour}}
        {{if $prescription && $prescription->_id}}
          {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
            {{assign var=prescription_guid value=$prescription->_guid}}
            <span id="span-icon-alert-medium-{{$prescription_guid}}">
              {{mb_include module=system template=inc_icon_alerts object=$prescription callback="function() {compteurAlerte('medium', '$prescription_guid')}" nb_alerts=$prescription->_count_alertes}}
            </span>
          {{else}}
            {{if $prescription->_count_fast_recent_modif}}
              <img src="images/icons/ampoule{{if in_array($_sejour->type, array("psy", "ssr"))}}_green{{/if}}.png" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')"/>
              {{mb_include module=system template=inc_vw_counter_tip count=$prescription->_count_fast_recent_modif}}
            {{/if}}
          {{/if}}
        {{/if}}
      </td>
    {{/if}}
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="11" class="empty">{{tr}}CSejour.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>
