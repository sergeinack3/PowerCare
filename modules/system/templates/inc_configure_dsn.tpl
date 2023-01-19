{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=section value="db"}}
{{assign var=dsnConfig value=0}}

{{if $dsn|array_key_exists:$conf.$section}}
  {{assign var=dsnConfig value=$conf.$section.$dsn}}
{{/if}}

{{unique_id var=input_pwd}}

<script>
  Main.add(function () {
    togglePasswordModificationField = function (lock, field) {
      lock.classList.toggle('lock');
      lock.classList.toggle('unlock');
      field.disabled = !field.disabled;
    };

    submitFormConfirmation = function (form, element) {
      if (!element.disabled && $V(element) === '') {
        Modal.confirm($T('SQLDSNConfiguration-confirm-Are you sure to reset the password?'), {
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
            Attention � configurer un utilisateur qui n'a des droits qu'en lecture sur la base par pr�caution. <br/>
            <strong>Surtout, ne pas utiliser le m�me utilisateur que la source de donn�es principale.</strong>
          </div>
        {{/if}}
      </td>
    </tr>

    <tr>
      {{assign var="var" value="dbtype"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}-desc{{/tr}}">
          {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
        {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
        <select name="{{$section}}[{{$dsn}}][{{$var}}]">
          {{foreach from='Ox\Core\CSQLDataSource'|static:engines key=engine item=class}}
            <option value="{{$engine}}"
                    {{if $engine == $value}}selected{{/if}}>{{tr}}config-{{$section}}-{{$var}}-{{$engine}}{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      {{assign var="var" value="dbhost"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}-desc{{/tr}}">
          {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
        {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{$value}}"/>
      </td>
    </tr>

    <tr>
      {{assign var="var" value="dbname"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}-desc{{/tr}}">
          {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
        {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{$value}}"/>
      </td>
    </tr>

    <tr>
      {{assign var="var" value="dbuser"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}{{/tr}}">
          {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
        {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
        <input type="text" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{$value}}"/>
      </td>
    </tr>

    <tr>
      {{assign var="var" value="dbpass"}}
      <th>
        <label for="{{$section}}[{{$dsn}}][{{$var}}]" title="{{tr}}config-{{$section}}-{{$var}}{{/tr}}">
          {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
        {{mb_ternary test=$dsnConfig var=value value=$dsnConfig.$var other=""}}
        <input type="password" name="{{$section}}[{{$dsn}}][{{$var}}]" id="{{$input_pwd}}" disabled/>
        <button type="button" class="lock notext" onclick="togglePasswordModificationField(this, this.previous())"></button>
      </td>
    </tr>

    <tr>
      {{assign var="var" value="nocache"}}
      <th>
        <label title="{{tr}}config-{{$section}}-{{$var}}-desc{{/tr}}">
          {{tr}}config-{{$section}}-{{$var}}{{/tr}}
        </label>
      </th>
      <td>
        {{if $dsnConfig && array_key_exists($var,$dsnConfig)}}
          {{assign var="value" value=$dsnConfig.$var}}
        {{else}}
          {{assign var="value" value=""}}
        {{/if}}

        <input type="hidden" name="{{$section}}[{{$dsn}}][{{$var}}]" value="{{$value}}" />
        <input type="checkbox" onclick="$V(this.previous(),this.checked?1:0)" {{if $value}}checked{{/if}} />
      </td>
    </tr>
    
    <tr>
      <td></td>
      <td>
        <button class="{{$dsnConfig|@ternary:modify:new}}">{{tr}}Save{{/tr}}</button>

        <button type="button" class="search compact" onclick="DSN.test('{{$dsn}}', 'dsn-status-{{$dsn}}');">
          {{tr}}commmon-action-Test{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<table class="main form">
  <tbody id="dsn-status-{{$dsn}}"></tbody>
</table>
