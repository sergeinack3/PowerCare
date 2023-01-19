{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    var form = getForm("tools-{{$_tool_class}}-{{$_tool}}");
    form.limit_admit.addSpinner({min: 1});
    Calendar.regField(form.elements._date_min);
    Calendar.regField(form.elements._date_max);
  });

  disabledFieldsForm = function (element) {
    if (element.name == "list_nda_all") {
      if (element.value) {
        document.getElementById('list_ipp_all').disabled  = true;
      }
      else {
        document.getElementById('list_ipp_all').disabled  = false;
      }
    }
    if (element.name == "list_ipp_all") {
      if (element.value) {
        document.getElementById('list_nda_all').disabled  = true;
      }
      else {
        document.getElementById('list_nda_all').disabled  = false;
      }
    }
  };

  function automatic{{$_tool}}(){
    var form = getForm("tools-{{$_tool_class}}-{{$_tool}}");

    if (!$V(form["continue"])) {
      return;
    }
    form.onsubmit();
  }
</script>

<form name="tools-{{$_tool_class}}-{{$_tool}}" method="get" action="?"
      onsubmit="return onSubmitFormAjax(this, null, 'tools-{{$_tool_class}}-{{$_tool}}')">
  <input type="hidden" name="m" value="eai" />
  <input type="hidden" name="a" value="ajax_create_all_movement" />
  <input type="hidden" name="action" value="" />
  <input type="hidden" name="tools" value="{{$_tool}}" />
  <input type="hidden" name="reset" value="" />

  <fieldset class="me-no-box-shadow me-padding-0">
    <table class="main form">
      <tr>
        <th>{{tr}}CInteropReceiver{{/tr}}</th>
        <td>
          <select name="receiver_guid" id="receiver_guid" onchange="disabledFieldsForm(this)">
            <option></option>
            {{foreach from=`$receivers.CReceiverHL7v2` item=_receiver}}
              <option value="{{$_receiver->_guid}}">{{$_receiver}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <th>Nombre max de séjours par envoi</th>
        <td><input type="number" name="limit_admit" value="30" size="3" title="Nombre max de séjours à traiter" /></td>
      </tr>
      <tr>
        <th>Annuler les mouvements associés</th>
        <td><input type="checkbox" name="cancel_movement" checked/></td>
      </tr>
      <tr>
        <th>Uniquement les séjours en cours</th>
        <td><input type="checkbox" name="admit_in_progress"/></td>
      </tr>
      <tr>
        <th>Uniquement les séjours terminés</th>
        <td><input type="checkbox" name="admit_closed"/></td>
      </tr>
      <tr>
        <th>Automatique</th>
        <td><input type="checkbox" name="continue"/></td>
      </tr>
      <tr>
        <th>Essai à blanc</th>
        <td><input type="checkbox" checked="checked" name="blank"/></td>
      </tr>
      <tr>
        <th>Type de message</th>
        <td><input type="text" name="type_message"/></td>
      </tr>
    </table>
  </fieldset>
  
  <table>
    <tr>
      <td width="33%">
        <fieldset class="me-no-box-shadow me-padding-0">
          <table class="main form">
            <tr>
              <th>{{mb_label class=CExchangeDataFormat field="_date_min"}}</th>
              <td>
                <input class="dateTime notNull" type="hidden" name="_date_min" value="{{$date_min}}" />
              </td>
            </tr>
            <tr>
              <th>{{mb_label class=CExchangeDataFormat field="_date_max"}}</th>
              <td>
                <input class="dateTime notNull" type="hidden" name="_date_max" value="{{$date_max}}" />
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td>OU</td>
    </tr>
    <tr>
      <td>
        <fieldset class="me-no-box-shadow me-padding-0">
          <table class="main form">
            <tr>
              <th>IPP (si plusieurs mettre des |)</th>
              <td><input type="text" name="list_ipp_all" id="list_ipp_all" value="" size="40" onchange="disabledFieldsForm(this)"
                         placeholder="IPP des patients des séjours à rejouer" /></td>
            </tr>
            <tr>
              <th>NDA (si plusieurs mettre des |)</th>
              <td><input type="text" name="list_nda_all" id="list_nda_all" value="" size="40" onchange="disabledFieldsForm(this)"
                         placeholder="NDA des séjours à rejouer" /></td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <button type="submit" class="new" onclick="this.form.elements.reset.value = '0'">
          {{tr}}CEAI-tools-{{$_tool_class}}-generate{{/tr}}
        </button>
        <button type="submit" class="cancel" onclick="this.form.elements.reset.value = '1'">
          {{tr}}Reset{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>


