{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=advanced_prot value=0}}
{{mb_default var=mode value=""}}
{{mb_default var=sterilisable value=0}}
{{mb_default var=readonly value=0}}
{{mb_default var=print value=0}}

<tr id="{{$_materiel_operatoire->_guid}}">
  {{if !$print}}
    <td>
      <div class="not-printable">
        {{if $advanced_prot}}
          {{if !$readonly}}
            <input type="checkbox" name="materiels_operatoires[{{$_materiel_operatoire->_id}}]"
                   value="{{$_materiel_operatoire->_id}}" checked />
          {{/if}}
        {{elseif $mode === "validation"}}
          {{if !$readonly}}
            {{if !$sterilisable}}
              {{if $_materiel_operatoire->status === "ok"}}
                <button type="button" class="cancel"
                        onclick="ProtocoleOp.editMaterielOperation('{{$_materiel_operatoire->_id}}', {status: ''}, 1);">
                  {{tr}}Cancel{{/tr}}
                </button>
              {{elseif !$_materiel_operatoire->status}}
                <button type="button" class="tick"
                        onclick="ProtocoleOp.editMaterielOperation('{{$_materiel_operatoire->_id}}', {status: 'ok'}, 1);">
                  {{tr}}Validate{{/tr}}
                </button>
              {{/if}}
            {{/if}}
          {{/if}}
        {{else}}
          <button type="button" class="trash notext"
                  onclick="ProtocoleOp.editMaterielOperation('{{$_materiel_operatoire->_id}}', {libelle: '{{$_materiel_operatoire->_view|JSAttribute}}'}, null, 1);">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </div>
    </td>
  {{/if}}
  {{if $mode === "validation"}}
  <td>
    <div class="not-printable">
      {{if !$readonly && !$sterilisable}}
        {{if $_materiel_operatoire->status === "ko"}}
          <button type="button" class="cancel"
                  onclick="ProtocoleOp.editMaterielOperation('{{$_materiel_operatoire->_id}}', {status: ''}, 1);">
            {{tr}}Cancel{{/tr}}
          </button>
        {{elseif !$_materiel_operatoire->status}}
          <button type="button" class="cancel"
                  onclick="ProtocoleOp.editMaterielOperation('{{$_materiel_operatoire->_id}}', {status: 'ko'}, 1);">
            {{tr}}CMaterielOperatoire-Materiel missing{{/tr}}
          </button>
        {{/if}}
      {{/if}}
    </div>
  </td>
  {{/if}}
  <td>
    <span {{if $_materiel_operatoire->_ref_dm->_id}}onmouseover="ObjectTooltip.createEx(this, '{{$_materiel_operatoire->_ref_dm->_guid}}');"{{/if}}>
      {{$_materiel_operatoire->_view}}
    </span>

    <div class="compact">
      {{if $_materiel_operatoire->_ref_dm->_ref_location}}
        {{$_materiel_operatoire->_ref_dm->_ref_location->name}}
      {{/if}}
      {{if $_materiel_operatoire->_code_produit}}
        {{if $_materiel_operatoire->_ref_dm->_ref_location}}
          &mdash;
        {{/if}}
        {{$_materiel_operatoire->_code_produit}}
      {{/if}}
    </div>
  </td>
  <td class="me-text-align-center">
      {{if $_materiel_operatoire->completude_panier}}
        {{tr}}Yes{{/tr}}
      {{else}}
        {{tr}}No{{/tr}}
      {{/if}}
  </td>
  <td>
    {{if $mode === "consommation" && $_materiel_operatoire->qte_prevue && !$readonly}}
      <button type="button" class="hslip notext" style="float: right;"
              onclick="$V(getForm('addConsommation-{{$_materiel_operatoire->_id}}').qte_consommee, '{{$_materiel_operatoire->qte_prevue}}');"></button>
    {{/if}}

    {{if $readonly || $advanced_prot || in_array($mode, array("validation", "consommation"))}}
      {{mb_value object=$_materiel_operatoire field=qte_prevue}}
    {{else}}
      <input type="text" id="qte_{{$_materiel_operatoire->_guid}}" value="{{$_materiel_operatoire->qte_prevue}}"
             onchange="ProtocoleOp.editMaterielOperation('{{$_materiel_operatoire->_id}}', {qte_prevue: this.value});" />

      <script>
        Main.add(function() {
          $('qte_{{$_materiel_operatoire->_guid}}').addSpinner();
        });
      </script>
    {{/if}}
  </td>
  {{if $mode === "consommation" || $print}}
    <td>
      {{if !$readonly}}
        {{assign var=manage_lot value=0}}
        {{assign var=dm value=$_materiel_operatoire->_ref_dm}}

        {{if $_materiel_operatoire->dm_id && $dm->_ref_product}}
          {{assign var=manage_lot value=1}}
        {{/if}}

        <form name="addConsommation-{{$_materiel_operatoire->_id}}" {{if $manage_lot}}class="consommation_{{$dm->product_id}}"{{/if}} method="post"
              onsubmit="return onSubmitFormAjax(this, ProtocoleOp.refreshListMaterielsOperation.curry('{{$_materiel_operatoire->_id}}'));">
          {{mb_class object=$consommation}}
          {{mb_key   object=$consommation}}

          {{mb_field object=$consommation field=datetime value="now" hidden=true}}
          {{mb_field object=$consommation field=materiel_operatoire_id value=$_materiel_operatoire->_id hidden=true}}
          {{mb_field object=$consommation field=dm_sterilisation_id hidden=true}}

          {{if $manage_lot && $dm->product_id}}
            {{assign var=class_notnull value=""}}
            {{if $dm->type_usage === "sterilisable"}}
              {{assign var=class_notnull value="notNull"}}
            {{/if}}

            {{if $manage_lot}}
              <button type="button" class="add" onclick="ProtocoleOp.createLot('{{$dm->product_id}}');">
                {{tr}}CProductOrderItemReception-New lot{{/tr}}
              </button>
            {{/if}}

            <select name="lot_id">
              {{if !$class_notnull}}
                <option value="">&mdash; {{tr}}CProductOrderItemReception-Choose{{/tr}}</option>
              {{/if}}

              {{foreach from=$dm->_ref_product->_lots item=_lot name=lots}}
                <option value="{{$_lot->_id}}"
                        {{if $_lot->_ref_dm_sterilisation}}data-dm-sterilisation-id="{{$_lot->_ref_dm_sterilisation->_id}}"{{/if}}
                        {{if $smarty.foreach.lots.first && $class_notnull}}selected{{/if}}>
                  [{{$_lot->code}}]

                  {{if $dm->_sterilisable && $_lot->libelle}}
                    &mdash; {{mb_value object=$_lot field=libelle}}
                  {{/if}}

                  {{if $_lot->lapsing_date}}
                    &ndash; {{mb_value object=$_lot field=lapsing_date}}
                  {{/if}}

                  {{if $_lot->_ref_order_item->_ref_reference->societe_id}}
                    &ndash; {{mb_value object=$_lot->_ref_order_item->_ref_reference field=societe_id}}
                  {{/if}}
                </option>
              {{/foreach}}
            </select>
          {{/if}}

          {{mb_field object=$consommation field=qte_consommee form="addConsommation-`$_materiel_operatoire->_id`" increment=true}}

          {{if $manage_lot && $dm->product_id}}
            <button type="button" class="tick notext"
                    onclick="{{if $class_notnull}}
                      if (!$V(this.form.lot_id)) {
                        return;
                      }
                      {{/if}}
                      $V(this.form.dm_sterilisation_id, this.form.lot_id.selectedOptions[0].get('dm-sterilisation-id'));
                      if ($V(this.form.qte_consommee)) {
                      this.form.onsubmit();
                      $V(this.form.qte_consommee, ''); }">{{tr}}Validate{{/tr}}</button>
          {{/if}}
        </form>
      {{/if}}

      <div id="consommations_area_{{$_materiel_operatoire->_id}}">
        {{mb_include module=planningOp template=inc_list_consommations}}
      </div>
    </td>
  {{/if}}
</tr>
