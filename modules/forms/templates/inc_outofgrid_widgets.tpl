{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  Glisser-déposer les widgets dans la zone de formulaire. <br />
  Veuillez double-cliquer sur les widgets pour modifier leur comportement ou leur affichage.
</div>

<div id="draggable-widget-container">
  {{assign var=counter value=0}}
  {{foreach from='Ox\Mediboard\System\Forms\CExClassWidget::getWidgetTypes'|static_call:"" item=_widget name=test}}
    <div class="draggable form-widget-wrapper"
         data-ex_class_id="{{$ex_class->_id}}"
         data-type="form-widget"
         data-name="{{$_widget->name}}"
         data-title="{{tr}}CExClassWidget.name.{{$_widget->name}}{{/tr}}"
         data-reset_id="draggable-widget-container"
         data-reset_pos="{{$counter}}"
         data-movable="true"
         style="width: {{$_widget->default_dimensions.width}}px; height: {{$_widget->default_dimensions.height}}px;"
    >
      <div style="width: 600px">
        <div class="clonable">
          {{mb_include module=forms template=inc_widget_draggable_pixel widget=$_widget ex_object=$object->_ex_object}}
        </div>
      </div>
    </div>
    {{assign var=counter value=$counter+1}}
  {{/foreach}}
</div>