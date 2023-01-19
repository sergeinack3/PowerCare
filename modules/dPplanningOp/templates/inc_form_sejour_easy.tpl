{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=mode_easy           value=$conf.dPplanningOp.COperation.mode_easy}}

{{assign var=easy_cim10          value=$conf.dPplanningOp.CSejour.easy_cim10}}
{{assign var=easy_handicap       value=$conf.dPplanningOp.CPatient.easy_handicap}}
{{assign var=easy_service        value=$conf.dPplanningOp.CSejour.easy_service}}
{{assign var=required_uf_soins   value="dPplanningOp CSejour required_uf_soins"|gconf}}

{{assign var=easy_entree_sortie  value=$conf.dPplanningOp.CSejour.easy_entree_sortie}}
{{assign var=easy_atnc           value=0}}
{{assign var=required_atnc       value="dPplanningOp CSejour required_atnc"|gconf}}
{{assign var=easy_isolement      value=$conf.dPplanningOp.CSejour.easy_isolement}}
{{assign var=easy_chambre_simple value=0}}

{{if $conf.dPplanningOp.CSejour.easy_chambre_simple && "dPhospi prestations systeme_prestations"|gconf === "standard"}}
  {{assign var=easy_chambre_simple value=1}}
{{/if}}

{{if ("dPplanningOp CSejour fields_display show_atnc"|gconf && $conf.dPplanningOp.CSejour.easy_atnc) || $required_atnc}}
  {{assign var=easy_atnc           value=1}}
{{/if}}

{{assign var=infos_sejour      value=0}}
{{assign var=infos_admission   value=0}}

{{if $easy_cim10 || $easy_handicap || $easy_service || $required_uf_soins !== "no"}}
  {{assign var=infos_sejour    value=1}}
{{/if}}

{{if $easy_entree_sortie || $easy_atnc || $easy_isolement || $easy_chambre_simple}}
  {{assign var=infos_admission value=1}}
{{/if}}

{{if $mode_easy === "1col" || (!$infos_sejour && !$infos_admission)}}
  <div class="big-info">
    Ceci est le <strong>mode simplifié</strong> de planification d'intervention.<br/>
    Il est nécessaire de <strong>sélectionner un protocole</strong> pour créer une intervention.<br/>
    <em>Pour plus de paramètres vous pouvez passer en mode expert.</em>
  </div>
  {{mb_return}}
{{/if}}

{{mb_default var=_duree_prevue value=0}}
{{mb_default var=_duree_prevue_heure value=0}}

{{assign var=maternite_active value="0"}}
{{if "maternite"|module_active}}
  {{assign var=maternite_active value="1"}}
  {{if !$sejour->_id && $sejour->grossesse_id}}
    {{assign var=_duree_prevue value=$sejour->_duree_prevue}}
    {{if $_duree_prevue === ""}}
      {{assign var=_duree_prevue value=0}}
    {{/if}}
    {{assign var=_duree_prevue_heure value=$sejour->_duree_prevue_heure}}
  {{/if}}
{{/if}}

