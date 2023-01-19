{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $count_collision > 1}}
    <div class="small-warning">
        Plusieurs collisions ont été détectées, aucun traitement possible.
        Veuillez contacter un administrateur pour fusionner les dossiers.
    </div>
    {{mb_return}}
{{/if}}

{{if $sejour->sortie_reelle}}
    <div class="small-warning">
        {{tr}}CRPU-Cannot hospitalize with sortie reelle{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

{{mb_script module=planningOp script=sejour ajax=true}}

{{assign var=label value=$conf.dPurgences.create_sejour_hospit|ternary:"simple":"transfert"}}

<script>
    preselectUf = function () {
        new Url("planningOp", "ajax_get_ufs_ids")
            .addParam("type_sejour", $V(getForm("confirmHospitalization").type))
            .addParam("chir_id", $V(getForm("confirmHospitalization").praticien_id))
            .requestJSON(function (ids) {
                var form = getForm("confirmHospitalization");
                var field = form.uf_medicale_id;
                $V(field, "");

                [ids.principale_chir, ids.principale_cab, ids.secondaires].each(
                    function (_ids) {
                        if ($V(field)) {
                            return;
                        }

                        if (!_ids || !_ids.length) {
                            return;
                        }

                        var i = 0;

                        while (!$V(field) && i < _ids.length) {
                            $V(field, _ids[i]);
                            i++;
                        }
                    }
                );

                for (i = 0; i < form.uf_medicale_id.options.length; i++) {
                    var _option = form.uf_medicale_id.options[i];
                    var _option_value = parseInt(_option.value);

                    var statut = !(
                        (ids.secondaires && ids.secondaires.indexOf(_option_value) != -1)
                        || (ids.principale_chir && ids.principale_chir.indexOf(_option_value) != -1)
                        || (ids.principale_cab && ids.principale_cab.indexOf(_option_value) != -1)
                    );

                    _option.writeAttribute("disabled", statut);
                }
            });
    };
</script>

<form name="confirmHospitalization" method="post"
      action="?g={{if $group_id}}{{$group_id}}{{else}}{{$g}}{{/if}}"
      onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close, useFormAction: true});">
    <input type="hidden" name="m" value="urgences"/>
    <input type="hidden" name="dosql" value="do_transfert_aed"/>
    <input type="hidden" name="del" value="0"/>
    <input type="hidden" name="rpu_id" value="{{$rpu->_id}}"/>
    <input type="hidden" name="confirme" value="0"/>
    <input type="hidden" name="current_g" value="{{$g}}"/>

    {{if $sejour_collision}}<input type="hidden" name="sejour_id_merge" value="{{$sejour_collision->_id}}">{{/if}}
    <div class="small-info">{{tr}}confirm-RPU-Hospitalisation-{{$label}}{{/tr}}</div>

    {{mb_include module=planningOp template=inc_choose_sejour_merge_or_futur}}

    <table class="form">
        {{if "dPurgences CRPU change_group"|gconf}}
            <tr>
                <th>
                    {{mb_label object=$sejour field=group_id}}
                </th>
                <td>
                    <select name="group_id"
                            onchange="Control.Modal.close(); Urgences.hospitalize('{{$rpu->_id}}', this.value)">
                        {{foreach from=$etablissements item=curr_etab}}
                            <option value="{{$curr_etab->group_id}}"
                                    {{if $sejour->group_id === $curr_etab->group_id}}selected{{/if}}>{{$curr_etab}}</option>
                        {{/foreach}}
                    </select>
                </td>
            </tr>
        {{/if}}
        {{mb_include module=urgences template=inc_modalites_hospitalization}}

        {{assign var=show_type_pec value="dPplanningOp CSejour fields_display show_type_pec"|gconf}}
        {{if $show_type_pec !== "hidden"}}
            {{if $show_type_pec === "mandatory"}}
                {{assign var=canNull value="false"}}
            {{else}}
                {{assign var=canNull value="true"}}
            {{/if}}
            <tr>
                <th>{{mb_label object=$sejour field="type_pec"}}</th>
                <td>
                    <span onmouseover="ObjectTooltip.createDOM(this, 'type_pec_legend')">
                        {{mb_field object=$sejour field="type_pec" typeEnum="radio" canNull=$canNull }}
                    </span>
                    {{mb_include module=dPplanningOp template=inc_tooltip_type_pec}}
                </td>
            </tr>
        {{/if}}
    </table>

    <div>
        <table class="tbl">
            <tr>
                <th style="width: 33%; text-align: center;"></th>

                <th style="width: 33%; text-align: center;">
                    {{tr}}CSejour-Confirm creation DHE{{/tr}}
                </th>
                {{if !$conf.dPurgences.create_sejour_hospit}}
                    <th style="text-align: center;">
                        {{tr}}CSejour-Confirm creation DHE and exit patient from emergency{{/tr}}
                    </th>
                {{/if}}
                <th>

                </th>
            </tr>
            <tr>
                <td class="button">
                    <button class="close" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
                </td>
                <td class="button">
                    <button class="tick oneclick" type="submit">
                        {{if $count_collision}}
                            {{tr}}Merge{{/tr}}
                        {{else}}
                            {{tr}}Confirm{{/tr}}
                        {{/if}}
                    </button>
                </td>
                {{if !$conf.dPurgences.create_sejour_hospit}}
                    <td class="button">
                        <button class="tick oneclick" type="submit" onclick="$V(this.form.confirme, 1);">
                            {{tr}}CRPU-Confirm exit{{/tr}}
                        </button>
                    </td>
                {{/if}}
                <td>

                </td>
            </tr>
        </table>
    </div>
</form>
