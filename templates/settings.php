<?php
/**
 * @package Error Notifier
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Live reporting'); ?></label>
    <div class="col-md-4">
      <select name="settings[live]" class="form-control">
        <option value="0"<?php echo empty($settings['live']) ? ' selected' : ''; ?>><?php echo $this->text('Disabled'); ?></option>
        <option value="1"<?php echo isset($settings['live']) && $settings['live'] == 1 ? ' selected' : ''; ?>><?php echo $this->text('Only current errors'); ?></option>
        <option value="2"<?php echo isset($settings['live']) && $settings['live'] == 2 ? ' selected' : ''; ?>><?php echo $this->text('All saved errors'); ?></option>
      </select>
      <div class="help-block"><?php echo $this->text('Select which PHP errors to show in live report'); ?></div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('live_limit', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Live reporting limit'); ?></label>
    <div class="col-md-4">
      <input name="settings[live_limit]" class="form-control" value="<?php echo isset($settings['live_limit']) ? $this->e($settings['live_limit']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('live_limit'); ?>
        <div class="text-muted">
          <?php echo $this->text('Max number of errors to show for live reporting'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-4 col-md-offset-2">
      <div class="checkbox">
        <label>
          <input name="settings[email]" type="checkbox"<?php echo empty($settings['email']) ? '' : ' checked'; ?>> <?php echo $this->text('Email logged errors'); ?>
        </label>
        <div class="help-block"><?php echo $this->text('If selected the specified number of last PHP errors will be sent to the specified recipients'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('email_limit', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('E-mail limit'); ?></label>
    <div class="col-md-4">
      <input name="settings[email_limit]" class="form-control" value="<?php echo isset($settings['email_limit']) ? $this->e($settings['email_limit']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('email_limit'); ?>
        <div class="text-muted">
          <?php echo $this->text('Max number of errors to send via E-mail'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('recipient', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Recipients'); ?></label>
    <div class="col-md-4">
      <textarea name="settings[recipient]" rows="4" class="form-control"><?php echo empty($settings['recipient']) ? '' : $this->e($settings['recipient']); ?></textarea>
      <div class="help-block">
        <?php echo $this->error('recipient'); ?>
        <div class="text-muted">
          <?php echo $this->text('List of recipients, one E-mail per line'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-4 col-md-offset-2">
      <div class="btn-toolbar">
        <a href="<?php echo $this->url("admin/module/list"); ?>" class="btn btn-default"><?php echo $this->text("Cancel"); ?></a>
        <button class="btn btn-default save" name="save" value="1"><?php echo $this->text("Save"); ?></button>
      </div>
    </div>
  </div>
</form>