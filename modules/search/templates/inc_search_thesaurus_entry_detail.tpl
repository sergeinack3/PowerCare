{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    var list_result_elastic = document.getElementById('list_result_elastic');

    if (list_result_elastic != null) {
      var collection = document.querySelectorAll('.btnExecuterThesaurus');
      for (i = 0; i < collection.length; i++) {
        collection[i].style.display = 'none';
      }
    }
  });

</script>

<table class="main tbl me-no-align me-no-border-radius-top">
  <tr>
    <th class="category narrow" rowspan="2"></th>
    <th class="category narrow" rowspan="2"></th>
    <th class="category" rowspan="2">Auto</th>
    <th class="category" rowspan="2">{{mb_label object=$entry field=titre}}</th>
    <th class="category" rowspan="2">{{mb_label object=$entry field=entry}}</th>
    <th class="category" rowspan="2">{{mb_label object=$entry field=types}}</th>
    <th class="category" rowspan="2">{{mb_label object=$entry field=contextes}}</th>
    <th class="category" colspan="3" rowspan="1">{{tr}}CSearchTargetEntry{{/tr}}</th>
  </tr>
  <tr>
    <th class="section" colspan="1">Codes CCAM</th>
    <th class="section" colspan="1">Codes CIM10</th>
    <th class="section" colspan="1">Classes ATC</th>
  </tr>

  {{foreach from=$thesaurus item=_entry}}
    <tr>
      <td class="button">
        <button class="edit notext" onclick="Thesaurus.addeditThesaurusEntry(null, '{{$_entry->_id}}', null)"
                title="{{tr}}mod-search-thesaurus-edit{{/tr}}"></button>
      </td>
      <td class="button">
        <button class="lookup notext btnExecuterThesaurus" onclick="Thesaurus.executerThesaurusEntry('{{$_entry->_id}}')"
                title="{{tr}}mod-search-thesaurus-search{{/tr}}"></button>
      </td>

      <td class="text" style="text-align: center">
        {{if $_entry->search_auto}}
          <i class="fas fa-check fa-lg" style="color:#87c540;"></i>
        {{/if}}
      </td>

      <td class="text">
        {{mb_value object=$_entry field=titre}}
      </td>

      <td class="text">
        {{mb_value object=$_entry field=entry}}
      </td>

      <td class="text">
        {{if $_entry->types !== null}}
          {{assign var=values_search_types value="|"|@explode:$_entry->types}}
          <div class="columns-2">
            {{foreach from=$types item=_value}}
              {{if in_array($_value, $values_search_types)}}
                <i class="far fa-check-square" style="color:grey"></i> {{tr}}{{$_value}}{{/tr}}
                <br />
              {{/if}}
            {{/foreach}}
          </div>
        {{else}}
          <span style="color:grey; font-style: italic;">{{tr}}CSearchThesaurusEntry-all-types{{/tr}}</span>
        {{/if}}
      </td>

      <td class="text">
          {{mb_value object=$_entry field=contextes}}
      </td>

      <!-- hide target -->
      {{if $_entry->_cim_targets|@count > 0 || $_entry->_ccam_targets|@count > 0}}
        <td>
          <div style="float: right;">
            <ul class="tags">
              {{foreach from=$_entry->_ccam_targets item=_target}}
                <li class="tag me-tag" title="{{$_target->_ref_target->libelle_long}}"
                    style="background-color: rgba(153, 204, 255, 0.6); cursor:auto">
                  <span>{{$_target->_ref_target->code}}</span>
                </li>
                <br />
              {{/foreach}}
            </ul>
          </div>
        </td>
        <td>
          <div style="float: right;">
            <ul class="tags">
              {{foreach from=$_entry->_cim_targets item=_target}}
                <li class="tag " title="{{$_target->_ref_target->libelle}}" style="background-color: #CCFFCC; cursor:auto">
                  <span>{{$_target->_ref_target->code}}</span>
                </li>
                <br />
              {{/foreach}}
            </ul>
          </div>
        </td>
        <td>
          <div style="float: right;">
            <ul class="tags">
              {{foreach from=$_entry->_atc_targets item=_target}}
                <li class="tag me-tag" title="{{$_target->_libelle}}" style="background-color: rgba(240, 255, 163, 0.6); cursor:auto">
                  <span>{{$_target->object_id}}</span>
                </li>
                <br />
              {{/foreach}}
            </ul>
          </div>
        </td>
      {{else}}
        <td colspan="3">
          <div class="empty" colspan="10" style="text-align: center">
            {{tr}}CSearchCibleEntry.none{{/tr}}
          </div>
        </td>
      {{/if}}
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="9" style="text-align: center">
        {{tr}}CSearchThesaurusEntry.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
