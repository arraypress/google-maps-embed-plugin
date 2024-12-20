<?php return array(
    'root' => array(
        'name' => 'arraypress/google-maps-embed-plugin',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => null,
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'arraypress/google-maps-embed' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '2d1a870611a86ce81bbaee8f2dc9a8cce07b3094',
            'type' => 'library',
            'install_path' => __DIR__ . '/../arraypress/google-maps-embed',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'arraypress/google-maps-embed-plugin' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
