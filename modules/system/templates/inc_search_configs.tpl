{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=value value='test'}}

<script>
  ConfigSearch = {
    editTrad: function (source) {
      new Url("system", "view_translations_config")
        .addParam("feature", source)
        .requestModal("50%", null, {
          onClose: function () {
            var form = getForm('config-search');
            form.onsubmit();
          }
        });
    },

    goTo: function (module, key, type) {
      var href;

      switch (type) {
        case '{{'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_INSTANCE}}':
          href = "?m=" + module + "&tab=configure";
          store.set("target-config", {module: module, key: key});
          break;

        case '{{'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_ETAB}}':
          href = "?m=" + module + "&tab=configure#CConfigEtab";

          if (module === "pharmacie") {
            href = "?m=" + module + "&tab=vw_idx_scores";
          }

          store.set("target-config", {module: module, key: key});
          break;

        case '{{'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_PREF}}':
          href = "?m=mediusers&tab=edit_infos#edit_prefs,module-" + module;
          break;

        case '{{'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_FUNC_PERM}}':
          href = "?m=admin&tab=vw_functional_perms#module-" + module;
          break;

        case '{{'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_SERVICE}}':
          href = "?m=dPprescription&tab=vw_edit_config_service";
          break;
      }

      window.open(href);
    }
  };
</script>

<table class="main tbl">
  <tr>
    <td colspan="10">
      {{mb_include module=system template=inc_pagination current=$start step=$step total=$total change_page="changePageConfig"}}
    </td>
  </tr>

  <tr>
    <th class="narrow">
      {{tr}}Type{{/tr}}
    </th>
    <th class="narrow">
      {{tr}}Module{{/tr}}
    </th>
    <th>
      {{tr}}Name{{/tr}}
    </th>
    <th {{if $conf.debug}}colspan="2"{{/if}}>
      {{tr}}Description{{/tr}}
    </th>
    <th>
      {{tr}}Value{{/tr}}
    </th>
    <th></th>
  </tr>

  {{foreach from=$filtered item=_tr key=_key}}
    {{assign var=key_split value='-'|explode:$_key}}
    {{assign var=use_span value=true}}
    {{if $_tr.tr != $_tr.old_tr || $_tr.desc != $_tr.old_desc}}
      {{assign var=use_span value=false}}
    {{/if}}
    <tr>
      <td rowspan="2">
        <span class="config-search-{{$_tr.type}}">
          {{if $_tr.type     == 'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_INSTANCE}}
            Configuration d'instance
          {{elseif $_tr.type == 'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_ETAB}}
            Configuration de service / établissement
          {{elseif $_tr.type == 'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_PREF}}
            Préférence
          {{elseif $_tr.type == 'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_FUNC_PERM}}
            Permission fonctionnelle
          {{elseif $_tr.type == 'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_SERVICE}}
            Configuration par service
          {{/if}}
        </span>
      </td>

      <td rowspan="2">
        {{tr}}module-{{$_tr.module}}-court{{/tr}}
      </td>

      {{if $_tr.type == 'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_SERVICE}}
        <td colspan="2" class="text" rowspan="2">
          {{tr}}{{$key_split.0|emphasize:$keywords:'u'}}{{/tr}}
        </td>
      {{else}}
        <td class="text" {{if $use_span}}rowspan="2"{{/if}}>
          {{$_tr.tr|emphasize:$keywords:'u'}}
        </td>
        {{if $conf.debug}}
          <td class="narrow" rowspan="2">
            {{if $_tr.type == 'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_INSTANCE || $_tr.type == 'Ox\Mediboard\System\CConfigSearch'|const:TYPE_CONFIG_ETAB}}
              <button class="edit notext" style="margin-left: 1em;" onclick="ConfigSearch.editTrad('{{$_key}}')">
                {{tr}}CTranslationOverwrite-title-modify{{/tr}}
              </button>
            {{/if}}
          </td>
        {{/if}}
        <td class="text" {{if $use_span}}rowspan="2"{{/if}}>
          {{$_tr.desc|emphasize:$keywords:'u'}}
        </td>
      {{/if}}

      <td class="text" rowspan="2">
        {{$_tr.value}}
      </td>

      <td class="narrow" rowspan="2">
        <button class="edit" onclick="return ConfigSearch.goTo('{{$_tr.module}}', '{{$key_split.0}}', '{{$_tr.type}}')">
          Acccéder au paramétrage
        </button>
      </td>
    </tr>
    <tr>
      {{if !$use_span}}
        <td class="compact text">
          {{$_tr.old_tr|emphasize:$keywords:'u'}}
        </td>
        <td class="compact text">
          {{$_tr.old_desc|emphasize:$keywords:'u'}}
        </td>
      {{/if}}
    </tr>
  {{/foreach}}
</table>