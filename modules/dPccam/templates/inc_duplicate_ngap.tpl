{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPccam script=CCodageCCAM ajax=true}}

<script type="text/javascript">
  submiFormDuplicateCodage = function(form) {
    return onSubmitFormAjax(form, {onComplete: Control.Modal.closeAll.curry()});
  };

  Main.add(function() {
    var dates = {};
    dates.limit = {
      start: '{{$codable->entree|iso_date}}',
      stop: '{{$codable->sortie|iso_date}}'
    };

    var oForm = getForm("duplicateNGAP");
    if (oForm) {
      Calendar.regField(oForm.date, dates);
    }
  });
</script>

<form name="duplicateNGAP" method="post" target="?" onsubmit="return submiFormDuplicateCodage(this);">
  <input type="hidden" name="m" value="ccam"/>
  <input type="hidden" name="dosql" value="duplicateNgap"/>
  <input type="hidden" name="codable_guid" value="{{$codable->_guid}}"/>
  <input type="hidden" name="multiple_date" value=""/>
  <input type="hidden" name="type_of_date" value="one_date"/>
  <input type="hidden" name="actes" value="{{if $acte->_id}}{{$acte->_id}}{{/if}}"/>

  <table class="tbl">
    <tr>
      <th class="title" colspan="8">Duplication d'actes NGAP</th>
    </tr>
    <tr>
      <th>{{tr}}CActeNGAP-executant_id{{/tr}}</th>
      <th>{{tr}}CActeNGAP-execution{{/tr}}</th>
      <th>{{tr}}CActeNGAP-code{{/tr}}</th>
      <th>{{tr}}CActeNGAP-coefficient{{/tr}}</th>
      <th>{{tr}}CActeNGAP-complement{{/tr}}</th>
      <th>{{tr}}CActeNGAP-_tarif{{/tr}}</th>
      <th>Dupliquer jusqu'au :</th>
    </tr>
    <tr>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$acte->_ref_executant}}
      </td>
      <td>
        {{mb_value object=$acte field=execution}}
      </td>
      <td>
        {{mb_value object=$acte field=code}}
      </td>
      <td>
        {{mb_value object=$acte field=coefficient}}
      </td>
      <td{{if !$acte->complement}} class="empty"{{/if}}>
        {{mb_value object=$acte field=complement}}
      </td>
      <td>
        {{mb_value object=$acte field=_tarif}}
      </td>

      <td>
        <input type="hidden" name="date" class="date" value="{{$codable->sortie|date_format:'%Y-%m-%d'}}"/>
        <button type="button" class="edit notext" onclick="CCodageCCAM.chooseDateDuplication('{{$acte->_guid}}','{{$codable->_guid}}');">
      </td>
    </tr>
    <tr>
      <td colspan="8" class="button">
        <button type="button" class="copy" onclick="this.form.onsubmit();">
          {{tr}}Duplicate{{/tr}}
        </button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">
          {{tr}}Cancel{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
