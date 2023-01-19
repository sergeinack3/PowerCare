{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    var form = getForm("tools-{{$_tool_class}}-{{$_tool}}");
    form.count.addSpinner({min: 1});

    Calendar.regField(form.elements.date_min);
    Calendar.regField(form.elements.date_max);
  });
  
  function next{{$_tool}}(){
    var form = getForm("tools-{{$_tool_class}}-{{$_tool}}");
  
    if (!$V(form["continue"])) {
      return;
    }
  
    form.onsubmit();
  }

  function disabledFields(element) {
    if (element.name == "sejour_type") {
      $('charge_price_id').disabled = element.value;
      $('mediuser_id').disabled     = element.value;
      $('list_ipp').disabled        = element.value;
      $('list_nda').disabled        = element.value;
    }
    if (element.name == "charge_price_id") {
      $('sejour_type').disabled = element.value;
      $('mediuser_id').disabled = element.value;
      $('list_ipp').disabled    = element.value;
      $('list_nda').disabled    = element.value;
    }
    if (element.name == "mediuser_id") {
      $('sejour_type').disabled     = element.value;
      $('charge_price_id').disabled = element.value;
      $('list_ipp').disabled        = element.value;
      $('list_nda').disabled        = element.value;
    }
    if (element.name == "list_ipp") {
      $('sejour_type').disabled     = element.value;
      $('charge_price_id').disabled = element.value;
      $('mediuser_id').disabled     = element.value;
      $('list_nda').disabled        = element.value;
    }
  }
</script>

{{mb_script module="eai" script="action"}}

<form name="tools-{{$_tool_class}}-{{$_tool}}" method="get" action="?" 
  onsubmit="return onSubmitFormAjax(this, null, 'tools-{{$_tool_class}}-{{$_tool}}')">
  <input type="hidden" name="m" value="eai" />
  <input type="hidden" name="a" value="ajax_resend_exchange" />
  <input type="hidden" name="tool" value="{{$_tool}}" />
  <input type="hidden" name="suppressHeaders" value="1" />
  <input type="hidden" name="action" value="" />

  <table class="main form">
    <tr>
      <td width="32%">
        <fieldset class="me-no-box-shadow me-padding-0">
          <table class="main form">
            <tr>
              <th>{{mb_label class=CExchangeDataFormat field="_date_min"}}</th>
              <td>
                <input class="dateTime notNull" type="hidden" name="date_min" value="{{$date_min}}" />
              </td>
            </tr>
            <tr>
              <th>{{mb_label class=CExchangeDataFormat field="_date_max"}}</th>
              <td>
                <input class="dateTime notNull" type="hidden" name="date_max" value="{{$date_max}}" />
              </td>
            </tr>
            <tr>
              <th>{{tr}}CSejour-type{{/tr}}</th>
              <td>
                {{assign var=types_sejours value='Ox\Mediboard\PlanningOp\CSejour'|static:"types"}}
                <select name="sejour_type" id="sejour_type" onchange="disabledFields(this)">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$types_sejours item=_sejour_type}}
                    <option value="{{$_sejour_type}}">{{tr}}CSejour.type.{{$_sejour_type}}{{/tr}}</option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <th>{{tr}}CChargePriceIndicator{{/tr}}</th>
              <td>
                <select name="charge_price_id" id="charge_price_id" onchange="disabledFields(this)">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$modes_traitement item=_mode_traitement}}
                    <option value="{{$_mode_traitement->_id}}">{{$_mode_traitement}}</option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <th>{{tr}}CSejour-praticien_id{{/tr}}</th>
              <td>
                <select name="mediuser_id" id="mediuser_id" onchange="disabledFields(this)">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$mediusers item=_mediuser}}
                    <option value="{{$_mediuser->_id}}">{{$_mediuser}}</option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td width="1%">
        <strong>ou</strong>
      </td>
      <td width="33%">
        <fieldset class="me-no-box-shadow me-padding-0">
          <table class="main form">
            <tr>
              <th>IPP (si plusieurs mettre des |)</th>
              <td><input type="text" name="list_ipp" id="list_ipp" value="" size="40" onchange="disabledFields(this)"
                         placeholder="IPP des patients des séjours à rejouer" /></td>
            </tr>

            <tr>
              <th>NDA (si plusieurs mettre des |)</th>
              <td><input type="text" name="list_nda" id="list_nda" value="" size="40" onchange="disabledFields(this)"
                         placeholder="NDA des séjours à rejouer" /></td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td width="33%">
        <fieldset class="me-no-box-shadow me-padding-0">
          <table class="main form">
            <tr>
              <th>{{tr}}CInteropReceiver{{/tr}}</th>
              <td>
                <select name="receiver_guid">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$receivers item=_receivers key=_class}}
                    <optgroup label="{{tr}}{{$_class}}{{/tr}}">
                      {{foreach from=$_receivers item=_receiver}}
                        <option value="{{$_receiver->_guid}}">{{$_receiver}}</option>
                      {{/foreach}}
                    </optgroup>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <th>Séjours ayant aucun échange généré</th>
              <td>
                <label><input type="radio" name="without_exchanges" value="1"  /> Oui</label>
                <label><input type="radio" name="without_exchanges" value="0" checked /> Non</label>
              </td>
            </tr>
            <tr>
              <th>Uniquement les séjours de préadmission</th>
              <td>
                <label><input type="radio" name="only_pread" value="1"  /> Oui</label>
                <label><input type="radio" name="only_pread" value="0" checked /> Non</label>
              </td>
            </tr>
            <tr>
              <th>{{tr}}CMovement{{/tr}}</th>
              <td>
                <select name="movement_type">
                  {{foreach from=$movement->_specs.movement_type->_locales key=_type item=_locale}}
                    <option value="{{$_type}}">{{$_locale}}</option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <th>Nombre max de séjours par envoi</th>
              <td><input type="text" name="count" value="30" size="3" title="Nombre d'échanges à traiter" /></td>
            </tr>
            <tr>
              <th>Automatique</th>
              <td><input type="checkbox" name="continue" value="1" title="Automatique" /></td>
            </tr>
            <tr>
              <td colspan="2">
                <button type="button" class="new" onclick="$V(this.form.action, 'start'); this.form.onsubmit()">
                  {{tr}}CEAI-tools-{{$_tool_class}}-{{$_tool}}-button{{/tr}}
                </button>
                <button type="button" class="change" onclick="$V(this.form.action, 'retry'); this.form.onsubmit()">
                  {{tr}}Retry{{/tr}}
                </button>
                <button type="button" class="tick" onclick="$V(this.form.action, 'continue'); this.form.onsubmit()">
                  {{tr}}Continue{{/tr}}
                </button>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
</form>