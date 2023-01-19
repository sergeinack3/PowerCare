{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=in_corridor value=0}}

{{if $mode_vue_tempo == "classique"}}
    {{assign var=height_affectation value=2.8}}
{{else}}
    {{assign var=height_affectation value=1.6}}
{{/if}}

{{assign var=chambre value=$_lit->_ref_chambre}}
{{assign var=systeme_presta value="dPhospi prestations systeme_prestations"|gconf}}

{{if $prestation_id && !$in_corridor}}
    <th class="text">{{$_lit->_selected_item->nom}}</th>
{{/if}}

<th class="text first_cell"
  {{if !$in_corridor}}
      onclick="chooseLit('{{$_lit->_id}}'); this.down('input').checked = 'checked';"
  {{/if}}
    style="text-align: left; {{if $_lit->_lines|@count}}height: {{math equation=x*y x=$_lit->_lines|@count y=$height_affectation}}em{{/if}}"
    data-rank="{{$_lit->_selected_item->rank}}">

    {{if $_lit->_id && !"dPhospi vue_temporelle hide_alertes_temporel"|gconf}}
        <span style="float: right;">
      {{if $_lit->_lines|@count && $chambre->_overbooking && !$suivi_affectation}}
          <img src="modules/dPhospi/images/surb.png" title="Collision">
      {{/if}}
            {{if $chambre->_ecart_age > 15}}
                <img src="modules/dPhospi/images/age.png" alt="warning"
                     title="Ecart d'âge important: {{$chambre->_ecart_age}} ans"/>
            {{/if}}
            {{if $chambre->_genres_melanges}}
                <img src="modules/dPhospi/images/sexe.png" alt="warning" title="Sexes opposés"/>
            {{/if}}
            {{if $chambre->_chambre_seule}}
                <img src="modules/dPhospi/images/seul.png" alt="warning" title="Chambre seule obligatoire"/>
            {{/if}}
            {{if $chambre->_chambre_double}}
                <img src="modules/dPhospi/images/double.png" alt="warning" title="Chambre double possible"/>
            {{/if}}
            {{if $chambre->_conflits_chirurgiens}}
                <img src="modules/dPhospi/images/prat.png" alt="warning"
                     title="{{$chambre->_conflits_chirurgiens}} Conflit(s) de praticiens"/>
            {{/if}}
            {{if $chambre->_conflits_pathologies}}
                <img src="modules/dPhospi/images/path.png" alt="warning"
                     title="{{$chambre->_conflits_pathologies}} Conflit(s) de pathologies"/>
            {{/if}}
            {{if $chambre->annule == 1}}
                <img src="modules/dPhospi/images/annule.png" alt="warning" title="Chambre plus utilisée"/>
            {{/if}}
    </span>
    {{/if}}
    {{if !$readonly && !$in_corridor}}
        <input type="radio" name="lit_move" style="float: left;" id="lit_move_{{$_lit->_id}}"/>
    {{/if}}
    {{$chambre}} - {{$_lit->_shortview}}
</th>

{{assign var=onmouseevent value=onmouseout}}
{{if $ua->getCodeName() == "msie"}}
    {{assign var=onmouseevent value=onmouseleave}}
{{/if}}

