{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=prop value=$props|@first}}
{{assign var=components value='|'|explode:$prop.components}}

<script type="text/javascript">
  Main.add(function () {
    var row = DOM.tr({id: 'config_header_row'});
    row.insert(DOM.th({class: 'category'}));
    {{foreach from=$configs item=_config name=configs_header}}
    {{if $_config.object != "default"}}
    {{if $smarty.foreach.configs_header.last && !$_config.object|instanceof:'Ox\Mediboard\Etablissement\CGroups'}}
    row.insert(DOM.th({class: 'category'}, '{{tr}}{{$context_class}}{{/tr}}'));
    {{elseif $_config.object == "global"}}
    row.insert(DOM.th({class: 'category'}, '{{tr}}config-inherit-{{$_config.object}}{{/tr}}'));
    {{else}}
    row.insert(DOM.th({class: 'category'}, '{{$_config.object|JSAttribute}}'));
    {{/if}}
    {{/if}}
    {{/foreach}}

    if ($('config_header_row')) {
      $('config_header_row').replace(row);
    } else {
      $('configurations').insert({before: row});
    }

    {{foreach from=$constants key=_index item=_constant}}
    {{assign var=config_name value=$configs_names[$_index]}}
    var form = getForm('constants_configs');
    form['{{$config_name}}-{{$components[0]}}'].addSpinner({type: 'num', min: -1, string: 'num min|-1'});
    form['{{$config_name}}-{{$components[1]}}'].addSpinner({type: 'num', min: -1, string: 'num min|-1'});
    form['{{$config_name}}-{{$components[4]}}'].addSpinner({type: 'float', min: 0, string: 'float min|0'});
    form['{{$config_name}}-{{$components[5]}}'].addSpinner({type: 'float', min: 0, string: 'float min|0'});
    form['{{$config_name}}-{{$components[6]}}'].addSpinner({type: 'float', min: 0, string: 'float min|0'});
    form['{{$config_name}}-{{$components[7]}}'].addSpinner({type: 'float', min: 0, string: 'float min|0'});
    {{/foreach}}
  });
</script>

{{foreach from=$constants key=_index item=_constant}}
  {{mb_include module=patients template=constantes_configs/inc_config constant=$_constant config_name=$configs_names[$_index] props=$props[$_index] script=false}}
{{/foreach}}