{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  generateKey = function() {
    var url = new Url('messagerie', 'do_generate_hprim_key', 'dosql');
    url.requestUpdate('systemMsg', {
      method: 'post',
      getParameters: {m: 'messagerie', dosql: 'do_generate_hprim_key'},
      onComplete: reloadHprimConfigs.curry()
    });
  };
</script>

<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this, reloadHprimConfigs.curry());">
  {{mb_configure module=$m}}

  <table class="form">
    {{mb_include module=system template=inc_config_str var=hprimnet_key_directory}}
    {{mb_include module=system template=inc_config_str var=hprimnet_certificates_directory}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="form">
  <tr>
    <th colspan="2" class="title">Etat des répertoires</th>
  </tr>
  <tr>
    <th class="halfPane" style="vertical-align: middle;">Clé</th>
    <td style="vertical-align: middle;">
      {{if $key_status}}
        <i class="fa fa-lg fa-check" style="color: forestgreen"></i>
      {{else}}
        {{if $conf.messagerie.hprimnet_key_directory == ''}}
          {{assign var=error value='Hprim.net-msg-key_directory_not_set'}}
        {{elseif !$key_directory_status}}
          {{assign var=error value='Hprim.net-msg-key_directory_dont_exists'}}
        {{else}}
          {{assign var=error value='Hprim.net-msg-key_not_generated'}}
        {{/if}}
        <i class="fa fa-lg fa-times" style="color: firebrick" title="{{$error}}"></i>
      {{/if}}
      <button class="change" onclick="generateKey();"{{if $key_status || !$key_directory_status || $conf.messagerie.hprimnet_key_directory == ''}} disabled="disabled"{{/if}}>Générer la clé</button>
    </td>
  </tr>
  <tr>
    <th class="halfPane" style="vertical-align: middle;">Répertoire des certificats</th>
    <td style="vertical-align: middle;">
      {{if $certifcates_directory_status}}
        <i class="fa fa-lg fa-check" style="color: forestgreen"></i>
      {{else}}
        {{if $conf.messagerie.hprimnet_certificates_directory == ''}}
          {{assign var=error value='Hprim.net-msg-certificates_directory_not_set'}}
        {{elseif !$certifcates_directory_status}}
          {{assign var=error value='Hprim.net-msg-certificates_directory_dont_exists'}}
        {{/if}}
        <i class="fa fa-lg fa-times" style="color: firebrick" title="{{$error}}"></i>
      {{/if}}
    </td>
  </tr>
</table>