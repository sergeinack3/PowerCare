{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=class value=CConstantesMedicales}}
{{mb_script module=patients script=ConstantConfig ajax=true}}

<script type="text/javascript">
  Main.add(function () {
    /* Il est nécessaire de spécifier la taille ou la taille maximum de l'élément afin d'utiliser le overflow hidden */
    var th = $('contexts_header');
    th.setStyle({maxWidth: th.getWidth() + 'px'});
    Context.list(getForm('selectSchema'));
  });
</script>

<div id="context" style="width: 100%">
  <div id="context_header">
    <form action="?" method="post" name="selectSchema" onsubmit="return Context.list(this);">
      <span id="schemas">
        Schéma :
        <select name="schema" id="select_schema" onchange="this.form.onsubmit();" class="me-small">
          {{foreach from=$schemas item=_schema}}
            <option value="{{$_schema}}">
              {{tr}}config-inherit-{{$_schema}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </span>

      <span id="groups">
        {{tr}}CGroups{{/tr}} :
        <select name="group" id="select_group" onchange="this.form.onsubmit();" class="me-small">
          {{if $app->_ref_user->isAdmin()}}
            <option value="global" data-guid="global" data-class="" data-name="Global">Global</option>
          {{/if}}
          {{foreach from=$groups item=_group}}
            <option value="{{$_group->_guid}}" {{if $_group->_id == $g}} selected{{/if}} data-class="CGroups" data-name="{{$_group}}"
                    data-guid="{{$_group->_guid}}">
              {{$_group}}
            </option>
          {{/foreach}}
        </select>
      </span>
    </form>
  </div>
  <form action="?" method="post" name="selectContexts" onsubmit="">
    <table class="tbl me-small-tbl me-no-align">
      <tr>
        <th class="title" colspan="6">
          Contextes
        </th>
      </tr>
      <tr>
        <th id="contexts_header" class="category" colspan="6" style="text-align: left; height: 20px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
          <span>
            <i id="toggle_context_list" class="fa fa-lg fa-chevron-circle-down" onclick="Context.toggle();"
               style="cursor: pointer;"></i>
          </span>
          Contextes sélectionnés :
          <span id="selected_contexts" data-context_class=""></span>
        </th>
      </tr>
      <tbody id="context_list"></tbody>
    </table>
  </form>
</div>

<table class="layout">
  <tr>
    <td style="width: 230px;">
      <table class="tbl">
        <tr>
          <th class="title">
            <input type="checkbox" name="check_all_constants" onchange="ConstantConfig.checkAll(this);" style="float: left">
            {{tr}}CConstantesMedicales{{/tr}}
            <input type="text" class="me-small" name="filter_constants" size="6" onkeyup="ConstantConfig.filter(this);"></th>
        </tr>
      </table>
    </td>
    <td class="greedyPane">
      <table class="tbl">
        <tr>
          <th class="title" style="height: 21px; padding: 2px;">
            <button type="button" class="erase notext" onclick="ConstantConfig.resetAll();" style="margin-right: 5px; float: right;">
              {{tr}}ConstantConfig-action-reset_all{{/tr}}
            </button>
            Configurations
          </th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="width: 230px;">
      <div id="constants_container">
        <table id="constants" class="tbl me-no-align" style="width: 230px;">
          {{mb_include module=patients template=constantes_configs/inc_constants_list}}
        </table>
      </div>
    </td>
    <td class="greedyPane" style="vertical-align: top;">
      <form action="?" method="post" name="constants_configs" onsubmit="return ConstantConfig.submit(this);">
        <input type="hidden" name="m" value="system" />
        <input type="hidden" name="dosql" value="do_configuration_aed">
        <input type="hidden" name="object_guid" value="">

        <div id="config_container">
          <table id="table_constant_configs" class="tbl me-no-align" style="width: 100%;">
            <tr>
              <td colspan="5" class="me-padding-0">
                <div class="info">
                  Veuillez sélectionner les constantes à configurer
                </div>
              </td>
            </tr>
            <tr id="no_constant_selected">
              <td class="empty" style="text-align: center;" colspan="5">
                Aucune constante sélectionnée
              </td>
            </tr>
            <tbody id="configurations">
            </tbody>
          </table>
          <div id="div-submit-constant_configs"
               style="background-color: #ffffff; width: 100%; height: 2.5em; text-align: center; display: none;">
            <button type="button" class="submit" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
          </div>
        </div>
      </form>
    </td>
  </tr>
</table>
