{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main" id="table_main">
  <tr>
    <td>
      <form name="EditConfig-Search" action="?m={{$m}}&tab=configure" method="post" onsubmit="return onSubmitFormAjax(this)">
        {{mb_configure module=$m}}
        <table class="form">

          <tr>
            <th class="category" colspan="2">{{tr}}CSearch-indexing server{{/tr}}</th>
          </tr>

          {{mb_include module=system template=inc_config_str var=nb_replicas}}
          {{mb_include module=system template=inc_config_str var=interval_indexing}}

          <tr>
            <th>Pas d'indexation adapté</th>
            <td>
              {{assign var=adapted_step value=$cache->get('search_indexing_step')}}

              {{if $adapted_step}}
                {{$adapted_step}}

                <button class="erase notext" onclick="new Url('search', 'ajax_reset_indexing_step').requestUpdate(this.up())">
                  {{tr}}Reset{{/tr}}
                </button>
              {{else}}
                <em class="empty">Aucun</em>
              {{/if}}
            </th>
          </tr>



          <tr>
            <th class="category" colspan="2">{{tr}}CSearch-history{{/tr}}</th>
          </tr>

          {{mb_include module=system template=inc_config_str var=history_purge_probability}}
          {{mb_include module=system template=inc_config_str var=history_purge_day}}

          <tr>
            <th class="category" colspan="2">{{tr}}CSearch-display{{/tr}}</th>
          </tr>
          {{mb_include module=system template=inc_config_bool var=obfuscation_body}}

          <tr>
            <td class="button" colspan="2">
              <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>
