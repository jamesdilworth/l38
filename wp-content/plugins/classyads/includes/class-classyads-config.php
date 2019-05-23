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
            'duration_options' => array(
                array(
                    'months' => 1,
                    'amount' => 30
                ),
            ),
            'amount' => 0,
            'months' => 1,
            'description' => "",
            'in_print' => false,
            'print_photo' => false,
            'multiple_photos' => false,
            'categories' => array('boats','gear'),
            'features' => array(
                'The right price!',
                'Items under $1000',
                'One Picture',
            )
        ),
        'basic' => array(
            'type' => 'basic',
            'status' => 'active',
            'name' => 'Basic',
            'duration_options' => array(
                array(
                    'months' => 1,
                    'amount' => 30
                ),
                array(
                    'months' => 3,
                    'amount' => 60
                ),
                array(
                    'months' => 6,
                    'amount' => 100
                )
            ),
            'amount' => 30,
            'months' => 1,
            'description' => "",
            'in_print' => true,
            'print_chars' => '250',
            'print_photo' => false,
            'multiple_photos' => false,
            'categories' => array('boats','gear'),
            'features' => array(
                'Online & Magazine Posting',
                '250 Char Magazine Ad',
                'Unlimited Words Online',
                'Picture Online',
            )
        ),
        'premium' => array(
            'type' => 'premium',
            'status' => 'active',
            'name' => 'Premium',
            'duration_options' => array(
                array(
                    'months' => 1,
                    'amount' => 60
                ),
                array(
                    'months' => 3,
                    'amount' => 100
                ),
                array(
                    'months' => 6,
                    'amount' => 150
                )
            ),
            'amount' => 60,
            'months' => 1,
            'description' => "",
            'in_print' => true,
            'print_chars' => '250',
            'print_photo' => true,
            'multiple_photos' => true,
            'categories' => array('boats'),
            'features' => array(
                '400 Character Magazine Ad',
                'Picture in the magazine',
                'Online posting with multiple photos',
                'Preferred Placement in Search Results'
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