<?php

use PHPUnit\Framework\TestCase;

class DiffTest extends TestCase
{
    public function testAddOneItem()
    {
        $diff = new \UfwVpn\Diff();
        $changes = $diff->compare([], ['1.2.3.4']);
        $this->assertEquals(
            ['add' => ['1.2.3.4', ], 'remove' => [], ],
            $changes
        );
    }

    public function testRemoveOneItem()
    {
        $diff = new \UfwVpn\Diff();
        $changes = $diff->compare(['1.2.3.4'], []);
        $this->assertEquals(
            ['add' => [], 'remove' => ['1.2.3.4', ], ],
            $changes
        );
    }

    public function testChangeAllItems()
    {
        $diff = new \UfwVpn\Diff();
        $changes = $diff->compare(['1.2.3.4'], ['1.2.3.5']);
        $this->assertEquals(
            ['add' => ['1.2.3.5'], 'remove' => ['1.2.3.4', ], ],
            $changes
        );
    }

    public function testChangeSomeItems()
    {
        $diff = new \UfwVpn\Diff();
        $changes = $diff->compare(
            ['1.2.3.4', '1.2.3.5', '1.2.3.6',], // Delete one
            ['1.2.3.5', '1.2.3.6', '1.2.3.7',]  // ... and add one
        );
        $this->assertEquals(
            ['add' => ['1.2.3.7'], 'remove' => ['1.2.3.4', ], ],
            $changes
        );
    }
}
