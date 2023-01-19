{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  chooseTypeReceiver = function () {
    var form = getForm("create_phast_actor");

    var value_input = form.type_receiver.value;
    form.elements["@class"].value = value_input;

    switch (value_input) {
      case "CDestinataireHprim":
        form.nom.value = "OUT-HXML";
        form.libelle.value = "OUT HXML";
        break;
      case "CDestinataireHprim21":
        form.nom.value = "OUT-HPRIM-21";
        form.libelle.value = "OUT HPRIM 21";
        break;
      case "CPhastDestinataire":
        form.nom.value = "OUT-PHAST";
        form.libelle.value = "OUT PHAST";
        break;
      case "CReceiverFHIR":
        form.nom.value = "OUT-FHIR";
        form.libelle.value = "OUT FHIR";
        break;
      case "CReceiverHL7v2":
        form.nom.value = "OUT-HL7v2";
        form.libelle.value = "OUT HL7v2";
        break;
      case "CReceiverHL7v3":
        form.nom.value = "OUT-HL7v3";
        form.libelle.value = "OUT HL7v3";
        break;
      case "CSyslogReceiver":
        form.nom.value = "OUT-SYSLOG";
        form.libelle.value = "OUT SYSLOG";
        break;
      default :
        return false;
    }

    form.nom.fire("ui:change");
  }
</script>

{{if $actor->_class != "CInteropReceiver"}}
  <div class="small-info">{{tr}}CInteropReceiver-msg-Receiver always exist{{/tr}}</div>
  {{mb_return}}
{{/if}}

<form name="create_phast_actor" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$actor}}
  <input type="hidden" name="libelle" value=""/>
  <input type="hidden" name="callback" value="InteropActor.showExchangeReceiver"/>

  <fieldset>
    <legend>{{tr}}CInteropReceiver-msg-Receiver type{{/tr}}</legend>
    <table class="form">
        <tr>
          <td>
            {{foreach from=$actors key=type_actor item=_actors}}
              <label>
                <input type="radio" name="type_receiver" value="{{$_actors}}"
                       onchange="chooseTypeReceiver(this.form)"/> {{tr}}{{$_actors}}-type-destinataire{{/tr}}
              </label>
            {{/foreach}}
          </td>
        </tr>
    </table>
  </fieldset>

  <fieldset>
    <legend>{{tr}}CInteropActor{{/tr}}</legend>

    <table class="form">
      <tr>
        <th>{{mb_label object=$actor field="nom"}}</th>
        <td>{{mb_field object=$actor field="nom"}}</td>
      </tr>
      <tr>
      <tr>
        <th>{{mb_label object=$actor field="group_id"}}</th>
        <td>{{mb_field object=$actor field="group_id" form="create_phast_actor" autocomplete="true,1,50,true,true"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$actor field="actif"}}</th>
        <td>{{mb_field object=$actor field="actif"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$actor field="role"}}</th>
        <td>{{mb_field object=$actor field="role" typeEnum="radio"}}</td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        </td>
      </tr>
    </table>
  </fieldset>
</form>