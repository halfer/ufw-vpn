<?php

use PHPUnit\Framework\TestCase;

class RuleDiffTest extends TestCase
{
    public function testRuleDifferences()
    {
        $diff = new \UfwVpn\Diff();
        $firewall = Mockery::mock(\UfwVpn\Firewall::class);
        $firewall->
            shouldReceive('getConfiguration')->
            andReturn(['1.2.3.4', '1.2.3.5', ]);
        $vpn = Mockery::mock(\UfwVpn\Vpn::class);
        $vpn->shouldReceive('getIpAddresses')->andReturn([
            '1.2.3.4', '1.2.3.6',
        ]);

        $ruleDiff = new UfwVpn\RuleDiff($diff, $vpn, $firewall);
        $changes = $ruleDiff->getRuleDiff();

        $this->assertEquals(
            ['add' => ['1.2.3.6', ], 'remove' => ['1.2.3.5', ], ],
            $changes
        );
    }
}
