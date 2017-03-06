#!/usr/bin/env php
<?php

/**
 * Script to create UFW rules to allow VPN connections
 *
 * To-do items
 *
 * Validate output of getVpnIps, report an error if necessary
 * Allow the 443 port to be changed or removed using a console option
 * Add shell comment explaining what the script does
 * Add shell comment about when it was generated
 * Add shell comment how many rules have been generated
 */

use UfwVpn\Diff;

$root = __DIR__;
require_once $root . '/src/bootstrap.php';

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
    if (!is_array($allow))
    {
        throw new \Exception(
            sprintf("Could not fetch IP list from `%s`", $vpnAddress)
        );
    }

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

/**
 * Returns an array of "allow" commands for a given set of IP addresses
 *
 * @param array $ips
 * @return array
 */
function generateAllowCommands($ips)
{
    return generateCommands($ips);
}

/**
 * Returns an array of "delete" commands for a given set of IP addresses
 *
 * @param array $ips
 * @return array
 */
function generateDeleteCommands($ips)
{
    return generateCommands($ips, "delete");
}

/**
 * Returns the smallest number of commands to bring the rules up to date
 *
 * @return array
 */
function generateDiffCommands($newIps, $oldIps)
{
    $diff = new Diff();
    $changes = $diff->compare($oldIps, $newIps);
    $allowCommands = generateAllowCommands($changes['add']);
    $deleteCommands = generateDeleteCommands($changes['remove']);

    return array_merge($allowCommands, $deleteCommands);
}

function processRequest($vpnAddress, $command)
{
    $commands = null;
    switch ($command)
    {
        case 'add':
            $commands = generateAllowCommands(getAllowedIpList($vpnAddress));
            break;
        case 'delete':
            $commands = generateDeleteCommands(getAllowedIpList($vpnAddress));
            break;
        case 'diff':
            $firewall = new \UfwVpn\Firewall();
            $oldIps = $firewall->getConfiguration();
            $commands = generateDiffCommands(getAllowedIpList($vpnAddress), $oldIps);
        default:
            echo "Not a valid command\n";
    }

    if ($commands)
    {
        echo "#!/bin/bash\n\n";
        echo implode("\n", $commands) . "\n";
    }
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
    try
    {
        processRequest($vpnAddress, $command);
    }
    catch (\Exception $e)
    {
        echo sprintf(
            "Error: %s\n",
            $e->getMessage()
        );
    }
}
else
{
    printSyntax();
}
