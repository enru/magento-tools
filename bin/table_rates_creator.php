<?php
/*
 * Magento Table Rates file creator
 * this set up is for price v. destination but it can easier be changed to do weight v. destination
 * Created on Sep 28, 2009
 * @author     Neill Russell <paste@n3ru.net>
 */

if($argc < 2) die(sprintf("Usage: %s <MAGENTO_ROOT>\n", $_SERVER['SCRIPT_FILENAME']));

define('MAGENTO_DIR', realpath($argv[1]));

$app_dir = MAGENTO_DIR . '/app';
if ( ! file_exists($app_dir) ) { die("Can't find Magento\n"); }
require_once($app_dir . '/Mage.php');
umask(0);
Mage::app('admin');
//error_reporting(0);

$locale = Mage::getModel('core/locale');

$eu = array(
'Austria',
'Belgium',
'Bulgaria',
'Cyprus',
'Czech Republic',
'Denmark',
'Estonia',
'Finland',
'France',
'Germany',
'Greece',
'Hungary',
'Ireland',
'Italy',
'Latvia',
'Lithuania',
'Luxembourg',
'Malta',
'Netherlands',
'Poland',
'Portugal',
'Romania',
'Slovakia',
'Slovenia',
'Spain',
'Sweden',
'United Kingdom',
);

$non_eu = array(
'Albania',
'Andorra',
'Armenia',
'Azerbaijan',
'Belarus',
'Bosnia and Herzegovina',
'Georgia',
'Iceland',
'Liechtenstein',
'Moldova',
'Monaco',
'Montenegro',
'Norway',
'Russia',
'San Marino',
'Serbia',
'Switzerland',
'Ukraine',
'Vatican', //'Vatican City State',
);

// get Magento Country Directory
$countryCodesToIds = $countryCodesIso2 = array(); 
$countryCollection = Mage::getResourceModel('directory/country_collection')/*->addCountryCodeFilter($countryCodes)*/->load();
foreach ($countryCollection->getItems() as $country) {
	$countryCodesToIds[$country->getData('iso3_code')] = $country->getData('country_id');
	$countryCodesToIds[$country->getData('iso2_code')] = $country->getData('country_id');
	$countryCodesIso2[] = $country->getData('iso2_code');
}

// COUNTRIES
$translationList  = $locale->getCountryTranslationList();
$countries = array();
foreach ($translationList as $code=>$name) {
	// skip countries not in Magento Directory
	if(!in_array($code, $countryCodesIso2)) continue; 
	
	// add to countries array
	if($code == 'GB') $countries['UK'][$code] = $name;
	elseif(in_array($name, $eu)) $countries['EU'][$code] = $name;
	elseif(in_array($name, $non_eu)) $countries['NON-EU'][$code] = $name;
	elseif($code == 'US') $countries['US'][$code] = $name;
	else $countries['ROW'][$code] = $name;	
}

/* setup your prices per order subtotal for each region */

$prices=array();

$prices['NON-EU']=array();

$prices['EU']=array(
// subtotal => price
'0'=>10,
'50'=>20,
);

$prices['US']=array(
// subtotal => price
'0'=>12.50,
'50'=>25,
);

$prices['CA']=array(
// subtotal => price
'0'=>12.50,
'50'=>25,
);

$prices['ROW']=array(
// subtotal => price
'0'=>15,
'50'=>35,
);

$prices['UK']=array(
// subtotal => price
'0'=>0,
);

echo "Country, Region/State, Zip/Postal Code, Order Subtotal (and above), Shipping Price\r\n";
foreach($countries as $region => $_countries) {
	foreach($_countries as $code => $country) {
		foreach($prices[$region] as $threshold => $price) {
			printf("%s, *, *, %s, %s\n", $code, $threshold, $price);
		}
	}
}