{{foreach from=0|range:$nb_ticks_r item=_i}}
    {{assign var=datetime value=$datetimes.$_i}}
    <td
      class=" mouvement_lit {{if $datetime == $current}}current_hour{{/if}} {{if $granularite == "week" && $_i > 0 && $_i % 4 == 0}}left_day_week{{/if}} me-border-width-1"
      data-date="{{$datetime}}"
      {{if $in_corridor && $_i == 0}}
        {{if $_lit->_affectation_id}}
            id="wrapper_line_{{$_lit->_affectation_id}}"
        {{else}}
            id="wrapper_line_{{$_lit->_sejour_id}}"
        {{/if}}
      {{/if}}>
        {{if $_i == 0}}

            {{*  Parcours des affectations / séjours *}}
        {{foreach from=$_lit->_lines item=_lines_by_level key=_level}}
        {{foreach from=$_lines_by_level item=_object_info name=foreach_aff}}
            {{assign var=is_aff value=false}}
            {{assign var=in_permission value=false}}
            {{if $_object_info|strpos:"-"}}
                {{* Sejour *}}
                {{assign var=explode_info value= "-"|explode:$_object_info}}
                {{assign var=sejour_id value=$explode_info.1}}
                {{assign var=object value=$sejours.$sejour_id}}
                {{assign var=sejour value=$object}}
            {{else}}
                {{* Affectation *}}
                {{assign var=object value=$affectations.$_object_info}}
                {{assign var=sejour value=$object->_ref_sejour}}
                {{assign var=is_aff value=true}}
                {{if $object->_in_permission}}
                    {{assign var=in_permission value=true}}
                {{/if}}
            {{/if}}
            {{assign var=object_id value=$object->_id}}
            {{assign var=patient value=$sejour->_ref_patient}}
            {{assign var=praticien value=$sejour->_ref_praticien}}

            {{assign var=offset_op value=0}}
            {{assign var=width_op value=0}}

            {{if $praticien->_id}}
                {{assign var=color value=$praticien->_ref_function->color}}
            {{else}}
                {{assign var=color value="688"}}
            {{/if}}

            {{math equation=x*y x=$object->_entree_offset y=$td_width assign=offset}}
            {{math equation=x*y x=$object->_width y=$td_width assign=width}}
            {{assign var=mode_vue_reelle value=$mode_vue_tempo}}
            {{if $is_aff && $object->parent_affectation_id}}
                {{assign var=mode_vue_reelle value="compacte"}}
            {{/if}}

        <div id="{{if $is_aff}}affectation{{else}}sejour{{/if}}_temporel_{{$object->_id}}"
             class="affectation draggable text {{$object->_guid}}
            {{if $is_aff}}
              {{if !$sejour->_id}}clit_bloque{{else}}clit{{/if}}
              {{if $sejour->confirme}}sejour_sortie_confirmee{{/if}}
              {{if !$object->sejour_id && $object->entree >= $date_min}}debut_blocage{{/if}}
              {{if !$object->sejour_id && $object->sortie <= $date_max}}fin_blocage{{/if}}
              {{if $object->entree > $date_min && $sejour->_id}}affect_left{{/if}}
              {{if $object->sortie < $date_max && $sejour->_id}}affect_right{{/if}}
              {{if $object->entree == $sejour->entree && $object->entree >= $date_min}}debut_sejour{{/if}}
              {{if $object->sortie == $sejour->sortie && $object->sortie <= $date_max}}fin_sejour{{/if}}
              {{if $object->parent_affectation_id}}child{{/if}}
            {{else}}
              clit sejour_non_affecte
              {{if $object->entree >= $date_min}}debut_sejour{{/if}}
              {{if $object->sortie <= $date_max}}fin_sejour{{/if}}
            {{/if}}
            background_temporel_{{$patient->sexe}}
            {{$mode_vue_reelle}}
            {{$sejour->_guid}}"

            {{if $is_aff}}
                data-affectation_id="{{$object->_id}}"
                data-lit_id="{{$object->lit_id}}"
                data-width="{{$object->_width}}"
                data-offset="{{$object->_entree_offset}}"
                data-affectations_enfant="{{'-'|implode:$object->_affectations_enfant_ids}}"
                data-entree="{{$object->entree}}"
                data-sortie="{{$object->sortie}}"
            {{else}}
                data-patient_id="{{$patient->_id}}"
                data-sejour_id="{{$sejour->_id}}"
                data-affectations_enfant="{{'-'|implode:$object->_sejours_enfants_ids}}"
            {{/if}}

             style="left: {{$offset}}%; width: {{$width}}%; border: 1px solid #{{$color}}; margin-left: 15.1%;
               margin-top: {{math equation=x*y x=$_level y=$height_affectation}}em; {{if $in_permission}}opacity: 0.5 !important;{{/if}}"
             onmouseover="
              if ($(this).hasClassName('classique')) {
                this.setStyle({width: this.down('table').getStyle('width')});
              }"
        {{if $mode_vue_reelle == "classique" || !$is_aff}}
            {{$onmouseevent}}="this.setStyle({width: '{{$width}}%'});"
        {{/if}}>

        {{if $mode_vue_reelle != "compacte" || $in_corridor}}
            <div class="toolbar_affectation me-margin-top--3">
                {{if "dPhospi vue_temporelle display_placer_couloir"|gconf}}
                    <button type="button"
                            onclick="changeAffService('{{$object->_id}}', '{{$object->_class}}' {{if $is_aff}},'{{$object->sejour_id}}', '{{$object->lit_id}}'{{/if}})"
                            class="opacity-40 door-out notext me-tertiary"
                            onmouseover="this.toggleClassName('opacity-40')"
                            onmouseout="this.toggleClassName('opacity-40')">
                        {{tr}}CAffectation-Move in corridor{{/tr}}
                    </button>
                {{/if}}
                {{if $is_aff && $object->sejour_id}}
                    {{if "dPadmissions General show_deficience"|gconf}}
                        <span style="margin-top: 3px; margin-right: 3px;">
                  {{mb_include module=patients template=inc_vw_antecedents patient=$patient type=deficience readonly=1}}
                </span>
                    {{/if}}
                    {{if $object->uf_hebergement_id && $object->uf_medicale_id && $object->uf_soins_id && "dPhospi General show_uf"|gconf}}
                        <a style="margin-top: 3px; display: inline" href="#1"
                           onclick="AffectationUf.affecter('{{$object->_id}}','{{$_lit->_id}}', refreshMouvements.curry(null, '{{$object->lit_id}}'))">
                      <span class="texticon texticon-uf opacity-40" title="Affecter les UF"
                            onmouseover="this.toggleClassName('opacity-40')"
                            onmouseout="this.toggleClassName('opacity-40')">UF</span>
                        </a>
                    {{/if}}
                {{/if}}
                {{if $is_aff}}
                    {{if $object->_in_permission}}
                        {{mb_include module=soins template=inc_button_permission affectation=$object from_placement=1}}
                    {{/if}}
                    <button type="button" class="edit notext opacity-40 me-tertiary"
                            onmouseover="this.toggleClassName('opacity-40')"
                            onmouseout="this.toggleClassName('opacity-40')"
                            onclick="Affectation.edit('{{$object->_id}}')">{{tr}}Edit{{/tr}}</button>
                {{/if}}
            </div>
        {{/if}}

        {{if $object->_width}}
        {{foreach from=$sejour->_ref_operations item=_operation}}
            {{math equation=(x/y)*100 x=$_operation->_debut_offset.$object_id y=$object->_width assign=offset_op}}
            {{math equation=(x/y)*100 x=$_operation->_width.$object_id y=$object->_width assign=width_op}}
            <div class="operation_in_mouv {{if $mode_vue_reelle == "compacte"}}compacte{{/if}} opacity-40"
                 style="left: {{$offset_op}}%; width: {{$width_op}}%; z-index: 0;"></div>
        {{if $_operation->duree_uscpo}}
            {{math equation=x+y x=$offset_op y=$width_op assign=offset_uscpo}}
            {{math equation=x/y*100 x=$_operation->_width_uscpo.$object_id y=$object->_width assign=width_uscpo}}
            <div class="soins_uscpo {{if $mode_vue_reelle == "compacte"}}compacte{{/if}} opacity-40"
                 style="left: {{$offset_uscpo}}%; width: {{$width_uscpo}}%; z-index: 0;"></div>
        {{/if}}
        {{/foreach}}
        {{/if}}
        {{if $is_aff && $object->_width_prolongation && $object->_is_prolong}}
            {{math equation=(x/y)*100 x=$object->_start_prolongation y=$object->_width_prolongation assign=offset_prolongation}}
            {{math equation=(x/y)*100 x=$object->_width_prolongation y=$object->_width_prolongation assign=width_prolongation}}
            <div class="prolongation {{if $mode_vue_reelle == "compacte"}}compacte{{/if}} opacity-60"
                 style="left: {{$offset_prolongation}}%; width: {{$width_prolongation}}%; z-index: 0;"></div>
        {{/if}}

        {{if !$readonly}}
            <table class="layout_affectation" style="z-index: 2; position: relative;">
                <tr>
                    {{if $sejour->_id && $mode_vue_reelle == "classique"}}
                        <td class="narrow" style="vertical-align: top; padding-right: 2px !important;">
                            {{mb_include module=patients template=inc_vw_photo_identite mode=read patient=$patient size=22}}
                        </td>
                    {{/if}}

                    {{if $sejour->_ref_patient->_refs_patient_handicaps}}
                        <td class="narrow me-valign-top">
                            <button class="deficience me-small notext"
                                    title="{{foreach from=$sejour->_ref_patient->_refs_patient_handicaps item=_handicap}}{{tr}}CPatientHandicap.handicap.{{$_handicap->handicap}}{{/tr}}. {{/foreach}}"></button>
                        </td>
                    {{/if}}

                    <td class="me-line-height-normal"
                        style="vertical-align: top; {{if $mode_vue_reelle == "classique"}}overflow: hidden;{{/if}}">
                        {{if $sejour->_id}}
                            <div
                              style="width: 10px; margin-bottom: 1px; {{if $sejour->type == "ambu"}}font-style: italic;{{/if}}"
                              class="me-width-auto
                      {{if ($object->entree == $sejour->entree && !$sejour->entree_reelle) ||
                              (!$is_aff && !$sejour->entree_reelle)}}
                        patient-not-arrived
                      {{elseif ($object->entree != $sejour->entree && !$object->_ref_prev->effectue)}}
                        patient-not-moved
                      {{elseif $is_aff && $object->effectue && !$object->_ref_next->_id}}
                        effectue
                      {{/if}}">
                                {{if $mode_vue_reelle == "compacte"}}
                                    <span
                                      onmouseover="ObjectTooltip.createEx(this, '{{$praticien->_guid}}')">({{$praticien->_shortview}})</span>
                                {{/if}}
                                <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');"
                                      class="CPatient-view {{if $sejour->recuse == "-1"}}opacity-70{{/if}}">
                        {{if $sejour->recuse == "-1"}}[Att] {{/if}}
                                    {{$patient}}
                                    {{if "dPImeds"|module_active}}
                                        {{mb_include module=Imeds template=inc_sejour_labo link="#1" float="none"}}
                                        <script>
                            ImedsResultsWatcher.addSejour('{{$sejour->_id}}', '{{$sejour->_NDA}}');
                          </script>
                                    {{/if}}
                      </span>
                                {{assign var=service_id_imc value=$object->service_id}}
                                {{if ($service_id_imc && "dPhospi vue_temporelle show_imc_patient"|conf:"CService-$service_id_imc")
                                || (!$service_id_imc  && "dPhospi vue_temporelle show_imc_patient"|gconf)}}
                                    {{if $patient->_ref_constantes_medicales->poids}}
                                        - {{mb_value object=$patient->_ref_constantes_medicales field=poids}} kg
                                    {{/if}}
                                    {{if $patient->_ref_constantes_medicales->_imc}}
                                        ({{mb_value object=$patient->_ref_constantes_medicales field=_imc}})
                                    {{/if}}
                                {{/if}}

                                {{if $sejour->circuit_ambu}}
                                    <span class="texticon"
                                          title="{{tr}}CSejour-circuit_ambu{{/tr}} : {{tr}}CSejour.circuit_ambu.{{$sejour->circuit_ambu}}{{/tr}}">
                    {{mb_value object=$sejour field=circuit_ambu}}
                  </span>
                                {{/if}}
                            </div>
                            {{if $in_permission}}
                                <span class="texticon">PERM.</span>
                            {{/if}}

                            {{if $patient->_overweight}}
                                <img src="images/pictures/overweight.png"/>
                            {{/if}}

                            {{if $sejour->rques}}
                                <i class="fas fa-exclamation-circle" title="{{$sejour->rques}}"
                                   style="cursor: help; color:red;"></i>
                            {{/if}}

                            {{if $is_aff && (!$object->uf_hebergement_id || !$object->uf_medicale_id || !$object->uf_soins_id) && "dPhospi General show_uf"|gconf}}
                                <a style="margin-top: 3px; display: inline" href="#1"
                                   onclick="AffectationUf.affecter('{{$object->_id}}','{{$_lit->_id}}', refreshMouvements.curry(null, '{{$object->lit_id}}'))">
                                    <span class="texticon texticon-uf-warning" title="Affecter les UF">UF</span>
                                </a>
                            {{/if}}

                            {{if $mode_vue_reelle == "classique"}}
                                <span class="compact" style="width: 10px;">
                        {{if "dPhospi General show_age_patient"|gconf && $patient->_age}}({{$patient->_age}}){{/if}}
                                    {{if $systeme_presta == "expert"}}
                                        {{if $prestation_id}}
                                            {{if $sejour->_liaisons_for_prestation|@count}}
                                                {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$sejour->_liaisons_for_prestation}}
                                            {{/if}}
                                            {{if $sejour->_liaisons_for_prestation_ponct|@count}}
                                                {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$sejour->_liaisons_for_prestation_ponct}}
                                            {{/if}}
                                        {{/if}}
                                    {{else}}
                                        <em style="color: #f00;"
                                            title="Chambre {{if $sejour->chambre_seule}}seule{{else}}double{{/if}}">
                            {{if $sejour->chambre_seule}}CS{{else}}CD{{/if}}
                                            {{if $sejour->prestation_id}}- {{$sejour->_ref_prestation->nom}}{{/if}}
                          </em>
                                    {{/if}}
                  <span
                    onmouseover="ObjectTooltip.createEx(this, '{{$praticien->_guid}}')">({{$praticien->_shortview}})</span>
                  {{$sejour->_motif_complet|spancate:15:"...":true}}

                                    {{if $granularite == "day" && "dPhospi vue_temporelle infos_interv"|gconf && $sejour->_ref_next_operation}}
                                        {{assign var=next_op value=$sejour->_ref_next_operation}}
                                        - {{$next_op->_datetime_best|date_format:$conf.date}} {{$next_op->_datetime_best|date_format:$conf.time}} -
                                        {{mb_value object=$next_op field=temp_operation}} -
                                        {{mb_value object=$next_op field=type_anesth}}
                                    {{/if}}
                      </span>
                            {{/if}}

                            {{mb_include module=patients template=inc_icon_bmr_bhre}}
                        {{else}}
                            <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');">
                      BLOQUE {{if $object->function_id}}POUR {{mb_value object=$object field=function_id}}{{/if}}
                    </span>
                            {{if $object->rques}}
                                <br/>
                                <em>
                                    {{$object->rques|spancate:50:"...":true}}
                                </em>
                            {{/if}}
                        {{/if}}
                    </td>
                    {{if $mode_vue_reelle != "compacte" || $in_corridor}}
                        <td class="narrow me-valign-top" style="vertical-align: middle;">
                            {{if $is_aff}}
                                <input type="radio" name="affectation_move" id="aff_move_{{$object->_id}}"
                                       onclick="chooseAffectation('{{$object->_id}}');"/>
                            {{else}}
                                <input type="radio" name="sejour_move" id="sejour_move_{{$sejour->_id}}"
                                       onclick="chooseSejour('{{$sejour->_id}}');"/>
                            {{/if}}
                        </td>
                    {{/if}}
                    {{if $mode_vue_reelle != "compacte" && $is_aff && in_array($granularite, array("day", "week")) && $sejour->sortie <= $date_max && $in_corridor == 0}}
                        <td class="narrow me-valign-top">
                            <div class="handle grippie-v footer"></div>
                        </td>
                    {{/if}}
                </tr>
            </table>
        {{/if}}
            </div>

        {{if !$readonly}}
            <script>
                {{if $is_aff}}
                var container = $('affectation_temporel_{{$object->_id}}');
                {{else}}
                var container = $('sejour_temporel_{{$object->_id}}');
                {{/if}}
                new Draggable(container, {
                    constraint:   "vertical",
                    revert:       true,
                    starteffect:  function (element) {
                        new Effect.Opacity(element, {duration: 0.2, from: 1.0, to: 0.7});
                    },
                    reverteffect: function (element) {
                        element.style.top = "auto";
                        {{if $in_corridor}}
                        element.style.left = element.save_left;
                        element.style.width = element.save_width;
                        element.style.marginLeft = "15.1%";
                        {{/if}}
                    },
                    {{if $in_corridor}}
                    onStart: function (drgObj, mouseEvent) {
                        var element = drgObj.element;
                        element.save_left = element.getStyle("left");
                        element.save_width = element.getStyle("width");
                        var table = element.up('table');
                        var left = element.cumulativeOffset().left;
                        var width = element.getWidth();
                        //var top = element.viewportOffset().top - element.cumulativeScrollOffset().top;

                        $(document.body).insert(element);
                        element.setStyle({
                            left:       left + 'px',
                            marginLeft: '0',
                            width:      width + 'px',
                            top:        '100px'
                        });

                        {{if !$is_aff && $prestation_id && $object->_first_liaison_for_prestation->_id && $object->_first_liaison_for_prestation->item_souhait_id}}
                        {{assign var=first_item_prestation_id value=$object->_first_liaison_for_prestation->item_souhait_id}}
                        {{assign var=item_prestation value=$items_prestation.$first_item_prestation_id}}
                        $$(".first_cell").each(function (elt) {
                            var rank = {{$item_prestation->rank}};
                            var rank_elt = parseInt(elt.get('rank'));
                            var classItem = "";

                            // Vert
                            if (rank == rank_elt) {
                                classItem = "item_egal";
                            }
                            // Orange
                            else if (rank < rank_elt) {
                                classItem = "item_inferior";
                            }
                            // Rouge
                            else if (rank > rank_elt) {
                                classItem = "item_superior";
                            }

                            elt.addClassName(classItem);
                            elt.set("classItem", classItem);
                        });
                        {{/if}}
                    },
                    onEnd:   function (drbObj, mouseEvent) {
                        $$(".first_cell").each(function (elt) {
                            elt.removeClassName(elt.get('classItem'));
                        });
                        var element = drbObj.element;
                        {{if $is_aff}}
                        $('wrapper_line_' + element.get('affectation_id')).insert(element);
                        {{else}}
                        $('wrapper_line_' + element.get('sejour_id')).insert(element);
                        {{/if}}
                    },
                    {{/if}}
                });

                // Agrandissement de l'affectation lorsque c'est possible
                var handle = container.down("div.handle");
                if (handle) {
                    new Draggable(handle, {
                        constraint: "horizontal",
                        handle:     handle,
                        onStart:    function (d) {
                            var grip = d.element;
                            var div = grip.up("div.affectation");

                            var onmouseover = div.getAttribute("onmouseover");
                            var onmouseout = div.getAttribute("onmouseout");

                            div.writeAttribute("data-onmouseover", onmouseover);
                            div.writeAttribute("data-onmouseout", onmouseout);

                            div.writeAttribute("onmouseover", null);
                            div.writeAttribute("onmouseout", null);

                            div.set("width", div.getStyle("width"));
                        },
                        onEnd:      function (d) {
                            var grip = d.element;
                            var div = grip.up("div.affectation");

                            var onmouseover = div.get("onmouseover");
                            var onmouseout = div.get("onmouseout");

                            div.writeAttribute("onmouseover", onmouseover);
                            div.writeAttribute("onmouseout", onmouseout);

                            div.writeAttribute("data-onmouseover", null);
                            div.writeAttribute("data-onmouseout", null);

                            // Sauvegarde de l'affectation resizée
                            var entree = Date.fromDATETIME(div.get("entree"));
                            var sortie = Date.fromDATETIME(div.get("sortie"));

                            var td_width = parseInt($("time_line_temporelle").down("table, th.title", 2).getStyle("width")) / div.up("tr.droppable").select("td.mouvement_lit").length;
                            var diff_width = parseFloat(div.getStyle("width")) - parseFloat(div.get("width"));

                            var size_hour = parseInt(diff_width / td_width);
                            var size_min = parseInt(((diff_width / td_width) - size_hour) * 60);

                            sortie.addHours(size_hour);
                            sortie.addMinutes(size_min);

                            // Il faut forcer les secondes à 0 (car le séjour n'en tient pas compte)
                            sortie.setSeconds(0);

                            if (sortie > entree) {
                                var form = getForm("affectationResize");

                                $V(form.affectation_id, div.get("affectation_id"));
                                $V(form.sortie, sortie.toDATE() + " " + sortie.toTIME());

                                onSubmitFormAjax(form, refreshMouvements.curry(null, div.get("lit_id")));
                            }
                        },
                        change:     function (d) {
                            var grip = d.element;
                            var div = grip.up("div.affectation");
                            div.style.width = (grip.offsetLeft + grip.getWidth()) + "px";
                        }
                    });
                }
            </script>
        {{/if}}
        {{/foreach}}
        {{/foreach}}
        {{if !$_lit->_lines|@count}}
            <span class="CPatient-view" style="display: none;"></span>
        {{/if}}
        {{/if}}
    </td>
{{/foreach}}
