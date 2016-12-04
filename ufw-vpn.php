<?php

/**
 * Script to create ufw rules to allow VPN connections
 *
 * To-do items
 *
 * Add "add" and "delete" modes
 * Push to a public repo
 * Issue warnings if duplicates are detected
 * Create shell script rather than just list of commands to be pasted
 * Add shell comment explaining what the script does
 * Add shell comment about when it was generated
 * Add shell comment how many rules have been generated
 */

/**
 * IP addresses to ignore
 *
 * This was originally added to filter out local addresses parsed from nslookup;
 * now I am using PHP's own DNS read feature, it is less necessary. However it
 * may be useful to add an --ignore parameter in the future, so I will keep
 * this for now.
 */
function getIgnoreIpList()
{
	return [
	];
}

/**
 * Removes a set of IP addresses from another set
 */
function filterList(array $allow, array $deny)
{
	return array_diff($allow, $deny);
}

/**
 * Effectively does an nslookup on the supplied address
 */
function getVpnIps($vpnAddress) {
    $ips = gethostbynamel($vpnAddress);

    return $ips;
}

function getAllowedIpList($vpnAddress)
{
	$allow = getVpnIps($vpnAddress);
	$deny = getIgnoreIpList();
	$ips = filterList($allow, $deny);

	return $ips;
}

function generateCommands($ips, $subCommand = '')
{
	$commands = [];
	foreach ($ips as $ip)
	{
		$command = "ufw {$subCommand} allow out to {$ip} port 443";
		$commands[] = $command;
	}

	return $commands;
}

function generateAllowCommands($ips)
{
	return generateCommands($ips);
}

function generateDeleteCommands($ips)
{
	return generateCommands($ips, "delete");
}

// @todo This needs validation
$vpnAddress = isset($argv[1]) ? $argv[1] : '';

$ips = getAllowedIpList($vpnAddress);
$commands = generateAllowCommands($ips);
#$commands = generateDeleteCommands($ips);

echo "#!/bin/bash\n\n";
echo implode("\n", $commands) . "\n";

