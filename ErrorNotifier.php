<?php

/**
 * @package Error Notifier
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\error_notifier;

use gplcart\core\Module,
    gplcart\core\Config;

/**
 * Main class for Error Notifier module
 */
class ErrorNotifier extends Module
{

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/settings/error_notifier'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\error_notifier\\controllers\\Settings', 'editSettings')
            )
        );
    }

    /**
     * Implements hook "cron"
     * @param \gplcart\core\Controller $controller
     */
    public function hookCron($controller)
    {
        $this->setEmailReport($controller);
    }

    /**
     * Implements hook "template.output"
     * @param string $template
     * @param array $data
     * @param \gplcart\core\Controller $controller
     */
    public function hookTemplate($template, array &$data, $controller)
    {
        $this->setLiveReport($template, $data, $controller);
    }

    /**
     * Sends last PHP errors via Email
     * @param \gplcart\core\Controller $controller
     * @return bool
     */
    protected function setEmailReport($controller)
    {
        $settings = $this->config->getFromModule('error_notifier');

        if (empty($settings['email']) || empty($settings['recipient'])) {
            return false;
        }

        $messages = $this->getEmailErrors($settings, $controller);

        if (empty($messages)) {
            return false;
        }

        return $this->sendEmail($settings, $messages, $controller);
    }

    /**
     * Sends an E-mail
     * @param array $settings
     * @param array $messages
     * @param \gplcart\core\Controller $controller
     */
    protected function sendEmail(array $settings, array $messages, $controller)
    {
        /* @var $mailer \gplcart\core\models\Mail */
        $mailer = $this->getModel('Mail');

        $subject = 'Last PHP errors';
        $body = implode("\r\n", $messages);
        $from = $controller->getStore('data.email.0');

        $mailer->send($settings['recipient'], $subject, $body, $from);
    }

    /**
     * Returns an array of PHP errors to send via Email
     * @param array $settings
     * @param \gplcart\core\Controller $controller
     * @return array
     */
    protected function getEmailErrors(array $settings, $controller)
    {
        if (empty($settings['email_limit'])) {
            $settings['email_limit'] = null; // Unlimited
        }

        /* @var $logger \gplcart\core\Logger */
        $logger = $this->getInstance('gplcart\\core\\Logger');
        $errors = $logger->selectErrors($settings['email_limit']);

        return $this->getFormattedErrors($errors, $controller);
    }

    /**
     * Sets live error reporting
     * @param string $template
     * @param array $data
     * @param \gplcart\core\Controller $controller
     */
    protected function setLiveReport($template, array &$data, $controller)
    {
        $allowed_template = 'layout/body';

        if (substr($template, -strlen($allowed_template)) === $allowed_template) {
            foreach ($this->getLiveErrors($controller) as $message) {
                $data['_messages']['warning'][] = $message;
            }
        }
    }

    /**
     * Prepare an array of messages
     * @param array $messages
     * @param array $settings
     * @param \gplcart\core\Controller $controller
     * @return array
     */
    protected function prepareMessages(&$messages, $settings, $controller)
    {
        if (empty($messages) || $controller->path('^admin/report/events$')) {
            return $messages = array(); // Suppress errors on admin/report/events page
        }

        $remaining = count($messages) - $settings['live_limit'];

        if (!empty($settings['live_limit']) && $remaining > 0) {
            $messages = array_slice($messages, 0, $settings['live_limit']);
            $messages[] = $controller->text('...and @remaining more', array('@remaining' => $remaining));
        }

        if ($controller->access('report_events')) {

            $message = $controller->text('<a href="@url">see saved errors</a>', array('@url' => $controller->url('admin/report/events', array('type' => 'php_error'))));

            if ($settings['live'] == 2) {
                $vars = array('@url' => $controller->url('admin/report/events', array('clear' => true, 'target' => $controller->path())));
                $message .= ' | ' . $controller->text('<a href="@url">clear all saved errors</a>', $vars);
            }

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Returns an array of PHP errors for live reporting
     * @param \gplcart\core\Controller $controller
     * @return array
     */
    protected function getLiveErrors($controller)
    {
        $settings = $this->config->getFromModule('error_notifier');

        if (empty($settings['live'])) {
            return array();
        }

        /* @var $logger \gplcart\core\Logger */
        $logger = $this->getInstance('gplcart\\core\\Logger');

        $errors = array();
        if ($settings['live'] == 1) {
            $errors = $logger->getErrors(false);
        } else if ($settings['live'] == 2) {
            $errors = $logger->selectErrors();
        }

        $messages = $this->getFormattedErrors($errors, $controller);
        $this->prepareMessages($messages, $settings, $controller);

        return $messages;
    }

    /**
     * Returns an array of formatted error messages
     * @param array $errors
     * @param \gplcart\core\Controller $controller
     * @return array
     */
    protected function getFormattedErrors(array $errors, $controller)
    {
        $messages = array();
        foreach ($errors as $error) {

            $vars = array(
                '@line' => $error['line'],
                '@message' => $error['message'],
                '@file' => gplcart_path_relative($error['file'])
            );

            $messages[] = $controller->text('@message on line @line in @file', $vars);
        }

        return $messages;
    }

}
