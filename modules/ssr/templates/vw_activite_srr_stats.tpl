{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td style="width: 50%;">
      <!-- Eléments de prescription -->
      {{if "dPprescription"|module_active}}
        <div style="max-height: 500px; overflow-y: auto;">
          <table class="tbl">
            <tr>
              <th class="title">{{tr}}CPrescriptionLineElement-params{{/tr}}</th>
            </tr>
            {{foreach from=$activite->_ref_elements_by_cat item=_elements_by_cat}}
              {{foreach from=$_elements_by_cat item=_element name="foreach_elt"}}
                {{assign var=elt_prescription value=$_element->_ref_element_prescription}}
                {{if $smarty.foreach.foreach_elt.first}}
                  <tr>
                    <th class="text">{{$elt_prescription->_ref_category_prescription}}</th>
                  </tr>
                {{/if}}
                <tr>
                  <td class="text">
                    {{mb_include module=system template=inc_vw_mbobject object=$elt_prescription}}
                  </td>
                </tr>
              {{/foreach}}
            {{foreachelse}}
            <tr>
              <td class="empty">{{tr}}CActeCsARR-none_params{{/tr}}</td>
            </tr>
            {{/foreach}}
          </table>
        </div>
      {{else}}
        <div class="small-warning">
          <div>{{tr}}ssr-param_prescription_no_acces{{/tr}}</div>
          <div>{{tr}}ssr-stats_no_acces{{/tr}}</div>
        </div>
      {{/if}}
    </td>

    <td style="width: 50%;">
      <!-- Eléments de prescription -->
      <div style="max-height: 500px; overflow-y: auto;">
        <table class="tbl">
          <tr>
            <th colspan="2" class="title">{{tr}}ssr-nb_actes_realised_executant{{/tr}}</th>
          </tr>
          {{foreach from=$activite->_count_actes_by_executant key=_executant_id item=_count}}
            {{if isset($activite->_ref_all_executants.$_executant_id|smarty:nodefaults)}}
              <tr>
                <td>
                   {{assign var=executant value=$activite->_ref_all_executants.$_executant_id}}
                   {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$executant}}
                </td>
                <td style="text-align: center;">{{$_count}}</td>
              </tr>
            {{/if}}
          {{foreachelse}}
            <tr>
              <td class="empty">{{tr}}ssr-no_executant{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
      </div>
    </td>
  </tr>
</table>

