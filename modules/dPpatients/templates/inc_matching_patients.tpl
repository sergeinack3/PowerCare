{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  function mergePatientsModal(form) {
    var url = new Url();
    url.addFormData(form);
    url.modal(800, 600);
    return false;
  }
</script>

<form name="param-merge-matching-patients" method="get" action="?" onsubmit="return Url.update(this, 'matching-patients-messages')">
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="a" value="ajax_merge_matching_patients" />
  
  <fieldset>
    <legend>{{mb_title class=CPatient field=naissance}}</legend>
    <label><input type="checkbox" name="naissance[day]"
                  value="1" {{if @$naissance.day}}   checked="checked" {{/if}} /> {{tr}}Day{{/tr}}</label>
    <label><input type="checkbox" name="naissance[month]"
                  value="1" {{if @$naissance.month}} checked="checked" {{/if}} /> {{tr}}Month{{/tr}}</label>
    <label><input type="checkbox" name="naissance[year]"
                  value="1" {{if @$naissance.year}}  checked="checked" {{/if}} /> {{tr}}Year{{/tr}}</label>
  </fieldset>
  
  <button class="change">Chercher les patients identiques</button>
  {{*<label><input type="checkbox" id="do_merge"/> {{tr}}Merge{{/tr}} </label>*}}
</form>

<div id="matching-patients-messages"></div>
