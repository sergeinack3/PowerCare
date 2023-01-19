{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=cabinet script=plage_selector}}
{{mb_script module=cabinet  script=plage_consultation ajax=1}}

{{assign var=plage_count value=0}}

<script>
  Main.add(function () {
    CreneauConsultation.showListNextSlots('{{$list_functions->_id}}', '{{$week_number}}', '{{$rdv}}', '{{$debut|date_format:"%Y"}}');

    {{if !$list_functions->_id}}
    $$('input.praticiens').each(function (elt) {
      elt.disabled = true;
    });

    $$('input.hour').each(function (elt) {
      elt.disabled = true;
    });
    {{/if}}

    {{if $only_func}}
    $$('.all_praticiens').invoke('writeAttribute', 'checked', true);
    $$('.select_praticiens').each(function (elt) {
      elt.down('input').checked = 'checked';
    });
    {{/if}}

    // Selectionner toutes les heures par défaut
    {{if !$count_hours || $count_hours == 14}}
    $$('.select_hours').each(function (elt) {
      elt.down('input').checked = 'checked';
    });
    {{/if}}

    //autocomplete libelle plage
    var form = getForm('selectNextSlots');
    var url = new Url("cabinet", "ajax_plage_libelle_autocomplete");
    url.addParam('function_id', '{{$function}}');
    url.addParam('date', '{{$debut}}');
    url.addParam('prat_id', '{{$praticien->_id}}');
    url.addParam('rdv', '{{$rdv}}');
    url.autoComplete(form.plage_libelle, null, {
      minChars:           2,
      method:             "get",
      width:              "250px",
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        var name = selected.getAttribute("id");
        $V(form.libelle, name);
        $V(field, name);

        CreneauConsultation.showListNextSlots('{{$list_functions->_id}}', '{{$week_number}}', '{{$rdv}}', '{{$debut|date_format:"%Y"}}');
      }
    });
  });

</script>

<!-- Formulaire de sauvegarde du choix de la semaine + X en préférence utilisateur -->
<form name="editPrefFreeSlot" method="post">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$app->user_id}}" />
  <input type="hidden" name="pref[search_free_slot]" value="" />
</form>

{{assign var=start_week_number value=$app->user_prefs.search_free_slot}}

