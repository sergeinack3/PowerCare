{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=sejours_ssr ajax=true}}
<form name="select_sejour_collectif" id="select_sejour_collectif">
  <script>
    Main.add(function () {
      {{if $sejours|@count}}
        Seance.checkCountSejours();
      {{/if}}
    });
  </script>
  <input type="hidden" name="m" value="{{$m}}">
  <table class="form tbl">
    <tr>
      <th class="title" colspan="10">
        <span style="text-align: left">({{$sejours|@count}})</span>
        {{tr}}{{$m}}-sejour_{{$m}}|pl{{/tr}} {{$date|date_format:$conf.longdate}}
        <br/>{{tr}}ssr-sejour_ssr_content_elt{{/tr}} {{$element}}
      </th>
    </tr>

    <tr>
      <th>
        {{if $sejours|@count}}
          <input name="check_all_sejours" type="checkbox" onchange="Seance.selectSejours($V(this));"/>
        {{/if}}
      </th>
      <th style="width:  12em;">
        {{mb_colonne class=CAffectation field=lit_id order_col=$order_col order_way=$order_way function=Seance.sortBy}}
      </th>
      <th>
        {{mb_colonne class=CSejour field=patient_id order_col=$order_col order_way=$order_way function=Seance.sortBy}}
      </th>
      <th class="narrow">
        <input type="text" size="6" class="not-printable" onkeyup="SejoursSSR.filter(this, 'select_sejour_collectif')"/>
      </th>
      <th>{{mb_label class=CAffectation field=entree}}</th>
      <th>{{mb_label class=CAffectation field=sortie}}</th>
      <th>{{mb_label class=CSejour field=service_id}}</th>
      <th>{{mb_label class=CSejour field=libelle}}</th>
      <th>{{mb_label class=CSejour field=praticien_id}}</th>
      <th>{{mb_title class=CBilanSSR field=_kine_referent_id}} /{{mb_title class=CBilanSSR field=_kine_journee_id}}</th>
    </tr>

    {{foreach from=$sejours item=_sejour}}
      <tr>
        <td style="text-align: center;">
          {{assign var=sejour_guid value=$_sejour->_guid}}
          <input type="checkbox" name="_sejour_view_{{$_sejour->_id}}" class="sejour_collectif"
                 onchange="Seance.jsonSejours['{{$_sejour->_guid}}'].checked = (this.checked ? 1 : 0);Seance.checkCountSejours();"
            {{if isset($_sejours_guids.$sejour_guid|smarty:nodefaults) && $_sejours_guids.$sejour_guid.checked == 1}}
              checked="checked"
            {{/if}}
            />
          <script>
            var jsonLine = {checked : 0};
            Seance.jsonSejours["{{$_sejour->_guid}}"] = jsonLine;
            $('select_sejour_collectif__sejour_view_'+'{{$_sejour->_id}}').onchange();
          </script>
        </td>
        <td class="text">
          {{assign var=affectation value=$_sejour->_ref_curr_affectation}}
          {{if $affectation->_id}}
            {{$affectation->_ref_lit}}
          {{/if}}
        </td>
        <td class="text" colspan="2">
          {{mb_include module=ssr template=inc_view_patient patient=$_sejour->_ref_patient}}
        </td>

        {{assign var=distance_class value=ssr-far}}
        {{if $_sejour->_entree_relative == "-1"}}
          {{assign var=distance_class value=ssr-close}}
        {{elseif $_sejour->_entree_relative == "0"}}
          {{assign var=distance_class value=ssr-today}}
        {{/if}}
        <td class="{{$distance_class}}">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
            {{mb_value object=$_sejour field=entree format=$conf.date}}
          </span>

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
            {{mb_value object=$_sejour field=service_id}}
          </div>
        </td>

        <td class="text">
          {{mb_include module=system template=inc_get_notes_image object=$_sejour mode=view float=right}}
          {{mb_value object=$_sejour field=libelle}}
          {{assign var=libelle value=$_sejour->libelle|upper|smarty:nodefaults}}
          {{assign var=color value=$colors.$libelle}}
          {{if $color->color}}
            <div class="motif-color" style="background-color: #{{$color->color}};" title="{{$_sejour->libelle}}"></div>
          {{/if}}
        </td>

        <td class="text">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
          {{assign var=prat_demandeur value=$bilan->_ref_prat_demandeur}}
          {{if $prat_demandeur->_id}}
            <br />{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$prat_demandeur}}
          {{/if}}
        </td>

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
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">
          {{tr}}CSejour.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="10" class="button">
        {{if $sejours|@count}}
          <button type="button" class="tick" onclick="Seance.addSejour();">{{tr}}ssr-add_patients_to_seance{{/tr}}</button>
        {{/if}}
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>