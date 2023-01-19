{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=return_form}}

<script>
  Main.add(function () {
    Control.Tabs.create('tab_return-forms', true);

    refreshAll();
  });

  refreshAll = function () {
    var form = getForm("return-forms-list-filter");

    ReturnForm.statuses.each(function (t) {
      ReturnForm.refreshList(t, form);
    });

    return false;
  };

  resetPages = function (form) {
    ReturnForm.statuses.each(function (t) {
      $V(form["start[" + t + "]"], 0, false);
    });
  };
</script>

<div>
  <!-- Action buttons -->
  <div style="float: right;">
    <button class="new me-margin-top-8" style="float: right" onclick="ReturnForm.create()">{{tr}}CProductReturnForm-title-create{{/tr}}</button>
  </div>

  <!-- Filter -->
  <form name="return-forms-list-filter" action="?" method="get" onsubmit="return refreshAll()">
    <input type="hidden" name="start[new]" value="0" onchange="ReturnForm.refreshList('new', this.form)" />
    <input type="hidden" name="start[pending]" value="0" onchange="ReturnForm.refreshList('pending', this.form)" />
    <input type="hidden" name="start[sent]" value="0" onchange="ReturnForm.refreshList('sent', this.form)" />
    <button type="submit" class="search me-margin-top-8">{{tr}}Filter{{/tr}}</button>
  </form>

  <!-- Tabs titles -->
  <ul id="tab_return-forms" class="control_tabs">
    <li><a href="#list-return-forms-new" class="empty">{{tr}}CProductReturnForm.status.new{{/tr}}
        <small>(0)</small>
      </a></li>
    <li><a href="#list-return-forms-pending" class="empty">{{tr}}CProductReturnForm.status.pending{{/tr}}
        <small>(0)</small>
      </a></li>
    <li><a href="#list-return-forms-sent" class="empty">{{tr}}CProductReturnForm.status.sent{{/tr}}
        <small>(0)</small>
      </a></li>
  </ul>

  <!-- Tabs containers -->
  <div id="list-return-forms-new" class="me-no-align me-no-border-bottom" style="display: none;"></div>
  <div id="list-return-forms-pending" class="me-no-align me-no-border-bottom" style="display: none;"></div>
  <div id="list-return-forms-sent" class="me-no-align me-no-border-bottom" style="display: none;"></div>
</div>
