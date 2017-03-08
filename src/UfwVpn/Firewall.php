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

    /**
     * Gets firewall config, either for all rules or rules for a specific port
     *
     * @param integer $port
     * @return array
     */
    public function getConfiguration($port = null)
    {
        $output = $this->runUfwCommand();
        $this->checkFirewallStatus($output);
        $rules = $this->getFirewallAllowRules($output, $port);

        return $rules;
    }

    /**
     * Ensures the firewall status from the command output is active
     *
     * @todo Change both exceptions to specific ones
     *
     * @param array $output
     * @return string
     */
    protected function checkFirewallStatus(array $output)
    {
        // Ensure we don't have an error
        if (strpos($output[0], 'ERROR:') === 0)
        {
            throw new \Exception(
                $output[0]
            );
        }

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
     * @param integer|null $port
     * @return array
     */
    protected function getFirewallAllowRules(array $output, $port)
    {
        $matches = null;
        preg_match_all($this->getRuleRegex(), implode("\n", $output), $matches);

        // Collate the rules in an associative array
        $rules = [];
        foreach ($matches[1] as $ord => $ip)
        {
            $thisPort = (int) $matches[2][$ord];
            if (!$port || $port === $thisPort)
            {
                $rules[] = $ip;
            }
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

    /**
     * Get the status of the "ufw" firewall command
     *
     * I'm redirecting stderr here to capture ERROR output if necessary
     *
     * @return array
     */
    protected function runUfwCommand()
    {
        $output = $return = null;
        exec($this->command . ' status 2>&1', $output, $return);

        return $output;
    }
}
