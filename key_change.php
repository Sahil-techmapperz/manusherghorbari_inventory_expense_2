<?php

// $items = [
// [
// 'name' => 'Colgate Toothpaste',
// 'category_value' => 'Necessary Items',
// 'quantity' => 20,
// 'date_added' => '2024-03-12 12:08:22',
// ],
// // Add other items here following the same structure...
// ];

// Function to change the key of an array element
function changeArrayKey(&$array, $oldKey, $newKey) {
foreach ($array as &$item) {
if(array_key_exists($oldKey, $item)) {
$item[$newKey] = $item[$oldKey];
unset($item[$oldKey]);
}
}
unset($item); // break the reference with the last element
// return $item;
}

// Changing 'category_value' to 'category' for each item in the array
// changeArrayKey($items, 'category_value', 'category');

// Output the modified array
// print_r();
