<?php

<<<<<<< Updated upstream
placeholder
=======
///////////////////////////
//New Relic PHP Agent Configuration Test v1.0
//
//This runs through a series of tests to ensure the New Relic PHP extension is loaded and can connect to the daemon
///////////////////////////
//Links
$link_mod_php="https://docs.newrelic.com/docs/agents/php-agent/troubleshooting/threaded-apache-worker-mpms";
$link_requirements="https://docs.newrelic.com/docs/agents/php-agent/getting-started/php-agent-compatibility-requirements";
$link_download="http://download.newrelic.com/php_agent/release/";
//Resource strings
$res_unsupported="<font color=\"red\">WARNING - This verions of PHP, %s, is not supported by our latest agent version. You can find the supported versions <a href=$link_requirements>here</a>. <br><br>";

$res_installpath="<font color=\"red\">WARNING - No newrelic.ini loaded. <br>New Relic may not be installed for this PHP installation. <br><br> Path to the PHP bin directory is %s <br> Rerun installer with environment variable NR_INSTALL_PATH set to %s</font><br>";

$res_soexists="The file %s/newrelic.so exists. If a restart of your PHP handler does not resolve the issue this must be for the wrong version of PHP. Reinstall the agent or copy the correct version of the newrelic.so to this location.<br><br>";

$res_somissing="<font color=\"red\">WARNING - %s/newrelic.so does not exist. Reinstall the agent or copy the correct version of the newrelic.so to this location.<br><br>";

$res_manualinstall="The latest installer is located <a href=$link_download>here</a>. <br> Download and copy the .so file from agent/x64/newrelic-%s%s.so to %s/newrelic.so </font><br>";

$res_connectsuccess="Daemon connection successful. Application should be reporting. Check /var/log/newrelic/php_agent.log and /var/log/newrelic/newrelic-daemon.log for more details if this application is still not reporting.";

$res_apachempm="<font color=\"red\">WARNING - Currently using Apache mod_php with a threaded build of Apache. New Relic only supports mod_php when using Apache's mpm_prefork. More information can be found <a href=$link_mod_php>here</a>. <br>";
///////////////////////////


echo "<h1>This is a New Relic PHP Agent Configuration Test </h1><br><br><br>";

$phpversion = phpversion();
echo "<h3>This application is running PHP version $phpversion. <br><br>";


//First let's see if the extension is loaded. We can do a few follow up checks by grabbing php values, too
//But if it is loaded it is usually an issue of the agent/daemon socket

echo "<h3>Checking for New Relic PHP extension.<br>";

if (extension_loaded('newrelic'))
{
	$appname = ini_get("newrelic.appname");
	echo "<h3>New Relic PHP extension is loaded. <br> The reporting New Relic appname is <i> $appname</i>. <br><br>";
	
	$threadsafe = get_sapi();

	daemon_connect($res_connectsuccess);


}
else
{
	//Ok, extension is not loaded so we have a few things to check
	echo "<font color=\"red\"><h3>WARNING - New Relic PHP extension is NOT loaded!</font><br><br>";
	
	//Check the sapi
	$threadsafe = get_sapi($res_apachempm);
	$zts = '';
	if($threadsafe == 'enabled')
	{
		$zts = '-zts';
	}

	//Lets check if the newrelic.ini is showing up.
	$add_ini_files = php_ini_scanned_files();
	if(preg_match("/.*newrelic.ini/", $add_ini_files, $result))	
	{
		echo "The $result[0] is loaded. This is likely a missing or incorrect version of the newrelic.so <br><br>";
		$extbuild = get_extension_build();
		if($extbuild=="Unsupported")
		{
			echo sprintf($res_unsupported, $phpversion);
		}
		$phpext = ini_get("extension_dir");
		echo "The extension directory is $phpext. Checking this location for the newrelic.so <br><br>";
		if(file_exists("$phpext/newrelic.so"))
		{
			echo sprintf($res_soexists, $phpext);
			echo sprintf($res_manualinstall, $extbuild, $zts, $phpext);
		}
		else
		{
			echo sprintf($res_somissing, $phpext);
			echo sprintf($res_manualinstall, $extbuild, $zts, $phpext);
		}
	}
	else
	{
		$php_bindir = PHP_BINDIR;
		echo sprintf($res_installpath, $php_bindir, $php_bindir);
	}
}



//Gets SAPI and checks for mod_php/threaded issues
function get_sapi($res_apachempm)
{
	$sapi = php_sapi_name();
	echo "The Server API is $sapi. <br>";
	ob_start();
	phpinfo(INFO_GENERAL);
	preg_match('/Thread\s*Safety\s*.*/', ob_get_clean(), $result);
	preg_match('/[a-z]{2,3}abled/', $result[0], $result);
	$threadsafe = $result[0];
	echo "Thread Safety is $threadsafe. <br>";
	
	if(($sapi=="apache2handler")&&($threadsafe == 'enabled'))
	{
		echo $res_apachempm;
	}
	echo "<br>";
	
	return $threadsafe;
}

//Gets PHP version and returns extension build number for it
function get_extension_build()
{
	$phpversion = phpversion();
	$ext_build = "Unsupported";
	
	switch($phpversion)
	{
    		case 5.3:  
			$ext_build="20090626" ;
			break;
		case 5.4:
			$ext_build="20100525" ;
			break;
		case 5.5:
			$ext_build="20121212" ;
			break;
		case 5.6:
			$ext_build="20131226" ;
			break;
		case 7.0:
			$ext_build="20151012" ;
			break;
		case 7.1:
			$ext_build="20160303" ;
			break;
		case 7.2:
			$ext_build="20170718" ;
	}
	
	return $ext_build;	

}

//Tests connection to the daemon. 
function daemon_connect($res_connectsuccess)
{

	$daemonport = ini_get("newrelic.daemon.port");
        echo "Testing connection to daemon. Port/socket is set to $daemonport <br>";

	
	if(is_numeric($daemonport))
	{
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$result = socket_connect($socket, "127.0.0.1", $daemonport);
	}
	else
	{	
		//Abtract sockets start with @ but needs to start with \0 for connection
	        if(substr($daemonport, 0, 1) == "@")
		{
			$daemonport = "\0" . substr($daemonport, 1);
		}
       		$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
		$result = socket_connect($socket, $daemonport);
	}

	if($result)
        {
                //echo "Daemon connection successful. Application should be reporting. Check /var/log/newrelic/php_agent.log and /var/log/newrelic/newrelic-daemon.log for more details if this application is still not reporting.";
		echo $res_connectsuccess;
        }
        else
        {
                $socketcode = socket_last_error();
                $socketerror = socket_strerror($socketcode);
                echo "<font color=\"red\">WARNING - Daemon connection failed. Error code $socketcode with error message \"$socketerror\"</font><br>";
		if(($socketcode==111)&&($daemonport=="/tmp/.newrelic.sock")){
			echo "Connection refused to UDS socket file. Recommend changing newrelic.daemon.port to \"@newrelic-daemon\".<br>";
		}	
        }
}


?>
>>>>>>> Stashed changes
