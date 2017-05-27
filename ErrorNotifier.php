<?php

/**
 * @package Error Notifier
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\error_notifier;

use gplcart\core\Module;

/**
 * Main class for Error Notifier module
 */
class ErrorNotifier extends Module
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
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
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    public function hookCron($controller)
    {
        $this->setEmailReport($controller);
    }

    /**
     * Implements hook "template.output"
     * @param string $html
     */
    public function hookTemplateOutput(&$html)
    {
        $this->setLiveReport($html);
    }

    /**
     * Sends last PHP errors via Email
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    protected function setEmailReport($controller)
    {
        $settings = $this->config->module('error_notifier');
        if (!empty($settings['email']) && !empty($settings['recipient'])) {
            $messages = $this->getEmailErrorMessages($settings);
            if (!empty($messages)) {
                $this->sendEmail($settings, $messages, $controller);
            }
        }
    }

    /**
     * Sends an E-mail
     * @param array $settings
     * @param array $messages
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    protected function sendEmail(array $settings, array $messages, $controller)
    {
        /* @var $mailer \gplcart\core\models\Mail */
        $mailer = $this->getInstance('gplcart\\core\\models\Mail');

        $subject = 'Last PHP errors';
        $body = implode("\r\n", $messages);
        $from = $controller->getStore('data.email.0');

        $mailer->send($settings['recipient'], $subject, $body, $from);
    }

    /**
     * Returns an array of PHP errors to send via Email
     * @param array $settings
     * @return array
     */
    protected function getEmailErrorMessages(array $settings)
    {
        if (empty($settings['email_limit'])) {
            $settings['email_limit'] = null; // Unlimited
        }

        /* @var $logger \gplcart\core\Logger */
        $logger = $this->getInstance('gplcart\\core\\Logger');
        $errors = $logger->selectPhpErrors($settings['email_limit']);

        return $this->getFormattedErrorMessages($errors);
    }

    /**
     * Sets live error reporting
     * @param string $html
     */
    protected function setLiveReport(&$html)
    {
        $settings = $this->config->module('error_notifier');

        if (empty($settings['live']) || empty($settings['live_limit'])) {
            return null;
        }

        $messages = $this->getLiveErrorMessages($settings);

        if (empty($messages)) {
            return null;
        }

        $remaining = count($messages) - $settings['live_limit'];

        if ($remaining > 0) {
            $messages = array_slice($messages, 0, $settings['live_limit']);
            $messages[] = "...and $remaining more";
        }

        $encoded = json_encode(implode("\n", $messages));
        $html = substr_replace($html, "<script>alert($encoded);</script>", strpos($html, '</body>'), 0);
    }

    /**
     * Returns an array of PHP errors for live reporting
     * @param array $settings
     * @return array
     */
    protected function getLiveErrorMessages(array $settings)
    {
        /* @var $logger \gplcart\core\Logger */
        $logger = $this->getInstance('gplcart\\core\\Logger');

        $errors = array();
        if ($settings['live'] == 1) {
            $errors = $logger->getPhpErrors(false);
        } else if ($settings['live'] == 2) {
            $errors = $logger->selectPhpErrors();
        }

        return $this->getFormattedErrorMessages($errors);
    }

    /**
     * Returns an array of formatted error messages
     * @param array $errors
     * @return array
     */
    protected function getFormattedErrorMessages(array $errors)
    {
        $messages = array();
        foreach ($errors as $error) {
            $error['file'] = gplcart_relative_path($error['file']);
            $messages[] = "{$error['message']} on line {$error['line']} in {$error['file']}";
        }
        return $messages;
    }

}
