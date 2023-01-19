{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.setTabCount('tab_homonymes_patients', {{$homonymes.count_homonymes}});
  });
</script>

<table class="main tbl">
  <tr>
    <td colspan="4">
      {{mb_include module=system template=inc_pagination total=$homonymes.count_homonymes current=$start_homonymes step=$step change_page="PatientSignature.pageChange" change_page_arg="homonymes"}}
    </td>
  </tr>

  <tr>
    <th>{{tr}}CPatient|pl{{/tr}}</th>
    <th class="narrow">{{tr}}CPatient-naissance{{/tr}}</th>
    <th colspan="2">{{tr}}CPatientSignature-homonyme-error{{/tr}}</th>
  </tr>

  {{foreach from=$homonymes.patients item=_pats}}
    <tr>
      <td>
        {{foreach from=$_pats item=_pat}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_pat->_guid}}')">
            {{$_pat->_view}}
          </span>
          <br />
        {{/foreach}}
      </td>
      <td class="compact">
        {{$_pats.patient_1->naissance|date_format:$conf.date}}
      </td>
      <td colspan="3">
        <form name="remove-homonymes-{{$_pats.patient_1->_id}}-{{$_pats.patient_2->_id}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this, function() {
          return getForm('show_table_homonymes_patients').onsubmit();
        })">
          <input type="hidden" name="m" value="patients" />
          <input type="hidden" name="dosql" value="do_remove_homonymes_aed" />
          <input type="hidden" name="patient_1" value="{{$_pats.patient_1->_id}}" />
          <input type="hidden" name="patient_2" value="{{$_pats.patient_2->_id}}" />

          <button class="fa fa-bookmark" type="submit">Non homonymes</button>
        </form>
      </td>
    </tr>
  {{/foreach}}
</table>