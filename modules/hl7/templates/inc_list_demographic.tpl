{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script="patient" ajax=true}}
{{mb_include module=system template=inc_pagination total=$nb_pat current=$page change_page="TestHL7.changePageListDemographicSupplier" step=30}}


<script>
  Main.add(function(){
    var form = getForm("filter-pat-demographic-supplier");
    $A(form.elements).each(function(element){
      element.observe("change", function(){
        $V(form.page, 0);
      })
    })
  })
</script>

<form name="filter-pat-demographic-supplier" method="get" onsubmit="return TestHL7.refreshListDemographicSupplier(this)">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="ajax_list_demographic" />
  <input type="hidden" name="page" value="{{$page}}" />

  <table class="tbl">
    <tr>
      <th colspan="6" class="title">Données patients</th>
      <th colspan="4" class="title">{{tr}}Actions{{/tr}}</th>
    </tr>

    <tr>
      <th style="width: 20%;">{{mb_title object=$patient field=nom}}</th>
      <th style="width: 20%;">{{mb_title object=$patient field=prenom}}</th>
      <th style="width: 20%;">{{mb_title object=$patient field=nom_jeune_fille}}</th>
      <th style="width: 10%;">{{mb_title object=$patient field=sexe}}</th>
      <th>{{mb_title object=$patient field=_IPP}}</th>
      <th>Créateur</th>
      <th class="narrow">A28</th>
      <th class="narrow">A31</th>
      <th class="narrow">A47</th>
      <th class="narrow">A40</th>
    </tr>

    {{foreach from=$patients item=_patient}}
      <tr>
        <td>
          <span  onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}')">
            {{mb_value object=$_patient field=nom}}
          </span>
        </td>
        <td>{{mb_value object=$_patient field=prenom}}</td>
        <td>{{mb_value object=$_patient field=nom_jeune_fille}}</td>
        <td>{{mb_value object=$_patient field=sexe}}</td>
        <td>{{mb_value object=$_patient field=_IPP}}</td>
        <td>
          <span  onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_ref_first_log->_guid}}')">
            {{$_patient->_ref_first_log->_ref_user->_view}}
          </span>
        </td>
        <td>
          <button type="button" class="compact send notext"
                  onclick="TestHL7.sendA28('{{$_patient->_id}}')">
            A28
          </button>
        </td>
        <td>
          <button type="button" class="compact edit notext" onclick="Patient.editModal('{{$_patient->_id}}')">
            A31
          </button>
        </td>
        <td>
          <button type="button" class="compact idex notext" onclick="guid_ids('{{$_patient->_guid}}')">
            A47
          </button>
        </td>
        <td>
          <input type="checkbox" name="merge_patient_id" value="{{$_patient->_id}}" onclick="TestHL7.handleMergeClick(this.form)"/>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CPatient.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</form>