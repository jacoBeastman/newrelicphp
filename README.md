# newrelicphp
PHP script to check New Relic PHP Agent install and diagnose problems. 

Simply copy this to your web directory and browse to the page.

The New Relic PHP Agent is installed as a PHP extension and a separate newrelic-daemon process. This script checks to see if the extension is loaded and suggests fixes if it is not. If the extension is loaded it checks the connection to the newrelic-daemon and suggests fixes if it fails.

New Relic PHP Installation guide can be found here:
https://docs.newrelic.com/docs/agents/php-agent/installation/php-agent-installation-overview
