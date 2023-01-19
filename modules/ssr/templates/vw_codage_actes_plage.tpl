{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=csarr register=true}}
{{if !$plage->element_prescription_id}}
  {{mb_return}}
{{/if}}

{{assign var=use_acte_presta value="ssr general use_acte_presta"|gconf}}

<script>
  Main.add(function(){
    TrameCollective.use_acte_presta = '{{$use_acte_presta}}';
    var oFormEvenementSSR = getForm('Edit-'+'{{$plage->_guid}}');
    // CsARR other code autocomplete
    if ($('code_csarr_autocomplete')) {
      var url = new Url("ssr", "httpreq_do_csarr_autocomplete");
      url.autoComplete(oFormEvenementSSR.code_csarr, "code_csarr_autocomplete", {
        dropdown: true,
        minChars: 2,
        select: "value",
        callback: function(input, queryString){
          return (queryString + "&type_seance="+$V(oFormEvenementSSR.type_seance));
        },
        updateElement: function(selected) {
          Planification.updateFieldCodesSSR(selected, 'csarr', null, true);
        }
      } );
    }

    // Presta SSR other code autocomplete
    if ($('code_presta_ssr_autocomplete')) {
      var url = new Url("ssr", "ajax_presta_ssr_autocomplete");
      url.addParam("code", $V(oFormEvenementSSR.code_presta_ssr));
      url.autoComplete(oFormEvenementSSR.code_presta_ssr, "code_presta_ssr_autocomplete", {
        dropdown: true,
        minChars: 2,
        method: "get",
        select: "value",
        updateElement: function(selected) {
          Planification.updateFieldCodesSSR(selected, 'presta_ssr', null, true);
        }
      } );
    }

    {{if $plage->_ref_actes_other.csarr|@count}}
      $('Edit-'+'{{$plage->_guid}}'+'__csarr').click();
    {{/if}}
    {{if $plage->_ref_actes_other.presta|@count}}
      $('Edit-'+'{{$plage->_guid}}'+'__presta_ssr').click();
    {{/if}}
  });
</script>

{{if $use_acte_presta == "csarr"}}
  <tr id='tr-csarrs'>
    <th>{{tr}}CActeCsARR|pl{{/tr}}</th>
    <td class="text">
      <div id="csarrs-{{$plage->_guid}}" class="codes-csarr">
        {{foreach from=$plage->_ref_element_prescription->_ref_csarrs item=_csarr}}
          {{assign var=code_acte value=$_csarr->code}}
          <label>
            <input type="checkbox" class="checkbox-csarrs" name="_csarrs[{{$_csarr->code}}-{{$_csarr->_id}}]" value="{{$_csarr->code}}"
                   {{if $_csarr->_checked}}checked="checked"{{/if}}/>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_csarr->_guid}}')">{{$_csarr->code}}</span>
          </label>
        {{/foreach}}
      </div>
      <!-- Autre code CsARR -->
      <div id="div_other_csarr">
        <label>
          <input type="checkbox" name="_csarr" value="other" onclick="Planification.toggleOtherCsarr(this);"
                 id="Edit-{{$plage->_guid}}__csarr"/> {{tr}}Other{{/tr}}:
        </label>
        <span id="other_csarr" style="display: none;">
           <input type="text" name="code_csarr" class="autocomplete" canNull=true size="2" />
           <button type="button" class="search notext"
                   onclick="CsARR.viewSearch(function(code) {Planification.updateFieldCodesSSR(code, 'csarr', null, true);}, $V(this.form.user_id));">
             {{tr}}CActiviteCsARR-action-search{{/tr}}
           </button>
           <div style="display: none;" class="autocomplete" id="code_csarr_autocomplete"></div>
          {{foreach from=$plage->_ref_actes_other.csarr item=_acte_csarr}}
            <span>
              <input type="hidden" name="_csarrs[{{$_acte_csarr->code}}]" value="{{$_acte_csarr->code}}" class="checkbox-other-csarrs">
              <button class="cancel notext" type="button" onclick="Planification.deleteCode(this)">{{tr}}Delete{{/tr}}</button>
              <label>{{$_acte_csarr->code}}</label>
            </span>
          {{/foreach}}
        </span>
      </div>
    </td>
  </tr>
{{/if}}

