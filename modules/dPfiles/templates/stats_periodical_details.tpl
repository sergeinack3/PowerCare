{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">

  <tr>
    <th colspan="100">
      Statistiques pour le propriétaire : {{if $owner}}{{$owner}}{{else}}{{tr}}All{{/tr}}{{/if}}
      {{if $doc->_id}}
        &mdash; {{tr}}Name{{/tr}} : {{$doc->_view}}
      {{/if}}
      {{if $object_class}}
        &mdash; {{tr}}Type{{/tr}} : {{tr}}{{$object_class}}{{/tr}}
      {{/if}}
      {{if $category->_id}}
        &mdash; {{tr}}CFilesCategory{{/tr}} : {{$category}}
      {{/if}}
      {{if $factory}}
        &mdash; {{tr}}CCompteRendu-factory{{/tr}} : {{tr}}CCompteRendu.factory.{{$factory}}{{/tr}}
      {{/if}}
    </th>
  </tr>

  <tr>
    <th class="title narrow">Types de périodes</th>
    <th class="title" colspan="{{$periodical_details.year|@count}}">{{tr}}Period{{/tr}}</th>
  </tr>

  {{foreach from=$periodical_details key=_period_type item=_details}}
    <tr>
      <th rowspan="2">{{tr}}{{$_period_type}}{{/tr}}</th>
      {{foreach from=$_details key=_period item=_detail}}
      <th>
        {{if $_detail && $_detail.date_min && $_detail.date_max && !$object_class && !$category->_id}}
          <button
            class="search" type="button"
            onclick="Details.statOwner('{{$doc->_class}}', '{{$doc->_id}}', '{{$owner_guid}}', '{{$_detail.date_min}}', '{{$_detail.date_max}}', null, '{{$factory}}');"
          >
            {{$_period}}
          </button>
        {{else}}
          {{$_period}}
        {{/if}}
      </th>
      {{/foreach}}
    </tr>

    <tr style="text-align: center;">
      {{foreach from=$_details key=_period item=_detail name=details}}
        {{assign var=opacity value=$smarty.foreach.details.last|ternary:'opacity-50':0}}
        {{if !$_detail || !$_detail.count}}
          <td class="arretee {{$opacity}} empty">{{tr}}None{{/tr}}</td>
        {{else}}
          <td class="{{$opacity}}">
            {{$_detail.count|integer}}
            <br/>{{$_detail.weight|decabinary}}
            {{math equation=x/y x=$_detail.weight|default:0 y=$_detail.count assign=mean_size}}
            <br/>~ {{$mean_size|decabinary}} / item
            {{if $is_doc}}
              <div>
                {{$_detail.docs_read_time|date_format:'%M:%S'}} ({{tr}}CCompteRendu-Time-read{{/tr}})
              </div>
              <div style="cursor: pointer;" title="{{tr var1=$_detail.nb_writers_simultaneous}}CCompteRendu-Number of simultaneous writers{{/tr}}">
                {{$_detail.docs_write_time|date_format:'%M:%S'}} ({{tr}}CCompteRendu-Time-write{{/tr}})
              </div>
            {{/if}}
          </td>
        {{/if}}
      {{/foreach}}
    <tr>
  {{/foreach}}

</table>
