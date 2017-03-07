<?php

use UfwVpn\Firewall;

class FirewallTest extends \PHPUnit_Framework_TestCase
{
    public function testParsesUfwOutputCorrectly()
    {
        $firewall = $this->createFirewallMock();
        $firewall->
            shouldReceive('runUfwCommand')->
            andReturn($this->getExampleFirewallConfig());
        $rules = $firewall->getConfiguration();
        $this->assertEquals(
            ['1.2.3.4:443', '2.3.4.5:443', ],
            $rules
        );
    }

    /**
     * Checks that the status test works OK
     *
     * @expectedException \Exception
     */
    public function testThrowsExceptionWhenFirewallDisabled()
    {
        $firewall = $this->createFirewallMock();
        $firewall->
            shouldReceive('runUfwCommand')->
            andReturn($this->getDisabledFirewallConfig());
        $firewall->getConfiguration();
    }

    /**
     * Checks that the error detection works OK
     *
     * @expectedException \Exception
     */
    public function testThrowsExceptionWhenErrorShown()
    {
        $firewall = $this->createFirewallMock();
        $firewall->
            shouldReceive('runUfwCommand')->
            andReturn($this->getErrorFirewallConfig());
        $firewall->getConfiguration();
    }

    /**
     * Returns a mocked firewall instance
     *
     * @return \Mockery\Mock|Firewall
     */
    protected function createFirewallMock()
    {
        return Mockery::Mock(Firewall::class)->
            makePartial()->
            shouldAllowMockingProtectedMethods();
    }

    /**
     * A set of rules that might be found in the firewall, as UFW text output
     *
     * This contains an example non-VPN rule as well, we'll need to think how to filter out
     * these so they don't become additions or deletions.
     *
     * @return array
     */
    protected function getExampleFirewallConfig()
    {
$config = <<<CONFIG
Status: active

To                         Action      From
--                         ------      ----
192.168.1.1                ALLOW OUT   Anywhere
Anywhere                   ALLOW OUT   10.4.0.0/16 on tun0
1.2.3.4 443                ALLOW OUT   Anywhere
2.3.4.5 443                ALLOW OUT   Anywhere
CONFIG;

        return explode("\n", $config);
    }

    protected function getDisabledFirewallConfig()
    {
$config = <<<CONFIG
Status: inactive
CONFIG;

        return explode("\n", $config);
    }

    protected function getErrorFirewallConfig()
    {
$config = <<<CONFIG
ERROR: In order to run this script, you need to be root
CONFIG;

        return explode("\n", $config);

    }
}
