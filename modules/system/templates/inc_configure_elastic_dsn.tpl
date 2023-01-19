{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=section value="elastic"}}
{{assign var=dsnConfig value=0}}

{{unique_id var=input_pwd}}

{{if array_key_exists("elastic", $conf)}}
    {{if $dsn|array_key_exists:$conf.$section}}
        {{assign var=dsnConfig value=$conf.$section.$dsn}}
    {{/if}}
{{/if}}


<script>
  Main.add(function () {
    togglePasswordModificationField = function (lock, field) {
      lock.classList.toggle('lock');
      lock.classList.toggle('unlock');
      field.disabled = !field.disabled;
    };

    submitFormConfirmation = function (form, element) {
      if (!element.disabled && $V(element) === '') {
        Modal.confirm($T('ElasticDSNConfiguration-confirm-Are you sure to reset the password?'), {
          onOK : function () {
            return onSubmitFormAjax(form);
          }
        });
        return false;
      }
      return onSubmitFormAjax(form);
    };
  });

</script>

<form name="ConfigDSN-{{$dsn}}" method="post" onsubmit="return submitFormConfirmation(this, $('{{$input_pwd}}'));">
    {{mb_configure module=$m}}

  <table class="form">
    <tr>
      <th colspan="2" class="title">{{tr}}config-{{$section}}{{/tr}} '{{$dsn}}'</th>
    </tr>
    <tr>
      <td colspan="2">
          {{if $dsn === "readonly"}}
            <div class="small-info">
              Attention à configurer un utilisateur qui n'a des droits qu'en lecture sur la base par précaution. <br/>
              <strong>Surtout, ne pas utiliser le même utilisateur que la source de données principale.</strong>
            </div>
          {{/if}}
      </td>
    </tr>

    <tr>
        {{assign var="var" value="elastic_host"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}-desc{{/tr}}">
            {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
          {{if $dsnConfig != 0 && array_key_exists($var, $dsnConfig)}}
              {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
          {{/if}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{if $dsnConfig != 0}}{{$value}}{{/if}}" placeholder="localhost"/>
      </td>
    </tr>

    <tr>
        {{assign var="var" value="elastic_port"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}-desc{{/tr}}">
            {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
          {{if $dsnConfig != 0 && array_key_exists($var, $dsnConfig)}}
              {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
          {{/if}}
        <input type="number" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{if $dsnConfig != 0}}{{$value}}{{/if}}" placeholder="9200"/>
      </td>
    </tr>
    <tr>
        {{assign var="var" value="elastic_index"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}{{/tr}}">
            {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
          {{if $dsnConfig != 0 && array_key_exists($var, $dsnConfig)}}
              {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
          {{/if}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{if $dsnConfig != 0}}{{$value}}{{/if}}"/>
      </td>
    </tr>

    <tr>
        {{assign var="var" value="elastic_user"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}{{/tr}}">
            {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
          {{if $dsnConfig != 0 && array_key_exists($var, $dsnConfig)}}
              {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
          {{/if}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{if $dsnConfig != 0}}{{$value}}{{/if}}"/>
      </td>
    </tr>

    <tr>
        {{assign var="var" value="elastic_pass"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}{{/tr}}">
            {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
          {{if $dsnConfig != 0 && array_key_exists($var, $dsnConfig)}}
              {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
          {{/if}}
            <input type="password" name="{{$section}}[{{$dsn}}][{{$var}}]" id="{{$input_pwd}}" disabled/>
            <button type="button" class="lock notext" onclick="togglePasswordModificationField(this, this.previous())"></button>
      </td>
    </tr>

    <tr>
        {{assign var="var" value="elastic_curl-connection-timeout"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}{{/tr}}">
            {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
          {{if $dsnConfig != 0 && array_key_exists($var, $dsnConfig)}}
              {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
          {{/if}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{if $dsnConfig != 0}}{{$value}}{{/if}}" placeholder="2"/>
      </td>
    </tr>

    <tr>
        {{assign var="var" value="elastic_curl-timeout"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}{{/tr}}">
            {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
          {{if $dsnConfig != 0 && array_key_exists($var, $dsnConfig)}}
              {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
          {{/if}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{if $dsnConfig != 0}}{{$value}}{{/if}}" placeholder="2"/>
      </td>
    </tr>

    <tr>
        {{assign var="var" value="elastic_connection-retries"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}{{/tr}}">
            {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
          {{if $dsnConfig != 0 && array_key_exists($var, $dsnConfig)}}
              {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
          {{/if}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{if $dsnConfig != 0}}{{$value}}{{/if}}" placeholder="1"/>
      </td>
    </tr>

    <tr>
      <td></td>
      <td>
        <button class="{{$dsnConfig|@ternary:modify:new}}">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="main form">
  <tbody id="dsn-status-{{$dsn}}"></tbody>
</table>
