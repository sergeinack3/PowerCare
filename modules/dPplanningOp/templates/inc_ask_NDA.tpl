{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    validerNDA = function (NDA) {
        var form = getForm("editSejour");
        $V(form._copy_NDA, NDA);

        window.NDA_callback();

        Control.Modal.close();
    }
</script>

<form name="selectNDA" method="get">
    <div class="ask_nda_scrollable">
        <table class="form">
            {{foreach from=$sejours_by_NDA key=NDA item=_sejours}}
                <tr>
                    <th class="title modify" colspan="2">
                        <label>
                            <input type="radio" name="_NDA" value="{{$NDA}}" style="float: left;"/>
                            {{$NDA}}
                            {{if array_key_exists($NDA, $locked_nda)}}
                                ({{mb_label class=CSejour field=last_seance}}: {{$locked_nda.$NDA|date_format:$conf.date}})
                            {{/if}}
                        </label>
                    </th>
                </tr>
                {{foreach from=$_sejours item=object}}
                    {{mb_include module=patients template=CSejour_event}}
                {{/foreach}}
            {{/foreach}}

            <tr>
                <th class="title" colspan="2">
                    <label>
                        <input type="radio" name="_NDA" value="" style="float: left;" checked/>
                        {{tr}}CSejour.new_dossier_adm{{/tr}}
                    </label>
                </th>
            </tr>
        </table>
    </div>

    <div class="me-text-align-center">
        <hr class="me-margin-top-5 me-margin-bottom-5">

        <button type="button" class="tick"
                onclick="window.ask_next_sejour = false; validerNDA($V(this.form._NDA));">{{tr}}CSejour-Validate_all_sejour{{/tr}}</button>
        <button type="button" class="tick"
                onclick="window.ask_next_sejour = true;  validerNDA($V(this.form._NDA));">{{tr}}CSejour-Validate_one_sejour{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    </div>
</form>
