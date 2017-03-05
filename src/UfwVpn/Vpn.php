<?php

namespace UfwVpn;

class Vpn
{
    protected $vpnAddress;

    public function __construct($vpnAddress)
    {
        $this->vpnAddress = $vpnAddress;
    }

    public function getIpAddresses()
    {
        $ips = gethostbynamel($this->vpnAddress);

        return $ips;
    }

    protected function getVpnAddress()
    {
        return $this->vpnAddress;
    }
}