<div id="next_slot">
  <table class="main">
    <tr>
      <th class="title">
        {{tr}}CPlageconsult-The next 10 slots for the|pl{{/tr}} {{tr}}CFunction{{/tr}} {{$list_functions->_view}} <br />
        {{tr var1=$debut|date_format:$conf.longdate}}CPlageconsult-Beginning of the search on %s{{/tr}} ( + <input type="number"
                                                                                                                   style="width: 30px;"
                                                                                                                   value="{{$start_week_number}}"
                                                                                                                   onchange="CreneauConsultation.savePrefSlotAndReload(this.value);" />
        {{tr}}common-week(|pl){{/tr}} )
      </th>
    </tr>
    <tr>
      <td>
        <form name="selectNextSlots" method="get">
          <table class="tbl">
            <tr>
              <td class="text halfPane me-valign-top">
                <fieldset>
                  <legend>
                    <label for="check_all_prats" title="{{tr}}common-Practitioner|pl{{/tr}}">
                      <input type="checkbox" name="check_all_prats" value=""
                             onclick="$$('.select_praticiens').each(function(elt) { elt.down('input').checked=this.checked ? 'checked' : ''; }.bind(this));
                               CreneauConsultation.showListNextSlots('{{$list_functions->_id}}', '{{$week_number}}', '{{$rdv}}', '{{$debut|date_format:"%Y"}}');"
                             class="all_praticiens" />
                      <i class="fas fa-user-md"></i> {{tr}}CConsultation-Practitioner office(|pl){{/tr}} {{$list_functions->_view}}
                    </label>
                  </legend>

                  {{if ($praticien->_id && $rdv) || !$only_func}}
                  <label>
                        <span class="select_praticiens">
                          <input type="checkbox" name="prats_ids[{{$praticien->_id}}]"
                                 value="{{$praticien->_id}}" {{if $prat_id == $praticien->_id}}checked{{/if}}
                                 class="praticiens" />
                          {{$praticien->_view}}
                        </span>
                  </label>
                  {{else}}
                    {{counter start=0 skip=1 assign=curr_data}}
                    <table>
                    {{foreach from=$listPrat item=_praticien name=list_praticiens}}
                        {{if $curr_data is div by 4 || $curr_data == 0}}
                          <tr>
                        {{/if}}
                          <td>
                            <label>
                              <span class="select_praticiens">
                                <input type="checkbox" name="prats_ids[{{$_praticien->_id}}]"
                                       value="{{$_praticien->_id}}" {{if in_array($_praticien->_id, $prats_ids)}}checked{{/if}}
                                       onchange="CreneauConsultation.showListNextSlots('{{$list_functions->_id}}', '{{$week_number}}', '{{$rdv}}', '{{$debut|date_format:"%Y"}}');"
                                       class="praticiens" />
                                {{$_praticien->_view}}
                              </span>
                            </label>
                          </td>
                        {{if (($curr_data+1) is div by 4 || $smarty.foreach.list_praticiens.last)}}
                          </tr>
                        {{/if}}
                      {{counter}}
                    {{/foreach}}
                    </table>
                  {{/if}}
                </fieldset>
              </td>
              <td class="me-valign-top">
                <table>
                  <tr>
                    <td class="quarterPane me-valign-top">
                      <fieldset>
                        <legend><i class="fas fa-check-circle"></i> {{tr}}CPlageconsult-Name of time slot|pl{{/tr}}</legend>
                        <label for="">{{tr}}common-Name{{/tr}}</label>
                        <input type="text" name="plage_libelle" class="autocomplete" value="{{$libelle_plage}}" style="width: 200px;" />
                        <input type="hidden" name="libelle" value="" />
                      </fieldset>
                    </td>
                    <td class="quarterPane me-valign-top">
                      <fieldset>
                        <legend>
                          <label for="check_all_weekday" title="{{tr}}CPlageconsult-date{{/tr}}">
                            <input type="checkbox" name="check_all_weekday" value=""
                                   onclick="$$('.select_days').each(function(elt) { elt.down('input').checked=this.checked ? 'checked' : ''; }.bind(this));
                                     CreneauConsultation.showListNextSlots('{{$list_functions->_id}}', '{{$week_number}}', '{{$rdv}}', '{{$debut|date_format:"%Y"}}');"
                                   class="all_days" />
                            <i class="far fa-calendar-alt"></i> {{tr}}CPlageconsult-date{{/tr}}
                          </label>
                        </legend>
                        {{foreach from=$days_name item=_day}}
                          <label>
                            <span class="select_days">
                              <input type="checkbox" name="days[{{$_day}}]" value="{{$_day}}"
                                     {{if in_array($_day, $days)}}checked{{/if}}
                                     onchange="CreneauConsultation.showListNextSlots('{{$list_functions->_id}}', '{{$week_number}}', '{{$rdv}}', '{{$debut|date_format:"%Y"}}');"
                                     class="weekday" />
                               {{tr}}{{$_day}}{{/tr}}
                            </span>
                          </label>
                        {{/foreach}}
                      </fieldset>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      <fieldset>
                        <legend>
                          <label for="check_all_times" title="{{tr}}CPlageconsult-Time of day{{/tr}}">
                            <input type="checkbox" name="check_all_times" value="" {{if !$count_hours || $count_hours == 14}}checked{{/if}}
                                   class="select_all_hours"
                                   onclick="$$('.select_hours').each(function(elt) { elt.down('input').checked=this.checked ? 'checked' : ''; }.bind(this));
                                     CreneauConsultation.showListNextSlots('{{$list_functions->_id}}', '{{$week_number}}', '{{$rdv}}', '{{$debut|date_format:"%Y"}}');"
                                   class="all_times" />
                            <i class="far fa-clock"></i> {{tr}}common-Hour|pl{{/tr}}
                          </label>
                        </legend>
                        {{foreach from=$times_hour item=_time}}
                          <label>
                      <span class="select_hours">
                        <input type="checkbox" name="times[{{$_time}}]" value="{{$_time}}"
                               {{if in_array($_time, $times)}}checked{{/if}}
                               onclick="CreneauConsultation.showListNextSlots('{{$list_functions->_id}}', '{{$week_number}}', '{{$rdv}}', '{{$debut|date_format:"%Y"}}');"
                               class="timeday" />
                        {{$_time|date_format:$conf.time}}
                      </span>
                          </label>
                        {{/foreach}}
                      </fieldset>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
  </table>
</div>

<br><br>
<div id="list_next_slots">
  {{mb_include module=cabinet template=inc_list_next_slots}}
</div>
