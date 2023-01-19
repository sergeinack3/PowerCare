{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="dropPrescriptionItem" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="dPlabo" />
  <input type="hidden" name="dosql" value="do_prescription_examen_aed" />
  <input type="hidden" name="_pack_examens_labo_id" value="" />
  <input type="hidden" name="prescription_labo_examen_id" value="" />
  <input type="hidden" name="examen_labo_id" value="" />
  <input type="hidden" name="prescription_labo_id" value="" />
  <input type="hidden" name="del" value="0" />
</form>


<table class="tbl">
  <tr>
    <th class="title" colspan="3">Prescriptions</th>
  </tr>
  <tr>
    <th>Prescriptions du patient</th>
    <th>Analyses</th>
    <th>Etat</th>
  </tr>
  {{foreach from=$patient->_ref_prescriptions item="curr_prescription"}}
  <tr class="{{if $curr_prescription->_id == $prescription->_id}}selected{{/if}}">
    <td id="drop-prescription-{{$curr_prescription->_id}}">
      <script type="text/javascript">
        Droppables.add('drop-prescription-{{$curr_prescription->_id}}', {
          onDrop: function(element) {
            Prescription.Examen.drop(element.id, {{$curr_prescription->_id}})
          },
        hoverclass:'selected'
        } );
      </script>
      <a href="#{{$curr_prescription->_class}}-{{$curr_prescription->_id}}" onclick="Prescription.select({{$curr_prescription->_id}})">
        {{$curr_prescription->_view}}
      </a>
      <form name="delPrescription-{{$curr_prescription->_id}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="dPlabo" />
        <input type="hidden" name="dosql" value="do_prescription_aed" />
        <input type="hidden" name="prescription_labo_id" value="{{$curr_prescription->_id}}" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="verouillee" value="{{$curr_prescription->verouillee}}" />
        <input type="hidden" name="validee" value="{{$curr_prescription->validee}}" />
        {{if $curr_prescription->_status < $curr_prescription|const:"PRELEVEMENTS"}}
        <button type="button" class="trash notext" onclick="Prescription.del(this.form);" title="{{tr}}Delete{{/tr}}">
          {{tr}}Delete{{/tr}}
        </button>
        {{/if}}
        <button type="button" class="print notext" onclick="Prescription.print({{$curr_prescription->_id}});" title="{{tr}}Print{{/tr}}">
          {{tr}}Print{{/tr}}
        </button>
        {{if $curr_prescription->_status < $curr_prescription|const:"VEROUILLEE"}}
        <button type="button" class="edit notext" onclick="Prescription.edit({{$curr_prescription->_id}});" title="{{tr}}Edit{{/tr}}">
          {{tr}}Edit{{/tr}}
        </button>
        {{/if}}
        {{if $curr_prescription->_status == $curr_prescription|const:"PRELEVEMENTS"}}
        <button type="button" class="lock notext" onclick="Prescription.lock(this.form);" title="{{tr}}Lock{{/tr}}">
          {{tr}}Lock{{/tr}}
        </button>
        {{/if}}
        {{if $curr_prescription->_status == $curr_prescription|const:"VEROUILLEE"}}
        <button type="button" class="change notext" onclick="Prescription.send(this.form);" title="{{tr}}Send{{/tr}}">
          {{tr}}Send{{/tr}}
        </button>
        {{/if}}
        {{if $curr_prescription->_status == $curr_prescription|const:"SAISIE"}}
        <button type="button" class="tick notext" onclick="Prescription.valide(this.form);" title="{{tr}}Validate{{/tr}}">
          {{tr}}Validate{{/tr}}
        </button>
        {{/if}}
        {{if $curr_prescription->_status >= $curr_prescription|const:"SAISIE"}}
        <button type="button" class="search notext"onclick="Prescription.results({{$curr_prescription->_id}});" title="{{tr}}Results{{/tr}}">
          {{tr}}Results{{/tr}}
        </button>
        {{/if}}
      </form>
    </td>
    <td style="text-align: center">
      {{if $curr_prescription->_ref_internal_items|@count}}
      {{$curr_prescription->_ref_internal_items|@count}} int.
      {{/if}}
      {{if $curr_prescription->_ref_external_items|@count}}
      {{$curr_prescription->_ref_external_items|@count}} ext.
      {{/if}}
    </td>
    <td class="text">
      {{tr}}CPrescriptionLabo-_status.{{$curr_prescription->_status}}{{/tr}}
    </td>
  </tr>
{{/foreach}}
</table>