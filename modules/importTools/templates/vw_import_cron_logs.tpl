{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
Main.add(function() {
  var form = getForm('filter_import_logs');
  form.onsubmit();
});
</script>

<div class="small-info">
  Le cron d'import est disponible à l'adresse : <strong>mediboard/?m=importTools&a=ajax_exec_import</strong> <br/>
  Les arguments sont :
  <ul>
    <li>
      <strong>import_module</strong> str notNull : Le nom du module avec lequel on veut faire l'import.
    </li>
    <li>
      <strong>import_class</strong> str notNull : Le nom de la classe principale d'import du module.
    </li>
    <li>
      <strong>limit</strong> bool default|1 : Le sélect utilise une limite ou non.
    </li>
    <li>
      <strong>count</strong> num default|200 : Nombre d'objets à importer à la fois.
    </li>
    <li>
      <strong>reimport</strong> bool default|0 : Les objets doivent être réimportés ou non.
    </li>
    <li>
      <strong>exclude</strong> str : Noms des classes d'import à ne pas traiter séparée par |.
    </li>
    <li>
      <strong>adapt_step</strong> bool default|1 : Le pas d'import (count) s'adapte automatiquement aux performances du système ou non.
    </li>
    <li>
      <strong>max_exec_time</strong> num default|290 : Le temps d'exécution après lequel le script se termine.
    </li>
    <li>
      <strong>max_memory</strong> num default|200 : La mémoire maximum que le script peut utiliser (en Mo).
    </li>
  </ul>

</div>

<form name="filter_import_logs" method="get" action="?" onsubmit="return onSubmitFormAjax(this, null, 'vw_logs_result')">
  <input type="hidden" name="m" value="importTools"/>
  <input type="hidden" name="a" value="ajax_filter_import_logs"/>

  <table class="main form">
    <tr>
      <th>{{mb_label object=$import_log field=import_mod_name}}</th>
      <td>{{mb_field object=$import_log field=import_mod_name prop=str}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$import_log field=import_class_name}}</th>
      <td>{{mb_field object=$import_log field=import_class_name prop=str}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$import_log field=date_log}}</th>
      <td>
        {{mb_field object=$import_log field=_date_log_min prop=dateTime form='filter_import_logs' register=true}}
        &raquo;
        {{mb_field object=$import_log field=_date_log_max prop=dateTime form='filter_import_logs' register=true}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="vw_logs_result" class="me-padding-9"></div>