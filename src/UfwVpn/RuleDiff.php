<?php

namespace UfwVpn;

class RuleDiff
{
    protected $firewall;
    protected $vpn;
    protected $diff;

    public function __construct(Diff $diff, Vpn $vpn, Firewall $firewall)
    {
        $this->diff = $diff;
        $this->vpn = $vpn;
        $this->firewall = $firewall;
    }

    public function getRuleDiff($newPort = 443)
    {
        $oldAddresses = $this->getFirewall()->getConfiguration();
        $newAddresses = $this->getVpn()->getIpAddresses();
        $changes = $this->getDiff()->compare(
            $oldAddresses,
            $this->addPort($newAddresses, $newPort)
        );

        return $changes;
    }

    protected function addPort(array $ips, $port)
    {
        return array_map(
            function($ip) use ($port) {
                return $ip . ':' . $port;
            },
            $ips
        );
    }

    protected function getFirewall()
    {
        return $this->firewall;
    }

    protected function getVpn()
    {
        return $this->vpn;
    }

    protected function getDiff()
    {
        return $this->diff;
    }
}
