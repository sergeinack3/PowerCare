{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=soins script=personnel_sejour}}
{{mb_script module=soins script=soins}}
{{if $isImedsInstalled}}
    {{mb_script module=Imeds script=Imeds_results_watcher}}
{{/if}}
{{mb_script module=system script=alert}}

{{mb_default var=service_id value=0}}
<script>
    showDossierSoins = function (sejour_id, date, default_tab) {
        var url = new Url("soins", "viewDossierSejour");
        url.addParam("sejour_id", sejour_id);
        url.addParam("date", date);
        url.addNotNullParam('default_tab', default_tab);
        url.requestModal("100%", "100%");
        modalWindow = url.modalObject;
    };

    popEtatSejour = function (sejour_id) {
        var url = new Url("hospi", "vw_parcours");
        url.addParam("sejour_id", sejour_id);
        url.requestModal(1000, 700);
    };

    savePref = function (form) {
        var formPref = getForm('editPrefServiceSoins');
        var service_id = $V(form.default_service_id);

        var default_service_id_elt = formPref.elements['pref[default_services_id]'];
        var default_service_id = $V(default_service_id_elt).evalJSON();
        default_service_id.g{{$g}} = service_id;
        $V(default_service_id_elt, Object.toJSON(default_service_id));
        return onSubmitFormAjax(formPref, function () {
            Control.Modal.close();
            $V(form.service_id, service_id);
        });
    };
    Main.add(function () {
        Calendar.regField(getForm('filtresSoignants').date, null, {noView: true});

        {{if "soins UserSejour soin_refresh_user_sejour"|gconf}}
        if (PersonnelSejour.interval != null) {
            clearInterval(PersonnelSejour.interval);
        }
        var url = new Url('soins', 'vw_affectations_soignant');
        url.addFormData(getForm('filtresSoignants'));
        url.addParam("list_only", 1);
        PersonnelSejour.interval = setInterval(function () {
            url.requestUpdate("list_affectations", function () {
                {{if $isImedsInstalled}}
                ImedsResultsWatcher.loadResults();
                {{/if}}
            });
        }, {{math equation="a*60000" a="soins UserSejour soin_refresh_user_sejour"|gconf}});
        {{/if}}

        {{if $isImedsInstalled}}
        ImedsResultsWatcher.loadResults();
        {{/if}}
    });