<!-- Affichage des prestations SSR -->
{{if $use_acte_presta == "presta"}}
  <!-- prestation SSR -->
  <tr id='tr-presta-ssr'>
    <th>{{tr}}CPrestaSSR{{/tr}}</th>
    <td class="text">
      <div id="prestas-ssr-{{$plage->_guid}}" class="prestas-ssr">
        {{foreach from=$plage->_ref_element_prescription->_refs_presta_ssr item=_presta}}
          {{assign var=code_acte value=$_presta->code}}
          <label>
            <input type="checkbox" class="checkbox-prestas_ssr nocheck" name="_prestas_ssr[{{$_presta->code}}-{{$_presta->_guid}}]" value="{{$_presta->code}}"
                   {{if $_presta->_checked}}checked="checked"{{/if}}/>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_presta->_guid}}')">{{$_presta->code}}</span>

            (<span title="{{tr}}CActePrestationSSR-Amount of code to add{{/tr}}">x</span>
            <input type="text" name="_prestas_quantity[{{$_presta->code}}-{{$_presta->_guid}}]"
                   value="{{$_presta->_quantite}}"
                   id="Edit-{{$plage->_guid}}__prestas_quantity[{{$_presta->code}}-{{$_presta->_guid}}]"
                   style="width: 17px;" />)
            <script>
              Main.add(function () {
                $('Edit-{{$plage->_guid}}__prestas_quantity[{{$_presta->code}}-{{$_presta->_guid}}]').addSpinner({min: 0.1});
              });
            </script>
          </label>
        {{/foreach}}
      </div>
      <!-- Autre prestations SSR -->
      <div id="div_other_presta_ssr">
        <label>
          <input type="checkbox" name="_presta_ssr" value="other" onclick="Planification.toggleOtherPresta(this);"
                 id="Edit-{{$plage->_guid}}__presta_ssr"/> {{tr}}Other{{/tr}}:
        </label>
        <span id="other_presta_ssr" style="display: none;">
          <input type="text" name="code_presta_ssr" class="autocomplete" canNull=true size="2" />
          <div style="display: none;" class="autocomplete" id="code_presta_ssr_autocomplete"></div>
          {{foreach from=$plage->_ref_actes_other.presta item=_acte_presta}}
            <span>
              <input type="hidden" name="_prestas_ssr[{{$_acte_presta->code}}-{{$_acte_presta->_guid}}]" value="{{$_acte_presta->code}}" class="checkbox-other-prestas_ssr">
              <button class="cancel notext" type="button" onclick="Planification.deleteCode(this)">{{tr}}Delete{{/tr}}</button>
              <label>{{$_acte_presta->code}}</label>
              (<span title="{{tr}}CActePrestationSSR-Amount of code to add{{/tr}}">x</span>
              <input type="text" name="_prestas_quantity[{{$_acte_presta->code}}-{{$_acte_presta->_guid}}]"
                     value="{{$_acte_presta->quantite}}"
                     id="Edit-{{$plage->_guid}}__prestas_quantity[{{$_acte_presta->code}}-{{$_acte_presta->_guid}}]"
                     style="width: 17px;" />)
              <script>
                Main.add(function () {
                  $('Edit-{{$plage->_guid}}__prestas_quantity[{{$_acte_presta->code}}-{{$_acte_presta->_guid}}]').addSpinner({min: 0.1});
                });
              </script>
            </span>
          {{/foreach}}
        </span>
      </div>
    </td>
  </tr>
{{/if}}