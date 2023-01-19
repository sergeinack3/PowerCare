{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=importTools script=cronJobImport ajax=true}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs_logs_cron_import');
    getForm('filter_log_error').onsubmit();
    getForm('filter_log_warning').onsubmit();
    getForm('filter_log_info').onsubmit();
  });
</script>

<ul class="control_tabs" id="tabs_logs_cron_import">
  <li><a href="#tab_log_info">{{tr}}mod-importTools-log-infos{{/tr}}</a></li>
  <li><a href="#tab_log_warning">{{tr}}mod-importTools-log-warning{{/tr}}</a></li>
  <li><a href="#tab_log_error">{{tr}}mod-importTools-log-error{{/tr}}</a></li>
</ul>

{{foreach from=$log_type item=_type}}
  <div id="tab_log_{{$_type}}" style="display: none" class="me-no-align">

    <form name="filter_log_{{$_type}}" method="get" action="?" onsubmit="return onSubmitFormAjax(this, null, 'filter_log_{{$_type}}')">
      <input type="hidden" name="m" value="importTools"/>
      <input type="hidden" name="a" value="ajax_filter_logs"/>
      <input type="hidden" name="import_mod_name" value="{{$import_mod_name}}"/>
      <input type="hidden" name="import_class_name" value="{{$import_class_name}}"/>
      <input type="hidden" name="date_log_min" value="{{$date_log_min}}"/>
      <input type="hidden" name="date_log_max" value="{{$date_log_max}}"/>
      <input type="hidden" name="type" value="{{$_type}}"/>
    </form>

    <div id="filter_log_{{$_type}}" class="me-no-align"></div>
  </div>
{{/foreach}}
