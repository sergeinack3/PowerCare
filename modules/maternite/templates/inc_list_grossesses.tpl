{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        Grossesse.formFrom = getForm("bindFormGrossesse");

        // Après création d'une grossesse, si l'objet concerné n'est relié à aucune grossesse,
        // alors
        {{if $show_checkbox && !$object->grossesse_id && $grossesses|@count == 1}}
            {{foreach from=$grossesses item=_grossesse name=lopp_grossesses}}
                {{if $smarty.foreach.lopp_grossesses.first && $_grossesse->active}}
                    Grossesse.formFrom.unique_grossesse_id.checked = true;
                {{/if}}
            {{/foreach}}
        {{/if}}

        Grossesse.editGrossesse($V(Grossesse.formFrom.unique_grossesse_id), '{{$parturiente_id}}');
    });
</script>

<form name="bindFormGrossesse" method="get">
    <table class="tbl me-margin-0">
        <tr>
            <th colspan="4" class="title">
                <div>
                    <span>
                        {{tr}}CGrossesse-pregnancies_list{{/tr}}
                    </span>
                    <button id="button_new_grossesse"
                            type="button"
                            class="me-primary new me-float-right"
                            onclick="Grossesse.editGrossesse(0, '{{$parturiente_id}}')">
                        {{tr}}CGrossesse-title-create{{/tr}}
                    </button>
                </div>
            </th>
        </tr>
        {{foreach from=$grossesses item=_grossesse}}
            <tr {{if !$_grossesse->active}}class="hatching"{{/if}}>
                <td class="narrow">
                    {{if $show_checkbox}}
                        <input type="radio"
                               name="unique_grossesse_id"
                               data-active="{{$_grossesse->active}}"
                               data-view_grossesse="{{$_grossesse}}"
                               data-date="{{$_grossesse->terme_prevu}}"
                               {{if $_grossesse->_id == $object->grossesse_id || ($grossesse_id && $_grossesse->_id == $grossesse_id)}}
                                  checked
                              {{/if}}
                              value="{{$_grossesse->_id}}"
                              {{if !$_grossesse->active}}
                                  disabled
                              {{/if}}
                        />
                    {{/if}}
                </td>
                <td {{if $_grossesse->isOneMonthAnterior()}}style=" text-decoration: line-through"{{/if}}>
                    <a href="#1" onclick="Grossesse.editGrossesse('{{$_grossesse->_id}}')">{{$_grossesse}}</a>
                </td>
                <td class="compact">
                    {{if $_grossesse->_count.sejours}}
                        <div>
                            {{$_grossesse->_count.sejours}} {{tr}}CGrossesse-back-sejours{{/tr}}
                        </div>
                    {{/if}}
                    {{if $_grossesse->_count.consultations}}
                        <div>
                            {{$_grossesse->_count.consultations}} {{tr}}CGrossesse-back-consultations{{/tr}}
                        </div>
                    {{/if}}
                    {{if $_grossesse->_count.naissances}}
                        <div>
                            {{$_grossesse->_count.naissances}} {{tr}}CGrossesse-back-naissances{{/tr}}
                        </div>
                    {{/if}}
                </td>
                {{if "forms"|module_active}}
                    <td class="narrow">
                        <button class="forms notext compact"
                                type="button"
                                {{if $_grossesse->_count.consultations == 0}}
                                    disabled
                                {{/if}}
                                onclick="ExObject.loadExObjects('{{$_grossesse->_class}}', '{{$_grossesse->_id}}', 'edit_grossesse', 0.5)">
                            {{tr}}CExClass|pl{{/tr}}
                            {{if $_grossesse->_count.consultations == 0}}
                                (la grossesse doit être liée à une consultation pour accèder aux formulaires)
                            {{/if}}
                        </button>
                    </td>
                {{/if}}
            </tr>
        {{foreachelse}}
            <tr>
                <td class="empty" colspan="4">
                    {{tr}}CGrossesse.none{{/tr}}
                </td>
            </tr>
        {{/foreach}}
    </table>
</form>
