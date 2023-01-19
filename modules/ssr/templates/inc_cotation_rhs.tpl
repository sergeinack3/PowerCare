{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $bilan->_id && !$bilan->planification}} 
  <div class="small-info">
    {{tr}}CBilanSSR-msg-cotation-off{{/tr}}
    <br />
    {{tr}}CBilanSSR-msg-planification-cf{{/tr}}
  </div>

  {{mb_return}}
{{/if}}

<script>
  Main.add(function() {
    var options = {
      afterChange: function(newContainer) {
        var rhs_id = newContainer.get("rhs_id");
        CotationRHS.launchDrawDependancesGraph(rhs_id);
      }
    };
    Control.Tabs.create('tabs_rhss', true, options).activeLink.onmouseup();

    if ($$('.control_tabs_unfolded').length > 0) {
      var td_witdh = $$('.td_rhs')[0].offsetWidth;
      var tab_rhs_witdh = $('tabs_rhss').offsetWidth;

      $$('.rhs_item').each(function(div) {
        div.setStyle({marginLeft: "-" + (td_witdh - tab_rhs_witdh) + "px"})
      });
    }
  });
</script>

<table class="main">
  <tr>
    <td class="narrow td_rhs">
      <ul id="tabs_rhss" class="control_tabs_vertical" style="width: 14em;">
        {{foreach from=$rhss item=_rhs}}
        <li>
          <a href="#cotation-{{if $_rhs->_id}}{{$_rhs->_id}}{{else}}{{$_rhs->date_monday}}{{/if}}"
             onmouseup="{{if $_rhs->_id}}CotationRHS.refreshRHS('{{$_rhs->_id}}');{{/if}}"
             {{if !$_rhs->_id}}onmousedown="CotationRHS.refreshNewRHS('{{$_rhs->date_monday}}', '{{$sejour->_id}}');"{{/if}}
            {{if !$_rhs->_id}}class="empty"{{/if}}
            {{if !$_rhs->_in_bounds}}class="wrong"{{/if}}
            >
            {{$_rhs}}
          <br />
          <small>
            {{tr}}date.from{{/tr}} {{mb_value object=$_rhs field=date_monday}}
            {{tr}}date.to{{/tr}} {{mb_value object=$_rhs field=_date_sunday}}
          </small>
          </a>
        </li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      {{foreach from=$rhss item=_rhs}}
        {{if $_rhs->_id}}
          <div id="cotation-{{$_rhs->_id}}" class="rhs_item" style="display: none;" data-rhs_id="{{$_rhs->_id}}">
          </div>
        {{else}}
          <div id="cotation-{{$_rhs->date_monday}}" class="rhs_item" style="display: none;" data-rhs_id="">
          {{mb_include module=ssr template=inc_create_rhs rhs=$_rhs}}
          </div>
        {{/if}}
      {{/foreach}}
    </td>
  </tr>
</table>