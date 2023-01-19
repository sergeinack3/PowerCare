{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=CISP ajax=true}}

<script>
  Main.add(function() {
    getForm('searchCISP').onsubmit();
  });
</script>

<form name="editDossier" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier_medical}}
  {{mb_key object=$dossier_medical}}

  {{if !$dossier_medical->_id}}
    <input type="hidden" name="object_id" value="{{$dossier_medical->patient_id}}">
    <input type="hidden" name="object_class" value="CPatient">
  {{/if}}

  {{mb_field object=$dossier_medical field=codes_cim hidden=true}}
</form>

{{* Recherche par mot-clés non implémentée *}}
<fieldset style="display: none">
  <legend>{{tr}}Search{{/tr}}</legend>

  <form name="searchCISP" method="get" onsubmit="return onSubmitFormAjax(this, null, 'cisp_area');">
    <input type="hidden" name="m" value="cim10" />
    <input type="hidden" name="a" value="ajax_cisp" />
    <input type="hidden" name="consult_id" value="{{$consult_id}}" />
    <input type="hidden" name="mode" value="{{$mode}}" />

    <table class="form">
      <tr>
        <th class="narrow">
          {{mb_label class=CCISP field=_keywords}}
        </th>
        <td>
          {{mb_field class=CCISP field=_keywords}}
        </td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button class="search">{{tr}}Search{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</fieldset>

<div id="cisp_area"></div>