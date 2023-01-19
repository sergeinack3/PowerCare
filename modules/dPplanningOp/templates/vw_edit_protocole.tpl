{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=CIM ajax=$ajax}}
{{mb_script module=planningOp script=ccam_selector ajax=$ajax}}
{{mb_script module=planningOp script=protocole_op ajax=$ajax}}
{{if "appFineClient"|module_active}}
  {{mb_script module=appFineClient script=appFineClient ajax=$ajax}}
{{/if}}

{{assign var=use_charge_price_indicator value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}

<script>
  window.oCcamFieldProtocole = null;

  Main.add(function() {
    window.packs_non_stored = [];

    var form = getForm('editProtocole');
    ProtocoleDHE.refreshListCCAMProtocole();
    ProtocoleDHE.setOperationActive($V(form.for_sejour) == 0);

    oCcamFieldProtocole = new TokenField(form.codes_ccam, {
      onChange : ProtocoleDHE.refreshListCCAMProtocole,
      sProps : "notNull code ccam"
    } );
    ProtocoleDHE.editHour();

    {{if $conf.dPplanningOp.CSejour.show_only_charge_price_indicator}}
    ProtocoleDHE.showOnlyChargePriceIndicator = true;
    {{/if}}

    {{if $use_charge_price_indicator != "no"}}
    ProtocoleDHE.updateListCPI(form);
    {{/if}}
  });
</script>


<form name="addBesoinProtocole" method="post">
  <input type="hidden" name="m" value="bloc" />
  <input type="hidden" name="dosql" value="do_besoin_ressource_aed" />
  <input type="hidden" name="besoin_ressource_id" />
  <input type="hidden" name="protocole_id" value="{{$protocole->_id}}" />
  <input type="hidden" name="type_ressource_id" />
</form>

<form name="toggleProtocoleOp" method="post">
  {{mb_class object=$protocole_op_dhe}}
  {{mb_key   object=$protocole_op_dhe}}
  {{mb_field object=$protocole_op_dhe field=protocole_operatoire_id hidden=true}}
  {{mb_field object=$protocole_op_dhe field=protocole_id value=$protocole->_id hidden=true}}
  <input type="hidden" name="callback" value="ProtocoleOp.addProtocoleOpButton" />
  <input type="hidden" name="del" value="0" />
</form>

{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
  <form name="addPackProtocole" method="post">
    <input type="hidden" name="m" value="appFineClient" />
    <input type="hidden" name="dosql" value="do_pack_protocole_aed" />
    <input type="hidden" name="pack_id" />
    <input type="hidden" name="protocole_id" value="{{$protocole->_id}}" />
  </form>
{{/if}}

<form name="editProtocole" action="?m={{$m}}" method="post" class="{{$protocole->_spec}}"
      onsubmit="if(ProtocoleDHE.checkFormSejour()){
        return onSubmitFormAjax(this, function() {
          ProtocoleDHE.applyModifProtocole();
        });
      }" >
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_protocole_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_ccam_object_class" value="COperation" />
  <input type="hidden" name="callback" value=""/>
  {{mb_key object=$protocole}}
  <input type="hidden" name="_types_ressources_ids"
    onchange="ProtocoleDHE.{{if $protocole->_id}}addBesoins(this.value){{else}}synchronizeTypes($V(this)){{/if}}"/>
  {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    <input type="hidden" name="_pack_appFine_ids"
           onchange="ProtocoleDHE.{{if $protocole->_id}}addPacksAppFine(this.value){{else}}synchronizeTypesPacksAppFine($V(this)){{/if}}"/>
  {{/if}}

  {{if $dialog}}
    <input type="hidden" name="postRedirect" value="m=planningOp&a=vw_protocoles&dialog=1" />
  {{/if}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$protocole}}

    <tr>
      <th>{{mb_label object=$protocole field="chir_id"}}</th>
      <td>
        <select name="chir_id" class="{{$protocole->_props.chir_id}}"
                onchange="$V(this.form.libelle_protocole, '');
                          $V(this.form.protocole_prescription_chir_id, '');"
                style="width: 15em;">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser selected=$chir->_id list=$listPraticiens}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$protocole field="function_id"}}</th>
      <td>
        <select name="function_id" class="{{$protocole->_props.function_id}}"
                onchange="$V(this.form.libelle_protocole, '');
                          $V(this.form.protocole_prescription_chir_id, '');"
                style="width: 15em; {{if $protocole->function_id && !in_array($protocole->function_id, array_keys($listFunctions))}}background-color: #ffd700;{{/if}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_function list=$listFunctions selected=$protocole->function_id}}

          {{if $protocole->function_id && !in_array($protocole->function_id, array_keys($listFunctions))}}
            <option value="{{$protocole->function_id}}" style="background-color: #ffd700;" selected>
              [{{$protocole->_ref_function->_ref_group}}] {{$protocole->_ref_function}}
            </option>
          {{/if}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$protocole field="actif"}}</th>
      <td>
        {{mb_field object=$protocole field="actif"}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$protocole field="for_sejour"}}</th>
      <td>
        {{mb_field object=$protocole field="for_sejour"
          onchange="ProtocoleDHE.setOperationActive(\$V(this.form.elements[this.name]) != 1)"}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$protocole field=facturation_rapide}}</th>
      <td>{{mb_field object=$protocole field=facturation_rapide onchange="ProtocoleDHE.toggleCodageButton(this);"}}</td>
    </tr>
  </table>

  <table class="main layout">
    <tr>
      <td id="operation" class="halfPane">{{mb_include module=planningOp template=inc_edit_protocole_operation}}</td>
      <td id="sejour">{{mb_include module=planningOp template=inc_edit_protocole_sejour}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
      {{if $protocole->_id}}
        <button class="copy" type="button"
                onclick="ProtocoleDHE.copier({{if $is_praticien}}1{{else}}0{{/if}}, '{{$mediuser->_id}}')">
          Dupliquer
        </button>
        <button class="submit" type="button" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
        <button class="trash" type="button"
                onclick="confirmDeletion(
                  this.form,
                  {typeName:'le {{$protocole->_view|smarty:nodefaults|JSAttribute}}'},
                  function(){ ProtocoleDHE.applyModifProtocole(); }
                  );">
          {{tr}}Delete{{/tr}}
        </button>
      {{else}}
        <button id="didac_button_create_edit_protocole" class="submit" type="button" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
      {{/if}}
      </td>
    </tr>
  </table>
</form>
