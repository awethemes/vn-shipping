<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
    'VNShipping\\AddressHooks' => $baseDir . '/inc/AddressHooks.php',
    'VNShipping\\Address\\AddressMapper' => $baseDir . '/inc/Address/AddressMapper.php',
    'VNShipping\\Address\\DataLoader' => $baseDir . '/inc/Address/DataLoader.php',
    'VNShipping\\Address\\District' => $baseDir . '/inc/Address/District.php',
    'VNShipping\\Address\\Province' => $baseDir . '/inc/Address/Province.php',
    'VNShipping\\Address\\Ward' => $baseDir . '/inc/Address/Ward.php',
    'VNShipping\\AdminActions' => $baseDir . '/inc/AdminActions.php',
    'VNShipping\\CartShippingContext' => $baseDir . '/inc/CartShippingContext.php',
    'VNShipping\\Courier\\AbstractCourier' => $baseDir . '/inc/Courier/AbstractCourier.php',
    'VNShipping\\Courier\\Couriers' => $baseDir . '/inc/Courier/Couriers.php',
    'VNShipping\\Courier\\Exception\\BadResponseException' => $baseDir . '/inc/Courier/Exception/BadResponseException.php',
    'VNShipping\\Courier\\Exception\\InvalidAddressDataException' => $baseDir . '/inc/Courier/Exception/InvalidAddressDataException.php',
    'VNShipping\\Courier\\Exception\\InvalidParameterException' => $baseDir . '/inc/Courier/Exception/InvalidParameterException.php',
    'VNShipping\\Courier\\Exception\\RequestException' => $baseDir . '/inc/Courier/Exception/RequestException.php',
    'VNShipping\\Courier\\Exception\\UnauthorizedException' => $baseDir . '/inc/Courier/Exception/UnauthorizedException.php',
    'VNShipping\\Courier\\Factory' => $baseDir . '/inc/Courier/Factory.php',
    'VNShipping\\Courier\\GHN' => $baseDir . '/inc/Courier/GHN.php',
    'VNShipping\\Courier\\RequestParameters' => $baseDir . '/inc/Courier/RequestParameters.php',
    'VNShipping\\Courier\\Response\\CollectionResponseData' => $baseDir . '/inc/Courier/Response/CollectionResponseData.php',
    'VNShipping\\Courier\\Response\\JsonResponseData' => $baseDir . '/inc/Courier/Response/JsonResponseData.php',
    'VNShipping\\Courier\\Response\\PaginatedResponseData' => $baseDir . '/inc/Courier/Response/PaginatedResponseData.php',
    'VNShipping\\Courier\\Response\\ShippingOrderResponseData' => $baseDir . '/inc/Courier/Response/ShippingOrderResponseData.php',
    'VNShipping\\Courier\\ShippingStatus' => $baseDir . '/inc/Courier/ShippingStatus.php',
    'VNShipping\\DatabaseUpgrader' => $baseDir . '/inc/DatabaseUpgrader.php',
    'VNShipping\\OptionsResolver\\OptionConfigurator' => $baseDir . '/inc/OptionsResolver/OptionConfigurator.php',
    'VNShipping\\OptionsResolver\\OptionsResolver' => $baseDir . '/inc/OptionsResolver/OptionsResolver.php',
    'VNShipping\\OrderHelper' => $baseDir . '/inc/OrderHelper.php',
    'VNShipping\\OrderListTable' => $baseDir . '/inc/OrderListTable.php',
    'VNShipping\\OrderShippingContext' => $baseDir . '/inc/OrderShippingContext.php',
    'VNShipping\\Plugin' => $baseDir . '/inc/Plugin.php',
    'VNShipping\\REST\\AddressController' => $baseDir . '/inc/REST/AddressController.php',
    'VNShipping\\REST\\ShippingController' => $baseDir . '/inc/REST/ShippingController.php',
    'VNShipping\\ShippingData' => $baseDir . '/inc/ShippingData.php',
    'VNShipping\\ShippingMethod\\AccessTokenAwareInterface' => $baseDir . '/inc/ShippingMethod/AccessTokenAwareInterface.php',
    'VNShipping\\ShippingMethod\\GHNShippingMethod' => $baseDir . '/inc/ShippingMethod/GHNShippingMethod.php',
    'VNShipping\\ShippingMethod\\ShippingMethodInterface' => $baseDir . '/inc/ShippingMethod/ShippingMethodInterface.php',
    'VNShipping\\ShippingMethod\\ShippingMethodTrait' => $baseDir . '/inc/ShippingMethod/ShippingMethodTrait.php',
    'VNShipping\\Traits\\SingletonTrait' => $baseDir . '/inc/Traits/SingletonTrait.php',
);
