{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include style=mediboard_ext template=common ignore_errors=true}}
{{*<link href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">*}}
<link href="style/mediboard_ext/vendor/fonts/roboto/roboto.css" rel="stylesheet">

{{assign var=bg_custom value="images/pictures/bg_custom.jpg"}}

{{assign var=bg value=false}}
{{if is_file($bg_custom)}}
    {{assign var=bg value=true}}
{{/if}}

<script>
  Main.add(
    function () {
      getForm('loginFrm').username.focus();
        {{if $conf.login_browser_check}}
      MediboardExt.jsControl.control({{if $ua->_obsolete || $ua->_too_recent || $ua->_badly_detected}}true{{else}}false{{/if}})
        {{/if}}
    }
  );
</script>
<div id="login" {{if $bg}}class="me-bg-custom"{{/if}}></div>
<div class="login-wrap">
  <div class="me-caution-wrap">
      {{if $conf.login_browser_check}}
          {{assign var=supported_browsers value=$ua|const:"SUPPORTED_BROWSERS"}}
        <div id="jscontrol" class="me-caution small-warning">
          <div class="control-browser-error control-browser-error-title">
              {{tr}}Browser-error-title{{/tr}}
          </div>
          <div class="control-error control-browser-error">
              {{tr var1=$ua->browser_name var2=$ua->browser_version}}Browser-error-content{{/tr}}
            <ul>
              <li>{{tr var1=$supported_browsers.Chrome.0 var2=$supported_browsers.Chrome.1}}Browser-error-content-chrome{{/tr}}</li>
              <li>{{tr var1=$supported_browsers.Firefox.0 var2=$supported_browsers.Firefox.1}}Browser-error-content-firefox{{/tr}}</li>
              <li>{{tr var1=$supported_browsers.Edge.0 var2=$supported_browsers.Edge.1}}Browser-error-content-edge{{/tr}}</li>
            </ul>
          </div>
          <div class="control-error-title">
              {{tr}}Frontside-error-title{{/tr}}
          </div>
          <div class="control-error control-front-error" id="controlScript">
              {{tr}}Script-loading-error{{/tr}}
          </div>
        </div>
      {{/if}}
  </div>

  <div class="login-form" id="login-form">
      {{if $conf.instance_role === "qualif"}}
        <div class="me-qualif-ribbon login-ribbon">
            {{mb_include style="mediboard_ext" template="logo_white" alt="Logo"}}
          <span class="me-ribbon-qualif-text">Qualif</span>
        </div>
      {{/if}}
    <form name="loginFrm" action="?" method="post" onsubmit="return checkForm(this)">
      <div>
          {{mb_include style="mediboard_ext" template="logo" id="mediboard-logo" alt="Logo Application"}}

        <div id="systemMsg">
            {{$errorMessage|nl2br|smarty:nodefaults}}
        </div>

          {{me_form_field label="common-User" field_class="me-margin-top-16 me-margin-bottom-16"}}
            <input type="text" class="notNull str" size="15" maxlength="20" name="username"/>
          {{/me_form_field}}

          {{me_form_field label="CMbFieldSpec.type.password" field_class="me-margin-top-16 me-margin-bottom-16"}}
            <input type="password" class="notNull str" size="15" maxlength="32" name="password"/>
          {{/me_form_field}}
        <button type="submit">
            {{tr}}Login{{/tr}}
        </button>

          {{if "mbHost"|module_active || $kerberos_button || $psc_button}}
            <div class="login-form-others">
                {{if $kerberos_button}}
                    {{mb_include module=admin template=inc_kerberos_login_button}}
                {{/if}}

                {{if "mbHost"|module_active}}
                    {{mb_include module=mbHost template=inc_login_cps}}
                {{/if}}

              <!-- Last one-->
                {{if $psc_button}}
                    {{mb_include module=admin template=inc_psc_login}}
                  <input type="hidden" name="psc_mapping" value="{{$app->_psc_rpps_attempt_mapping}}" tabindex="-1"/>
                {{/if}}
            </div>
          {{/if}}
      </div>


      <input type="hidden" name="login" value="{{$time}}"/>
      <input type="hidden" name="redirect" value="{{$redirect}}"/>
      <input type="hidden" name="dialog" value="{{$dialog}}"/>
      <input type="text" name="_login" value="" style="position: absolute; top: -10000px;" tabindex="-1"/>
      <input type="password" name="_pwd" value="" style="position: absolute; top: -10000px;" tabindex="-1"/>
    </form>
  </div>
</div>

{{if !$dialog}}
  <div class="login-footer">
    <span class="me-text-align-left">{{$conf.company_name}}</span>
    <span class="me-text-align-center">{{$conf.product_name}}</span>
    <span class="me-text-align-right">
    {{if $applicationVersion.releaseCode && $applicationVersion.releaseTitle|capitalize}}
        {{$applicationVersion.releaseTitle|capitalize}}
    {{/if}}
  </span>
  </div>
{{/if}}

{{if $psc_button}}
  <div id="PscCGU" class="PscCGU" style="display: none">
      {{mb_include module=admin template=inc_psc_cgu}}
  </div>
{{/if}}

{{mb_include style=mediboard_ext template=common_end nodebug=true}}