</script>
<div id="liste_soignants">
    <form name="editPrefServiceSoins" method="post">
        <input type="hidden" name="m" value="admin"/>
        <input type="hidden" name="dosql" value="do_preference_aed"/>
        <input type="hidden" name="user_id" value="{{$app->user_id}}"/>
    </form>
    <form name="filtresSoignants" method="get" action="?">
        <table class="main tbl form">
            <tr>
                <th colspan="19" class="button title">
          <span style="float: left;width: 150px;margin-right: -150px;font-size: 12px;">
            <button type="button" name="filter_services" class="search me-tertiary me-small"
                    onclick="PersonnelSejour.selectServices();">
                Services
            </button>
          </span>
                    <span style="width: 50%; display: block">
            <span style="float:right;">
            <button type="button" class="left notext me-tertiary me-dark"
                    onclick="$V(this.form.date, '{{$date_before}}');"></button>
              {{tr}}mod-soins-tab-vw_affectations_soignant.title{{/tr}} le {{$date|date_format:$conf.date}}
              <input type="hidden" name="date" value="{{$date}}" onchange="PersonnelSejour.refreshListeSoignant();"/>
            <button type="button" class="right notext me-tertiary me-dark"
                    onclick="$V(this.form.date, '{{$date_after}}');"></button>
            </span>
          </span>

                </th>
            </tr>

            <tr>
                <th class="category"></th>
                <th class="category narrow">{{tr}}CLit{{/tr}}</th>
                <th class="category" colspan="2" style="width: 70px;">{{tr}}CUserSejour-affectations{{/tr}}</th>
                <th class="category" colspan="2">{{tr}}CPatient{{/tr}}</th>
                <th class="category narrow text">{{tr}}CSejour-notion_depart{{/tr}}</th>
                <th class="category">{{mb_title class=CSejour field=libelle}}</th>
                <th class="category narrow">{{mb_title class=CSejour field=entree}}</th>
                <th class="category narrow text">{{tr}}CSejour-title-praticien_anesth{{/tr}}</th>
                <th class="category narrow">{{tr}}CAntecedent.court{{/tr}}
                    /{{tr}}CAntecedent.appareil.alle|pl{{/tr}}</th>
                {{if $isImedsInstalled}}
                    <th class="category narrow">{{tr}}Labo{{/tr}}</th>
                {{/if}}
                <th class="category narrow"><label
                      title="{{tr}}CPrescription-desc{{/tr}}">{{tr}}CPrescription-court{{/tr}}</label></th>
                <th class="category narrow"><label
                      title="{{tr}}CPrescription-urgence-desc{{/tr}}">{{tr}}CPoseDispositifVasculaire-urgence-court{{/tr}}</label>
                </th>
                {{if "soins Observations manual_alerts"|gconf}}
                    <th class="category narrow"><label
                          title="{{tr}}CObservationMedicale{{/tr}}">{{tr}}CObservationMedicale._show_obs{{/tr}}</label>
                    </th>
                {{/if}}
                <th class="category narrow">{{tr}}CSejour-attentes{{/tr}}</th>
                <th class="category narrow">{{tr}}CCategoryPrescription-cible_importante|pl{{/tr}}</th>
                {{if "soins UserSejour elts_colonne_regime"|gconf}}
                    <th class="category narrow">{{tr}}CUserSejour-regime{{/tr}}</th>
                {{/if}}
                <th class="category narrow">{{mb_title class=CSejour field=sortie}}</th>
            </tr>

            <tbody id="list_affectations">
            {{foreach from=$sejours_by_service_id item=_sejours key=_service_id}}
            {{assign var=responsable_jour value=$responsables_jour[$_service_id]}}
            <tbody class="sejours_by_service">
            <tr>
                <th class="section" style="width:38px;">
                    <input name="check_all_sejours" id="check_all_sejours" type="checkbox"
                           onchange="PersonnelSejour.selectAllSejoursByService(this);"/>
                    <button type="button" class="mediuser_black notext me-tertiary me-float-none" style="float:right;"
                            onclick="PersonnelSejour.gestionMultiplePersonnel('{{$_service_id}}', '{{$date}}');">
                        {{tr}}mod-planningOp-tab-vw_affectations_multiple_personnel{{/tr}}
                    </button>
                </th>
                <th class="section">
                    {{$services[$_service_id]->_view}}
                </th>
                <th class="section" colspan="17">
                    <button type="button" class="mediuser_black notext"
                            onclick="Soins.reponsableJour('{{$date}}', '{{$_service_id}}', false);"
                            style="float:right;{{if !$responsables_jour|@count}}opacity: 0.6;{{/if}}">
                        {{tr}}CAffectationUserService.day{{/tr}}
                    </button>
                    {{if $responsable_jour->_id}}
                        <span style="float:right;font-size: 12px;">
                    {{tr}}CAffectationUserService.day{{/tr}}:
                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$responsable_jour->_ref_user}}
                  </span>
                    {{else}}
                        <span class="empty" style="float:right;font-size: 12px;">
                    {{tr}}CUserSejour.none_responsable{{/tr}}
                  </span>
                    {{/if}}
                </th>
            </tr>
            {{mb_include module=soins template=inc_affectations_soignant sejours=$_sejours service_id=$_service_id}}
            </tbody>
            {{foreachelse}}
            <tr>
                <td colspan="18" class="empty">
                    {{tr}}Sejours Or Services-none{{/tr}}
                </td>
            </tr>
            {{/foreach}}
            </tbody>
        </table>
    </form>
</div>
