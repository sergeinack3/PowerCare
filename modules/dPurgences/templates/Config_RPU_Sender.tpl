{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences script=rpu_sender}}
{{mb_default var=source_warm_bed value=null}}

<script>
  Main.add(function() {
    Control.Tabs.create("tabs_configure", false, { afterChange: function (container) {
      if (container.id == "CConfigEtab") {
        Configuration.edit('ror', ['CGroups'], $('CConfigEtab'));
      }
    }});

    Control.Tabs.create("tabs_configure_source", false);

    {{if !$writable}}
      $('import-key').disabled = true;
    {{/if}}
  });
</script>

<ul id="tabs_configure" class="control_tabs">
  <li> <a href="#Config_RPU_sender">{{tr}}Config_RPU_Sender{{/tr}}</a> </li>
  <li> <a href="#RPU_sender_source">{{tr}}RPU_sender_source{{/tr}}</a> </li>
  {{if $m == "ror"}}
    <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
  {{/if}}
</ul>

<div id="Config_RPU_sender" style="display: none;">
  <form name="editConfigOpale" method="post" onsubmit="return onSubmitFormAjax(this)">
    {{mb_configure module=$m}}

    <table class="form">
      <tr>
        <th class="category" colspan="10">{{tr}}config-dPurgences-chiffrement{{/tr}}</th>
      </tr>

      {{mb_include module=system template=inc_config_str var=pattern_keyinfo}}

      <tr>
        <th class="button">
          <button class="hslip" id="import-key" onclick="return RPU_Sender.popupImport('{{$m}}');" type="submit">
            {{tr}}config-dPurgences-import-key{{/tr}}
          </button>
        </th>
        <td id="import_key">
          {{if !$writable}}
            <div class="big-error">
              Le dossier '{{$home}}' n'est pas autorisé en écriture. <br />
              Il est nécessaire de réaliser les manipulations suivantes : <br />
              <pre>$ mkdir {{$home}} <br />$ chown {{$user_apache}} {{$home}}</pre>
            </div>
          {{/if}}
        </td>
      </tr>
      <tr>
        <th class="button">
          <button class="lookup" onclick="return RPU_Sender.showEncryptKey();" type="button">
            {{tr}}config-dPurgences-show-encrypt-key{{/tr}}
          </button>
        </th>
        <td id="show_encrypt_key"></td>
      </tr>

      <tr>
        <th class="category" colspan="2">{{tr}}config-dPurgences-extraction{{/tr}}</th>
      </tr>

      {{mb_include module=$m template="Config_RPU_Sender_inc"}}

      <tr>
        <td class="button" colspan="10">
          <button class="modify" type="submit">{{tr}}Modify{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<div id="RPU_sender_source" style="display: none;">
  <table class="main">
    <tr>
      <td style="width: 10%">
        <ul class="control_tabs_vertical" id="tabs_configure_source" style="white-space: nowrap;">
          <li><a href="#config_source">{{tr}}config_source{{/tr}}</a></li>
          <li><a href="#config_source_rescue">{{tr}}config_source_rescue{{/tr}}</a></li>
          {{if $source_warm_bed}}
            <li><a href="#config_source_warm_bed">{{tr}}config_source_warm_bed{{/tr}}</a></li>
          {{/if}}
        </ul>
      </td>
      <td style="width: 90%">
        <div id="config_source">
          <table class="form">
            <tr>
              <th class="title">
                {{tr}}config-exchange-source{{/tr}}
              </th>
            </tr>
            <tr>
              <td>{{mb_include module=system template=inc_config_exchange_source source=$source}}</td>
            </tr>
          </table>
        </div>
        <div id="config_source_rescue">
          <table class="form">
            <tr>
              <th class="title">
                {{tr}}config-exchange-source-rescue{{/tr}}
              </th>
            </tr>
            <tr>
              <td>{{mb_include module=system template=inc_config_exchange_source source=$source_rescue}}</td>
            </tr>
          </table>
        </div>
      </td>
    </tr>
  </table>
</div>

{{if $m == "ror"}}
  <div id="CConfigEtab" style="display: none"></div>
{{/if}}
