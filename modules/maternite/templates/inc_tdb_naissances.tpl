{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPImeds"|module_active}}
    {{mb_script module="Imeds" script="Imeds_results_watcher" ajax=true}}
{{/if}}

{{mb_script module=maternite  script=tdb        ajax=true}}
{{mb_script module=admissions script=admissions ajax=true}}
{{mb_script module=system     script=alert      ajax=true}}
{{mb_script module=planningOp script=sejour     ajax=true}}

<script>
    Main.add(function () {
        var form = getForm('tdbNaissances');
        Tdb.listPediatres();
        Tdb.listNaisances(form);
        Calendar.regField(form.date_guthrie_min);
        Calendar.regField(form.date_guthrie_max);
    });
</script>

<div id="msg_status_consult" class="small-warning" style="display: none;">
    {{tr}}CNaissance-msg-Status consultation with pediatrician{{/tr}}
</div>

<fieldset>
    <legend><i class="fas fa-filter"></i> {{tr}}filters{{/tr}}</legend>

    <form name="tdbNaissances" action="#" method="get">
        <input type="hidden" name="order_col" value="patient_id"/>
        <input type="hidden" name="order_way" value="ASC"/>
        <input type="hidden" name="praticien_id" value=""/>
        <input type="hidden" name="page" value="{{$page}}"/>

        <table class="main form me-no-box-shadow">
            <tr>
                <th>{{tr}}CSejour-filtre_date_min{{/tr}}</th>
                <td>
                    {{mb_field object=$filter field=_date_min register=true form="tdbNaissances"
                    onchange="Tdb.emptyDates(this, 'naissance');"}}
                </td>
                <th>{{tr}}CNaissance-filtre_date_min{{/tr}}</th>
                <td>
                    {{mb_field object=$naissance field=_datetime_min register=true form="tdbNaissances"
                    onchange="Tdb.emptyDates(this, 'sejour');"}}
                </td>
                <th>{{tr}}State{{/tr}}</th>
                <td>
                    <select name="state" style="width: 180px;"
                            onchange="Tdb.emptyDates(this);
                            (this.value == 'consult_pediatre')
                            ? $('msg_status_consult').show()
                            : $('msg_status_consult').hide()">
                        <option value="none">&mdash; {{tr}}Choose{{/tr}}</option>
                        <option value="present" selected>
                            {{tr}}CNaissance-Present baby{{/tr}}
                        </option>
                        <option value="consult_pediatre">
                            {{tr}}CNaissance-Baby having a consultation with a pediatrician{{/tr}}
                        </option>
                    </select>
                </td>
                <th>{{tr}}CExamenNouveauNe.filter_guthrie.date_guthrie_min{{/tr}}</th>
                <td><input type="hidden" name="date_guthrie_min"></td>
            </tr>
            <tr>
                <th>{{tr}}CSejour-filtre_date_max{{/tr}}</th>
                <td>
                    {{mb_field object=$filter field=_date_max register=true form="tdbNaissances"
                    onchange="Tdb.emptyDates(this, 'naissance');"}}
                </td>
                <th>{{tr}}CNaissance-filtre_date_max{{/tr}}</th>
                <td>
                    {{mb_field object=$naissance field=_datetime_max register=true form="tdbNaissances"
                    onchange="Tdb.emptyDates(this, 'sejour');"}}
                </td>
                <th>{{tr}}CNaissanceRea.rea_par.ped{{/tr}}</th>
                <td><input type="text" name="_prat_autocomplete"/></td>
                <th>{{tr}}CExamenNouveauNe.filter_guthrie.date_guthrie_max{{/tr}}</th>
                <td><input type="hidden" name="date_guthrie_max"></td>
            </tr>
            <tr>
                <th></th>
                <td colspan="6">
                    <button type="button" name="filter_services" class="me-tertiary"
                            onclick="Tdb.selectServices('tdb_naissances');">
                        <i class="fas fa-search" style="font-size: 1.2em;"></i> {{tr}}CService|pl{{/tr}}
                    </button>
                </td>
            </tr>
            <tr>
                <td class="button" colspan="6">
                    <button type="button" class="me-primary" onclick="Tdb.listNaisances(this.form);">
                        <i class="fas fa-search"></i> {{tr}}Search{{/tr}}
                    </button>
                </td>
            </tr>
        </table>
    </form>
</fieldset>

<div id="tdb_naissances" class="me-padding-0"></div>
