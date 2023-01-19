{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="form-widget overlayed"
     onclick="ExClass.focusResizable(event, this)" 
     style="position: relative; display: inline-block;"
     ondblclick="Event.stop(event); this.up('.form-widget') && ExWidget.edit(this.up('.form-widget').get('ex_class_widget_id'));">
  {{$widget->display($ex_object,'preview')}}
  <div class="overlay"></div>
</div>
