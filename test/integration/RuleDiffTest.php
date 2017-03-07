<?php

class RuleDiffTest extends \PHPUnit_Framework_TestCase
{
    public function testRuleDifferences()
    {
        $diff = new \UfwVpn\Diff();
        $firewall = Mockery::mock(\UfwVpn\Firewall::class)->makePartial();
        $firewall->shouldReceive('getConfiguration')->andReturn([
            '1.2.3.4:443', '1.2.3.5:443',
        ]);
        $vpn = Mockery::mock(\UfwVpn\Vpn::class)->makePartial();
        $vpn->shouldReceive('getIpAddresses')->andReturn([
            '1.2.3.4', '1.2.3.6',
        ]);

        $ruleDiff = new UfwVpn\RuleDiff($diff, $vpn, $firewall);
        $changes = $ruleDiff->getRuleDiff();

        $this->assertEquals(
            ['add' => ['1.2.3.6:443', ], 'remove' => ['1.2.3.5:443', ], ],
            $changes
        );
    }
}
