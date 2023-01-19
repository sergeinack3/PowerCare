{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=id_form_replacement}}
<script>
  Main.add(function(){
    var oForm = getForm("editReplacement-{{$id_form_replacement}}");
    if($V(oForm.replacer_id)){
      Planification.refreshReplacerPlanning($V(oForm.replacer_id));
    }
  });

  onSubmitReplacement = function(form, sejour_id, conge_id, type) {
    return onSubmitFormAjax(form, { onComplete: function() {
        Planification.refreshReplacement(sejour_id, conge_id, type);
        Planification.refreshlistSejour(sejour_id, type);
      if (type == 'kine'       ) Planification.refreshlistSejour('','reeducateur');
      if (type == 'reeducateur') Planification.refreshlistSejour('','kine'       );
      if (type == 'reeducateur') $('replacement-reeducateur').update('');
    } } );
  };

  selectPraticien = function (element2, element) {
    // Autocomplete des users
    var url = new Url("mediusers", "ajax_users_autocomplete");
    url.addParam("input_field", element.name);
    url.addParam("edit", 0);
    url.autoComplete(element, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      callback: function (input, queryString) {
        var form = getForm("editReplacement-{{$id_form_replacement}}");
        if (form._limit_search.checked) {
          return queryString + "&function_id="+'{{$user->function_id}}';
        }
        return queryString;
      },
      afterUpdateElement: function(field, selected) {
        if ($V(element) == "") {
          $V(element, selected.down('.view').innerHTML);
        }
        var id = selected.getAttribute("id").split("-")[2];
        $V(element2, id);
      }
    });
  };
</script>

<table class="tbl">
  <tr>
    <th class="title text" colspan="4">
      {{tr}}ssr-reeduc_fct{{/tr}}
      {{mb_include module=mediusers template=inc_vw_function function=$user->_ref_function}}
      <br />
      {{if $sejour_id}} 
        - {{$sejour->_ref_patient}}
      {{else}}
        {{tr}}ssr-many_sejour{{/tr}}
      {{/if}}
    </th>
  </tr> 
  <tr>
    <th>{{mb_title class=CEvenementSSR field=sejour_id    }}</th>
    <th>{{mb_title class=CEvenementSSR field=therapeute_id}}</th>
    <th>{{tr}}CEvenementSSR|pl{{/tr}}</th>
    <th>{{tr}}CBilanSSR-planification{{/tr}}</th>
  </tr>
  
  {{foreach from=$evenements_counts key=_sejour_id item=_counts_by_sejour}}
  <tbody class="hoverable">
    
  {{foreach from=$_counts_by_sejour key=therapeute_id item=_count name=therapeutes}}
    {{assign var=_sejour value=$sejours.$_sejour_id}}
    <tr {{if array_key_exists($_sejour->_id, $sejours)}} class="selected" {{/if}} >
      {{if $smarty.foreach.therapeutes.first}} 
      <td rowspan="{{$_counts_by_sejour|@count}}" class="text">
        {{if !$sejour_id}}
          {{$_sejour->_ref_patient}}
          <br />
        {{/if}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
          {{mb_include module=system template=inc_interval_date from=$_sejour->entree to=$_sejour->sortie}}
        </span>
      </td>
      {{/if}}
      {{assign var=technicien value=$_sejour->_ref_bilan_ssr->_ref_technicien}}
      <td>
        {{if $technicien->kine_id == $therapeute_id}}
        <strong>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$therapeutes.$therapeute_id}} /
          {{mb_value object=$technicien field=plateau_id}}
          </strong>
        {{else}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$therapeutes.$therapeute_id}}
        {{/if}}
        </td>
      <td style="text-align: center;">{{$_count}}</td>
      {{if $smarty.foreach.therapeutes.first}}
        <td class="button" rowspan="{{$_counts_by_sejour|@count}}">
          <button type="button" class="search notext"
                  onclick="Planification.showPlanificationPatient('{{$_sejour->_id}}');">
            {{tr}}CBilanSSR-planification{{/tr}}
          </button>
        </td>
      {{/if}}
    </tr>
  {{/foreach}}

  {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}None{{/tr}}</td>
    </tr>
  {{/foreach}}
  </tbody>
</table>

