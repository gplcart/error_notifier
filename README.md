[![Build Status](https://scrutinizer-ci.com/g/gplcart/error_notifier/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/error_notifier/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/error_notifier/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/error_notifier/?branch=master)

## ABANDONED!
Error Notifier is a [GPL Cart](https://github.com/gplcart/gplcart) module that helps to better control PHP errors on your site and fix them as soon as possible.

**Features:**

1. Track and alert current/saved errors
2. Email the latest errors to a list of recipients


**Installation**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/error_notifier`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Adjust settings at `admin/module/settings/error_notifier`