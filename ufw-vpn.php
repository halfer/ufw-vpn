#!/usr/bin/env php
<?php

/**
 * Script to create UFW rules to allow VPN connections
 *
 * To-do items
 *
 * Push to a public repo
 * Add port restrictions to only allow OpenVPN through these rules?
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
	return [];
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

function printSyntax()
{
echo <<<SYNTAX
This script is used to generate UFW firewall setup/teardown scripts to
allow VPN connections through.

Syntax:

  ufw-vpn.php <vpn-url> <add|delete>

Where:

  vpn-url is the address of your VPN, e.g. uk.myexamplevpn.net

  "add" specifies that an add script is to be generated
  "delete" specifies that a rule delete script is to be generated

Example:

  ufw-vpn.php uk.myexamplevpn.net add > add-rules.sh
  chmod u+x add-rules.sh
  sudo ./add-rules.sh

SYNTAX;
}

$vpnAddress = isset($argv[1]) ? $argv[1] : '';
$command = isset($argv[2]) ? $argv[2] : '';

if ($vpnAddress && $command)
{
    $ips = getAllowedIpList($vpnAddress);

    $commands = null;
    switch ($command)
    {
        case 'add':
            $commands = generateAllowCommands($ips);
            break;
        case 'delete':
            $commands = generateDeleteCommands($ips);
            break;
        default:
            echo "Not a valid command\n";
    }

    if ($commands)
    {
        echo "#!/bin/bash\n\n";
        echo implode("\n", $commands) . "\n";
    }
}
else
{
    printSyntax();
}

