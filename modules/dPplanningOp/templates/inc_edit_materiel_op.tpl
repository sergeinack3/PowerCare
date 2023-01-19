{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dmi_active value="dmi"|module_active}}

<script>
  Main.add(function() {
    var form = getForm('editMaterielOp');

    {{if $dmi_active}}
    ProtocoleOp.dmi_active = true;
    {{/if}}

    ProtocoleOp.bdm = '{{'Ox\Mediboard\Medicament\CMedicament::getBase'|static_call:null}}';
    ProtocoleOp.makeAutocompletesProduit(form);

    form.up('div.content').setStyle({overflow: 'visible'});
    form.up('div.modal').setStyle({overflow: 'visible'});
  });
</script>

<form name="editMaterielOp" method="post" class="{{$materiel_op->_spec}}" onsubmit="return ProtocoleOp.onSubmit(this);">
  {{mb_class object=$materiel_op}}
  {{mb_key   object=$materiel_op}}

  {{mb_field object=$materiel_op field=protocole_operatoire_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$materiel_op}}

    {{if $dmi_active}}
    <tr>
      <td>
        {{mb_include module=planningOp template=inc_edit_materiel_op_dm}}
      </td>
      <td rowspan="3" style="vertical-align: middle;">
        {{mb_include module=planningOp template=inc_edit_materiel_op_qte}}
      </td>
    </tr>
    <tr>
      <td>
        <div>
          <h2>{{tr}}common-or{{/tr}}</h2>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        {{mb_include module=planningOp template=inc_edit_materiel_op_produit}}
      </td>
    </tr>
      <tr id="complPanier"
          {{if $materiel_op->_ref_dm && $materiel_op->_ref_dm->type_usage === 'sterilisable'}}class="hidden"{{/if}}>
        <td>
            {{mb_field object=$materiel_op field=completude_panier form=editMaterielOp typeEnum=checkbox}}
            {{mb_label object=$materiel_op field=completude_panier form=editMaterielOp typeEnum=checkbox}}
        </td>
      </tr>
    {{else}}
    <tr>
      <td>
        {{mb_include module=planningOp template=inc_edit_materiel_op_produit}}
      </td>
      <td>
        {{mb_include module=planningOp template=inc_edit_materiel_op_qte}}
      </td>
    </tr>
      <tr>
        <td>
            {{mb_field object=$materiel_op field=completude_panier form=editMaterielOp typeEnum=checkbox}}
            {{mb_label object=$materiel_op field=completude_panier form=editMaterielOp typeEnum=checkbox}}
        </td>
      </tr>
    {{/if}}

    {{mb_include module=system template=inc_form_table_footer object=$materiel_op
                 options="{typeName: \$T('CProtocoleOperatoire'), objName: '`$materiel_op->_view`'}" options_ajax=Control.Modal.close}}
  </table>
</form>
