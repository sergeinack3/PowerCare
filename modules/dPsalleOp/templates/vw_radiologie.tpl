{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=protocole_selector ajax=true}}


<script>
  ProtocoleSelector.init = Prototype.emptyFunction;

  Main.add(
    function () {
      var form = getForm("listFilterInterv");

      /* Autocomplete des actes CCAM */
      var url = new Url('ccam', 'autocompleteCcamCodes');
      url.addParam('_codes_ccam', $V(form._codes_ccam));
      url.autoComplete(form._codes_ccam, '', {
        minChars:      1,
        dropdown:      true,
        width:         '250px',
        updateElement: function (selected) {
          Traceability.addCCAMCode(selected, form);
        }
      });

      var url = new Url("planningOp", "ajax_protocoles_autocomplete");
      url.addParam("field", "protocole_id");
      url.addParam("input_field", "search_protocole");
      url.addParam("for_sejour", "0");
      url.autoComplete(form.elements.search_protocole, null, {
        minChars:           3,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.protocole_id, id);
        }
      });

      Traceability.viewRadiologieList(form);
    }
  )
</script>

<form name="listFilterInterv" action="?" method="get" target="_blank">
  <input type="hidden" name="page" value="0" />
  <input type="hidden" name="order_col" value="" />
  <input type="hidden" name="order_way" value="ASC" />

  <fieldset>
    <table class="form">
      <tr>
        <th>{{mb_label object=$filter field="_date_min"}}</th>
        <td>{{mb_field object=$filter field="_date_min" form="listFilterInterv" register=true canNull=false}} </td>

        <th>{{mb_label object=$filter field="_date_max"}}</th>
        <td>{{mb_field object=$filter field="_date_max" form="listFilterInterv" register=true canNull=false}}</td>

        <th>{{mb_label object=$filter field=chir_id}}</th>
        <td>
          <div style="display: inline-block">
            <select name="chir_id" class="me-small" style="width: 12em;">
              <option value="">&mdash; {{tr}}CMediusers.praticiens.all{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_mediuser list=$prats selected=$filter->chir_id}}
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <th>
          <label for="ccam_codes">{{tr}}CFilterCotation-ccam_codes{{/tr}}</label>
        </th>
        <td>
          <input type="hidden" name="ccam_codes" value="{{'|'|implode:$ccam_codes}}"/>
          <input type="text" name="_codes_ccam" class="autocomplete" size="10"/>
          <span id="display_ccam_codes">
          {{foreach from=$ccam_codes item=_code}}
            {{if $_code != ''}}
              <span class="circled ccam_{{$_code}}">
                {{$_code}}
                <span style="margin-left: 5px; cursor: pointer;" onclick="Traceability.deleteCCAMCode('{{$_code}}')"
                      title="{{tr}}Delete{{/tr}}"><i class="fa fa-times"></i></span>
              </span>
            {{/if}}
          {{/foreach}}
        </span>
        </td>
        <th>
          {{tr}}CProtocole{{/tr}}
        </th>
        <td>
          <input type="hidden" name="protocole_id" value="" />
          <input name="search_protocole" />
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$filter field=ampli_id}}</th>
        <td>
          <select name="ampli_ids" size="3" multiple>
            <option value="">&mdash; {{tr}}CAmpli.all{{/tr}}</option>
            {{foreach from=$amplis item=_ampli}}
              <option value="{{$_ampli->_id}}"
                      {{if in_array($_ampli->_id, $ampli_ids)}}selected{{/if}}
              >{{$_ampli->_view}}</option>
            {{/foreach}}
          </select>
        </td>
        <th>{{mb_label object=$filter field=salle_id}}</th>
        <td>
          <select name="salle_ids" size="3" multiple>
            <option value="">&mdash; {{tr}}CSalle.all{{/tr}}</option>
            {{foreach from=$blocs item=curr_bloc}}
              <optgroup label="{{$curr_bloc->nom}}">
                {{foreach from=$curr_bloc->_ref_salles item=curr_salle}}
                  <option value="{{$curr_salle->_id}}"
                          {{if in_array($curr_salle->_id, $salle_ids)}}selected="selected"{{/if}}
                  >
                    {{$curr_salle->nom}}
                  </option>
                  {{foreachelse}}
                  <option value="" disabled>{{tr}}CSalle.none{{/tr}}</option>
                {{/foreach}}
              </optgroup>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="6" class="button">
          <button class="search" type="button"
                  onclick="Traceability.viewRadiologieList(this.form);">{{tr}}Search{{/tr}}</button>
          <button type="button" class="download"
                  onclick="Traceability.viewRadiologieList(this.form, 1);">
            {{tr}}common-action-Export{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </fieldset>
</form>

<div id="result_search_interv" style="overflow: hidden" class="me-padding-10"></div>
