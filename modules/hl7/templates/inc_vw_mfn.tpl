{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  sendMessageHL7 = function () {
    var url = new Url('hl7', 'ajax_export_structure');
    url.addParam('value_entity', $V('entity'));
    url.requestUpdate('messageHL7MFN');
  }
</script>

<fieldset class="me-no-box-shadow">
  <select name="entity" id="entity">
    {{foreach from=$entity_types item=_entity_name key=_entity_value}}
      <option value="{{$_entity_value}}">{{tr}}CHL7v2EventMFN-msg-entity_type_{{$_entity_value}}{{/tr}}</option>
    {{/foreach}}
  </select>

  <button type="submit" class="export" onclick="sendMessageHL7();">{{tr}}Export{{/tr}}</button>
</fieldset>

<div id="messageHL7MFN"></div>