<form name="editSejourEasy" method="post">
  <table class="form">
    {{if $sejour->annule}}
      <tr>
        <th class="category cancelled" colspan="3">
          {{tr}}CSejour-annule{{/tr}}
          {{if $sejour->recuse == 1}}
            ({{tr}}CSejour.recuse.1{{/tr}})
          {{/if}}
        </th>
      </tr>
    {{/if}}

    {{if $infos_sejour}}
      <tr>
        <th class="category" colspan="3">

          {{if $sejour->_id}}
            {{mb_include module=system template=inc_object_idsante400 object=$sejour}}
            {{mb_include module=system template=inc_object_history    object=$sejour}}
            {{mb_include module=system template=inc_object_notes      object=$sejour}}

            <a class="action" style="float: right"  title="Modifier uniquement le sejour" href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$sejour->_id}}">
              {{me_img src="edit.png" alt="modifier" icon="edit" class="me-primary"}}
            </a>
          {{/if}}

          {{tr}}CSejour-msg-informations{{/tr}}
          {{if $sejour->_NDA}}
            {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
          {{/if}}
        </th>
      </tr>
    {{/if}}

    {{if $easy_cim10}}
      <tr>
        <th>{{mb_label object=$sejour field="DP"}}</th>
        <td colspan="2">
          <script>
            Main.add(function() {
              CIM.autocomplete(getForm("editSejourEasy").keywords_code, null, {
                limit_favoris: '{{$app->user_prefs.cim10_search_favoris}}',
                chir_id: $V(getForm('editSejourEasy').chir_id),
                field_type: 'dp',
                /* Permet de prendre en compte le type de séjour de façon dynamique */
                callback: function(input, queryString) {
                  var form = getForm("editSejour");
                  var sejour_type = 'mco';
                  if ($V(form.elements['type']) == 'ssr') {
                    sejour_type = 'ssr';
                  }
                  else if ($V(form.elements['type']) == 'psy') {
                    sejour_type = 'psy';
                  }
                  return queryString + "&sejour_type=" + sejour_type;
                },
                afterUpdateElement: function(input) {
                  $V(getForm("editSejourEasy").DP, input.value);
                }
              });
            });
          </script>

          <input type="text" name="keywords_code" class="autocomplete str code cim10" value="{{$sejour->DP}}" onchange="Value.synchronize(this, 'editSejour');" style="width: 12em" />
          <button type="button" class="cancel notext" onclick="$V(this.form.DP, '');">{{tr}}Cancel{{/tr}}</button>
          <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['DP']), $V(this.form.elements['chir_id']){{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, $V.curry(getForm('editSejour').elements['type']), 'dp'{{/if}});">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
          <input type="hidden" name="DP" value="{{$sejour->DP}}" onchange="$V(this.form.keywords_code, this.value); Value.synchronize(this, 'editSejour');"/>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$sejour field="DR"}}</th>
        <td colspan="2">
          <script>
            Main.add(function() {
              CIM.autocomplete(getForm("editSejourEasy").DR_keywords_code, null, {
                {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
                  field_type: 'dr',
                  /* Permet de prendre en compte le type de séjour de façon dynamique */
                  callback: function(input, queryString) {
                    var form = getForm("editSejour");
                    var sejour_type = 'mco';
                    if ($V(form.elements['type']) == 'ssr') {
                      sejour_type = 'ssr';
                    }
                    else if ($V(form.elements['type']) == 'psy') {
                      sejour_type = 'psy';
                    }
                    return queryString + "&sejour_type=" + sejour_type;
                  },
                {{/if}}
                afterUpdateElement: function(input) {
                  $V(getForm("editSejourEasy").DR, input.value);
                }
              });
            });
          </script>

          <input type="text" name="DR_keywords_code" class="autocomplete str code cim10" value="{{$sejour->DR}}" onchange="Value.synchronize(this, 'editSejour');" style="width: 12em" />
          <button type="button" class="cancel notext" onclick="$V(this.form.DR, '');"></button>
          <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['DR']), $V(this.form.elements['chir_id']){{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, $V.curry(getForm('editSejour').elements['type']), 'dr'{{/if}});">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
          <input type="hidden" name="DR" value="{{$sejour->DR}}" onchange="$V(this.form.DR_keywords_code, this.value); Value.synchronize(this, 'editSejour');"/>
        </td>
      </tr>
    {{/if}}

    {{if $easy_handicap}}
      <tr>
          {{mb_include module=planningOp template=inc_field_handicap onchange="Value.synchronize(this, 'editSejour');"}}
      </tr>
    {{/if}}

    {{if $easy_service}}
      <!-- Selection du service -->
      <tr>
        <th>{{mb_label object=$sejour field="service_id"}}</th>
        <td colspan="2">
         {{if $sejour->_id && $sejour->_ref_curr_affectation->_id}}
           {{$sejour->_ref_curr_affectation->_ref_service }} - {{$sejour->_ref_curr_affectation}}
           {{mb_field object=$sejour field=service_id hidden=true disabled="disabled"}}
         {{else}}
            <select name="service_id" class="{{$sejour->_props.service_id}}" onchange="Value.synchronize(this, 'editSejour');" style="width: 15em;">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$listServices item=_service}}
                <option value="{{$_service->_id}}" {{if $sejour->service_id === $_service->_id}}selected{{/if}}>
                  {{$_service}}
                </option>
              {{/foreach}}
            </select>
          {{/if}}
        </td>
      </tr>
    {{/if}}

    {{if $required_uf_soins !== "no"}}
      <!-- Selection de l'unité de soins -->
      <tr>
        <th>{{mb_label object=$sejour field="uf_soins_id"}}</th>
        <td colspan="2">
          <select name="uf_soins_id" class="ref {{if $required_uf_soins === "obl"}}notNull{{/if}}" style="width: 15em;"
                  onchange="Value.synchronize(this, 'editSejour');">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$ufs.soins item=_uf}}
              <option value="{{$_uf->_id}}" {{if $sejour->uf_soins_id === $_uf->_id}}selected{{/if}}>
                {{mb_value object=$_uf field=libelle}}
              </option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}

    {{if $infos_admission}}
    <tr>
      <th class="category" colspan="3">
        {{if $sejour->_id && !$infos_sejour}}
          {{mb_include module=system template=inc_object_idsante400 object=$sejour}}
          {{mb_include module=system template=inc_object_history    object=$sejour}}
          {{mb_include module=system template=inc_object_notes      object=$sejour}}

          <a class="action" style="float: right"  title="Modifier uniquement le sejour" href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$sejour->_id}}">
            {{me_img src="edit.png" alt="modifier" icon="edit" class="me-primary"}}
          </a>
        {{/if}}

        {{tr}}CSejour-_admission-court{{/tr}}
      </th>
    </tr>
    {{/if}}

    {{if $easy_entree_sortie}}
      <tr>
        <th>{{mb_label object=$sejour field="_date_entree_prevue"}}</th>
        <td>
          {{mb_field object=$sejour form=editSejourEasy field=_date_entree_prevue register=true canNull=false onchange="Value.synchronize(this, 'editSejour');"}}
          à
          <select name="_hour_entree_prevue" onchange="Value.synchronize(this, 'editSejour');">
            {{foreach from=$conf.dPplanningOp.CSejour.heure_deb|range:$conf.dPplanningOp.CSejour.heure_fin item=hour}}
              <option value="{{$hour}}" {{if $sejour->_hour_entree_prevue == $hour || (!$sejour->_id && $hour == $heure_entree_jour)}}selected{{/if}}>{{$hour}}</option>
            {{/foreach}}
          </select> h
          <select name="_min_entree_prevue" onchange="Value.synchronize(this, 'editSejour');">
            {{foreach from=0|range:59:$conf.dPplanningOp.CSejour.min_intervalle item=min}}
              <option value="{{$min}}" {{if $sejour->_min_entree_prevue === $min}}selected{{/if}}>{{$min}}</option>
            {{/foreach}}
          </select> min
        </td>
        <td>
          {{if $can->admin}}
            (admin: {{mb_value object=$sejour field=entree_prevue}})
          {{/if}}
        </td>
      </tr>

      <tr>
        <th>{{mb_label object=$sejour field="_duree_prevue"}}</th>
        <td colspan="2" style="vertical-align: middle">
          {{mb_field object=$sejour field="_duree_prevue" increment=true form=editSejourEasy prop="num min|0" size=2 onchange="updateSortiePrevue(this.form); Value.synchronize(this, 'editSejour');" value=$sejour->_id|ternary:$sejour->_duree_prevue:$_duree_prevue}}
          {{tr}}night{{/tr}}(s)
          <span class="duree_prevue_view" {{if $sejour->_duree_prevue >0}}style="display: none;"{{/if}}>
            {{mb_field object=$sejour field="_duree_prevue_heure" increment=true form=editSejourEasy size=2 value=$sejour->_id|ternary:$sejour->_duree_prevue_heure:$_duree_prevue_heure onchange="Value.synchronize(this, 'editSejour');"}}
            {{tr}}hour{{/tr}}(s)
           </span>
          - (<span id="dureeEst"></span>)
          <span {{if !"dPplanningOp CSejour fields_display show_days_duree"|gconf}}style="display: none"{{/if}}>
          <span id="jours_prevus">{{math equation=x+1 x=$sejour->_id|ternary:$sejour->_duree_prevue:$_duree_prevue}}</span> {{tr}}day{{/tr}}(s)
          </span>
        </td>
      </tr>


      <tr {{if !$can->admin}}style="display: none;"{{/if}}>
        <th>{{mb_label object=$sejour field="_date_sortie_prevue"}}</th>
        <td>
          {{mb_field object=$sejour form=editSejourEasy field=_date_sortie_prevue register=true canNull=false onchange="Value.synchronize(this, 'editSejour');"}}
          à
          <select name="_hour_sortie_prevue" onchange="Value.synchronize(this, 'editSejour');">
            {{foreach from=$conf.dPplanningOp.CSejour.heure_deb|range:$conf.dPplanningOp.CSejour.heure_fin item=hour}}
              <option value="{{$hour}}" {{if $sejour->_hour_sortie_prevue == $hour || (!$sejour->_id && $hour == $heure_sortie_ambu)}}selected{{/if}}>{{$hour}}</option>
            {{/foreach}}
          </select> h
          <select name="_min_sortie_prevue"  onchange="Value.synchronize(this, 'editSejour');">
            {{foreach from=0|range:59:$conf.dPplanningOp.CSejour.min_intervalle item=min}}
              <option value="{{$min}}" {{if $sejour->_min_sortie_prevue === $min}}selected{{/if}}>{{$min}}</option>
            {{/foreach}}
          </select> min
        </td>
        <td>
          (admin: {{mb_value object=$sejour field=sortie_prevue}})
        </td>
      </tr>
    {{/if}}

    {{if $easy_atnc}}
      {{mb_ternary var=notnull_atnc test=$required_atnc value="notNull" other=""}}
      <th>{{mb_label object=$sejour field="ATNC" class=$notnull_atnc}}</th>
      <td colspan="2">
        {{mb_field object=$sejour field="ATNC" class=$notnull_atnc typeEnum="select" emptyLabel="Non renseigné"
        onchange="checkATNC(this, 'easy')"}}
      </td>
    {{/if}}

    {{if $easy_isolement}}
      <tr>
        <th>{{mb_label object=$sejour field=isolement}}</th>
        <td colspan="2">{{mb_field object=$sejour field=isolement onchange="Value.synchronize(this, 'editSejour', false);"}}</td>
      </tr>
    {{/if}}

    <!-- Selection du type de chambre et du régime alimentaire-->
    {{if $easy_chambre_simple}}
      <tr>
        <th>{{mb_label object=$sejour field="chambre_seule"}}</th>
        <td colspan="2">{{mb_field object=$sejour field="chambre_seule" onchange="checkChambreSejourEasy();"}}</td>
      </tr>
    {{/if}}
  </table>
</form>
