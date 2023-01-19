{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
AppCache = {
  doFilterKeys: function(input) {
    var terms = $V(input).strip();
    if (!terms) {
      $$('tr.cache-key-stat').invoke('show');
      return;
    }

    terms = terms.split(" ");
    console.trace(terms);

    $$('td.cache-key-name').each(function(e) {
      var found = true;
      terms.each(function(term) {
        if (!e.innerHTML.like(term)) {
          found = false;
          throw $break;
        }
      });
      e.up('tr').setVisible(found);
    });
  },

  timeout: null,
  filterKeys: function (input) {
    clearTimeout(this.timeout);
    this.timeout = AppCache.doFilterKeys.delay(.5, input);
  }
}

</script>


<table class="tbl">
  <tr>
    <th>Prefix</th>
    <th>
      <input type="search" placeholder="key search" onkeyup="AppCache.filterKeys(this);" onsearch="AppCache.filterKeys(this);" />
    </th>
    {{foreach from=$all_layers item=_layer}}
      <th class="narrow"><tt>{{$_layer}}</tt></th>
    {{/foreach}}
  </tr>

  {{foreach from=$latest_cache.totals key=_prefix item=_layers}}
    <tr style="font-weight: bold;">
      <td colspan="2">{{$_prefix}}</td>
      {{foreach from=$_layers key=_layer item=_count}}
          <td style="text-align: right;">
            {{if $_count}}{{$_count|integer}}{{/if}}
          </td>
      {{/foreach}}
    </tr>

    <tbody>
      {{foreach from=$latest_cache.hits.$_prefix key=_key item=_layers}}
      <tr class="cache-key-stat">
        <td class="cache-key-name" colspan="2" style="padding-left: 2em;">{{$_key}}</td>
        {{foreach from=$_layers key=_layer item=_count}}
          <td style="text-align: right;">
            {{if $_count}}{{$_count|integer}}{{/if}}
          </td>
        {{/foreach}}
      </tr>
    {{/foreach}}
    </tbody>

  {{foreachelse}}
    <tr><td class="empty">Aucun cache utilisé</td></tr>
  {{/foreach}}
</table>
