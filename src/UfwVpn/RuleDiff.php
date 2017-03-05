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

    public function getRuleDiff()
    {
        $oldAddresses = $this->getFirewall()->getConfiguration();
        $newAddresses = $this->getVpn()->getIpAddresses();
        $changes = $this->getDiff()->compare($oldAddresses, $newAddresses);

        return $changes;
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
