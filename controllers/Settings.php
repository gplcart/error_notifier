<?php

/**
 * @package Error Notifier
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\error_notifier\controllers;

use gplcart\core\models\Module as ModuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to Error Notifier module
 */
class Settings extends BackendController
{

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * @param ModuleModel $module
     */
    public function __construct(ModuleModel $module)
    {
        parent::__construct();

        $this->module = $module;
    }

    /**
     * Route page callback to display the module settings page
     */
    public function editSettings()
    {
        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();

        $this->setData('settings', $this->config->getFromModule('error_notifier'));

        $this->submitSettings();
        $this->prepareDataEditSettings();

        $this->outputEditSettings();
    }

    /**
     * Prepare data before passing to templates
     */
    protected function prepareDataEditSettings()
    {
        $recipients = $this->getData('settings.recipient');

        if (is_array($recipients)) {
            $this->setData('settings.recipient', implode("\n", $recipients));
        }
    }

    /**
     * Set title on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $vars = array('%name' => $this->text('Error Notifier'));
        $title = $this->text('Edit %name settings', $vars);
        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the module settings page
     */
    protected function setBreadcrumbEditSettings()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('save') && $this->validateSettings()) {
            $this->updateSettings();
        }
    }

    /**
     * Validate submitted module settings
     */
    protected function validateSettings()
    {
        $this->setSubmitted('settings');
        $this->setSubmittedBool('email');

        $this->validateElement('live_limit', 'integer');
        $this->validateElement('email_limit', 'integer');

        foreach ($this->setSubmittedArray('recipient') as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = $this->text('@field has invalid value', array('@field' => $this->text('Recipients')));
                $this->setError('recipient', $message);
                break;
            }
        }

        return !$this->hasErrors();
    }

    /**
     * Update module settings
     */
    protected function updateSettings()
    {
        $this->controlAccess('module_edit');
        $this->module->setSettings('error_notifier', $this->getSubmitted());
        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Render and output the module settings page
     */
    protected function outputEditSettings()
    {
        $this->output('error_notifier|settings');
    }

}
