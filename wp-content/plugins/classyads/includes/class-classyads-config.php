<?php

global $classyads_config;
$classyads_config = array(
    'plans' => array(
        'free' => array(
            'status' => 'active',
            'name' => 'Free (Online Only)',
            'amount' => 0,
            'months' => 1,
            'description' => "",
            'in_print' => false,
            'print_photo' => false,
            'multiple_photos' => false,
            'categories' => array('boats','gear')
        ),
        'basic' => array(
            'status' => 'active',
            'name' => 'Basic',
            'amount' => 20,
            'months' => 1,
            'description' => "",
            'in_print' => true,
            'print_chars' => '250',
            'print_photo' => false,
            'multiple_photos' => false,
            'categories' => array('boats','gear'),
            'features' => array(
                'Feature 1',
                'Feature 2',
                'Feature 3',
            )
        ),
        'premium' => array(
            'status' => 'active',
            'name' => 'Premium',
            'amount' => 60,
            'months' => 1,
            'description' => "",
            'in_print' => true,
            'print_chars' => '250',
            'print_photo' => true,
            'multiple_photos' => true,
            'categories' => array('boats')
        ),
        'premium2' => array(
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