{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create('tabs-configure', true,
      {
        afterChange: function (container) {
          if (container.id == "config_etab") {
            Configuration.edit('maternite', ['CGroups'], $('config_etab'));
          }
        }
      });

    var oform = getForm('guess_caesarean');
    Calendar.regField(oform.start);
    Calendar.regField(oform.end);

    reloadListCesar();
  });

  reloadListCesar = function () {
    var form = getForm('guess_caesarean');
    form.onsubmit();
  };
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CGrossesse">{{tr}}CGrossesse{{/tr}}</a></li>
  <li><a href="#config_etab">{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#tools">Outils</a></li>
  <li><a href="#rattrapage_cesarienne">Rattrapage césariennes</a></li>
</ul>

<div id="CGrossesse" style="display: none">
  {{mb_include template=CGrossesse_configure}}
</div>

<div id="tools" style="display: none">
  {{mb_include template=inc_maternite_tools}}
</div>

<div id="rattrapage_cesarienne" style="display: none;">
  <h2>Outil d'aide à la définition de césarienne sur des naissances</h2>
  <form method="get" name="guess_caesarean" onsubmit="return onSubmitFormAjax(this, null, 'result_guess')">
    <input type="hidden" name="m" value="maternite" />
    <input type="hidden" name="a" value="ajax_list_guess_caesarean" />

    <input type="text" name="start" value="{{$start}}" style="display: none;" />
    <input type="text" name="end" value="{{$end}}" style="display: none;" />

    <button class="search"></button>
  </form>
  <div id="result_guess"></div>
</div>

<div id="config_etab" style="display: none;"></div>