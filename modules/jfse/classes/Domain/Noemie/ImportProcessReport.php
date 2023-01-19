<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use Ox\Core\CAppUI;
use Ox\Core\Chronometer;

/**
 *
 */
final class ImportProcessReport
{
    public const IMPORT_TYPE_NOEMIE           = 'noemie';
    public const IMPORT_TYPE_ACKNOWLEDGEMENTS = 'acknowledgements';

    /** @var Chronometer */
    protected $timer;

    /** @var bool */
    protected $fatal_error = false;

    /** @var int */
    protected $success_counter;

    /** @var int */
    protected $errors_counter;

    /** @var string */
    protected $import_type;

    /** @var string */
    protected $message;

    /**
     * @param string $import_type
     */
    public function __construct(string $import_type)
    {
        if (!in_array($import_type, [self::IMPORT_TYPE_ACKNOWLEDGEMENTS, self::IMPORT_TYPE_NOEMIE])) {
            $import_type = self::IMPORT_TYPE_NOEMIE;
        }

        $this->import_type     = $import_type;
        $this->timer           = new Chronometer();
        $this->success_counter = 0;
        $this->errors_counter  = 0;
    }

    /**
     * @return void
     */
    public function startTimer(): void
    {
        $this->timer->start();
    }

    /**
     * @return void
     */
    public function stopTimer(): void
    {
        $this->timer->stop();
    }

    /**
     * @return int
     */
    public function getProcessDuration(): int
    {
        return (int)$this->timer->total;
    }

    /**
     * @param string $error
     * @param mixed  $args
     *
     * @return void
     */
    public function setFatalError(string $error, $args = null): void
    {
        $this->fatal_error = true;
        $this->setMessage($error, $args);
    }

    /**
     * @return void
     */
    public function incrementSuccessCounter(): void
    {
        $this->success_counter++;
    }

    /**
     * @return int
     */
    public function getSuccessCounter(): int
    {
        return $this->success_counter;
    }

    /**
     * @return void
     */
    public function incrementErrorsCounter(): void
    {
        $this->success_counter++;
    }

    /**
     * @return int
     */
    public function getErrorsCounter(): int
    {
        return $this->errors_counter;
    }

    /**
     * @param string $message
     * @param mixed  $args
     *
     * @return void
     */
    public function setMessage(string $message, $args = null): void
    {
        $this->message = CAppUI::tr($message, $args);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the report in string format
     *
     * @return string
     */
    public function getFinalReport(): string
    {
        $report = '';

        if ($this->timer->total) {
            $report = CAppUI::tr('NoemieTaskService-message-duration', $this->timer->total) . "\n";
        }

        if ($this->fatal_error) {
            $report .= CAppUI::tr('common-Error') . ': ' . CAppUI::tr($this->message);
        } else {
            if ($this->success_counter) {
                $report .= CAppUI::tr("NoemieTaskService-title-{$this->import_type}") . ' '
                    . CAppUI::tr('NoemieTaskService-message-success', $this->success_counter);
            }

            if ($this->errors_counter) {
                if ($this->success_counter) {
                    $report .= "\n";
                }

                $report .= CAppUI::tr("NoemieTaskService-title-{$this->import_type}") . ' '
                    . CAppUI::tr('NoemieTaskService-message-error', $this->errors_counter);
            }
            if ($this->message) {
                $report .= $report !== '' ? "\n{$this->message}" :  $this->message;
            }
        }

        return $report;
    }
}
