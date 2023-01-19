{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=modal_validation ajax=true}}
{{mb_script module=ssr script=planification ajax=true}}
{{mb_script module=ssr script=csarr register=true}}
{{assign var=use_acte_presta value="ssr general use_acte_presta"|gconf}}

<table class="main tbl" id="choose_codes_seances_collectives">
  <tr>
    <th class="category narrow">{{mb_title class=CEvenementSSR field=debut}}</th>
    <th class="category narrow">{{mb_title class=CEvenementSSR field=duree}}</th>
    <th class="category narrow">{{mb_title class=CEvenementSSR field=therapeute_id}}</th>
    <th class="category narrow">{{mb_label class=CEvenementSSR field=prescription_line_element_id}}</th>
    <th class="category">{{tr}}CEvenementSSR-code|pl{{/tr}}</th>
  </tr>
  {{foreach from=$evenements item=_evt_collectif}}
    {{assign var=line_element value=$_evt_collectif->_ref_prescription_line_element}}
    {{assign var=line_element_sejour value=""}}
    {{foreach from=$sejour->_ref_prescription_sejour->_ref_prescription_lines_element item=_line_sejour}}
      {{if $_line_sejour->element_prescription_id == $line_element->element_prescription_id}}
        {{assign var=line_element_sejour value=$_line_sejour}}
      {{/if}}
    {{/foreach}}
    <tr>
      <td>{{mb_value object=$_evt_collectif field=debut}}</td>
      <td>{{mb_value object=$_evt_collectif field=duree}}</td>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_evt_collectif->_ref_therapeute}}</td>
      <td>{{$line_element->_view}}</td>
      <td>
        {{unique_id var=unique_id_collectif}}
        <form name="editEvenementSSR-{{$unique_id_collectif}}" method="post" action="?">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_evenement_ssr_multi_aed" />
          <input type="hidden" name="sejour_id" value="{{$sejour->_id}}">
          <input type="hidden" name="therapeute_id" value="{{$_evt_collectif->_ref_therapeute->_id}}" />
          <input type="hidden" name="_type_seance" value="collective" />
          <input type="hidden" name="seance_collective_id" value="{{$_evt_collectif->_id}}" />
          <input type="hidden" name="line_id" value="{{$line_element_sejour->_id}}" />
          <script>
            Main.add(function(){
              var formSSR = getForm('editEvenementSSR-{{$unique_id_collectif}}');

              // CsARR other code autocomplete
              if ($('code_csarr_autocomplete-{{$unique_id_collectif}}')) {
                var url = new Url("ssr", "httpreq_do_csarr_autocomplete");
                url.autoComplete(formSSR.code_csarr, "code_csarr_autocomplete", {
                  dropdown: true,
                  minChars: 2,
                  select: "value",
                  callback: function(input, queryString){
                    return (queryString + "&type_seance="+$V(formSSR.type_seance));
                  },
                  updateElement: function(selected) {
                    Planification.updateFieldCodesSSR(selected, 'csarr', '{{$unique_id_collectif}}');
                  }
                } );
              }

              // Presta SSR other code autocomplete
              if ($('code_presta_ssr_autocomplete-{{$unique_id_collectif}}')) {
                var url = new Url("ssr", "ajax_presta_ssr_autocomplete");
                url.autoComplete(formSSR.code_presta_ssr, "code_presta_ssr_autocomplete", {
                  dropdown: true,
                  minChars: 2,
                  method: "get",
                  select: "value",
                  updateElement: function(selected) {
                    Planification.updateFieldCodesSSR(selected, 'presta_ssr', '{{$unique_id_collectif}}');
                  }
                } );
              }
            });
          </script>
          {{if $use_acte_presta == "csarr"}}
            <fieldset class='line_csarr'>
              <legend>{{tr}}CActeCsARR|pl{{/tr}}</legend>
              <div id="csarrs-{{$line_element->_id}}" class="codes-csarr">
                {{foreach from=$line_element->_ref_element_prescription->_ref_csarrs item=_csarr}}
                  <label>
                    <input type="checkbox" class="checkbox-csarrs nocheck" name="csarrs[{{$_csarr->code}}]" value="{{$_csarr->code}}"/>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_csarr->_guid}}')">{{$_csarr->code}}</span>
                  </label>
                {{/foreach}}
              </div>
              <!-- Autre code CsARR -->
              <div id="div_other_csarr{{$unique_id_collectif}}">
                <label>
                  <input type="checkbox" name="_csarr" value="other" onclick="Planification.toggleOtherCsarr(this, '{{$unique_id_collectif}}');" /> {{tr}}Other{{/tr}}:
                </label>
                <span id="other_csarr{{$unique_id_collectif}}" style="display: none;">
                   <input type="text" name="code_csarr" class="autocomplete" canNull=true size="2" />
                   <button type="button" class="search notext" onclick="CsARR.viewSearch(function(code) {Planification.updateFieldCodesSSR(code, 'csarr', '{{$unique_id_collectif}}');}, '{{$_evt_collectif->_ref_therapeute->_id}}');">
                     {{tr}}CActiviteCsARR-action-search{{/tr}}
                   </button>
                   <div style="display: none;" class="autocomplete" id="code_csarr_autocomplete-{{$unique_id_collectif}}"></div>
                </span>
              </div>
            </fieldset>
          {{/if}}

          <!-- Affichage des prestations SSR -->
          {{if $use_acte_presta == "presta"}}
          <!-- prestation SSR -->
            <fieldset class="line_presta-ssr" style="width: 40%;float: left;">
              <legend>{{tr}}CPrestaSSR{{/tr}}</legend>

              <div id="prestas-ssr-{{$line_element->_id}}" class="prestas-ssr">
                {{foreach from=$line_element->_ref_element_prescription->_refs_presta_ssr item=_presta}}
                  <label>
                    <input type="checkbox" class="checkbox-prestas_ssr nocheck" name="prestas_ssr[{{$_presta->code}}]" value="{{$_presta->code}}"/>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_presta->_guid}}')">{{$_presta->code}}</span>
                  </label>

                  (<span title="{{tr}}CActePrestationSSR-Amount of code to add{{/tr}}">x</span>
                  <input type="text" id="prestas_ssr_quantity_{{$_presta->code}}"
                         name="prestas_ssr_quantity[{{$_presta->code}}]" value="{{$_presta->quantite}}" style="width: 17px;" />)

                  <script>
                    Main.add(function () {
                      $('prestas_ssr_quantity_{{$_presta->code}}').addSpinner({min: 0});
                    });
                  </script>
                {{/foreach}}
              </div>

                <!-- Autre prestations SSR -->
              <div id="div_other_presta_ssr">
                <label>
                  <input type="checkbox" name="_presta_ssr" value="other" onclick="Planification.toggleOtherPresta(this, '{{$unique_id_collectif}}');" /> {{tr}}Other{{/tr}}:
                </label>
                <span id="other_presta_ssr{{$unique_id_collectif}}" style="display: none;">
                   <input type="text" name="code_presta_ssr" class="autocomplete" canNull=true size="2" />
                   <div style="display: none;" class="autocomplete" id="code_presta_ssr_autocomplete-{{$unique_id_collectif}}"></div>
                </span>
              </div>
            </fieldset>
          {{/if}}
        </form>
      </td>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="7" class="button" style="background-color:white;border-color: white;">
      {{if $evenements|@count}}
        <button type="button" class="submit" onclick="Seance.createEvtsCollectifsCodes('{{$sejour->_id}}', '{{$use_acte_presta}}');">
          {{tr}}Save{{/tr}}
        </button>
      {{/if}}
      <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>