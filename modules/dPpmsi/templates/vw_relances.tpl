{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=pmsi script=relance}}

<script>

  Main.add(function() {
    var form = getForm("filterRelances");

    Calendar.regField(form.date_min_relance);
    Calendar.regField(form.date_max_relance);
    Calendar.regField(form.date_min_sejour);
    Calendar.regField(form.date_max_sejour);

    Relance.usersAutocomplete(form);
    Relance.searchRelances();
  });
</script>

<form name="filterRelances" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result_relances');">
  <input type="hidden" name="m" value="pmsi" />
  <input type="hidden" name="a" value="searchRelances" />

  <table class="form">
    <tr>
      <th class="title" colspan="2">{{tr}}CRelancePMSI-Filter reminders{{/tr}}</th>
    </tr>
    <tr>
      <td class="halfPane">
        <fieldset>
          <legend class="fas fa-filter"> {{tr}}CRelance{{/tr}}</legend>
          <table class="form me-no-box-shadow">
            <tr>
              {{me_form_field animated=false nb_cells=2 label="CRelancePMSI-Restate Status"}}
                <select name="status">
                  <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
                  <option value="non_cloturees"     {{if $status == "non_cloturees"    }}selected{{/if}}>{{tr}}CRelancePMSI-Not closed{{/tr}}</option>
                  <option value="datetime_creation" {{if $status == "datetime_creation"}}selected{{/if}}>{{tr}}CRelancePMSI-First raise{{/tr}}</option>
                  <option value="datetime_relance"  {{if $status == "datetime_relance" }}selected{{/if}}>{{tr}}CRelancePMSI-Second raise{{/tr}}</option>
                  <option value="datetime_cloture"  {{if $status == "datetime_cloture" }}selected{{/if}}>{{tr}}CRelancePMSI-Closed{{/tr}}</option>
                </select>
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field animated=false nb_cells=2 label="CRelancePMSI-urgence"}}
                <select name="urgence">
                  <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
                  <option value="normal" {{if $urgence == "normal"}}selected{{/if}}>{{tr}}CRelancePMSI.urgence.normal{{/tr}}</option>
                  <option value="urgent" {{if $urgence == "urgent"}}selected{{/if}}>{{tr}}CRelancePMSI.urgence.urgent{{/tr}}</option>
                </select>
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field animated=false nb_cells=2 label="CRelancePMSI-Doc type"}}

                <select name="type_doc">
                  <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
                  <option value="cra" {{if $type_doc == "cra"}}selected{{/if}} {{if !"dPpmsi relances cra"|gconf}}style="display: none;"{{/if}}>
                      {{tr}}CRelancePMSI-cra-desc{{/tr}}
                  </option>
                  <option value="crana" {{if $type_doc == "crana"}}selected{{/if}} {{if !"dPpmsi relances crana"|gconf}}style="display: none;"{{/if}}>
                      {{tr}}CRelancePMSI-crana-desc{{/tr}}
                  </option>
                  <option value="cro" {{if $type_doc == "cro"}}selected{{/if}} {{if !"dPpmsi relances cro"|gconf}}style="display: none;"{{/if}}>
                      {{tr}}CRelancePMSI-cro-desc{{/tr}}
                  </option>
                  <option value="ls"  {{if $type_doc == "ls" }}selected{{/if}} {{if !"dPpmsi relances ls"|gconf}}style="display: none;"{{/if}}>
                      {{tr}}CRelancePMSI-ls{{/tr}}
                  </option>
                  <option value="cotation"  {{if $type_doc == "cotation" }}selected{{/if}} {{if !"dPpmsi relances cotation"|gconf}}style="display: none;"{{/if}}>
                      {{tr}}CRelancePMSI-cotation{{/tr}}
                  </option>
                  <option value="autre"  {{if $type_doc == "autre" }}selected{{/if}} {{if !"dPpmsi relances autre"|gconf}}style="display: none;"{{/if}}>
                      {{tr}}CRelancePMSI-autre{{/tr}}
                  </option>
                </select>
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field animated=false nb_cells=2 label="PMSI-Medical comment-court"}}
                <select name="commentaire_med">
                  <option value="">&mdash; {{tr}}CRelancePMSI-Indifferent{{/tr}}</option>
                  <option value="0" {{if $commentaire_med == "0"}}selected{{/if}}>{{tr}}CRelancePMSI-Empty{{/tr}}</option>
                  <option value="1" {{if $commentaire_med == "1"}}selected{{/if}}>{{tr}}CRelancePMSI-Informed{{/tr}}</option>
                </select>
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field animated=false nb_cells=2 mb_object=$sejour mb_field=type}}
                {{mb_field object=$sejour field=type canNull="true" emptyLabel="CRelancePMSI-Indifferent"}}
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 label="PMSI-Responsible doctor-court"}}
                <input type="hidden" name="chir_id" value="{{$chir->_id}}"/>
                <input type="text" name="chir_id_view" value="{{$chir}}" />
                <button type="button" class="cancel notext me-tertiary me-dark" onclick="$V(this.form.chir_id, ''); $V(this.form.chir_id_view, '');"></button>
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 label="CIdSante400-_start_date"}}
                <input type="hidden" name="date_min_relance" class="date notNull" value="{{$date_min_relance}}" />
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 label=CIdSante400-_end_date-court}}
                <input type="hidden" name="date_max_relance" class="date notNull" value="{{$date_max_relance}}" />
              {{/me_form_field}}
            </tr>
          </table>
        </fieldset>
      </td>
      <td>
        <fieldset>
          <legend>{{tr}}CSejour{{/tr}}</legend>
          <table class="form me-no-box-shadow">
            <tr>
              {{me_form_field nb_cells=2 label="NDA"}}
                <input type="text" name="NDA" class="barcode" {{*onkeyup="if (this.value.length)"*}} />
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 label="CIdSante400-_start_date"}}
                <input type="hidden" name="date_min_sejour" class="date" value="{{$date_min_sejour}}" />
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 label=CIdSante400-_end_date-court}}
                <input type="hidden" name="date_max_sejour" class="date" value="{{$date_max_sejour}}" />
              {{/me_form_field}}
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="tick me-primary" onclick="Relance.searchRelances();">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result_relances" class="me-padding-0"></div>
