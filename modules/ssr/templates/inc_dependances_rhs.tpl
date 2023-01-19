{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dependances value=$rhs->_ref_dependances}}

<table class="form">
  <tr>
    <th class="title">
      {{mb_include module=system template=inc_object_idsante400 object=$dependances}}
      {{mb_include module=system template=inc_object_history    object=$dependances}}

      {{if $dependances->_id}}
        <button type="button" class="duplicate notext compact me-primary me-margin-4 me-float-none" style="float: left;"
                onclick="CotationRHS.duplicate('{{$rhs->_id}}', '{{$rhs->sejour_id}}', 'dependances');">{{tr}}Duplicate{{/tr}}</button>
      {{/if}}
      {{tr}}CDependancesRHS{{/tr}}
    </th>
  </tr>
</table>

<script>
CotationRHS.drawDependancesGraph(
  $("radar-dependances-{{$rhs->_id}}"),
    "{{$rhs->_id}}", 
    [
      {{foreach from=$rhs->_ref_dependances_chonology item=_dep key=_date name=_deps}}
        {
              label: "S{{$_date}}",
                {{if $_date != "+0"}} 
          radar: {
                        fillOpacity: 0.1,
              lineWidth: 0.5
                    },
                {{/if}}
                data: [
                    [0, {{$_dep->habillage}}],
                [1, {{$_dep->deplacement}}],
                [2, {{$_dep->alimentation}}],
                [3, {{$_dep->continence}}],
                [4, {{$_dep->comportement}}],
                [5, {{$_dep->relation}}]
                ]
            }{{if !$smarty.foreach._deps.last}},{{/if}}
        {{/foreach}}
  ]
);
</script>

<div id="radar-dependances-{{$rhs->_id}}" style="width: 250px; height: 250px; cursor: pointer;"
     onclick="CotationRHS.editDependancesRHS({{$rhs->_id}})" title="{{tr}}Edit{{/tr}}"></div>
