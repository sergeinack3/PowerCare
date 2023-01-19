{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin     script=preferences     ajax=$ajax}}
{{mb_script module=patients  script=autocomplete    ajax=$ajax}}
{{mb_script module=system    script=exchange_source ajax=$ajax}}

{{if "mbHost"|module_active}}
    {{mb_script module=mbHost script=cps ajax=$ajax}}
{{/if}}

{{if "oncomip"|module_active && $app->_ref_user->isPraticien()}}
    {{mb_script module=oncomip script=Oncomip ajax=$ajax}}
{{/if}}

{{if "dPpatients"|module_active}}
    {{mb_script module=patients script=salutation ajax=$ajax}}
{{/if}}

{{if 'oxERP'|module_active}}
    {{mb_script module=oxERP script=ox.erp ajax=$ajax}}
{{/if}}

<script>
    Main.add(function () {
        Control.Tabs.create("tab_edit_mediuser", true, {
            afterChange: function (container) {
                switch (container.id) {
                    case "edit_prefs":
                        Preferences.refresh('{{$user->_id}}');
                        break;
                    case "edit-exchange_source":
                        ExchangeSource.refreshUserSources();
                        break;
                    case "edit-holidays":
                        PlageConge.refresh();
                        break;
                    case "user-remplacements":
                        Remplacement.refreshList('{{$user->_id}}');
                        break;
                    case "edit-astreintes":
                        PlageAstreinte.refreshList('edit-astreintes', '{{$user->_id}}');
                        break;
                    case "list_bris_de_glace":
                        BrisDeGlace.refreshList('list_bris_de_glace', '{{$user->_id}}');
                        break;
                    case "param_auth_cps":
                        CPS.paramAuthCPS();
                        break;
                    case "edit-factureox":
                        Facture.factureUser();
                        break;
                      case 'tab-user-auth':
                        new Url('admin','ajax_vw_user_authentications').addParam('user_id', '{{$user->_id}}').requestUpdate('tab-user-auth');
                        break;

                {{if "oncomip"|module_active && $app->_ref_user->isPraticien()}}
                    case 'oncomip-account':
                        Oncomip.account.reload('{{$user->_id}}');
                        break;
                {{/if}}

                {{if 'oxERP'|module_active}}
                  case 'user-subscriptions':
                    OXERP.viewMySubscriptions('user-subscriptions');
                    break;
                {{/if}}

                    case "support" :
                    case "dPboard" :
                    case "edit-mediuser":
                    default :
                        break;
                }
            }
        });

        {{if $user->isProfessionnelDeSante()}}
        var url = new Url('cabinet', 'vw_edit_lieux');
        url.requestUpdate($('places'));
        {{/if}}
    });
</script>

<ul id="tab_edit_mediuser" class="control_tabs me-margin-top-0">
    <li><a href="#edit-mediuser">{{tr}}Identity{{/tr}}</a></li>

    {{if $user->isPraticien() && @$modules.dPfiles->_can->read}}
        <li><a href="#iconographie">{{tr}}common-Iconography{{/tr}}</a></li>
    {{/if}}

    <li><a href="#edit-exchange_source">{{tr}}CExchangeSource.plural{{/tr}}</a></li>

    <li><a href="#edit_prefs">{{tr}}Preferences{{/tr}}</a></li>

    {{if @$modules.dPpersonnel->_can->read}}
        {{mb_script module=personnel script=plage}}
        {{mb_script module=personnel script=remplacement ajax=true}}
        <script>
            PlageConge.refresh = function () {
                PlageConge.content();
                PlageConge.loadUser('{{$user->_id}}', '');
                PlageConge.edit('', '{{$user->_id}}');
            }
        </script>
        <li><a href="#edit-holidays">{{tr}}Holidays{{/tr}}</a></li>
        <li><a href="#user-remplacements">{{tr}}CRemplacement|pl{{/tr}}</a></li>
    {{/if}}

    {{if "astreintes"|module_active}}
        {{mb_script module=astreintes script=plage}}
        <li><a href="#edit-astreintes">{{tr}}CPlageAstreinte{{/tr}}</a></li>
    {{/if}}

    {{if $b2g}}
        {{mb_script module=admin script=brisDeGlace}}
        <li><a href="#list_bris_de_glace">{{tr}}CBrisDeGlace{{/tr}}</a></li>
    {{/if}}

    {{if "oxFacturation"|module_active}}
        {{mb_script module=oxFacturation script=facture}}
        <li><a href="#edit-factureox" id="edit-factureox-count">{{tr}}CFactureOX{{/tr}}</a></li>
    {{/if}}

    {{if "ecap"|module_active}}
        {{mb_script module=astreintes script=plage}}
        <li><a href="#support">{{tr}}Support{{/tr}}</a></li>
    {{/if}}

    {{if 'dPboard'|module_active}}
        <li><a href="#dPboard">{{tr}}common-Agenda{{/tr}}</a></li>
    {{/if}}

    {{if 'notifications'|module_active && @$modules.notifications->_can->read}}
        <li><a href="#notifications">{{tr}}module-notifications-court{{/tr}}</a></li>
    {{/if}}

    {{if "courrier"|module_active}}
        <li><a href="#courrier">{{tr}}module-courrier-court{{/tr}}</a></li>
    {{/if}}

    {{if "mbHost"|module_active}}
        <li><a href="#param_auth_cps">{{tr}}CPX-auth_CPX{{/tr}}</a></li>
    {{/if}}

    {{if "oncomip"|module_active && $app->_ref_user->isPraticien()}}
        <li><a href="#oncomip-account">{{tr}}Oncomip{{/tr}}</a></li>
    {{/if}}

    <li><a href="#tab-user-auth">{{tr}}common-Connection|pl{{/tr}}</a></li>

    {{if $user->_id && $user->_id == $app->user_id}}
        <li><a href="#user-security">{{tr}}common-Security{{/tr}}</a></li>
    {{/if}}

    {{if  $user->isProfessionnelDeSante()}}
        <li><a href="#places">{{tr}}cabinet-tab-vw_edit_lieux-mine{{/tr}}</a></li>
    {{/if}}

    {{if 'oxERP'|module_active}}
        <li><a href="#user-subscriptions">{{tr}}COXAbonnement-title-My subscription|pl{{/tr}}</a></li>
    {{/if}}
