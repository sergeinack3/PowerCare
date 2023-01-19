{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=editRights   value=0}}
{{mb_default var=with_buttons value=1}}

<script>
  uncheckPrestation = function(elts) {
    elts.each(function(elt) {
      elt.checked = "";
    });
  };
  
  openModal = function(id_div) {
    var div = $(id_div);
    window.save_checked = div.select("input").pluck("checked");
    Modal.open(id_div,
      {
        className: 'sejour-modif-prestations',
        onClose: function() {
          Control.Modal.refresh();
        }
      }
    );
  };
  
  closeModal = function(id_div) {
    var div = $(id_div);
    div.select("input").each(function(elt, index) {
      elt.checked = window.save_checked[index];
    });
    Control.Modal.close();
  };
  
  autoRealiser = function(input) {
    var name = input.name.replace(/souhait/g, "realise").replace("new", "temp");
    $A(input.form.elements[name]).each(function(elt) {
      if (elt.value == input.value) {
        elt.checked = "checked";
      }
    });
  };
  
  switchToNew = function(input) {
    input.name = input.name.replace("[temp]", "[new]");
  };

  switchToNewSousItem = function(input) {
    var input_item = input.up('fieldset').down('legend').down('input');
    input_item.checked = true;
    input.up('td').select('.sous_item').each(function(input) {
      input.checked = false;
    });
    input.checked = true;
    switchToNew(input_item);
    switchToNew(input);
  };

  onSubmitLiaisons = function(form, callback) {
    return onSubmitFormAjax(form, function() {
        {{if $is_contextual_call}}
          document.location.reload();
        {{else}}
          if (Object.isFunction(callback)) {
            callback();
          }
          Control.Modal.refresh();
        {{/if}}
    });
  };

  removeLiaison = function(liaison_id, item_id, prestation_id, date, type, sous_item_id) {
    var field_empty = type == "souhait" ? "item_souhait_id" : "item_realise_id";
    var field = type == "souhait" ? "item_realise_id" : "item_souhait_id";
    var form = getForm("delLiaison");
    $V(form.item_liaison_id, liaison_id != "temp" ? liaison_id : "");
    $V(form.elements[field_empty], "");
    $V(form.elements[field], item_id);
    $V(form.prestation_id, prestation_id);
    $V(form.sous_item_id, type == "souhait" ? "" : sous_item_id);
    $V(form.date, date);
    onSubmitFormAjax(form, function() {
      Control.Modal.close();

      {{if $is_contextual_call}}
        document.location.reload();
      {{else}}
        Control.Modal.refresh();
      {{/if}}
    });
  };

  removeLiaisons = function(date) {
    new Url('hospi', 'do_remove_liaisons_for_date', 'dosql')
      .addParam('sejour_id', '{{$sejour->_id}}')
      .addParam('date', date)
      .requestUpdate('systemMsg', {method: 'POST', onComplete: Control.Modal.refresh});
  };

  Main.add(function() {
    // Pour que la modale de modification des niveaux de prestation dépasse correctement de la modale principale
    var form = getForm('add_prestation_ponctuelle');
    if (form) {
      form.up('div.modal').setStyle({overflow: 'visible'});
      form.up('div.content').setStyle({overflow: 'visible'});
    }
  });
</script>
{{math equation=x+2 x=$dates|@count assign="colspan"}}

<form name="add_prestation_ponctuelle" method="post" action="?" onsubmit="return onSubmitLiaisons(this);">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_add_item_ponctuelle_aed" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="item_prestation_id" value="" />
  <input type="hidden" name="date" value="" />
</form>

<form name="delLiaison" method="post">
  {{mb_class class=CItemLiaison}}
  <input type="hidden" name="item_liaison_id" />
  <input type="hidden" name="item_souhait_id" />
  <input type="hidden" name="item_realise_id" />
  <input type="hidden" name="sous_item_id" />
  <input type="hidden" name="prestation_id" />
  <input type="hidden" name="date" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
</form>

{{mb_include module=soins template=inc_patient_banner object=$sejour patient=$sejour->_ref_patient}}

{{assign var=count_prestations_j value=$prestations_j|@count}}
{{if $count_prestations_j == 0}}
  {{assign var=count_prestations_j value=1}}
{{/if}}
{{math equation=45/x x=$count_prestations_j assign=width_prestation}}

<div style="height: 80%; overflow-y: auto;">
  <form name="edit_prestations" method="post" action="?" onsubmit="return onSubmitLiaisons(this);">
    <input type="hidden" name="m" value="hospi"/>
    <input type="hidden" name="dosql" value="do_items_liaisons_aed" />
    <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />

    {{if $prestations_p_forfait|@count}}
    <table class="tbl">
      <tr>
        <th class="title" colspan="{{$count_prestations_j+2}}">
          Au forfait
        </th>
      </tr>
      {{foreach from=$prestations_p_forfait item=_prestation}}
        <tr>
          <th class="section" colspan="{{$count_prestations_j+2}}">{{$_prestation}}</th>
        </tr>
        <tr>
          <td class="text" colspan="{{$count_prestations_j+2}}">
            {{foreach from=$_prestation->_ref_items item=_item}}
              {{assign var=item_id value=$_item->_id}}
              {{assign var=liaison_id value="empty"}}
              {{if isset($liaisons_p_forfait.$item_id|smarty:nodefaults)}}
                {{assign var=liaison_id value=$liaisons_p_forfait.$item_id}}
              {{/if}}

              <label style="display: inline-table">
                <input type="hidden" name="liaisons_p_forfait[{{$_item->_id}}]" value="{{$liaison_id}}" />
                <input type="checkbox" {{if $liaison_id != "empty"}}checked{{/if}}
                       onclick="$V(this.form.elements['liaisons_p_forfait[{{$_item->_id}}]'], this.checked ? 'on' : ''); onSubmitLiaisons(this.form);" /> {{$_item}}
              </label>
            {{/foreach}}
          </td>
        </tr>
      {{/foreach}}
    </table>
    {{/if}}

    <br />

    {{if "dPhospi prestations expert_colonne"|gconf}}
      {{mb_include module=planningOp template=inc_vw_prestations_colonnes}}
    {{else}}
      {{mb_include module=planningOp template=inc_vw_prestations_lignes}}
    {{/if}}


    <p style="text-align: center">
      {{mb_include module=hospi template=inc_button_send_prestations_sejour _sejour=$sejour}}

        {{if $with_buttons}}
          <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
        {{/if}}
    </p>
  </form>
</div>
