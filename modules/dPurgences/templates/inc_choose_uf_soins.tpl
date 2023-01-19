{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=required_uf_soins value="dPplanningOp CSejour required_uf_soins"|gconf}}
{{assign var=required_uf_med   value="dPplanningOp CSejour required_uf_med"|gconf}}
{{assign var=use_cpi           value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}

<script>
  Main.add(function() {
    var form = getForm('choose-ufs-soins-form');

    new Url('hospi', 'ajax_lit_autocomplete')
      .addParam('group_id', '{{$sejour->group_id}}')
      .autoComplete(form.keywords, null, {
        minChars: 2,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function (field, selected) {
          var value = selected.id.split('-')[2];
          $V(form.lit_id, value);
        },
        callback: function (input, queryString) {
          var service_id = $V(form.service_id);
          return queryString + '&service_id=' + service_id;
        }
      });

    {{if $curr_aff->_id}}
      Calendar.regField(form.date_aff);
    {{/if}}
  });
</script>

<form name="choose-ufs-soins-form" method="get" onsubmit="if (!checkForm(this)) return false; {{$callback}}(this); Control.Modal.close(); return false;">
  {{mb_key object=$curr_aff}}

  <table class="main form">
    <tr>
      <th>{{tr}}Date{{/tr}}</th>
      <td>
        <input type="hidden" name="date_aff" value="{{$date_aff}}" class="dateTime" />
        {{if !$curr_aff->_id}}
          {{$date_aff|date_format:$conf.datetime}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$curr_aff field=service_id}}</th>
      <td>
        {{if $services|@count > 1}}
          <select name="service_id" onchange="$V(this.form.lit_id, ''); $V(this.form.keywords, '');">
            {{foreach from=$services item=_service}}
              <option value="{{$_service->_id}}" {{if $curr_aff->service_id === $_service->_id}}selected{{/if}}>{{$_service->_view}}</option>
            {{/foreach}}
          </select>
        {{else}}
          {{mb_field object=$curr_aff field=service_id hidden=true}}
          {{$curr_aff->_ref_service}}
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$curr_aff field=lit_id}}</th>
      <td>
        <input type="hidden" name="lit_id" value="{{$curr_aff->lit_id}}" class="notNull" />
        <input type="text" name="keywords" value="{{$curr_aff->_ref_lit->_view}}" class="autocomplete" />
      </td>
    </tr>

    {{if $required_uf_soins !== "no"}}
    <tr>
      <th>{{mb_label object=$curr_aff field=uf_soins_id}}</th>
      <td>
        <select name="uf_soins_id" class="ref {{if $required_uf_soins === "obl"}}notNull{{/if}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$ufs.soins item=_uf}}
            <option value="{{$_uf->_id}}" {{if $curr_aff->uf_soins_id == $_uf->_id}}selected{{/if}}>
              {{mb_value object=$_uf field=libelle}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    {{/if}}

    {{if $required_uf_med !== "no"}}
    <tr>
      <th>{{mb_label object=$curr_aff field=uf_medicale_id}}</th>
      <td>
        <select name="uf_medicale_id" class="ref {{if $required_uf_med === "obl"}}notNull{{/if}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$ufs.medicale item=_uf}}
            <option value="{{$_uf->_id}}" {{if $curr_aff->uf_medicale_id == $_uf->_id}}selected{{/if}}>
              {{mb_value object=$_uf field=libelle}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    {{/if}}

      {{if $use_cpi != "no"}}
    <tr>
      <th>{{mb_label object=$sejour field=charge_id}}</th>
      <td>
        <select name="charge_id" class="ref {{if $use_cpi == "obl"}}notNull{{/if}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$cpi_list item=_cpi}}
            <option value="{{$_cpi->_id}}" {{if $sejour->charge_id == $_cpi->_id}} selected {{/if}}>
              {{$_cpi|truncate:50:"...":false}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    {{/if}}

    <tr>
      {{if $list_mode_entree|@count}}
        <th>{{mb_label object=$curr_aff field=mode_entree_id}}</th>
        <td>
          {{mb_field object=$curr_aff field=mode_entree hidden=true}}

          <input type="hidden" name="mode_entree_id" value="{{$curr_aff->mode_entree_id}}" class="autocomplete notNull" size="30"/>
          <input type="text" name="mode_entree_id_autocomplete_view" size="30" value="{{if $curr_aff->mode_entree_id}}{{$curr_aff->_ref_mode_entree->libelle}}{{/if}}"
                 class="autocomplete" onchange="if (!this.value) { this.form['_mode_entree_id'].value = '' }" />

          <script>
            Main.add(function() {
              var form = getForm('choose-ufs-soins-form');
              var input = form.mode_entree_id_autocomplete_view;
              var url = new Url('system', 'httpreq_field_autocomplete');
              url.addParam('class', 'CAffectation');
              url.addParam('field', 'mode_entree_id');
              url.addParam('limit', 50);
              url.addParam('view_field', 'libelle');
              url.addParam('show_view', false);
              url.addParam('input_field', 'mode_entree_id_autocomplete_view');
              url.addParam('wholeString', true);
              url.addParam('min_occurences', 1);
              url.autoComplete(input, '_mode_entree_id_autocomplete_view', {
                minChars: 1,
                method: 'get',
                select: 'view',
                dropdown: true,
                afterUpdateElement: function(field, selected) {
                  $V(field.form['mode_entree_id'], selected.getAttribute('id').split('-')[2]);
                  var selectedData = selected.down('.data');
                  $V(field.form.mode_entree, selectedData.get('mode'));
                  $V(field.form.provenance, selectedData.get('provenance'));
                },
                callback: function(element, query) {
                  query += '&where[group_id]={{$g}}';
                  query += '&where[actif]=1';
                  return query;
                }
              });
            });
          </script>
        </td>
      {{else}}
        <th>{{mb_label object=$curr_aff field=mode_entree}}</th>
        <td>{{mb_field object=$curr_aff field=mode_entree}}</td>
      {{/if}}
    </tr>

    <tr>
      <th>{{mb_label object=$curr_aff field=provenance}}</th>
      <td>{{mb_field object=$curr_aff field=provenance}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button class="submit">
          {{tr}}Validate{{/tr}}
        </button>

        <button class="close" type="button" onclick="Control.Modal.close()">
          {{tr}}Close{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