</ul>

<div id="edit-mediuser" style="display: block;">
    <table class="main layout">
        <tr>
            <td class="halfPane" style="width: 60%">
                {{mb_include template=inc_info_mediuser}}
            </td>

            <td class="halfPane">
                {{mb_include template=inc_info_function}}
            </td>
        </tr>
    </table>
</div>

{{if $user->isPraticien() && @$modules.dPfiles->_can->read}}
    <table id="iconographie" class="form" style="display: none;">
        <form name="mediuser" method="post" onsubmit="return onSubmitFormAjax(this);">
            {{mb_class  object=$user}}
            {{mb_key    object=$user}}
            <input type="hidden" name="del" value="0"/>
            <input type="hidden" name="_user_id" value="{{$user->_user_id}}"/>
            {{mb_include template=inc_iconographie object=$user}}
            <tr>
                <td class="button" colspan="2">
                    <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                </td>
            </tr>
        </form>
    </table>
{{/if}}

<div id="edit-exchange_source" style="display: none;" class="me-padding-0">
</div>

<div id="edit_prefs" style="display: none;">
</div>

{{if "ecap"|module_active}}
    <div id="support" style="display: none;">
        {{mb_include module=ecap template=support}}
    </div>
{{/if}}

{{if "astreintes"|module_active}}
    <div id="edit-astreintes" style="display: none;">
    </div>
{{/if}}

{{if $b2g}}
    <div id="list_bris_de_glace" style="display: none;">
    </div>
{{/if}}

{{if @$modules.dPpersonnel->_can->read}}
    <div id="edit-holidays" style="display: none;">
        <table class="main me-w100">
            <tr>
                <td class="halfPane" id="vw_user"></td>
                <td class="halfPane" id="edit_plage"></td>
            </tr>
            <tr>
                <td colspan="2" id="planningconge"></td>
            </tr>
        </table>
    </div>
    <div id="user-remplacements" style="display: none;">
        <div id="remplacements-user_id"></div>
        <div id="planningremplacement"></div>
    </div>
{{/if}}

{{if "oxFacturation"|module_active}}
    <div id="edit-factureox" style="display: none;"></div>
{{/if}}

{{if 'dPboard'|module_active}}
    <div id="dPboard" style="display: none;">
        {{mb_include module=mediusers template=vw_agenda}}
    </div>
{{/if}}

{{if 'notifications'|module_active && @$modules.notifications->_can->read}}
    <div id="notifications" style="display: none;">
        {{mb_include module=notifications template=vw_notifications_user}}
    </div>
{{/if}}

{{if "courrier"|module_active}}
    <div id="courrier" style="display: none;">
        {{mb_include module=courrier template=vw_stats only_me=1}}
    </div>
{{/if}}

{{if "mbHost"|module_active}}
    <div id="param_auth_cps"></div>
{{/if}}

{{if "oncomip"|module_active && $app->_ref_user->isPraticien()}}
    <div id="oncomip-account">
    </div>
{{/if}}

<div id="tab-user-auth" style="display: none"></div>

{{if $user->_id && $user->_id == $app->user_id}}
    <div id="user-security" style="display: none;">
        {{mb_include module=admin template=inc_user_security user=$user->_ref_user}}
    </div>
{{/if}}

{{if $user->isProfessionnelDeSante()}}
    <div id="places" style="display: none;">
    </div>
{{/if}}

{{if 'oxERP'|module_active}}
    <div id="user-subscriptions" style="display: none;"></div>
{{/if}}
