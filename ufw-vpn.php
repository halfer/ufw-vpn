<?php

/**
 * Script to create ufw rules to allow VPN connections
 *
 * To-do items
 *
 * Add "add" and "delete" modes
 * Add to a repo
 * Push to a public repo
 * Read from `nslookup` instead of a file
 * Issue warnings if duplicates are detected
 * Create shell script rather than just list of commands to be pasted
 */

function getIgnoreIpList()
{
	return [
		'127.0.1.1',
	];
}

/**
 * Finds IP addresses from `nslookup` output
 *
 * @param string $nslookupOutput
 * @return array List of IP addresses in string format
 */
function getIpList($nslookupOutput)
{
	$lines = explode("\n", $nslookupOutput);
	$prefix = "Address:";

	$ips = [];

	foreach ($lines as $line)
	{
		if (substr($line, 0, strlen($prefix)) == $prefix)
		{
			$matches = [];
			$number = '\d+';
			$dot = '\.';
			preg_match("#{$number}{$dot}{$number}{$dot}{$number}{$dot}{$number}#", $line, $matches);
			if ($matches)
			{
				$ips[] = $matches[0];
			}
		}
	}

	return $ips;
}

function filterList(array $allow, array $deny)
{
	return array_diff($allow, $deny);
}

function getAllowedIpList()
{
	$data = file_get_contents('earth-vpn-ips.log');
	$allow = getIpList($data);
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

$ips = getAllowedIpList();
$commands = generateAllowCommands($ips);
#$commands = generateDeleteCommands($ips);

echo "#!/bin/bash\n\n";
echo implode("\n", $commands) . "\n";

