<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Responses;

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Jfse\Exceptions\RouterException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represents a smarty response
 *
 * @package Ox\Mediboard\Jfse\Responses
 */
final class SmartyResponse extends Response
{
    public const MESSAGE_SUCCESS = 'success';
    public const MESSAGE_INFO    = 'info';
    public const MESSAGE_WARNING = 'warning';
    public const MESSAGE_ERROR   = 'error';
    /** @var string The name of the smarty template to display */
    protected $template;
    /** @var array The parameters to assign to the template (the key must be the name of the parameter used in the template) */
    protected $variables;
    /** @var string An optional directory name (useful if the template is in another module) */
    protected $directory;

    /**
     * SmartyResponse constructor.
     *
     * @param string|null $template  The name of the template (file)
     * @param array|null  $variables Variables which are sent to the template
     */
    public function __construct(?string $template = null, ?array $variables = [], string $module = null)
    {
        parent::__construct('');

        if (is_countable($variables) && count($variables)) {
            $this->setVariables($variables);
        }

        if ($template) {
            $this->setTemplate($template);

            if ($module) {
                $this->setModule($module);
            }

            $this->setHTMLContent();
        }
    }

    /**
     * Make a new smarty response for messages
     *
     * @param string $locale
     * @param string $type
     *
     * @return SmartyResponse
     */
    public static function message(string $locale, string $type): SmartyResponse
    {
        $vars = [
            'message' => CAppUI::tr($locale),
            'type'    => $type,
        ];

        return new static('inc_message', $vars);
    }

    /**
     * Returns the template name.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Sets the template name.
     *
     * @param string $template The smarty template name
     *
     * @return $this
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Sets the template's parent directory.
     *
     * @param string $module The module name
     *
     * @return $this
     */
    public function setModule(string $module): self
    {
        $this->directory = "modules/{$module}";

        return $this;
    }

    /**
     * Return the value of the given smarty variable
     *
     * @param string $name The name of the variable
     *
     * @return $this
     */
    public function getVariables(string $name)
    {
        if (!array_key_exists($name, $this->variables)) {
            throw RouterException::smartyVariableNotFound($name);
        }

        return $this->variables[$name];
    }

    /**
     * Sets the template variables
     *
     * @param array $variables The variables to assign to smarty
     *
     * @return $this
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Render the template with smarty, and catch the display
     *
     * @return $this
     */
    public function setHTMLContent(): Response
    {
        $smarty = new CSmartyDP($this->directory);

        if (is_countable($this->variables)) {
            foreach ($this->variables as $name => $value) {
                $smarty->assign($name, $value);
            }
        }

        ob_start();
        $smarty->display("{$this->template}.tpl");
        $content = ob_get_clean();

        return parent::setContent($content);
    }
}
