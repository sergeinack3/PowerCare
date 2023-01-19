{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=cronjob ajax=true}}


<script>
  Main.add(function () {
    Control.Tabs.create('tabs-cronjob', true, {
      afterChange: function (container) {
        switch (container.id) {
          case "tab_list_cronjobs":
            getForm('search_cronjob_list').onsubmit();
            break;

          case "tab_log_cronjobs":
            getForm('search_cronjob').onsubmit();
            break;
        }
      }
    });
  });
  var submit_search = function (form) {
    form = form.up(4);
    $V(form.page, 0);
    return onSubmitFormAjax(form, CronJob.refresh_list(form));
  }
</script>

<ul id="tabs-cronjob" class="control_tabs">
  <li><a href="#tab_list_cronjobs">{{tr}}CCronJob.list{{/tr}}</a></li>
  <li><a href="#tab_log_cronjobs">{{tr}}CCronJobLog{{/tr}}</a></li>
</ul>

<div id="tab_list_cronjobs" style="display: none;">

  <button class="new me-margin-top-10" type="button" onclick="CronJob.edit(0)">{{tr}}CCronJob.new{{/tr}}</button>

  <form name="search_cronjob_list" method="post" onsubmit="return onSubmitFormAjax(this, CronJob.refresh_list(this))">

    <input type="hidden" name="page">

    <table class="form">
      <tr>
        <th>{{mb_title object=$log_cron field="cronjob_id"}}</th>
        <td>{{mb_field object=$log_cron field="cronjob_id" canNull=true form="search_cronjob_list" autocomplete="true,1,50,true,true"}}</td>

        <th>{{tr}}CCronJob-active{{/tr}}</th>
        <td>
          <input type="radio" name="active_filter" value="1" >
          <label class="">{{tr}}Yes{{/tr}}</label>

          <input type="radio" name="active_filter" value="0" >
          <label class="">{{tr}}No{{/tr}}</label>

          <input type="radio" name="active_filter" value="" checked="checked" >
          <label class="">{{tr}}All{{/tr}}</label>
        </td>
      </tr>
      <tr>
        <td colspan="10" class="button">
          <button type="submit" class="search" onclick="submit_search(this)">{{tr}}Search{{/tr}}</button>
        </td>
      </tr>
    </table>

  </form>

  <div id="list_cronjobs"></div>

</div>

<div id="tab_log_cronjobs">

  <form name="search_cronjob" method="post" onsubmit="return onSubmitFormAjax(this, CronJob.refresh_logs(this))">

    <input type="hidden" name="page">

    <table class="form">
      <tr>
        <th>{{mb_title object=$log_cron field="cronjob_id"}}</th>
        <td>{{mb_field object=$log_cron field="cronjob_id" canNull=true form="search_cronjob" autocomplete="true,1,50,true,true"}}</td>

        <th>{{mb_title object=$log_cron field="status"}}</th>
        <td>{{mb_field object=$log_cron field="status" canNull=true emptyLabel="Choose"}}</td>

        <th>{{mb_title object=$log_cron field="severity"}}</th>
        <td>{{mb_field object=$log_cron field="severity" canNull=true emptyLabel="Choose"}}</td>

        <th>Du</th>
        <td>{{mb_field object=$log_cron field="_date_min" form="search_cronjob" register=true}}</td>

        <th>jusqu'au</th>
        <td>{{mb_field object=$log_cron field="_date_max" form="search_cronjob" register=true}}</td>
      </tr>

      <tr>
        <td colspan="10" class="button">
          <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
        </td>
      </tr>
    </table>

  </form>

  <div id="search_log_cronjob"></div>

</div>
