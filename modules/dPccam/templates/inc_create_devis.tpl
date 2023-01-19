{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=datetime value='Ox\Core\CMbDT::dateTime'|static_call:null}}
{{assign var=date value='Ox\Core\CMbDT::date'|static_call:null}}

<script type="text/javascript">
  callbackDevis = function(devis_id) {
    DevisCodage.edit(devis_id, DevisCodage.list.curry('{{$object->_class}}', '{{$object->_id}}'));
  }
</script>

<form name="createDevis" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="callback" value="callbackDevis"/>
  <input type="hidden" name="@class" value="CDevisCodage"/>
  <input type="hidden" name="devis_codage_id" value=""/>
  <input type="hidden" name="codable_class" value="{{$object->_class}}"/>
  <input type="hidden" name="codable_id" value="{{$object->_id}}"/>
  <input type="hidden" name="patient_id" value="{{$object->_ref_patient->_id}}"/>
  <input type="hidden" name="praticien_id" value="{{$object->_ref_praticien->_id}}"/>
  <input type="hidden" name="creation_date" value="{{$datetime}}"/>
  {{if $object->_class == 'COperation'}}
    <input type="hidden" name="libelle" value="{{$object->libelle}}"/>
    <input type="hidden" name="codes_ccam" value="{{$object->codes_ccam}}"/>
    <input type="hidden" name="event_type" value="{{$object->_class}}"/>
    <input type="hidden" name="date" value="{{$object->date}}"/>
  {{else}}
    <input type="hidden" name="date" value="{{$date}}"/>
  {{/if}}

  <button type="submit" class="new">
    {{tr}}CDevisCodage-title-create{{/tr}}
  </button>
</form>
