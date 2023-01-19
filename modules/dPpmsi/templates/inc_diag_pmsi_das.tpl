{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function(){
    DiagPMSI.getAutocompleteCim10(getForm("editDA"), getForm("editDA")._added_code_cim, true{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, '{{$sejour->type}}', 'da'{{/if}});
  });
</script>
<table class="tbl">
  <tr>
    <th class="category" colspan="2">{{tr}}PMSI.Diagnostic Associe{{/tr}}</th>
  </tr>
  <tr>
    <!--  Diagnostics Associés du dossier médical (OMS)-->
    <td>
      <form name="editDA" action="?m={{$m}}" method="post"
            onsubmit="return onSubmitFormAjax(this, PMSI.afterEditDiag.curry('{{$sejour->_id}}', {{$modal}}));">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_class" value="CSejour" />
        <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
        <input type="hidden" name="_praticien_id" value="{{$sejour->praticien_id}}" />
        <input type="hidden" name="_added_code_cim" onchange="this.form.onsubmit();"/>

        <label for="_added_code_cim" title="{{tr}}PMSI.Diagnostic Associe{{/tr}}">{{tr}}PMSI-cim10-oms{{/tr}}</label>
        <input type="text" name="keywords_code" class="autocomplete str" value="" size="10"/>
        <button class="search notext me-tertiary" type="button" onclick="CIM.viewSearch($V.curry(this.form.elements['_added_code_cim']), this.form.elements['_praticien_id']{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'da'{{/if}});">
          {{tr}}Search{{/tr}}
        </button>
      </form>
    </td>
  </tr>
  <tr>

    <!--  Liste des Diagnostics Associés du dossier médical (OMS)-->
    <td style="vertical-align: top">
      <ul class="tags" style="float: none;">
        {{foreach from=$sejour->_ref_dossier_medical->_ext_codes_cim item=curr_cim}}
          <li class="tag me-tag" style="white-space:normal">
            <form name="delCodeAsso-{{$curr_cim->code}}" action="?m={{$m}}" method="post"
                  onsubmit="return onSubmitFormAjax(this, PMSI.afterEditDiag.curry('{{$sejour->_id}}', {{$modal}}));">
              <input type="hidden" name="m" value="patients" />
              <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="object_class" value="CSejour" />
              <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
              <input type="hidden" name="_deleted_code_cim" value="{{$curr_cim->code}}" />
              <button class="delete notext me-no-box-shadow me-tertiary" type="submit" style="display: inline-block !important;">{{tr}}Delete{{/tr}}</button>
            </form>
              {{$curr_cim->code}} - {{$curr_cim->libelle|smarty:nodefaults}}
          </li>
          <br/>
        {{/foreach}}
      </ul>
    </td>
  </tr>
</table>