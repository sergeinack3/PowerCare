{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=vaccination ajax=$ajax}}
{{mb_script module=oxCabinet script=stock ajax=$ajax}}
{{mb_script module=dPcabinet script=datamatrix_vaccin ajax=$ajax}}


{{assign var=stock_active value=false}}
{{if isset($modules.dPstock|smarty:nodefaults) && $modules.dPstock->_can->view}}
    {{assign var=stock_active value=true}}
{{/if}}

<script>
    Main.add(function () {
        Vaccination.notVaccinated();

        var vaccination_radio = 'edit-injection_vaccination_yes';
        {{if !$vaccinated}}
        vaccination_radio = 'edit-injection_vaccination_no';
        {{/if}}
        $(vaccination_radio).click();

        {{if $stock_active && !$injection->_id}}
        var edit_vaccination = getForm('edit-injection');
        var speciality_onchange = function (e) {
          $V(edit_vaccination.quantity, 0);
          $V(edit_vaccination.use_stock, 0);
          $V(edit_vaccination.cip_product, '');
          $$('.stocks')[0].style.display = 'none';
        };

        var speciality_onfocusout = function() {
          edit_vaccination.speciality.addEventListener('change', speciality_onchange);
        }

        new Url('stock', 'httpreq_product_list_autocomplete')
            .autoComplete(edit_vaccination.speciality, null, {
                minChars: 1,
                updateElement: function (field) {
                    $V(edit_vaccination.quantity, 0);
                    $V(edit_vaccination.speciality, '');
                    $V(edit_vaccination.cip_product, '');
                    edit_vaccination.quantity.max = 0;

                    // suspend listener (prevent remove value after change it)
                    edit_vaccination.speciality.removeEventListener('change', speciality_onchange)

                    $V(edit_vaccination.cip_product, field.querySelector('small.code').innerHTML);
                    $V(edit_vaccination.speciality, field.querySelector('small.ucd-view').innerHTML);

                    let qte = field.querySelector('small.quantity').innerHTML
                    let qte_left = qte !== "" ? parseInt(qte) : 0;
                    $$('.qte-left')[0].innerHTML = qte_left;
                    $$('.tr-qte-left')[0].innerHTML = (qte_left > 1) ? $T('CProductStockGroup-left|pl') : $T('CProductStockGroup-left');
                    $('qte-text').className = (qte_left === 0) ? 'small-warning' : 'small-info';
                    edit_vaccination.quantity.max = qte_left;
                    $$('.stocks')[0].style.display = 'table-row';
                    $V(edit_vaccination.use_stock, 1);
                }
            });

            // when speciality change ==> reset values
            edit_vaccination.speciality.addEventListener('change', speciality_onchange);

            // after event "focusout" on speciality we add listener for change value (prevent reset value after change it)
            edit_vaccination.speciality.addEventListener('focusout', speciality_onfocusout)
          {{else}}
            var edit_vaccination = getForm('edit-injection');
            new Url("cabinet", 'autocompleteVaccination').autoComplete(edit_vaccination.speciality, null, {
              minChars: 2,
              dropdown : true,
              select : 'view',
              width: 305,
              updateElement: function (field) {
                $V(edit_vaccination.speciality, '');

                $V(edit_vaccination.speciality, field.querySelector('small.ucd-view').innerHTML);
              }
            });
        {{/if}}
      Calendar.regField(getForm("edit-injection").date_datepicker, null, {noView: true});

    })
</script>

