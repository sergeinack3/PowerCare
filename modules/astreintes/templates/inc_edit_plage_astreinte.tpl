{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    refreshlistPhone = function () {
        var form = getForm('editplage');
        var user_id = $V(form.user_id);

        var url = new Url("astreintes", "listPhonesFromUser");
        url.addParam("user_id", user_id);
        url.requestUpdate("list_phones");

    };

    showRepeatSlot = function (choose_astreinte) {
        if (choose_astreinte == 'reguliere') {
            $('astreinte_reguliere').show();
        } else {
            $('astreinte_reguliere').hide();
        }
    };

    setPhone = function (phone_value) {
        var form = getForm('editplage');
        $V(form.phone_astreinte, phone_value);
    };

    Main.add(function () {
        refreshlistPhone();
        showRepeatSlot('{{$plageastreinte->choose_astreinte}}');

        {{if $plageastreinte->_id && $plageastreinte->_count_duplicated_plages != 0}}
        getForm('editplage')._repeat_week.addSpinner({min: 0});
        {{/if}}

        PlageAstreinte.checkIssues($('categorie'));
    });
</script>

<table class="main">
    <tr>
        <td>
            <form name="editplage" action="" method="post"
                  onsubmit="return onSubmitFormAjax(this,{onComplete: Control.Modal.close}); ">
                {{mb_key object=$plageastreinte}}
                <input type="hidden" name="dosql" value="do_plageastreinte_aed"/>
                <input type="hidden" name="m" value="{{$m}}"/>
                <input type="hidden" name="tab" value="{{$a}}"/>
                <input type="hidden" name="del" value="0"/>
                <input type="hidden" name="group_id" value="{{$plageastreinte->group_id}}"/>
                <table class="form me-no-box-shadow">
                    {{mb_include module=system template=inc_form_table_header object=$plageastreinte}}

                    <tr>
                        <td colspan="2">
                            <fieldset class="me-no-box-shadow">
                                <legend>{{tr}}CPlageAstreinte-legend-Attributes of the time slot{{/tr}}</legend>
                                <table class="form">
                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=choose_astreinte}}
                                        {{mb_field object=$plageastreinte field="choose_astreinte" onchange="showRepeatSlot(this.value);" emptyLabel="Choose"}}
                                        {{/me_form_field}}
                                    </tr>

                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=user_id}}
                                            <select name="user_id" onchange="refreshlistPhone();">
                                                <option value="">{{tr}}CMediusers.all{{/tr}}</option>
                                                {{mb_include module=mediusers template=inc_options_mediuser list=$users selected=$plageastreinte->user_id}}
                                            </select>
                                        {{/me_form_field}}
                                    </tr>
                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=libelle}}
                                        {{mb_field object=$plageastreinte field=libelle}}
                                        {{/me_form_field}}
                                    </tr>

                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=categorie}}
                                            <select name="categorie" id="categorie" onchange="PlageAstreinte.checkIssues(this)">
                                                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                                                {{foreach from=$categories item=_categorie}}
                                                    <option value="{{$_categorie->_id}}"
                                                            {{if $plageastreinte->categorie == $_categorie->_id}}selected{{/if}}
                                                            data-issue="{{if $_categorie->group_id != $group->_id}}1{{else}}0{{/if}}">
                                                        {{$_categorie->name}}
                                                    </option>
                                                {{/foreach}}
                                                {{if $plageastreinte->categorie && !in_array($plageastreinte->categorie, 'Ox\Core\CMbArray::pluck'|static_call:$categories:'_id')}}
                                                  <option value="$plageastreinte->categorie" selected>
                                                    {{mb_value object=$plageastreinte field=categorie}}
                                                  </option>
                                                {{/if}}
                                            </select>
                                        {{/me_form_field}}
                                    </tr>

                                    <tr class="issue">
                                        <td colspan="2">
                                            <div class="small-error">{{tr}}CCategorieAstreinte-Wrongly set{{/tr}}</div>
                                        </td>
                                    </tr>

                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=group_id}}
                                            <div class="me-field-content">
                                                {{$plageastreinte->_ref_group}}
                                            </div>
                                        {{/me_form_field}}
                                    </tr>

                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=type}}
                                        {{mb_field object=$plageastreinte field="type"}}
                                        {{/me_form_field}}
                                    </tr>

                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=start}}
                                        {{mb_field object=$plageastreinte field="start" form="editplage" register="true"}}
                                        {{/me_form_field}}
                                    </tr>

                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=end}}
                                        {{mb_field object=$plageastreinte field="end" form="editplage" register="true"}}
                                        {{/me_form_field}}
                                    </tr>

                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=color}}
                                        {{mb_field object=$plageastreinte field="color" form="editplage"}}
                                        {{/me_form_field}}
                                    </tr>

                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=phone_astreinte}}
                                        {{mb_field object=$plageastreinte field="phone_astreinte" form="editplage"}}
                                        {{/me_form_field}}
                                    </tr>

                                    {{if $app->_ref_user->isAdmin()}}
                                        <tr>
                                            {{me_form_bool nb_cells=2 mb_object=$plageastreinte mb_field=locked}}
                                            {{mb_field object=$plageastreinte field=locked}}
                                            {{/me_form_bool}}
                                        </tr>
                                    {{/if}}
                                </table>
                            </fieldset>
                        </td>
                    </tr>

                    <tr id="astreinte_reguliere" style="display: none;">
                        <td>
                            <fieldset class="me-no-box-shadow">
                                <legend>{{tr}}CPlageConsult.repetition{{/tr}}</legend>
                                <table class="form">
                                    <tr>
                                        <td colspan="2">
                                            <div class="small-info">
                                                {{tr}}CPlageAstreinte-msg-To change multiple ranges (number of weeks greater than 1){{/tr}}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        {{me_form_field nb_cells=2 label="CPlageConsult.repetition_nb_week"
                                        title_label="CPlageConsult.repetition_nb_week.long"}}
                                            <input type="text" size="2" name="_repeat_week" value="1"
                                                   onchange="this.form._type_repeat.disabled = this.value <= 1 ? 'disabled' : '';"
                                                   onKeyUp="this.form._type_repeat.disabled = this.value <= 1 ? 'disabled' : '';"/>
                                        {{if $plageastreinte->_count_duplicated_plages}}
                                            ({{tr}}CPlageAstreinte-max modifiable-court{{/tr}}: {{$plageastreinte->_count_duplicated_plages+1}})
                                        {{/if}}
                                        {{/me_form_field}}
                                    </tr>
                                    {{if $plageastreinte->_id && $plageastreinte->_count_duplicated_plages}}
                                        <tr>
                                            {{me_form_field nb_cells=2 label="CPlageConsult.similar_plage"}}
                                                <div class="me-field-content">
                                                    {{$plageastreinte->_count_duplicated_plages}}
                                                </div>
                                            {{/me_form_field}}
                                        </tr>
                                    {{/if}}
                                    <tr>
                                        {{me_form_field nb_cells=2 mb_object=$plageastreinte mb_field=_type_repeat}}
                                        {{mb_field object=$plageastreinte field="_type_repeat" style="width: 15em;" typeEnum="select"}}
                                        {{/me_form_field}}
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="6" class="button">
                            <button class="submit" type="submit" id="plage_save_button">{{tr}}Save{{/tr}}</button>
                            {{if $plageastreinte->_id}}
                                <button class="trash" type="button"
                                        onclick="$V(this.form.del, 1); this.form.onsubmit();">
                                    {{tr}}Delete{{/tr}}
                                </button>
                            {{/if}}
                        </td>
                    </tr>
                </table>
                {{if @count($plageastreinte->_collisionList)}}
                    <div class="small-warning">
                        {{foreach from=$plageastreinte->_collisionList item=_collision}}
                            {{$_collision}}
                        {{/foreach}}
                    </div>
                {{/if}}
            </form>
        </td>
        <td id="list_phones"></td>
    </tr>
</table>
