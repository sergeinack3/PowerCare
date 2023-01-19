{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  enableFormElements = function (element) {
    element.fire("conf:enable");
    var inputs = element.select("input,select,textarea,button:not(.keepEnable)");
    inputs.invoke("enable");
    return element.show();
  };

  disableFormElements = function (element) {
    element.fire("conf:disable");
    var inputs = element.select("input,select,textarea,button:not(.keepEnable)");
    inputs.invoke("disable");
    return element.hide();
  };

  toggleCustomValue = function (button, b) {
    var customValue = button.up('div.custom-value');
    var inheritValue = button.up('td');

    if (b) {
      enableFormElements(customValue);
      inheritValue.down('input.inherit-value').disable();
      customValue.down("button.edit").hide();
      customValue.down("button.cancel").show();
    }
    else {
      disableFormElements(customValue).show();
      inheritValue.down('input.inherit-value').enable();
      customValue.down("button.edit").show();
      customValue.down("button.cancel").hide();
    }
  };

  displayTraduction = function (feature) {
    var url = new Url('system', 'view_translations_config');
    url.addParam('feature', 'config-' + feature);
    url.requestModal('50%', null, {onClose: editObjectConfig.curry('{{$object_guid}}', '{{$uid}}')});
  };
</script>

{{mb_include module=system template=inc_toggle_alt_configuration_mode mode=$mode}}

<form name="edit-configuration-{{$uid}}" method="post" action="?"
      onsubmit="return onSubmitFormAjax(this, {onComplete: editObjectConfig.curry('{{$object_guid}}', '{{$uid}}'), useIgnore: true})">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="dosql" value="do_configuration_aed"/>
  <input type="hidden" name="mode" value="{{$mode}}" />
  <input type="hidden" name="object_guid" value="{{$object_guid}}"/>
  <input type="hidden" name="static_configs" value="{{$static_configs}}" />

  <table id="table-edit-config-{{$uid}}" class="main tbl">
    {{assign var=cols value=$ancestor_configs|@count}}

    {{foreach from=$ancestor_configs item=_ancestor}}
      <col style="width: {{math equation='100/x' x=$cols}}%"/>
    {{/foreach}}

    {{assign var=level_0 value=null}}
    {{assign var=level_1 value=null}}
    {{assign var=level_2 value=null}}
    {{assign var=level_3 value=null}}

    {{foreach from=$configs key=_feature item=_prop}}
      {{assign var=space value=" "}}
      {{assign var=sections value=$space|explode:$_feature}}

      {{if $sections.0 != $level_0}}
        <tr>
          <th></th>
          {{foreach from=$ancestor_configs item=_ancestor name=ancestor}}
            {{if $_ancestor.object != "default"}}
              <th>
                {{if !$smarty.foreach.ancestor.last}} {{*  && $object_guid != $_ancestor.object->_guid *}}
                  {{if $_ancestor.object != "global" || $app->_ref_user->isAdmin()}}
                    <button type="button" class="edit notext"
                            onclick="$V($('object_guid-selector-{{$uid}}'), '{{if $_ancestor.object|instanceof:'Ox\Core\CMbObject'}}{{$_ancestor.object->_guid}}{{else}}{{$_ancestor.object}}{{/if}}')"></button>
                  {{/if}}
                {{/if}}
                {{if $_ancestor.object == "global"}}
                  {{tr}}config-inherit-{{$_ancestor.object}}{{/tr}}
                {{else}}
                  {{$_ancestor.object}}
                {{/if}}
              </th>
            {{/if}}
          {{/foreach}}
        </tr>
        <tr>
          <th class="title" colspan="{{$cols}}">{{tr}}module-{{$sections.0}}-court{{/tr}}</th>
        </tr>
        {{assign var=level_0 value=$sections.0}}
      {{/if}}

      {{if $sections.1 != $level_1}}
        <tr data-section="{{$sections.1}}">
          <th class="category" colspan="{{$cols}}">{{tr}}config-{{$sections.0}}-{{$sections.1}}{{/tr}}</th>
        </tr>
        {{assign var=level_1 value=$sections.1}}
      {{/if}}

      {{if $sections.2 != $level_2 && $sections|@count > 3}}
        <tr>
          <th class="section" colspan="{{$cols}}">{{tr}}config-{{$sections.0}}-{{$sections.1}}-{{$sections.2}}{{/tr}}</th>
        </tr>
        {{assign var=level_2 value=$sections.2}}
      {{/if}}

      {{assign var=_only_admin value=false}}
      {{if array_key_exists('onlyAdmin',$_prop) && $_prop.onlyAdmin}}
        {{assign var=_only_admin value=true}}
      {{/if}}

      {{assign var=_show value=true}}
      {{if $_only_admin && !$app->_ref_user->isAdmin()}}
        {{assign var=_show value=false}}
      {{/if}}

      {{if $_show}}
        <tr data-section="{{$sections.1}}">
          {{assign var=feature_clean value=$_feature|replace:' ':'-'}}
          {{assign var=trad value='Ox\Core\CAppUI::tr'|static_call:"config-$feature_clean"}}
          {{assign var=trad_desc value='Ox\Core\CAppUI::tr'|static_call:"config-$feature_clean-desc"}}
          <td style="font-weight: bold; vertical-align: top;"
              class="text{{if $conf.debug && ($trad == $trad_desc || strlen($trad) > strlen($trad_desc))}} warning{{/if}}">

            {{if $conf.debug}}
              <button type="button" class="edit notext" onclick="displayTraduction('{{$_feature}}')">
                {{tr}}common-modify traduction{{/tr}}
              </button>
            {{/if}}

            {{if $_only_admin}}
              <i class="fa fa-lock" style="float: right;" title="Configuration reservée aux administrateurs"></i>
            {{/if}}

            {{tr}}config-{{$feature_clean}}{{/tr}}

            <br/>

            <span class="compact">
            {{'Ox\Core\CAppUI::tr'|static_call:"config-$feature_clean-desc"|markdown}}
          </span>
          </td>

          {{assign var=prev_value value=null}}

          {{foreach from=$ancestor_configs item=_ancestor name=ancestor}}
            {{assign var=value value=$_ancestor.config_parent.$_feature|smarty:nodefaults}}
            {{assign var=is_inherited value=true}}

            {{if array_key_exists($_feature, $_ancestor.config)}}
              {{assign var=value value=$_ancestor.config.$_feature|smarty:nodefaults}}
              {{assign var=is_inherited value=false}}
            {{/if}}

            {{if $is_inherited}}
              {{assign var=value value=$prev_value|smarty:nodefaults}}
            {{/if}}

            {{if $_ancestor.object != "default"}}
              <td class="text" style="vertical-align: top; border-right: 2px solid #999;">
                {{if ($_ancestor.object === 'global' && $_feature|array_key_exists:$alt_global_features)
                 || ($smarty.foreach.ancestor.last && $_ancestor.object !== 'global' && $_feature|array_key_exists:$alt_features)}}
                  <div style="margin: 0 5px; vertical-align: middle; display: inline-block;">
                    <i class="fas fa-code-branch fa-lg" title="Une valeur alternative existe"></i>
                  </div>
                {{/if}}

                {{if !$is_inherited || ($_ancestor.object != "global" && $_feature|array_key_exists:$features) || ($_ancestor.object == "global" && $_feature|array_key_exists:$features_global)}}
                  <a style="float: right" href="#1"
                     onclick="viewObjectConfigHistory('{{$_feature}}'{{if $_ancestor.object != "global"}}, '{{$_ancestor.object->_class}}','{{$_ancestor.object->_id}}'{{/if}}); return false;">
                    {{me_img src="history.gif" icon="history" class="me-primary"}}
                  </a>
                {{/if}}

                {{if $smarty.foreach.ancestor.last}}
                  <input type="hidden" name="c[{{$_feature}}]"
                         value="{{'Ox\Mediboard\System\CConfiguration'|const:INHERIT}}" {{if !$is_inherited}} disabled {{/if}} class="inherit-value"/>
                {{/if}}

                <div class="custom-value {{if (!$smarty.foreach.ancestor.last && $is_inherited)}}opacity-30{{/if}}"
                     style="float: left;">
                  {{if $smarty.foreach.ancestor.last}}
                    <button type="button" class="edit notext compact keepEnable"
                            onclick="toggleCustomValue(this, true)" {{if !$is_inherited}} style="display: none;" {{/if}}></button>
                    <button type="button" class="cancel notext compact keepEnable"
                            onclick="toggleCustomValue(this, false)" {{if $is_inherited}} style="display: none;" {{/if}}></button>
                  {{/if}}

                  {{assign var=is_static value=false}}
                  {{if $inherit === 'static'}}
                    {{assign var=is_static value=true}}
                  {{/if}}

                  {{assign var=tpl value='config/inc_spec_'|cat:$_prop.type}}
                  {{mb_include module=system template=$tpl is_last=$smarty.foreach.ancestor.last is_static=$is_static}}
                </div>
              </td>
            {{/if}}

            {{assign var=prev_value value=$value|smarty:nodefaults}}
          {{/foreach}}
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
  <div id="div-submit-configs-{{$uid}}" class="me-no-bg" style="background-color: #ffffff; width: 100%; height: 2.5em; text-align: center;">
    <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
  </div>
</form>