<div id="edit_injection">
    <form method="post" name="edit-injection">
        {{mb_field object=$injection field=recall_age hidden=1}}
        {{mb_key object=$injection}}
        <input type="hidden" name="isInfoBulle" value="{{$view}}">
        <table class="form me-no-box-shadow">
            <tr>
                <th class="title" colspan="4">
                    {{if $injection->_id}}
                        {{tr}}Edit-vaccination{{/tr}}
                    {{else}}
                        {{tr}}New-vaccination{{/tr}}
                    {{/if}}
                </th>
            </tr>

            {{* Patient *}}
            <tr>
                {{me_form_field label=Patient nb_cells=4}}
                    <div class="me-field-content">
                        <input type="hidden" name="birthday" value="{{$patient->naissance}}">
                        <input type="hidden" name="patient_id" value="{{$patient->_id}}">
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
              {{$patient->_view}}
            </span>
                    </div>
                {{/me_form_field}}
            </tr>

            {{* Age to vaccinate *}}
            <tr>
                {{me_form_field label="Appropriate-age" nb_cells=4 field_class="me-field-max-w33"}}
                    <div class="me-field-content">{{$recall_string_date}}</div>
                {{/me_form_field}}
            </tr>

            <button class="me-tertiary fas fa-qrcode" type="button" onclick="dataVacc.openModalReadDatamatrix(0)">
                Ajouter vaccin
            </button>

            {{* Type (the id of the vaccine in the json file *}}
            <tr>
                {{me_form_field label=Vaccine nb_cells=4 layout=true field_class="me-margin-top-10"}}
                {{if $injection->_ref_vaccinations}}<ul class="me-padding-0" style="list-style: none;">{{/if}}
                {{foreach from=$vaccines item=_vaccine}}
                    {{assign var=active_item value='Ox\Mediboard\Cabinet\Vaccination\CVaccination::isVaccinationActive'|static_call:$_vaccine:$injection->_ref_vaccinations}}
                    {{assign var=recall_age value=$injection->recall_age}}

                    {{if !$injection->_id && isset($available_items[$recall_age]|smarty:nodefaults) && in_array($_vaccine->type, $available_items[$recall_age])}}
                        <input id="edit-injection_vaccine-{{$_vaccine->type}}"
                               class="form-vaccines {{if $active_item}}form-vaccines-active{{/if}}"
                               type="checkbox"
                               name="vaccines[]"
                               value="{{$_vaccine->type}}"
                               {{if $active_item}}checked{{/if}}
                                {{if count($available_items[$recall_age]) <= 1 || $_vaccine->type|in_array:$vaccines_same_recall}}disabled{{/if}}>
                        <label for="vaccine-{{$_vaccine->type}}">{{$_vaccine->longname}}</label>
                        <br>
                    {{else}}
                        {{if $active_item}}
                            <li>{{$_vaccine->longname}}</li>
                        {{/if}}
                    {{/if}}
                {{/foreach}}
                {{if $injection->_ref_vaccinations}}</ul>{{/if}}
                {{/me_form_field}}
            </tr>

            {{* Is an injection ? *}}
            <tr>
                {{me_form_field label=CVaccination nb_cells=4 layout=true}}
                    <input id="edit-injection_vaccination_yes" type="radio" name="vaccination" value="1"
                           {{if $vaccinated}}checked{{/if}}>
                    <label for="vaccination_yes">{{tr}}Yes{{/tr}}</label>
                    <input id="edit-injection_vaccination_no" type="radio" name="vaccination" value="0"
                           {{if !$vaccinated}}checked{{/if}}>
                    <label for="vaccination_no">{{tr}}No{{/tr}}</label>
                {{/me_form_field}}
            </tr>

            {{* Speciality (product) *}}
            <tr class="vaccination-yes">
                {{me_form_field mb_class=$injection mb_field=speciality nb_cells=4 field_class="me-margin-top-10"}}
                {{if $injection->_id}}
                    {{mb_field object=$injection field=speciality readonly=true}}
                {{else}}
                    {{mb_field object=$injection field=speciality}}
                {{/if}}

                {{/me_form_field}}
                {{mb_field object=$injection field=cip_product hidden=true}}
            </tr>

            {{* Quantity if stock is enabled *}}
            {{if $stock_active}}
                <tr class="stocks" style="display: none;">

                    {{me_form_field mb_class=CProductStockGroup mb_field=quantity nb_cells=4 field_class="me-input-field-max-w25"}}
                        <input type="number" name="quantity" style="width: 60px;" class="num" min="0" step="1"
                               value="0">
                        <div style="display: inline-block; width: 50%; font-size: 1.2em; margin-left: 8px;"
                             id="qte-text"><span class="qte-left"></span> <span class="tr-qte-left"></span></div>
                        <script>
                            Main.add(function () {
                                // Small hack to go over the form prepare
                                getForm('edit-injection').quantity.type = 'number';
                            })
                        </script>
                    {{/me_form_field}}
                </tr>
            {{/if}}

            {{* Batch (Lot) *}}
            <tr class="vaccination-yes">
                {{me_form_field mb_class=$injection mb_field=batch nb_cells=4}}
                {{mb_field object=$injection field=batch}}
                {{/me_form_field}}
            </tr>

            {{* Expiration date *}}
             <tr>
                {{me_form_field mb_class=$injection mb_field=expiration_date nb_cells=4}}
                {{mb_field object=$injection field=expiration_date register=true form="edit-injection"}}
                {{/me_form_field}}
            </tr>

            {{* Practitionner *}}
            <tr>
                {{me_form_field mb_class=$injection mb_field=practitioner_name nb_cells=4 field_class="me-margin-top-10"}}
                {{mb_field object=$injection field=practitioner_name}}
                {{/me_form_field}}
            </tr>

            {{* Injection date *}}
            <tr>
                {{me_form_field mb_class=$injection mb_field=_date_injection nb_cells=1}}
                  {{assign var=date value=$injection->injection_date|date_format:"%Y-%m-%d"}}
                  {{mb_field object=$injection field=_date_injection value=$date form="edit-injection" class="notNull" onchange='Vaccination.checkRecallCurrentDate()'}}

                {{/me_form_field }}
              <td class="me-padding-top-10">
                <input type="hidden" name="date_datepicker" class="date" value="{{$date}}" onchange="Vaccination.changeDate(getForm('edit-injection'));" />
                <button type="button" class="erase me-tertiary me-padding-bottom-2" onclick="getForm('edit-injection')._date_injection.value ='';"></button>
              </td>
            </tr>
          <tr>
            <td>
              <div id="warning-injection_date"
                   class="warning "
                   style="{{if $injection->isDateCoherent($patient->naissance)}}display: none;{{/if}}">
                <p>{{tr}}Date of injection is distant{{/tr}}
                    {{if $injection->recall_age}}
                        {{if $injection->recall_age > 23}}
                          {{assign var=years value=$injection->recall_age/12}}
                          ({{tr}}current recall{{/tr}} {{$years|floor}} {{tr}}years{{/tr}})
                        {{else}}
                          ({{tr}}current recall{{/tr}}: {{$injection->recall_age}} {{tr}}months{{/tr}})
                        {{/if}}
                    {{/if}}
                </p>
              </div>
            </td>
          </tr>
            <tr>
                {{me_form_field mb_class=$injection mb_field=_heure_injection nb_cells=4}}
                  {{assign var=heure value=$injection->injection_date|date_format:'%H:%M'}}
                  {{mb_field object=$injection field=_heure_injection value=$heure register=true form="edit-injection"}}
                {{/me_form_field }}
            </tr>

            {{* Remarques *}}
            <tr class="remarques">
                {{me_form_field mb_class=$injection mb_field=remarques nb_cells=4 field_class="me-margin-top-10"}}
                {{mb_field object=$injection field=remarques form="edit-injection" aidesaisie="validateOnBlur: 0"}}
                {{/me_form_field}}
            </tr>

            <tr>
                <td colspan="4" style="text-align: center">
                    <input type="hidden" name="delete" value="0">
                    <input type="hidden" name="use_stock" value="0">
                    <button class="save"
                            type="button"
                            onclick="Vaccination.makeInjection(this.form, true, !!parseInt(this.form.use_stock.value, 10), false)">
                        {{tr}}Save{{/tr}}
                    </button>
                    {{if $injection->_id}}
                        <button class="print"
                                type="button"
                                onclick="Vaccination.choiceNbEtiquette('{{$injection->_id}}')">
                            {{tr}}CVaccination-action-print{{/tr}}
                        </button>
                        <button class="trash"
                                type="button"
                                onclick="this.form.delete.value = 1; Vaccination.makeInjection(this.form, true, false)">
                            {{tr}}Delete{{/tr}}
                        </button>
                    {{else}}
                        <button class="save_print"
                                type="button"
                                onclick="Vaccination.makeInjectionAndPrint(this.form, true, !!parseInt(this.form.use_stock.value, 10))">
                            {{tr}}CVaccination-action-save and print{{/tr}}
                        </button>
                    {{/if}}
                </td>
            </tr>
        </table>
    </form>
</div>
