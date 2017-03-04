<?php

namespace UfwVpn;

class Diff
{
    /**
     * Method to determine what changes are required to turn list A into list B
     *
     * @param array $listA The "old" list
     * @param array $listB The "new" list
     */
    public function compare(array $listA, array $listB)
    {
        $itemsToAdd = array_diff($listB, $listA);
        $itemsToRemove = array_diff($listA, $listB);

        return [
            'add' => array_values($itemsToAdd),
            'remove' => array_values($itemsToRemove),
        ];
    }
}
