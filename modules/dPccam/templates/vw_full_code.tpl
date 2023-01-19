{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    function viewCodeComplet() {
        var form = getForm("findCode");
        $V(form._codes_ccam, "{{$code->code}}");
        form.submit();
    }

    refreshTarif = function (form) {
        var formSelect = getForm("findCode");
        $V(formSelect.elements['situation_patient'], $V(form.elements['situation_patient']));
        $V(formSelect.elements['speciality'], $V(form.elements['speciality']));
        $V(formSelect.elements['contract'], $V(form.elements['contract']));
        $V(formSelect.elements['sector'], $V(form.elements['sector']));
        $V(formSelect.elements['date'], $V(form.elements['date']));

        viewCodeComplet();
        return false;
    };

    function selectCode(code, tarif) {
        window.opener.CCAMSelector.set(code, tarif);
        window.close();
    }

    Main.add(function () {
        PairEffect.initGroup("chapEffect");

        var element = getForm("findCode")._codes_ccam;
        var url = new Url("ccam", "autocompleteCcamCodes");
        url.autoComplete(element, 'codeacte_auto_complete', {
            minChars:           2,
            frequency:          0.15,
            select:             "code",
            afterUpdateElement: function (field, selected) {
                $V(field, selected.get('code'));
                $V(getForm('addFavoris').favoris_code, selected.get('code'));
                $('btn_add_fav_user').enable();
                $('btn_add_fav_function').enable();
            }
        });

        Calendar.regField(getForm('selectTarif').date)
    });
</script>

<table class="fullCode">
    <tr>
        <td class="pane">
            <table>
                <tr>
                    <td colspan="2">
                        <form name="findCode" method="get">
                            <input type="hidden" name="m" value="{{$m}}"/>
                            <input type="hidden" name="{{$actionType}}" value="{{$action}}"/>
                            <input type="hidden" name="dialog" value="{{$dialog}}"/>
                            <input type="hidden" name="situation_patient" value="{{$situation_patient}}">
                            <input type="hidden" name="speciality" value="{{$speciality}}">
                            <input type="hidden" name="contract" value="{{$contract}}">
                            <input type="hidden" name="sector" value="{{$sector}}">
                            <input type="hidden" name="date" value="{{$date}}">

                            <table class="form">
                                <tr>
                                    <th><label for="_codes_ccam" title="Code CCAM de l'acte">Code de l'acte</label></th>
                                    <td>
                                        <input tabindex="1" type="text" size="30" name="_codes_ccam"
                                               class="code ccam autocomplete"
                                               value="{{if $codeacte!="-"}}{{$codeacte|stripslashes}}{{/if}}"/>
                                        <div style="display: none;" class="autocomplete"
                                             id="codeacte_auto_complete"></div>
                                        <button tabindex="2" class="search" type="submit">Afficher</button>
                                        {{if $codeComplet}}
                                            <button class="search" type="button" onclick="viewCodeComplet()">Code
                                                complet
                                            </button>
                                        {{/if}}
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table class="form">
                            <tr>
                                <td class="button">
                                    {{if $can->edit}}
                                        <form name="addFavoris" method="post" onsubmit="return onSubmitFormAjax(this)">
                                            <input type="hidden" name="m" value="ccam"/>
                                            <input type="hidden" name="dosql" value="storeFavoris"/>

                                            <input type="hidden" name="favoris_code" value="{{$codeacte}}"/>
                                            <input type="hidden" name="favoris_user" value="{{$user->_id}}"/>
                                            <input type="hidden" name="favoris_function"
                                                   value="{{$user->function_id}}"/>

                                            <select name="object_class">
                                                <option
                                                  value="COperation" {{if $object_class == "COperation"}} selected{{/if}}>{{tr}}COperation{{/tr}}   </option>
                                                <option value="CConsultation"
                                                        {{if $object_class == "CConsultation"}}selected{{/if}}>{{tr}}CConsultation{{/tr}}</option>
                                                <option
                                                  value="CSejour" {{if $object_class == "CSejour"}} selected{{/if}}>{{tr}}CSejour{{/tr}}      </option>
                                            </select>
                                            <button id="btn_add_fav_user" class="submit" type="button"
                                                    onclick="$V(this.form.elements['favoris_function'], ''); this.form.onsubmit();"{{if !$codeacte || $codeacte == '-'}} disabled{{/if}}>
                                                Ajouter à mes favoris
                                            </button>
                                            <button id="btn_add_fav_function" class="submit" type="button"
                                                    onclick="$V(this.form.elements['favoris_user'], ''); this.form.onsubmit();"{{if !$codeacte || $codeacte == '-'}} disabled{{/if}}>
                                                Ajouter au favoris du cabinet
                                            </button>
                                        </form>
                                    {{/if}}

                                    {{if $dialog && !$hideSelect}}
                                        <button class="tick" type="button"
                                                onclick="selectCode('{{$codeacte}}','{{$code->_default}}')">Sélectionner
                                            ce code
                                        </button>
                                    {{/if}}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <form name="selectTarif" method="get" onsubmit="return refreshTarif(this);">
                            <input type="hidden" name="m" value="{{$m}}"/>
                            <input type="hidden" name="{{$actionType}}" value="{{$action}}"/>
                            <input type="hidden" name="dialog" value="{{$dialog}}"/>

                            <table class="form">
                                <tr>
                                    <th colspan="2" style="font-weight: bold; text-align: center;">
                                        Sélection du contexte tarifaire
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="date">Date</label>
                                    </th>
                                    <td>
                                        <input type="hidden" class="date" name="date" value="{{$date}}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="situation_patient">Contexte patient</label>
                                    </th>
                                    <td>
                                        <select name="situation_patient">
                                            <option value="none"{{if $situation_patient == 'none'}} selected{{/if}}>Hors C2S</option>
                                            <option value="c2s"{{if $situation_patient == 'c2s'}} selected{{/if}}>C2S</option>
                                            <option value="acs"{{if $situation_patient == 'acs'}} selected{{/if}}>ACS</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2" style="font-weight: bold; text-align: center;">
                                        Contexte praticien
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="speciality">Spécialité</label>
                                    </th>
                                    <td>
                                        <select name="speciality">
                                            {{foreach from=$specialities item=_speciality}}
                                                <option
                                                  value="{{$_speciality->_id}}"{{if $speciality == $_speciality->_id}} selected{{/if}}>
                                                    {{$_speciality}}
                                                </option>
                                            {{/foreach}}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="sector">Secteur</label>
                                    </th>
                                    <td>
                                        <select name="sector">
                                            <option value="1"{{if $sector == '1'}} selected{{/if}}>Secteur 1</option>
                                            <option value="1dp"{{if $sector == '1dp'}} selected{{/if}}>Secteur 1 DP
                                            </option>
                                            <option value="2"{{if $sector == '2'}} selected{{/if}}>Secteur 2</option>
                                            <option value="nc"{{if $sector == 'nc'}} selected{{/if}}>Non conventionné
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="contract">Pratique tarifaire</label>
                                    </th>
                                    <td>
                                        <select name="contract">
                                            <option value="none"{{if $contract == 'none'}} selected{{/if}}>Aucune
                                            </option>
                                            <option value="optam"{{if $contract == 'optam'}} selected{{/if}}>OPTAM
                                            </option>
                                            <option value="optamco"{{if $contract == 'optamco'}} selected{{/if}}>
                                                OPTAM-CO
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="button" colspan="2">
                                        <button type="button" class="search" onclick="this.form.onsubmit();">Afficher le
                                            tarif
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td><strong>Description</strong><br/>{{$code->libelleLong}}</td>
                </tr>

                {{foreach from=$code->remarques item=_rq}}
                    <tr>
                        <td><em>{{$_rq|nl2br}}</em></td>
                    </tr>
                {{/foreach}}

                {{if $code->extensions|@is_array && $code->extensions|@count}}
                    <tr>
                        <td><strong>Extensions PMSI</strong></td>
                    </tr>
                    <tr>
                        <td>
                            <ul>
                                {{foreach from=$code->extensions item=_extension}}
                                    <li>{{$_extension->extension}} : {{$_extension->description}}</li>
                                {{/foreach}}
                            </ul>
                        </td>
                    </tr>
                {{/if}}

                {{if $code->activites|@is_array && $code->activites|@count}}
                    <tr>
                        <td><strong>Activités associées</strong></td>
                    </tr>
                    {{foreach from=$code->activites item=_act}}
                        <tr>
                            <td style="vertical-align: top; width: 100%">
                                <ul>
                                    <li>
                                        Activité {{$_act->numero}} <em>({{$_act->type}}) {{$_act->libelle}}</em> :
                                        <ul>
                                            <li>Phase(s) :
                                                <ul>
                                                    {{foreach from=$_act->phases item=_phase}}
                                                        <li>
                                                            Phase {{$_phase->phase}} <em>({{$_phase->libelle}})</em>
                                                            : {{$_phase->tarif|currency}}
                                                            {{if $_phase->charges}}
                                                                <br/>
                                                                Charges supplémentaires de cabinets possibles : {{$_phase->charges|currency}}
                                                            {{/if}}
                                                        </li>
                                                    {{/foreach}}
                                                </ul>
                                            </li>
                                            <li>Modificateur(s) :
                                                <ul>
                                                    {{foreach from=$_act->modificateurs item=_mod}}
                                                        <li>{{$_mod->code}} : {{$_mod->libelle}}</li>
                                                        {{foreachelse}}
                                                        <li class="empty">Aucun modificateur applicable à cet acte</li>
                                                    {{/foreach}}
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    {{/foreach}}
                {{/if}}

                {{if $code->procedure && $code->procedure.code}}
                    <tr>
                        <td><strong>Procédure associée</strong></td>
                    </tr>
                    <tr>
                        <td>
                            <a
                              href="?m={{$m}}&dialog={{$dialog}}&{{$actionType}}={{$action}}&_codes_ccam={{$code->procedure.code}}"><strong>{{$code->procedure.code}}</strong></a>
                            <br/>
                            {{$code->procedure.texte}}
                        </td>
                    </tr>
                {{/if}}

                {{if $code->remboursement !== null}}
                    <tr>
                        <td><strong>Remboursement</strong></td>
                    </tr>
                    <tr>
                        <td>{{tr}}CDatedCodeCCAM.remboursement.{{$code->remboursement}}{{/tr}}</td>
                    </tr>
                {{/if}}

                {{if $code->forfait !== null}}
                    <tr>
                        <td><strong>Forfait spécifique</strong></td>
                    </tr>
                    <tr>
                        <td>
              <span class="circled" title="{{tr}}CDatedCodeCCAM.forfait.{{$code->forfait}}-desc{{/tr}}"
                    style="color: firebrick; border-color: firebrick; cursor: help;">
                {{tr}}CDatedCodeCCAM.forfait.{{$code->forfait}}{{/tr}}
              </span>
                        </td>
                    </tr>
                {{/if}}
            </table>
        </td>

        <td class="pane">
            <table>
                <tr>
                    <th class="category">Place dans la CCAM {{$code->place}}</th>
                </tr>

                {{foreach from=$code->chapitres item=_chap}}
                    <tr id="chap{{$_chap.rang}}-trigger">
                        <td>
                            {{$_chap.rang}}
                            <br/>
                            {{$_chap.nom}}
                        </td>
                    </tr>
                    <tbody class="chapEffect" id="chap{{$_chap.rang}}">
                    <tr>
                        <td>
                            <ul>
                                <em>
                                    {{foreach from=$_chap.rq item=rq}}
                                        <li>{{$rq}}</li>
                                        {{foreachelse}}
                                        <li>Pas d'informations</li>
                                    {{/foreach}}
                                </em>
                            </ul>
                        </td>
                    </tr>
                    </tbody>
                {{/foreach}}
            </table>
        </td>
    </tr>
    <tr>
        <td class="pane">
            <table>
                <tr>
                    <th class="category" colspan="2">Actes associés</th>
                </tr>
                {{foreach from=$code->activites item=_activite}}
                    <tr>
                        <td colspan="2"><strong>{{$_activite->type}} ({{$_activite->assos|@count}})</strong></td>
                    </tr>
                    {{foreach name=associations from=$_activite->assos item=_asso}}
                        <tr>
                            <th>
                                <a
                                  href="?m={{$m}}&dialog={{$dialog}}&{{$actionType}}={{$action}}&_codes_ccam={{$_asso.code}}">
                                    {{$_asso.code}}
                                </a>
                            </th>
                            <td>{{$_asso.texte}}</td>
                        </tr>
                    {{/foreach}}
                {{/foreach}}
            </table>
        </td>

        <td class="pane">
            <table>
                <tr>
                    <th class="category" colspan="2">Actes incompatibles ({{$code->incomps|@count}})</th>
                </tr>

                {{foreach from=$code->incomps item=_incomp name=incompatibilites}}
                    <tr>
                        <th>
                            <a
                              href="?m={{$m}}&dialog={{$dialog}}&{{$actionType}}={{$action}}&_codes_ccam={{$_incomp.code}}">
                                {{$_incomp.code}}
                            </a>
                        </th>
                        <td>{{$_incomp.texte}}</td>
                    </tr>
                    {{foreachelse}}
                    <tr>
                        <td colspan="2" class="empty">Pas de code incompatible</td>
                    </tr>
                {{/foreach}}
            </table>
        </td>
    </tr>
</table>