<form name="editReplacement-{{$id_form_replacement}}" action="?" method="post" onsubmit="return onSubmitReplacement(this, '{{$sejour_id}}','{{$conge->_id}}','{{$type}}');">
        
  <input type="hidden" name="m" value="ssr" />
  {{if $type == "kine"}}
    <input type="hidden" name="dosql" value="do_replacement_aed" />
    {{mb_key object=$replacement}}
  {{else}}
    <input type="hidden" name="dosql" value="do_transfert_ssr_multi_aed" />
  {{/if}}
  <input type="hidden" name="del" value="0" />
  
  {{if $sejour_id}} 
    {{mb_field object=$replacement field=sejour_id hidden=1}}
  {{else}}
    {{assign var=sejours_ids value=$sejours|@array_keys}}
    <input type="hidden" name="sejour_ids" value="{{"-"|implode:$sejours_ids}}" />
  {{/if}}
  
  {{* Prop definition cannot be ref due to pseudo plage *}}
  {{mb_field object=$replacement field=conge_id hidden=1 prop=""}}

  <table class="form">
    <tr>
      {{if $type == "kine"}}
        {{if $replacement->_id}}
          <th class="text title modify" colspan="2">
            {{mb_include module=system object=$replacement template=inc_object_idsante400}}
            {{mb_include module=system object=$replacement template=inc_object_history   }}
            {{mb_include module=system object=$replacement template=inc_object_notes     }}
            {{tr}}CReplacement-_modify{{/tr}}<br />'{{$sejour}}'
          </th>
        {{else}}
         <th class="text title me-th-new" colspan="2">{{tr}}CReplacement-create{{/tr}}</th>
        {{/if}}
      {{else}}
        <th class="title" colspan="2">
          {{tr var1=$transfer_count}}ssr-transfert_evts{{/tr}}
        </th>
      {{/if}}
    </tr>
    
    <tr>
      <td colspan="2">
        <table class="tbl">
          <tr>
            {{foreach from=$transfer_counts key=_day item=_count}}
              <th>{{$_day|date_format:"%a"}}<br />{{$_day|date_format:"%d"}}</th>
            {{/foreach}}
          </tr>
          <tr>
            {{foreach from=$transfer_counts key=_day item=_count}}
              <td style="text-align: center;">{{$_count|ternary:$_count:"-"}}</td>
            {{/foreach}}
          </tr>
        </table>
      </td>
    </tr>
    
    {{if $type == "kine"}}
      <tr>
        <th>{{mb_label object=$replacement field=replacer_id}}</th>
        <td>
          {{if $replacement->_id}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$replacement->_ref_replacer}}
          {{else}}
            <script>
              Main.add(function () {
                var form = getForm("editReplacement-{{$id_form_replacement}}");
                selectPraticien(form.replacer_id, form.replacer_id_view);
              });
            </script>
            {{mb_field object=$replacement field="replacer_id" hidden=hidden value=""
              onchange="Planification.searchConflitsRemplacement(this.value, '`$conge->_id`', '`$sejour_id`');"}}
            <input type="text" name="replacer_id_view" class="autocomplete" style="width:15em;" value=""  placeholder="&mdash; Choisir un utilisateur"/>
            <input name="_limit_search" type="checkbox" checked title="Limiter la recherche sur la fonction" />
          {{/if}}
        </td>
      </tr>
      <tr>
        <td colspan="2" class="button">
          {{if $replacement->_id}}
            <button class="trash" type="button" onclick="confirmDeletion(this.form, {
              typeName: 'le remplacement ',
              objName: '{{$replacement->_view|smarty:nodefaults|JSAttribute}}',
              ajax: 1 } )">
              {{tr}}Delete{{/tr}}
            </button>
          {{else}}
            <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <div id="warning_conflit_remplacement"></div>
        </td>
      </tr>
    {{/if}}
    
    {{if $type == "reeducateur"}}
      <tr>
        <td colspan="2" class="button">
          <script>
            Main.add(function () {
              var form = getForm("editReplacement-{{$id_form_replacement}}");
              selectPraticien(form.replacer_id, form.replacer_id_view);
            });
          </script>
          {{mb_field object=$replacement field="replacer_id" hidden=hidden value="" onchange="Planification.refreshReplacerPlanning(this.value);"}}
          <input type="text" name="replacer_id_view" class="autocomplete" style="width:15em;" value=""  placeholder="&mdash; Choisir un utilisateur"/>
          <input name="_limit_search" type="checkbox" checked title="Limiter la recherche sur la fonction" />
        </td>
      </tr>
      <tr>
        <td colspan="2" class="button">
           <button type="submit" class="submit">{{tr}}CEvenementSSR-transfert{{/tr}}</button>
        </td>
      </tr>
    {{/if}}
  </table>
</form>

<div id="replacer-planning"></div>
