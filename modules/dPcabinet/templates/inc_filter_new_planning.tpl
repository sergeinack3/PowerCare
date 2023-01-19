{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=filter_ressources value=0}}
{{mb_default var=highlight value=0}}
{{mb_default var=isCabinet value=0}}
{{mb_script module=cabinet script=planning ajax=$ajax}}

{{if isset($prats|smarty:nodefaults) && isset($ressources|smarty:nodefaults)}}
  {{assign var=filter_ressources value=1}}
{{/if}}

{{if $isCabinet}}
  <script>
    Main.add(function () {
      Planning.togglePractitioners();
      Planning.reloadRessources('{{$function_id}}');
    });
  </script>
{{/if}}

<table class="main">
  <tr>
    <td class="halfPane" style="vertical-align: top;">
      <fieldset>
        <legend>
          {{tr}}CPlageconsult{{/tr}}
        </legend>
        <table class="form me-no-box-shadow">
          <tr>
            {{me_form_bool nb_cells=2 label="CPlageConsult-action-Show free range|pl"}}
              <label>
                <input type="radio" name="show_free" value="1" checked /> {{tr}}Yes{{/tr}}
              </label>
              <label>
                <input type="radio" name="show_free" value="0" /> {{tr}}No{{/tr}}
              </label>
            {{/me_form_bool}}
          </tr>
          <tr>
            {{me_form_field nb_cells=2 label="CPlageconsult-Display according to holiday|pl"}}
              <select name="hide_in_conge" style="width: 15em;">
                <option value="0">{{tr}}common-action-Display all{{/tr}}</option>
                <option value="1">{{tr}}CPlageconsult-action-Hide everything if holiday|pl{{/tr}}</option>
              </select>
            {{/me_form_field}}
          </tr>
          {{if $isCabinet}}
          <tr>
            {{me_form_bool nb_cells=2 label="CPlageconsult-Highlight in common"}}
              <label>
                <input type="radio" name="highlight" value="1" {{if $highlight}}checked{{/if}}> {{tr}}Yes{{/tr}}
              </label>
              <label>
                <input type="radio" name="highlight" value="0" {{if !$highlight}}checked{{/if}}> {{tr}}No{{/tr}}
              </label>
            {{/me_form_bool}}
          </tr>
          {{/if}}
          <tr>
            {{me_form_bool nb_cells=2 label="CPlageconsult-action-Hide practitioner without consultation range|pl"}}
              <label>
                <input type="radio" name="hide_empty_range" value="1" checked/> {{tr}}Yes{{/tr}}
              </label>
              <label>
                <input type="radio" name="hide_empty_range" value="0" /> {{tr}}No{{/tr}}
              </label>
            {{/me_form_bool}}
          </tr>
        </table>
      </fieldset>
    </td>
    <td style="vertical-align: top;">
      <fieldset>
        <legend>{{tr}}CConsultation{{/tr}}</legend>
        <table class="form me-no-box-shadow">
          <tr>
            {{me_form_bool nb_cells=2 label="CPlageConsult-action-Show canceled consultation|pl"}}
              <label>
                <input type="radio" name="cancelled" value="1" /> {{tr}}Yes{{/tr}}
              </label>
              <label>
                <input type="radio" name="cancelled" value="0" checked /> {{tr}}No{{/tr}}
              </label>
            {{/me_form_bool}}
          </tr>

          <tr>
            {{me_form_field nb_cells=2 label="CPlageConsult-Status of billed consultation|pl"}}
              <select name="facturated" style="width: 15em;">
                <option value="">{{tr}}CPlageConsult-See everything{{/tr}}</option>
                <option value="1">{{tr}}CPlageConsult-Only invoice|pl{{/tr}}</option>
                <option value="0">{{tr}}CPlageConsult-Only not invoice|pl{{/tr}}</option>
              </select>
            {{/me_form_field}}
          </tr>

          <tr>
            {{me_form_field nb_cells=2 label="CPlageConsult-Status of appointment|pl"}}
              <select name="finished" style="width: 15em;">
                <option value="">&mdash; {{tr}}All{{/tr}}</option>
                <option value="16">{{tr}}CConsultation.chrono.16{{/tr}}</option>
                <option value="32">{{tr}}CConsultation.chrono.32{{/tr}}</option>
                <option value="48">{{tr}}CConsultation.chrono.48{{/tr}}</option>
                <option value="64">{{tr}}CConsultation.chrono.64{{/tr}}</option>
              </select>
            {{/me_form_field}}
          </tr>

          <tr>
            {{assign var=label_field value="CActe"}}

            {{me_form_field nb_cells=2 label=$label_field}}
              <select name="actes" style="width: 15em;">
                <option value="">&mdash; {{tr}}All{{/tr}}</option>
                <option value="1">{{tr}}CActe-Only cote|pl{{/tr}}</option>
                <option value="0">{{tr}}CActe-Only not cote|pl{{/tr}}</option>
              </select>
            {{/me_form_field}}
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
  {{if $isCabinet}}
    <tr>
      <td>
        <fieldset>
          <legend>{{tr}}CMediusers-praticien|pl{{/tr}} <input type="checkbox" class="check-practitioners" checked></legend>
          <div id="filter_prats">
          </div>
        </fieldset>
      </td>
      <td>
        <fieldset>
          <legend>{{tr}}CRessourceCab|pl{{/tr}}</legend>
          <div id="filter_ressources">
          </div>
        </fieldset>
      </td>
    </tr>
  {{/if}}

  <tr>
    <td colspan="2" class="button">
      <button class="tick me-primary" type="button" onclick="{{if $isCabinet}}this.form.request_form.value = '1';{{/if}} Control.Modal.close();">{{tr}}OK{{/tr}}</button>
    </td>
  </tr>
</table>
