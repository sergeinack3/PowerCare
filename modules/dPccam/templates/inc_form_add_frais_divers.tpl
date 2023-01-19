{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
Main.add(function(){
  var form = getForm('formAddFraisDivers');

  var url = new Url('system', 'httpreq_field_autocomplete');
  url.addParam('class', 'CFraisDivers');
  url.addParam('field', 'type_id');
  url.addParam('limit', 30);
  url.addParam('view_field', 'code');
  url.addParam('show_view', true);
  url.addParam('input_field', 'type_id_autocomplete_view');
  url.addParam('wholeString', false);
  url.autoComplete(form.type_id_autocomplete_view, null, {
    minChars: 1,
    method: 'get',
    select: 'view',
    dropdown: true,
    afterUpdateElement: function(field,selected){
      var form = field.form;
      $V(form.type_id, selected.getAttribute('id').split('-')[2]);
      $V(form.montant, selected.down('.tarif').innerHTML.strip());
      $V(form.facturable, selected.down('.facturable').innerHTML.strip());
      updateMontant(form);
    }
  });
});

updateMontant = function(form){
  $V(form.montant_base, parseFloat($V(form.montant) * $V(form.coefficient) * $V(form.quantite)));
};

removeFraisDivers = function(id, form) {
  if (!confirm('Voulez vous réelement supprimer de frais divers ?')) {
    return false;
  }

  form.del.value = 1;
  form.frais_divers_id.value = id;

  return onSubmitFormAjax(form, {check: function(){return true}, onComplete: refreshFraisDivers});
};
</script>

<form name="formAddFraisDivers" method="post" action="?" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {refreshFraisDivers(); Reglement.reload();}})">
  {{mb_class object=$frais_divers}}
  {{mb_key object=$frais_divers}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="montant" value="0" />

  {{mb_field object=$frais_divers field=object_id hidden=true}}
  {{mb_field object=$frais_divers field=object_class hidden=true}}

  <table class="main form">
    {{if $object->_coded}}
      <tr>
        <td colspan="8">
          <div class="small-info">{{tr}}CCodable-codage_closed{{/tr}}</div>
        </td>
      </tr>
    {{/if}}
    <tr>
      <th class="category">{{mb_label class=CFraisDivers field=quantite}}</th>
      <th class="category">{{mb_label class=CFraisDivers field=type_id}}</th>
      <th class="category">{{mb_label class=CFraisDivers field=coefficient}}</th>
      <th class="category">{{mb_label class=CFraisDivers field=facturable}}</th>
      <th class="category">{{mb_label class=CFraisDivers field=montant_base}}</th>
      <th class="category">{{mb_label class=CFraisDivers field=execution}}</th>
      <th class="category">{{mb_label class=CFraisDivers field=num_facture}}</th>
      <th class="category">{{mb_label class=CFraisDivers field=executant_id}}</th>
      {{if !$object->_coded && $can->edit}}
        <th class="category narrow"></th>
      {{/if}}
    </tr>

    {{if !$object->_coded && $can->edit}}
      <tr>
        <td>{{mb_field object=$frais_divers field=quantite increment=true form=formAddFraisDivers min=0 size=2 onchange="updateMontant(this.form)"}}</td>
        <td>
          <input type="text" name="type_id_autocomplete_view" class="autocomplete" />
          {{mb_field object=$frais_divers field=type_id hidden=true}}
        </td>
        <td>{{mb_field object=$frais_divers field=coefficient increment=true form=formAddFraisDivers min=0 size=2 onchange="updateMontant(this.form)"}}</td>
        <td>{{mb_field object=$frais_divers field=facturable typeEnum=select}}</td>
        <td>{{mb_field object=$frais_divers field=montant_base}}</td>
        <td>{{mb_field object=$frais_divers field=execution form="formAddFraisDivers" register=true}}</td>
        <td>{{mb_field object=$frais_divers field=num_facture form=formAddFraisDivers increment=1 style="width:15px;"}}</td>
        <td>
          <select name="executant_id" style="width: 120px;" class="{{$frais_divers->_props.executant_id}}">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$frais_divers->_list_executants selected=$frais_divers->executant_id}}
          </select>
        </td>
        <td>
          <button type="button" class="new" onclick="onSubmitFormAjax(this.form, {onComplete: refreshFraisDivers});">{{tr}}Create{{/tr}}</button>
        </td>
      </tr>
    {{/if}}

    {{foreach from=$object->_back.frais_divers item=_frais}}
      <tr>
        <td>{{mb_value object=$_frais field=quantite}}</td>
        <td>{{mb_value object=$_frais field=type_id}}</td>
        <td>{{mb_value object=$_frais field=coefficient}}</td>
        <td>{{mb_value object=$_frais field=facturable}}</td>
        <td>{{mb_value object=$_frais field=montant_base}}</td>
        <td>{{mb_value object=$_frais field=execution}}</td>
        <td>{{mb_value object=$_frais field=num_facture}}</td>
        <td>{{mb_value object=$_frais field=executant_id}}</td>
        {{if !$object->_coded && $can->edit}}
          <td>
            <button type="button" class="trash notext" onclick="removeFraisDivers({{$_frais->_id}}, this.form)">
              {{tr}}Delete{{/tr}}
            </button>
          </td>
        {{/if}}
      </tr>
    {{/foreach}}
  </table>
</form>
