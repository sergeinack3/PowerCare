{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPplanningOp script=protocole_dhe ajax=1}}

{{assign var=can_edit_protocole value=0}}
{{if (!"dPplanningOp CSejour tab_protocole_DHE_only_for_admin"|gconf && $can->edit) || $can->admin}}
  {{assign var=can_edit_protocole value=1}}
{{/if}}

<script>
  window.aProtocoles = {};

  Main.add(function() {
    {{if !$can_edit_protocole}}
    ProtocoleDHE.canEditProtocole = false;
    {{/if}}
    ProtocoleDHE.initVwProtocoles(
            {{if $dialog}}1{{else}}0{{/if}},
            {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}1{{else}}0{{/if}},
            "{{$singleType}}",
            "{{$sejour_type}}"
    );
    {{if $dialog}}
      var urlComponents = Url.parse();
      $('{{$singleType}}' || "interv").show();
    {{/if}}
//Autocomplete : recherche et insertion de tags d'identifiants externes
    var form = getForm("selectFrm");

    var element_value = form.tags_to_search,
      tokenField_display = new TokenField(element_value, {
        onChange: function () {
          ProtocoleDHE.refreshList(form, null, null);
        }.bind(element_value)
      });

    var element_tag = form.elements._bind_tag_view_tag;

    var url = new Url('dPplanningOp', 'ajax_seek_protocole_idex_tag_autocomplete');
    url.addParam('object_class', 'CProtocole');
    url.addParam("input_field", element_tag.name);
    url.addParam('view_field', 'text');
    url.autoComplete(element_tag, null, {
      minChars:      2,
      width:         "250px",
      method:        "get",
      dropdown:      true,
      updateElement: function (selected) {
        var guid = selected.get("id");
        var _name = selected.down().getText();

        var to_insert = !tokenField_display.contains(guid);
        tokenField_display.add(guid);

        if (to_insert) {
          ProtocoleDHE.insertTag(guid, _name,"search-protocol-idex-tags_modal");
        }

        var element_input = form._bind_tag_view_med_resp;
        $V(element_input, "");
      }
    });

    window.tag_to_search_token = tokenField_display;


  });
</script>

<form name="exportProtocoles" method="get" target="_blank">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="raw" value="ajax_export_protocoles_dhe" />
  <input type="hidden" name="chir_id" />
  <input type="hidden" name="function_id" />
  <input type="hidden" name="idx_tags" />
  <input type="hidden" name="exclude_no_idx" />
</form>

<div id="get_protocole" style="display: none;"></div>

