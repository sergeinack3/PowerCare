{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="pmsi" script="PMSI"}}

<script type="text/javascript">
  Main.add(function () {
    var form = getForm("changeDate");

    Calendar.regField(form.date, null, {noView: true});
    Control.Tabs.create("tabs-dossiers", true, {
      afterChange: function (container) {
        switch (container.id) {
          case "sejours"  :
            PMSI.loadCurrentSejours(form);
            break;
          case "operations"  :
            PMSI.loadCurrentOperations(form);
            break;
          case "urgences" :
            PMSI.loadCurrentUrgences(form);
            break;
          default :
            PMSI.loadCurrentOperations(form);
            break;
        }
      }
    });
  });

  changePageOp = function (page) {
    PMSI.loadCurrentOperations(getForm("changeDate"),page);
  };

  changePageUrg = function (page) {
    PMSI.loadCurrentUrgences(getForm("changeDate"),page);
  };
</script>

<ul id="tabs-dossiers" class="control_tabs">
  {{foreach from=$counts key=category item=count}}
    <li>
      <a href="#{{$category}}"
         {{if !$count.total}}class="empty"{{/if}}
        {{if $count.facturees != $count.total}}class="wrong"{{/if}}>
        {{tr}}{{if $category === "sejours"}}CSejour{{else}}COperation-{{$category}}{{/if}}{{/tr}}
        <small>
          {{if $count.facturees == $count.total}}
            ({{$count.total}})
          {{else}}
            ({{$count.facturees}}/{{$count.total}})
          {{/if}}
        </small>
      </a>
    </li>
  {{/foreach}}
</ul>

<div id="sejours" style="display: none;"></div>
<div id="operations" style="display: none;"></div>
<div id="urgences" style="display: none;"></div>