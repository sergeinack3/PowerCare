{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!DOCTYPE html>

{{* MOTW http://msdn.microsoft.com/en-us/library/ms537628(v=vs.85).aspx *}}
{{if $allInOne}}
  <!-- saved from url=(0014)about:internet -->
{{/if}}

{{* When AIO is active, UA is not detected server side, but IE needs it (for vertical text) *}}
<!--[if IE 7]>
<html lang="{{$localeInfo.alpha2}}" class="ua-msie ua-msie-7"> <![endif]-->
<!--[if IE 8]>
<html lang="{{$localeInfo.alpha2}}" class="ua-msie ua-msie-8"> <![endif]-->
<!--[if IE 9]>
<html lang="{{$localeInfo.alpha2}}" class="ua-msie ua-msie-9"> <![endif]-->
<!--[if gt IE 9]><!-->
<html lang="{{$localeInfo.alpha2}}" class="ua-{{$ua->getCodeName()}} ua-{{$ua->getCodeName()}}-{{$ua->getMajorVersion()}}">
<!--<![endif]-->
<head>
  {{mb_default var=base_href value=null}}

  {{if $base_href}}
    <base href="{{$base_href}}">
  {{/if}}
  <!-- Content-Type meta tags need to be the first in the page (even before title) -->
  <meta http-equiv="Content-Type" content="text/html;charset={{$localeInfo.charset}}" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" /> {{* For IE in All-in-one mode *}}

  <title>
    {{if !$dialog}}
      {{$conf.page_title}}
      &mdash; {{tr}}module-{{$m}}-court{{/tr}}
      {{if $a || $tab}}
        &mdash; {{tr}}mod-{{$m}}-tab-{{if $tab}}{{$tab}}{{else}}{{$a}}{{/if}}{{/tr}}
      {{/if}}
    {{else}}
      {{tr}}mod-{{$m}}-tab-{{if $tab}}{{$tab}}{{else}}{{$a}}{{/if}}{{/tr}}
    {{/if}}
  </title>

  <meta name="Description" content="Mediboard: Plateforme Open Source pour les Etablissements de Santé" />
  <meta name="Version" content="{{$version.string}}" />
  <meta name="Robots" content="noindex, nofollow" />

  <!-- iOS specific -->
  {{* Can't use the "apple-mobile-web-app-capable" meta tags because any hyperlink will be opened in Safari *}}
  <link rel="apple-touch-icon" href="images/icons/apple-touch-icon.png?{{app_version_key}}" />
{{*  <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">*}}
  <link href="style/mediboard_ext/vendor/fonts/roboto/roboto.css" rel="stylesheet">
  <link href="style/mediboard_ext/vendor/fonts/materialdesignicons/css/materialdesignicons.min.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, shrink-to-fit=no" />
  <meta name="format-detection" content="telephone=no" />

  {{if $ua->getCodeName() == "msie" && $app->user_id}}
    <!-- IE9 specific JumpLists -->
    <meta name="application-name" content="{{$conf.page_title}}" />
    <meta name="application-tooltip" content="{{$conf.page_title}}" />
    <meta name="msapplication-starturl" content="./" />
  {{/if}}

  {{mb_include style=mediboard_ext template=system_date ignore_errors=true}}
  {{assign var=app_readonly value='Ox\Core\CApp::isReadonly'|static_call:""}}

  {{mb_script module="../style/mediboard_ext" script=mediboard_ext}}
  <script>
    var Preferences = {{$app->user_prefs|@json}},
      User = {{if $app->_ref_user}}{{$app->_ref_user->_basic_info|@json}}{{else}}{}{{/if}};

    // Reconnecting reload purpose
    var PreviousUserID = User.id;

    {{assign var=mail_refresh_frequency value=0}}
    {{if 'messagerie'|module_active && $g}}
    {{assign var=frequency value='messagerie access internal_mail_refresh_frequency'|gconf}}
    {{math assign=mail_refresh_frequency equation='x * 1000' x=$frequency}}
    {{/if}}

    App = {
      m:           "{{$m}}",
      a:           "{{$a}}",
      tab:         "{{$tab}}",
      action:      "{{$action}}",
      actionType:  "{{$actionType}}",
      dialog:      "{{$dialog}}",
      config:      {
        instance_role: {{$conf.instance_role|@json}},
        modal_windows_draggable: {{if $conf.modal_windows_draggable}}true{{else}}false{{/if}},
        internal_mail_refresh_frequency: {{$mail_refresh_frequency}},
        external_url: '{{$conf.external_url}}'
      },
      readonly:    "{{$app_readonly}}" == 1 && User.id != null,
      touchDevice: {{if $ua->pointing_method == "touchscreen"}}true{{else}}false{{/if}},
      sessionLifetime: {{'Ox\Core\Sessions\CSessionHandler::getLifeTime'|static_call:""}},
      sessionLocked: {{$smarty.session.locked|@json}},
      version: {{$version|@json}}
    };

    var Mediboard = {{$version|@json}};
  </script>

  {{$mediboardShortIcon|smarty:nodefaults}}
  {{$mediboardStyle|smarty:nodefaults}}
  {{$mediboardScript|smarty:nodefaults}}

  <script>
    AideSaisie.timestamp = "{{$conf.dPcompteRendu.CCompteRendu.timestamp}}";

    {{if $app->_ref_user}}
    //for holidays in datepicker
    Calendar.ref_pays = {{$conf.ref_pays|default:1}};   // france
    Calendar.ref_cp = {{$cp_group|default:"00000"}};  // fake cp
    {{/if}}

    {{if $dialog}}
    Event.observe(document, 'keydown', closeWindowByEscape);
    {{/if}}

    {{if @$conf.weinre_debug_host && !@$smarty.get.nodebug}}
    setTimeout(function () {
      $$('head')[0].insert(DOM.script({src: 'http://{{$conf.weinre_debug_host}}/target/target-script-min.js'}));
    }, 0);
    {{/if}}

    {{if $allInOne}}
    {{* any ajax method > /dev/null *}}
    Class.extend(Url, {
      requestUpdate:    function () {
      },
      requestJSON:      function () {
      },
      periodicalUpdate: function () {
      }
    });
    {{/if}}
  </script>

  {{if "mbHost"|module_active}}
    {{mb_script module=mbHost script=mbHost}}
  {{/if}}

<body class="
{{if !$app->user_id}} login {{/if}}
{{if @$app->user_prefs.accessibility_dyslexic == 1}} dyslexic {{/if}}
{{if @$app->user_prefs.touchscreen == 1 || $ua->device_type == 'tablet'}} touchscreen {{else}} desktop {{/if}}">

{{* if IDE is configured *}}
{{if @$conf.dPdeveloppement.ide_url || @$conf.dPdeveloppement.ide_path}}
  <iframe name="ide-launch-iframe" id="ide-launch-iframe" style="display: none;"></iframe>
{{/if}}

{{if $app_readonly}}
  <div class="big-info not-printable">
    <strong>
      {{tr}}Mode-readonly-title{{/tr}}

    </strong><br />

    {{tr}}Mode-readonly-description{{/tr}}<br />
    {{tr}}Mode-readonly-disclaimer{{/tr}}
  </div>
{{/if}}

<!-- Loading divs -->
<div id="waitingMsgMask" style="display: none;"></div>
<div id="waitingMsgText" style="top: -1500px;">
  <div class="nav-loading">
  </div>
</div>
{{if !$app->isPatient()}}
  <div id="sessionLock" class="me-session-lock" style="display: none;">
    {{if $app->_ref_user}}
      <form name="sessionLockForm" method="get" action="?" onsubmit="return Session.request(this)">
        <input type="hidden" name="unlock" value="unlock" />
        <input type="hidden" name="username" value="{{$app->_ref_user->_user_username}}" />
        <div class="me-session-lock_container">
          <div class="me-session-lock_logo">
            {{if $app->user_prefs.UISTYLE === "tamm"}}
              <img class="me-tamm" src="./style/mediboard_ext/images/pictures/tamm.png" alt="TAMM" />
            {{else}}
              {{mb_include style="mediboard_ext" template="logo" id="mediboard-logo" alt="MediBoard logo"}}
            {{/if}}
          </div>
          <div class="me-session-lock_message login-message"></div>
          <div class="me-session-lock_password">
            {{me_form_field label="Password" field_class="me-margin-top-16 me-margin-bottom-16"}}
              <input type="password" name="password" />
            {{/me_form_field}}
          </div>
          <div class="me-session-lock_actions">
            <div class="me-session-lock_action-primary">
              <button type="button" class="logout" onclick="Session.close()">{{tr}}Logout{{/tr}}</button>
              <button type="submit" class="unlock">{{tr}}Unlock{{/tr}}</button>
            </div>
            <div class="me-session-lock_action-secondary">
              <button type="button" class="me-tertiary" onclick="Session.window.close(); UserSwitch.popup();">
                  {{tr}}User switch{{/tr}}
              </button>
            </div>
          </div>
        </div>
      </form>
    {{/if}}
  </div>
  <div id="userSwitch" class="me-session-lock" style="display: none;">
    {{if $m == "admin" && $tab == "chpwd"}}
      <div class="big-error">
        Vous ne pourrez vous substituer qu'après avoir changé votre mot de passe.
      </div>
      <div style="text-align: center;">
        <button type="button" class="logout" onclick="Session.close()">{{tr}}Logout{{/tr}}</button>
      </div>
    {{else}}
      <form name="userSwitchForm" method="post" action="?" onsubmit="return UserSwitch.login(this)">
        <div class="me-session-lock_container">
          <div class="me-session-lock_logo">
            {{if $app->user_prefs.UISTYLE === "tamm"}}
              <img class="me-tamm" src="./style/mediboard_ext/images/pictures/tamm.png" alt="TAMM" />
            {{else}}
              {{mb_include style="mediboard_ext" template="logo" id="mediboard-logo" alt="MediBoard logo"}}
            {{/if}}
          </div>
          <div class="me-session-lock_message login-message"></div>
          <div class="me-session-lock_password">
              {{me_form_field label="User" field_class="me-margin-top-16 me-margin-bottom-16"}}
                <input name="username" tabIndex="1000" type="text" class="notNull" />
              {{/me_form_field}}

              {{me_form_field label="Password" field_class="userSwitchPassword me-margin-top-16 me-margin-bottom-16"}}
                <input name="password" tabIndex="1001" type="password" class="notNull" />
              {{/me_form_field}}
          </div>
          <div class="me-session-lock_actions">
            <div class="me-session-lock_action">
              <button type="submit" class="tick">{{tr}}Switch{{/tr}}</button>
            </div>
          </div>
        </div>
      </form>
    {{/if}}
  </div>
{{/if}}