<table class="main"  {{if !$IS_MEDIBOARD_EXT_DARK}}style="background-color: #fff{{/if}}">
  <tr>
    <td colspan="2" style="text-align: left;">
      {{if $can_edit_protocole}}
        <button id="didac_button_create_protocole" type="button" class="new me-primary" onclick="ProtocoleDHE.chooseProtocole(0);">{{tr}}CProtocole-title-create{{/tr}}</button>
      {{/if}}

      {{if !$dialog}}
      <button type="button" class="search" onclick="ProtocoleDHE.controleDurees();">{{tr}}CProtocole-_update_duree_button{{/tr}}</button>
      {{/if}}

      <form name="selectFrm" action="?" method="get" onsubmit="return false">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dialog" value="{{$dialog}}" />
        <input type="hidden" name="inactive" value="" />
        <input type="hidden" {{if $dialog}} name="a" {{else}} name="tab" {{/if}} value="vw_protocoles" />
        <input type="hidden" name="page[interv]" value="{{$page.interv}}"
               onchange="ProtocoleDHE.refreshList(this.form, ['interv'], null);" />
        <input type="hidden" name="page[sejour]" value="{{$page.sejour}}"
               onchange="ProtocoleDHE.refreshList(this.form, ['sejour'], null);" />
        <input type="hidden" name="tags_to_search" value="" >
        
        <table class="form me-no-box-shadow">
          <tr>
            <th><label for="chir_id" title="Filtrer les protocoles d'un praticien">Praticien</label></th>
            <td>
              <select name="chir_id" style="width: 20em;"
                      onchange="if (this.form.function_id) {this.form.function_id.selectedIndex=0;}
                        ProtocoleDHE.refreshList(this.form, null, null);">
                <option value="0">&mdash; {{tr}}Choose{{/tr}}</option>
                {{foreach from=$listPrat item=curr_prat}}
                <option class="mediuser" style="border-color: #{{$curr_prat->_ref_function->color}}; {{if !$curr_prat->_count_protocoles}}color: #999;{{/if}}"
                        value="{{$curr_prat->user_id}}" {{if ($chir_id == $curr_prat->user_id) && !$function_id}} selected="selected" {{/if}}>
                  {{$curr_prat->_view}} ({{$curr_prat->_count_protocoles}})
                </option>
                {{/foreach}}
              </select>
            </td>
            <th><label for="function_id" title="Filtrer les protocoles d'une fonction">Fonction</label></th>
            <td>
              <select name="function_id" style="width: 20em;"
                      onchange="if (this.form.chir_id) { this.form.chir_id.selectedIndex=0; }
                        ProtocoleDHE.refreshList(this.form, null, null);">
                <option value="0">&mdash; {{tr}}Choose{{/tr}}</option>
                {{foreach from=$listFunc item=curr_function}}
                <option class="mediuser" style="border-color: #{{$curr_function->color}}; {{if !$curr_function->_count_protocoles}}color: #999;{{/if}}"
                        value="{{$curr_function->_id}}" {{if $curr_function->_id == $function_id}}selected="selected"{{/if}}>
                  {{$curr_function->_view}} ({{$curr_function->_count_protocoles}})
                </option>
                {{/foreach}}
              </select>
            </td>
            <th>{{tr}}Search{{/tr}}</th>
            <td>
              <input name="search_protocole"/>
              {{if $idex_selector}}
                <label for="_search_all_protocole" title="{{tr}}CProtocole-Search all the protocols of the establishment-desc{{/tr}}">
                  <input type="checkbox" name="_search_all_protocole"
                         onclick="$V(this.form.search_all_protocole, this.checked?1:0);"/> {{tr}}CProtocole-In the establishment{{/tr}}
                </label>
                <input type="hidden" name="search_all_protocole" value="0"/>
              {{/if}}
            </td>
            <td>
              {{if $idex_selector}}
                <button type="button" class="search"
                        onclick="Modal.open('idex_display',
             {title:$T('planning_op-tab-search-tags'), width:'550px;'});">
                  {{tr}}planning_op-tab-search-tags{{/tr}}
                </button>
              {{/if}}
            </td>
          </tr>
          <tr>
            <td>
              <div id="idex_display" style="display:none;width:550px">
                <table class="main">
                  <tr>
                    <th>
                      <label for="_bind_tag_view_tag" title="{{tr}}mod-Planning-display-protocol-CIdSante400-autocomplete{{/tr}}">
                        {{tr}}mod-Planning-display-protocol-CIdSante400-autocomplete{{/tr}}
                      </label>
                    </th>
                    <td>
                      {{*              autocomplete : recherche de tags d'identifiants externes*}}
                      <input type="search" name="_bind_tag_view_tag" class="autocomplete" size="20" />
                    </td>
                  </tr>
                  <tr>
                    <td></td>
                    <td><input type="checkbox" name="exclude_no_idx" value="1"
                                           onchange="ProtocoleDHE.refreshList(this.form, null, null);"/>
                      <label for="exclude_no_idx">{{tr}}Exclude-no-index{{/tr}}</label></td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>
                      <ul class="tags me-padding-bottom-8" id="search-protocol-idex-tags_modal" style="display: inline-block;"></ul>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="button">
                      <button type="button" class="cancel" onclick="Control.Modal.close();">
                        {{tr}}Close{{/tr}}
                      </button>
                    </td>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <ul class="tags" id="search-protocol-idex-tags_modal_main" style="display: inline-block;"></ul>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>

  <tr>
    <td>
      {{if !$dialog}}
      <ul id="tabs-protocoles" class="control_tabs">
        <li><a href="#interv" class="empty">Chirurgicaux <small>(&ndash;)</small></a></li>
        <li><a href="#sejour" class="empty">Médicaux     <small>(&ndash;)</small></a></li>
        {{if !$dialog}}
          <li class="me-tabs-buttons">
            <button type="button" onclick="ProtocoleDHE.stats();" class="stats">{{tr}}Stats{{/tr}}</button>
            <button type="button" onclick="ProtocoleDHE.popupImport();" class="hslip">{{tr}}Import-CSV{{/tr}}</button>
            <button type="button" onclick="ProtocoleDHE.popupExport();" class="hslip">{{tr}}Export-CSV{{/tr}}</button>
            <button type="button" onclick="ProtocoleDHE.popupExportAll();" class="hslip">{{tr}}CProtocole-Export global{{/tr}}</button>
          </li>
        {{/if}}
        <label class="me-color-black-high-emphasis">
          <input type="checkbox" name="show_inactive" id="show_inactive_checkbox"
                 onclick="var form = getForm('selectFrm');ProtocoleDHE.refreshList(form,null,null);" />
          {{tr}}CProtocole-action-Show inactive|pl{{/tr}}
        </label>
      </ul>

      <script>
        Main.add(function() {
          // Don't use .create() because the #fragment of the url
          // is not taken into account, and this is important here
          new Control.Tabs("tabs-protocoles");
        });
        </script>
      {{/if}}
      
      <div id="interv" style="display: none;"></div>
      <div id="sejour" style="display: none;"></div>
    </td>
  </tr>
</table>
