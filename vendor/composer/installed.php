<?php return array(
    'root' => array(
        'name' => 'ovesio/ovesio-wordpress',
        'pretty_version' => '1.0.0',
        'version' => '1.0.0.0',
        'reference' => NULL,
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'ovesio/ovesio-php' => array(
            'pretty_version' => '1.1.2',
            'version' => '1.1.2.0',
            'reference' => '638fe7b413f108d8329af9daca0a64770d0fdd6e',
            'type' => 'library',
            'install_path' => __DIR__ . '/../ovesio/ovesio-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'ovesio/ovesio-wordpress' => array(
            'pretty_version' => '1.0.0',
            'version' => '1.0.0.0',
            'reference' => NULL,
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
