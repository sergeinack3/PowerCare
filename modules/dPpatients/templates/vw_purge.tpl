{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("tabs-class-purge", true, {
      afterChange: function (elt) {
        var div = elt.id;

        if (div) {
          var url = new Url('dPpatients', 'vw_purge_class');
          url.addParam('class_name', div);
          url.requestUpdate(elt);
        }
      }
    });
  });

  nextPurgeStep = function (class_name, start, new_count) {
    var form = getForm("purge-by-class-" + class_name);
    if (!form) {
      return;
    }

    $V(form.elements.total_count, new_count);
    $V(form.elements.start, start);

    if ($V(form.elements.continue) && new_count > 0) {
      form.onsubmit();
    }
  };

  confirmPurgeObjects = function (form, class_name) {
    if (confirm($T('common-confirm-Purge object %s?', $T(class_name + '|pl')))) {
      form.onsubmit();
    }
  }
</script>

<div class="small-error">{{tr}}system-msg-purge-warning{{/tr}}</div>

<table>
  <tr>
    <td style="vertical-align: top;">
      <ul id="tabs-class-purge" class="control_tabs_vertical small">
        <li><a href="#COperation-purge">{{tr}}COperation|pl{{/tr}}</a></li>
        <li><a href="#CSejour-purge">{{tr}}CSejour|pl{{/tr}}</a></li>
        <li><a href="#CPatient-purge">{{tr}}CPatient|pl{{/tr}}</a></li>
      </ul>
    </td>
    <td style="vertical-align: top; width: 100%;">
      <div id="COperation-purge" style="display: none;"></div>
      <div id="CSejour-purge" style="display: none"></div>
      <div id="CPatient-purge" style="display: none"></div>
    </td>
  </tr>
</table>