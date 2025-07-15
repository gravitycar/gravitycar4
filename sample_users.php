<?php
namespace Gravitycar;
require_once 'src/GCFoundation.php';
use Gravitycar\src\GCFoundation;
use Gravitycar\Gravitons\Users\Users as Users;
$app = GCFoundation::getInstance();

// Get Users field definitions
//$user = new \Gravitycar\Gravitons\Users\Users();
$user = new Users();
$fields = $user->getFields();

// Create array to store dummy users data
$dummyUsers = [];

// Sample data arrays for generating realistic dummy data
$firstNames = ['Hanibal', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Lisa', 'William', 'Jennifer'];
$lastNames = ['Smith', 'Johnsen', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
$domains = ['example.com', 'test.org', 'demo.net', 'sample.co', 'placeholder.io'];

// Generate 10 dummy users
for ($i = 0; $i < 10; $i++) {
    $userData = [];
    $firstName = $firstNames[$i];
    $lastName = $lastNames[$i];

    foreach ($fields as $fieldName => $field) {
        switch ($fieldName) {
            case 'id':
                // Will be auto-generated during create()
                break;
            case 'first_name':
            case 'firstName':
                $userData[$fieldName] = $firstName;
                break;
            case 'last_name':
            case 'lastName':
                $userData[$fieldName] = $lastName;
                break;
            case 'username':
                $userData[$fieldName] = strtolower($firstName . '.' . $lastName);
                break;
            case 'password':
                $userData[$fieldName] = 'password123'; // In real scenarios, this should be hashed
                break;
            case 'is_admin':
                $userData[$fieldName] = false;
                break;
            case 'deleted':
                $userData[$fieldName] = false; // Default to not deleted
                break;
            default:
                // For other fields, set a generic value or leave empty
                break;
        }
    }

    $dummyUsers[] = $userData;
}

// Create the users in the database
foreach ($dummyUsers as $userData) {
    $user = new Users();
    $user->populateFromRequest($userData);
    $userId = $user->create();
    echo "Created user with ID: {$userId}\n";
}