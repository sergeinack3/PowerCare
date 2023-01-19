{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  <li>Du patient
  <ul>
    {{foreach from=$patient->_ref_dossier_medical->_ext_codes_cim item=curr_code}}
    <li>
        <form name="addCim-{{$sejour->_id}}-{{$curr_code->code}}" action="?m={{$m}}" method="post">
        <input type="hidden" name="m" value="dPpatients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_class" value="CSejour" />
        <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
        <input type="hidden" name="_added_code_cim" value="{{$curr_code->code}}" />
        <button class="add notext" type="button" onclick="onSubmitFormAjax(this.form, reloadDiagnostic.curry({{$sejour->_id}}))">
          {{tr}}Add{{/tr}}
        </button>
        </form>
      {{$curr_code->code}} : {{$curr_code->libelle}}
    </li>
    {{foreachelse}}
    <li>{{tr}}CDossierMedical-codes_cim.unknown{{/tr}}</li>
    {{/foreach}}
  </ul>
  </li>
  <li>Significatifs du séjour
  <ul>
    {{foreach from=$sejour->_ref_dossier_medical->_ext_codes_cim item=curr_code}}
    <li>
      {{$curr_code->code}} : {{$curr_code->libelle}}
    </li>
    {{foreachelse}}
    <li>{{tr}}CDossierMedical-codes_cim.unknown{{/tr}}</li>
    {{/foreach}}
  </ul>
  </li>
</ul>