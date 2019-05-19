<?php

global $classyads_config;

/**
 * I think choosing the keys by name was a bad idea.
 * Now the key and value['type'] must be kept in sync.
 * @var  $classyads_config
 */

$classyads_config = array(
    'plans' => array(
        'free' => array(
            'type' => 'free',
            'status' => 'active',
            'name' => 'Free (Online Only)',
            'amount' => 0,
            'months' => 1,
            'description' => "",
            'in_print' => false,
            'print_photo' => false,
            'multiple_photos' => false,
            'categories' => array('boats','gear','jobs'),
            'features' => array(
                'The right price!',
                'Unlimited words',
                'One Picture',
            )
        ),
        'basic' => array(
            'type' => 'basic',
            'status' => 'active',
            'name' => 'Basic',
            'amount' => 30,
            'months' => 1,
            'description' => "",
            'in_print' => true,
            'print_chars' => '250',
            'print_photo' => false,
            'multiple_photos' => false,
            'categories' => array('boats','gear'),
            'features' => array(
                'Online Posting',
                '250 char ad in next issue',
                'Unlimited words',
                'Picture for online post',
            )
        ),
        'premium' => array(
            'type' => 'premium',
            'status' => 'active',
            'name' => 'Premium',
            'amount' => 60,
            'months' => 1,
            'description' => "",
            'in_print' => true,
            'print_chars' => '250',
            'print_photo' => true,
            'multiple_photos' => true,
            'categories' => array('boats'),
            'features' => array(
                '400 character ad in next issue',
                'Picture in the magazine',
                'Online posting with multiple photos',
                'Preferred placement in search results'
            )
        ),
        'job' => array(
            'type' => 'job',
            'status' => 'active',
            'name' => 'Full Job Listing',
            'amount' => 100,
            'months' => 3,
            'description' => "",
            'in_print' => true,
            'print_chars' => '400',
            'print_photo' => true,
            'multiple_photos' => false,
            'categories' => array('jobs'),
            'features' => array(
                '400 character ad in next issue',
                'Full Online Posting',
                'Picture available',
                'Promotion in emails'
            )
        ),
        'premium2' => array(
            'type' => 'premium2',
            'status' => 'inactive',
            'name' => 'Premium',
            'amount' => 100,
            'months' => 2,
            'description' => "",
            'in_print' => true,
            'print_chars' => '250',
            'print_photo' => true,
            'multiple_photos' => true,
            'categories' => array('boats')
        ),
        'premium3' => array(
            'type' => 'premium3',
            'status' => 'inactive',
            'name' => 'Premium',
            'amount' => 60,
            'months' => 3,
            'description' => "",
            'in_print' => true,
            'print_chars' => '250',
            'print_photo' => true,
            'multiple_photos' => true,
            'categories' => array('boats')
        )
    )
);


?>