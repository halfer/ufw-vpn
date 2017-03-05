<?php

/**
 * A class to represent a firewall instance
 *
 * @todo For items that need to be added/deleted, we need to check with DNS to see if they
 * belong to the VPN or if they are something else. Or if we can use rule naming that would
 * be great, not sure if that is possible in UFW though, only the UI? Or is there a func/lib
 * that will ignore local IP rules, which cannot be VPN by definition?
 */

namespace UfwVpn;

class Firewall
{
    const STATUS_ACTIVE = 'active';

    protected $command;

    public function __construct($command = 'ufw')
    {
        $this->command = $command;
    }

    public function getConfiguration()
    {
        $output = $this->runUfwCommand();
        $this->checkFirewallStatus($output);
        $rules = $this->getFirewallAllowRules($output);

        return $rules;
    }

    /**
     * Ensures the firewall status from the command output is active
     *
     * @todo Change the exception to a specific one
     *
     * @param array $output
     * @return string
     */
    protected function checkFirewallStatus(array $output)
    {
        $matches = null;
        preg_match('#Status: (.+)#', $output[0], $matches);
        $status = $matches ? $matches[1] : null;
        if ($status !== self::STATUS_ACTIVE)
        {
            throw new \Exception(
                sprintf("Firewall status is not active, seems to be `%s` instead", $status)
            );
        }
    }

    /**
     * Filters the command output for IP "ALLOW OUT   Anywhere" rules
     *
     * An empty set of firewall rules is valid, that should just return an empty array.
     *
     * @todo Change the exception to a specific one
     *
     * @param array $output
     * @return array
     */
    protected function getFirewallAllowRules(array $output)
    {
        $matches = null;
        preg_match_all($this->getRuleRegex(), implode("\n", $output), $matches);

        // Collate the rules in an associative array
        $rules = [];
        foreach ($matches[1] as $ord => $ip)
        {
            $port = $matches[2][$ord];
            $rules[] = [
                'ip' => $ip, 'port' => $port,
            ];
        }

        return $rules;
    }

    protected function getRuleRegex()
    {
        // Some simple regular expression subsections
        $digits = '\d+';
        $dot = '\.';
        $ipAddress = "{$digits}{$dot}{$digits}{$dot}{$digits}{$dot}{$digits}";
        $port = $digits;
        $spaces = '\\W+';
        $allow = "ALLOW OUT";
        $anywhere = "Anywhere";

        // Our primary regex
        $regex = "#({$ipAddress}) ({$port}){$spaces}{$allow}{$spaces}{$anywhere}#";

        return $regex;
    }

    public function runUfwCommand()
    {
        $output = $return = null;
        exec($this->command, $output, $return);

        return $output;
    }
}