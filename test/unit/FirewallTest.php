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
            [
                ['ip' => '1.2.3.4', 'port' => '443', ],
                ['ip' => '2.3.4.5', 'port' => '443', ],
            ],
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
     * Returns a mocked firewall instance
     *
     * @return \Mockery\Mock|Firewall
     */
    protected function createFirewallMock()
    {
        return Mockery::Mock(Firewall::class)->makePartial();
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
}
