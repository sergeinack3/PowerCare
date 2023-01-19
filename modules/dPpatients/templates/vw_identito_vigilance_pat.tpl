{{*
 * @package Mediboard\Patient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient_signature ajax=true}}

<script type="text/javascript">
  Main.add(function () {
    Control.Tabs.create('tabs-duplicate-homonyme', false, {
      afterChange: function (container) {
        container.previous("form").onsubmit();
      }
    });
  });
</script>

<ul id="tabs-duplicate-homonyme" class="control_tabs">
  <li>
    <a href="#tab_duplicates_patients">Doublons</a>
  </li>
  <li>
    <a href="#tab_homonymes_patients">Homonymes</a>
  </li>
</ul>

<form name="show_table_duplicates_patients" method="get" onsubmit="return Url.update(this, 'tab_duplicates_patients')">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="a" value="ajax_vw_table_duplicates" />
  <input type="hidden" name="start" value="{{$start_duplicates}}" onchange="this.form.onsubmit()" />
</form>
<div id="tab_duplicates_patients"></div>

<br />
<form name="show_table_homonymes_patients" method="get" onsubmit="return Url.update(this, 'tab_homonymes_patients')">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="a" value="ajax_vw_table_homonymes" />
  <input type="hidden" name="start" value="{{$start_homonymes}}" onchange="this.form.onsubmit()" />
</form>
<div id="tab_homonymes_patients"></div>