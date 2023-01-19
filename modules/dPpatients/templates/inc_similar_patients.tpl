{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th colspan="10" class="category">
      Fusion de patients similaires
    </th>
  <tr>
    <th>
      <label for="patNom">Choix du patient</label>
    </th>
    <td colspan="9">
      <input type="hidden" name="m" value="dPpatients" />
      <input type="hidden" name="patient_id" value="{{$patient->patient_id}}" onchange="this.form.submit()" />
      <input type="text" readonly="readonly" name="patNom" value="{{$patient->_view}}" ondblclick="PatSelector.init()" />

      <button class="search" type="button" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
      <script type="text/javascript">
        PatSelector.init = function () {
          this.sForm = "patFrm";
          this.sId = "patient_id";
          this.sView = "patNom";
          this.pop();
        }
      </script>
    </td>
  </tr>
  
  {{if $patient->_id}}
    <tr>
      <td colspan="10">
        <form name="fusion" action="?" method="get">
          <input type="hidden" name="m" value="system" />
          <input type="hidden" name="a" value="object_merger" />
          <input type="hidden" name="objects_class" value="CPatient" />
          <input type="hidden" name="readonly_class" value="CPatient" />
          <table class="form">
            <tr>
              <th class="title">
                <input type="radio" name="objects_id[]" value="{{$patient->_id}}" checked="checked" style="float: left;" />
                <button type="submit" class="search" style="float: left;">
                  {{tr}}Merge{{/tr}}
                </button>
                Patient analysé
              </th>
              {{foreach from=$listSiblings item="curr_sib"}}
                <th class="title">
                  <input type="checkbox" name="objects_id[]" value="{{$patient->_id}}" style="float: left;" />
                  <label for="fusion_fusion_{{$curr_sib->_id}}">Doublon</label>
                </th>
              {{/foreach}}
            </tr>
          </table>
        </form>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        {{mb_include template=inc_vw_patient}}
      </td>
      {{foreach from=$listSiblings item="_sibling"}}
        <td colspan="2">
          {{mb_include template=inc_vw_patient patient=$_sibling}}
        </td>
      {{/foreach}}
    </tr>
  {{/if}}
</table>