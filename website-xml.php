<?php
require 'utils/index.php';

header('Content-Type: application/xml; charset=UTF-8');

$baseUrl = 'https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma/';
$entityTypeId = 1066;
$fields = [
    'id',
    'ufCrm22ReferenceNumber',
    'ufCrm22PermitNumber',
    'ufCrm22ReraPermitNumber',
    'ufCrm22DtcmPermitNumber',
    'ufCrm22OfferingType',
    'ufCrm22PropertyType',
    'ufCrm22HidePrice',
    'ufCrm22RentalPeriod',
    'ufCrm22YearlyPrice',
    'ufCrm22MonthlyPrice',
    'ufCrm22WeeklyPrice',
    'ufCrm22DailyPrice',
    'ufCrm22Price',
    'ufCrm22ServiceCharge',
    'ufCrm22NoOfCheques',
    'ufCrm22City',
    'ufCrm22Community',
    'ufCrm22SubCommunity',
    'ufCrm22Tower',
    'ufCrm22TitleEn',
    'ufCrm22TitleAr',
    'ufCrm22DescriptionEn',
    'ufCrm22DescriptionAr',
    'ufCrm22TotalPlotSize',
    'ufCrm22Size',
    'ufCrm22Bedroom',
    'ufCrm22Bathroom',
    'ufCrm22AgentId',
    'ufCrm22AgentName',
    'ufCrm22AgentEmail',
    'ufCrm22AgentPhone',
    'ufCrm22AgentPhoto',
    'ufCrm22BuildYear',
    'ufCrm22Parking',
    'ufCrm22Furnished',
    'ufCrm_13_360_VIEW_URL',
    'ufCrm22PhotoLinks',
    'ufCrm22FloorPlan',
    'ufCrm22Geopoints',
    'ufCrm22Latitude',
    'ufCrm22Longitude',
    'ufCrm22AvailableFrom',
    'ufCrm22VideoTourUrl',
    'ufCrm22Developers',
    'ufCrm22ProjectName',
    'ufCrm22ProjectStatus',
    'ufCrm22ListingOwner',
    'ufCrm22Status',
    'ufCrm22PfEnable',
    'ufCrm22BayutEnable',
    'ufCrm22DubizzleEnable',
    'ufCrm22WebsiteEnable',
    'updatedTime'
];

$properties = fetchAllProperties($baseUrl, $entityTypeId, $fields, 'website');

if (count($properties) > 0) {
    $xml = generatePfXml($properties);
    echo $xml;
} else {
    echo '<?xml version="1.0" encoding="UTF-8"?><list></list>';
}
