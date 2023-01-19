{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Prescriptions = {
    collapse: function() {
      Element.hide.apply(null, $$("tbody.prescriptionEffect"));
    },

    expand: function() {
      Element.show.apply(null, $$("tbody.prescriptionEffect"));
    },

    initEffect: function(pack) {
      new PairEffect(pack);
    }
  };

  Object.extend(Droppables, {
    addPrescription: function(prescription_id) {
      var oDragOptions = {
        onDrop: function(element) {
          Prescription.Examen.drop(element.id, prescription_id)
        },
        hoverclass:'selected'
      };

      this.add('drop-listprescriptions-' + prescription_id,  oDragOptions);
    }
  });
</script>

{{if !$prescription->_id}}
  {{mb_return}}
{{/if}}

<table class="tbl" id="drop-listprescriptions-{{$prescription->_id}}">
  <tr>
    <th class="title" colspan="100">
      {{mb_include module=system template=inc_object_idsante400 object=$prescription}}
      {{mb_include module=system template=inc_object_history object=$prescription}}
      {{$prescription}}
      <script>
        Droppables.addPrescription({{$prescription->_id}});
      </script>
    </th>
  </tr>
  <tr>
    <th>Analyse</th>
    <th>Unité</th>
    <th>Références</th>
    <th>Resultat</th>
    <th>Int</th>
    <th>Ext</th>
  </tr>

  <!-- Affichage des prescriptions sous forme de packs -->
  {{foreach from=$tab_pack_prescription item="pack" key="key"}}
  <tr id="{{$key}}-trigger">
    <th colspan="6">
      <!-- Affichage du nom du pack en passant par la premiàère analyse -->
      {{$pack[0]->_ref_pack}}
    </th>
  </tr>

  <tbody class="prescriptionEffect" id="{{$key}}">
    <tr class="script">
      <td>
        <script>Prescriptions.initEffect("{{$key}}");</script>
      </td>
    </tr>
    {{foreach from=$pack item="_item"}}
      {{assign var="curr_examen" value=$_item->_ref_examen_labo}}
      {{mb_include module=labo template=inc_view_analyse}}
    {{/foreach}}
  </tbody>
 {{/foreach}}

  <!-- Affichage des autres analyses -->
  {{if $tab_pack_prescription && $tab_prescription}}
  <tr>
    <th colspan="6">Autres analyses</th>
  </tr>
  {{/if}}
  {{foreach from=$tab_prescription item="_item" key="key"}}
    {{assign var="curr_examen" value=$_item->_ref_examen_labo}}
    {{mb_include module=labo template=inc_view_analyse}}
  {{/foreach}}
</table>