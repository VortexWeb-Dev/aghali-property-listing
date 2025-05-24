<?php

require_once(__DIR__ . "/../crest/crest.php");
require_once(__DIR__ . "/../crest/settings.php");


function buildApiUrl($baseUrl, $entityTypeId, $fields, $start = 0)
{
    $selectParams = '';
    foreach ($fields as $index => $field) {
        $selectParams .= "select[$index]=$field&";
    }
    $selectParams = rtrim($selectParams, '&');
    return "$baseUrl/crm.item.list?entityTypeId=$entityTypeId&$selectParams&start=$start&filter[ufCrm22Status]=PUBLISHED";
}

function fetchAllProperties($baseUrl, $entityTypeId, $fields, $platform = null)
{
    $allProperties = [];
    $start = 0;

    try {
        while (true) {
            $apiUrl = buildApiUrl($baseUrl, $entityTypeId, $fields, $start);
            $response = file_get_contents($apiUrl);
            $data = json_decode($response, true);

            if (isset($data['result']['items'])) {
                $properties = $data['result']['items'];
                $allProperties = array_merge($allProperties, $properties);
            }

            // If there's no "next" key, we've fetched all data
            if (empty($data['next'])) {
                break;
            }

            $start = $data['next'];
        }

        if ($platform) {
            switch ($platform) {
                case 'pf':
                    $allProperties = array_filter($allProperties, function ($property) {
                        return $property['ufCrm22PfEnable'] === 'Y';
                    });
                    break;
                case 'bayut':
                    $allProperties = array_filter($allProperties, function ($property) {
                        return $property['ufCrm22BayutEnable'] === 'Y';
                    });
                    break;
                case 'dubizzle':
                    $allProperties = array_filter($allProperties, function ($property) {
                        return $property['ufCrm22DubizzleEnable'] === 'Y';
                    });
                    break;
                case 'website':
                    $allProperties = array_filter($allProperties, function ($property) {
                        return $property['ufCrm22WebsiteEnable'] === 'Y';
                    });
                    break;
                default:
                    break;
            }
        }

        return $allProperties;
    } catch (Exception $e) {
        error_log('Error fetching properties: ' . $e->getMessage());
        return [];
    }
}

function getPropertyPurpose($property)
{
    return ($property['ufCrm22OfferingType'] == 'RR' || $property['ufCrm22OfferingType'] == 'CR') ? 'Rent' : 'Buy';
}

function getPropertyType($property)
{
    $property_types = array(
        "AP" => "Apartment",
        "BW" => "Bungalow",
        "CD" => "Compound",
        "DX" => "Duplex",
        "FF" => "Full floor",
        "HF" => "Half floor",
        "LP" => "Land / Plot",
        "PH" => "Penthouse",
        "TH" => "Townhouse",
        "VH" => "Villa",
        "WB" => "Whole Building",
        "HA" => "Short Term / Hotel Apartment",
        "LC" => "Labor camp",
        "BU" => "Bulk units",
        "WH" => "Warehouse",
        "FA" => "Factory",
        "OF" => "Office space",
        "RE" => "Retail",
        "LP" => "Plot",
        "SH" => "Shop",
        "SR" => "Show Room",
        "SA" => "Staff Accommodation"
    );

    return $property_types[$property['ufCrm22PropertyType']] ?? '';
}

function getPermitNumber($property)
{
    if (!empty($property['ufCrm22PermitNumber'])) {
        return $property['ufCrm22PermitNumber'];
    }
    return $property['ufCrm22ReraPermitNumber'] ?? '';
}

function getFullAmenityName($shortCode)
{
    $amenityMap = [
        'BA' => 'Balcony',
        'BP' => 'Basement parking',
        'BB' => 'BBQ area',
        'AN' => 'Cable-ready',
        'BW' => 'Built in wardrobes',
        'CA' => 'Carpets',
        'AC' => 'Central air conditioning',
        'CP' => 'Covered parking',
        'DR' => 'Drivers room',
        'FF' => 'Fully fitted kitchen',
        'GZ' => 'Gazebo',
        'PY' => 'Private Gym',
        'PJ' => 'Jacuzzi',
        'BK' => 'Kitchen Appliances',
        'MR' => 'Maids Room',
        'MB' => 'Marble floors',
        'HF' => 'On high floor',
        'LF' => 'On low floor',
        'MF' => 'On mid floor',
        'PA' => 'Pets allowed',
        'GA' => 'Private garage',
        'PG' => 'Garden',
        'PP' => 'Swimming pool',
        'SA' => 'Sauna',
        'SP' => 'Shared swimming pool',
        'WF' => 'Wood flooring',
        'SR' => 'Steam room',
        'ST' => 'Study',
        'UI' => 'Upgraded interior',
        'GR' => 'Garden view',
        'VW' => 'Sea/Water view',
        'SE' => 'Security',
        'MT' => 'Maintenance',
        'IC' => 'Within a Compound',
        'IS' => 'Indoor swimming pool',
        'SF' => 'Separate entrance for females',
        'BT' => 'Basement',
        'SG' => 'Storage room',
        'CV' => 'Community view',
        'GV' => 'Golf view',
        'CW' => 'City view',
        'NO' => 'North orientation',
        'SO' => 'South orientation',
        'EO' => 'East orientation',
        'WO' => 'West orientation',
        'NS' => 'Near school',
        'HO' => 'Near hospital',
        'TR' => 'Terrace',
        'NM' => 'Near mosque',
        'SM' => 'Near supermarket',
        'ML' => 'Near mall',
        'PT' => 'Near public transportation',
        'MO' => 'Near metro',
        'VT' => 'Near veterinary',
        'BC' => 'Beach access',
        'PK' => 'Public parks',
        'RT' => 'Near restaurants',
        'NG' => 'Near Golf',
        'AP' => 'Near airport',
        'CS' => 'Concierge Service',
        'SS' => 'Spa',
        'SY' => 'Shared Gym',
        'MS' => 'Maid Service',
        'WC' => 'Walk-in Closet',
        'HT' => 'Heating',
        'GF' => 'Ground floor',
        'SV' => 'Server room',
        'DN' => 'Pantry',
        'RA' => 'Reception area',
        'VP' => 'Visitors parking',
        'OP' => 'Office partitions',
        'SH' => 'Core and Shell',
        'CD' => 'Children daycare',
        'CL' => 'Cleaning services',
        'NH' => 'Near Hotel',
        'CR' => 'Conference room',
        'BL' => 'View of Landmark',
        'PR' => 'Children Play Area',
        'BH' => 'Beach Access'
    ];

    return $amenityMap[$shortCode] ?? $shortCode;
}

function formatDate($date)
{
    return $date ? date('Y-m-d H:i:s', strtotime($date)) : date('Y-m-d H:i:s');
}

function formatField($field, $value, $type = 'string')
{
    if (empty($value) && $value != 0) {
        return '';
    }

    switch ($type) {
        case 'date':
            return '<' . $field . '>' . formatDate($value) . '</' . $field . '>';
        default:
            return '<' . $field . '>' . htmlspecialchars($value) . '</' . $field . '>';
    }
}

function formatPriceOnApplication($property)
{
    $priceOnApplication = ($property['ufCrm22HidePrice'] === 'Y') ? 'No' : 'Yes';
    return formatField('price_on_application', $priceOnApplication);
}

function formatRentalPrice($property)
{
    if (empty($property['ufCrm22RentalPeriod'])) {
        return formatField('price', $property['ufCrm22Price']);
    }

    switch ($property['ufCrm22RentalPeriod']) {
        case 'Y':
            return formatField('price', $property['ufCrm22YearlyPrice'] ?? $property['ufCrm22Price'], 'yearly');
        case 'M':
            return formatField('price', $property['ufCrm22MonthlyPrice'] ?? $property['ufCrm22Price'], 'monthly');
        case 'W':
            return formatField('price', $property['ufCrm22WeeklyPrice'] ?? $property['ufCrm22Price'], 'weekly');
        case 'D':
            return formatField('price', $property['ufCrm22DailyPrice'] ?? $property['ufCrm22Price'], 'daily');
        default:
            return formatField('price', $property['ufCrm22Price']);
    }
}

function formatBedroom($property)
{
    return formatField('bedroom', ($property['ufCrm22Bedroom'] > 7) ? '7+' : $property['ufCrm22Bedroom']);
}

function formatBathroom($property)
{
    return formatField('bathroom', ($property['ufCrm22Bathroom'] > 7) ? '7+' : $property['ufCrm22Bathroom']);
}

function formatFurnished($property)
{
    $furnished = $property['ufCrm22Furnished'] ?? '';
    if ($furnished) {
        switch ($furnished) {
            case 'Furnished':
                return formatField('furnished', 'Yes');
            case 'Unfurnished':
                return formatField('furnished', 'No');
            case 'Partly Furnished':
                return formatField('furnished', 'Partly');
            default:
                return '';
        }
    }
    return ''; // If no furnished value exists, return an empty string
}

function formatAgent($property)
{
    $xml = '<agent>';
    $xml .= formatField('id', $property['ufCrm22AgentId']);
    $xml .= formatField('name', $property['ufCrm22AgentName']);
    $xml .= formatField('email', $property['ufCrm22AgentEmail']);
    $xml .= formatField('phone', $property['ufCrm22AgentPhone']);
    $xml .= formatField('photo', $property['ufCrm22AgentPhoto'] ?? 'https://youtupia.com/thinkrealty/images/agent-placeholder.webp');
    $xml .= '</agent>';

    return $xml;
}

function formatPhotos($photos)
{
    if (empty($photos)) {
        return '';
    }

    $xml = '<photo>';
    foreach ($photos as $photo) {
        $xml .= '<url last_update="' . date('Y-m-d H:i:s') . '" watermark="Yes">' . htmlspecialchars($photo) . '</url>';
    }
    $xml .= '</photo>';

    return $xml;
}

function formatGeopoints($property)
{
    $geopoints = "";

    if (!empty($property['ufCrm22Latitude']) && !empty($property['ufCrm22Longitude'])) {
        $geopoints = ($property['ufCrm22Latitude'] . ',' . $property['ufCrm22Longitude'] ?? '');
    } else {
        $geopoints = ($property['ufCrm22Geopoints'] ?? '');
    }

    return formatField('geopoints', $geopoints);
}

function formatCompletionStatus($property)
{
    $status = $property['ufCrm22ProjectStatus'] ?? '';
    switch ($status) {
        case 'Completed':
        case 'ready_secondary':
            return formatField('completion_status', 'completed');
        case 'offplan':
        case 'offplan_secondary':
            return formatField('completion_status', 'off_plan');
        case 'ready_primary':
            return formatField('completion_status', 'completed_primary');
        case 'offplan_primary':
            return formatField('completion_status', 'off_plan_primary');
        default:
            return '';
    }
}

function generatePfXml($properties)
{
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<list last_update="' . date('Y-m-d H:i:s') . '" listing_count="' . count($properties) . '">';

    foreach ($properties as $property) {
        $xml .= '<property last_update="' . formatDate($property['updatedTime'] ?? '') . '" id="' . htmlspecialchars($property['id'] ?? '') . '">';

        $xml .= formatField('reference_number', $property['ufCrm22ReferenceNumber']);
        $xml .= formatField('permit_number', getPermitNumber($property));

        $xml .= formatField('dtcm_permit', $property['ufCrm22DtcmPermitNumber']);
        $xml .= formatField('offering_type', $property['ufCrm22OfferingType']);
        $xml .= formatField('property_type', $property['ufCrm22PropertyType']);
        $xml .= formatPriceOnApplication($property);
        $xml .= formatRentalPrice($property);

        $xml .= formatField('service_charge', $property['ufCrm22ServiceCharge']);
        $xml .= formatField('cheques', $property['ufCrm22NoOfCheques']);
        $xml .= formatField('city', $property['ufCrm22City']);
        $xml .= formatField('community', $property['ufCrm22Community']);
        $xml .= formatField('sub_community', $property['ufCrm22SubCommunity']);
        $xml .= formatField('property_name', $property['ufCrm22Tower']);

        $xml .= formatField('title_en', $property['ufCrm22TitleEn']);
        $xml .= formatField('title_ar', $property['ufCrm22TitleAr']);
        $xml .= formatField('description_en', $property['ufCrm22DescriptionEn']);
        $xml .= formatField('description_ar', $property['ufCrm22DescriptionAr']);

        $xml .= formatField('plot_size', $property['ufCrm22TotalPlotSize']);
        $xml .= formatField('size', $property['ufCrm22Size']);
        // $xml .= formatField('bedroom', $property['ufCrm22Bedroom']);
        $xml .= formatBedroom($property);
        $xml .= formatBathroom($property);

        $xml .= formatAgent($property);
        $xml .= formatField('build_year', $property['ufCrm22BuildYear']);
        $xml .= formatField('parking', $property['ufCrm22Parking']);
        $xml .= formatFurnished($property);
        $xml .= formatField('view360', $property['ufCrm_22_360_VIEW_URL']);
        $xml .= formatPhotos($property['ufCrm22PhotoLinks']);
        $xml .= formatField('floor_plan', $property['ufCrm22FloorPlan']);
        $xml .= formatGeopoints($property);
        $xml .= formatField('availability_date', $property['ufCrm22AvailableFrom'], 'date');
        $xml .= formatField('video_tour_url', $property['ufCrm22VideoTourUrl']);
        $xml .= formatField('developer', $property['ufCrm22Developers']);
        $xml .= formatField('project_name', $property['ufCrm22ProjectName']);
        $xml .= formatCompletionStatus($property);

        $xml .= '</property>';
    }

    $xml .= '</list>';
    return $xml;
}

function generateBayutXml($properties)
{
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<Properties last_update="' . date('Y-m-d H:i:s') . '" listing_count="' . count($properties) . '">';

    foreach ($properties as $property) {
        $xml .= '<Property id="' . htmlspecialchars($property['id'] ?? '') . '">';

        // Ensure proper CDATA wrapping and no misplaced closing tags
        $xml .= '<Property_Ref_No><![CDATA[' . ($property['ufCrm22ReferenceNumber'] ?? '') . ']]></Property_Ref_No>';
        $xml .= '<Permit_Number><![CDATA[' . getPermitNumber($property) . ']]></Permit_Number>';
        $xml .= '<Property_Status>live</Property_Status>';
        $xml .= '<Property_purpose><![CDATA[' . getPropertyPurpose($property) . ']]></Property_purpose>';
        $xml .= '<Property_Type><![CDATA[' . getPropertyType($property) . ']]></Property_Type>';
        $xml .= '<Property_Size><![CDATA[' . ($property['ufCrm22Size'] ?? '') . ']]></Property_Size>';
        $xml .= '<Property_Size_Unit>SQFT</Property_Size_Unit>';

        // Ensure proper condition for optional fields
        if (!empty($property['ufCrm22TotalPlotSize'])) {
            $xml .= '<plotArea><![CDATA[' . $property['ufCrm22TotalPlotSize'] . ']]></plotArea>';
        }

        $xml .= '<Bedrooms><![CDATA[' . (($property['ufCrm22Bedroom'] === 0) ? -1 : ($property['ufCrm22Bedroom'] > 10 ? "10+" : $property['ufCrm22Bedroom'])) . ']]></Bedrooms>';
        $xml .= '<Bathrooms><![CDATA[' . ($property['ufCrm22Bathroom'] ?? '') . ']]></Bathrooms>';

        $is_offplan = ($property['ufCrm22ProjectStatus'] === 'offplan_primary' || $property['ufCrm22ProjectStatus'] === 'offplan_secondary') ? 'Yes' : 'No';
        $xml .= '<Off_plan><![CDATA[' . $is_offplan . ']]></Off_plan>';

        $xml .= '<Portals>';
        if ($property['ufCrm22BayutEnable'] === 'Y') {
            $xml .= '<Portal>Bayut</Portal>';
        }
        if ($property['ufCrm22DubizzleEnable'] === 'Y') {
            $xml .= '<Portal>Dubizzle</Portal>';
        }
        $xml .= '</Portals>';

        $xml .= '<Property_Title><![CDATA[' . ($property['ufCrm22TitleEn'] ?? '') . ']]></Property_Title>';
        $xml .= '<Property_Description><![CDATA[' . ($property['ufCrm22DescriptionEn'] ?? '') . ']]></Property_Description>';

        if (!empty($property['ufCrm22TitleAr'])) {
            $xml .= '<Property_Title_AR><![CDATA[' . ($property['ufCrm22TitleAr'] ?? '') . ']]></Property_Title_AR>';
        }
        if (!empty($property['ufCrm22DescriptionAr'])) {
            $xml .= '<Property_Description_AR><![CDATA[' . ($property['ufCrm22DescriptionAr'] ?? '') . ']]></Property_Description_AR>';
        }

        $xml .= '<Price><![CDATA[' . ($property['ufCrm22Price'] ?? '') . ']]></Price>';

        if ($property['ufCrm22RentalPeriod'] == 'Y' && (isset($property['ufCrm22YearlyPrice']) || isset($property['ufCrm22Price']))) {
            $xml .= '<Rent_Frequency>Yearly</Rent_Frequency>';
        } elseif ($property['ufCrm22RentalPeriod'] == 'M' && (isset($property['ufCrm22MonthlyPrice']) || isset($property['ufCrm22Price']))) {
            $xml .= '<Rent_Frequency>Monthly</Rent_Frequency>';
        } elseif ($property['ufCrm22RentalPeriod'] == 'W' && (isset($property['ufCrm22WeeklyPrice']) || isset($property['ufCrm22Price']))) {
            $xml .= '<Rent_Frequency>Weekly</Rent_Frequency>';
        } elseif ($property['ufCrm22RentalPeriod'] == 'D' && (isset($property['ufCrm22DailyPrice']) || isset($property['ufCrm22Price']))) {
            $xml .= '<Rent_Frequency>Daily</Rent_Frequency>';
        }

        if ($property['ufCrm22Furnished'] === 'furnished' || strtolower($property['ufCrm22Furnished']) === 'yes') {
            $xml .= '<Furnished>Yes</Furnished>';
        } elseif ($property['ufCrm22Furnished'] === 'unfurnished' || strtolower($property['ufCrm22Furnished']) === 'no') {
            $xml .= '<Furnished>No</Furnished>';
        } elseif ($property['ufCrm22Furnished'] === 'semi-furnished' || strtolower($property['ufCrm22Furnished']) === 'partly') {
            $xml .= '<Furnished>Partly</Furnished>';
        }

        if (!empty($property['ufCrm22SaleType'])) {
            $xml .= '<offplanDetails_saleType><![CDATA[' . ($property['ufCrm22SaleType'] ?? '') . ']]></offplanDetails_saleType>';
        }

        $xml .= '<City><![CDATA[' . ($property['ufCrm22BayutCity'] ?: $property['ufCrm22City'] ?? '') . ']]></City>';
        $xml .= '<Locality><![CDATA[' . ($property['ufCrm22BayutCommunity'] ?: $property['ufCrm22Community'] ?? '') . ']]></Locality>';
        $xml .= '<Sub_Locality><![CDATA[' . ($property['ufCrm22BayutSubCommunity'] ?: $property['ufCrm22SubCommunity'] ?? '') . ']]></Sub_Locality>';
        $xml .= '<Tower_Name><![CDATA[' . ($property['ufCrm22BayutTower'] ?: $property['ufCrm22Tower'] ?? '') . ']]></Tower_Name>';

        $xml .= '<Listing_Agent><![CDATA[' . ($property['ufCrm22AgentName'] ?? '') . ']]></Listing_Agent>';
        $xml .= '<Listing_Agent_Phone><![CDATA[' . ($property['ufCrm22AgentPhone'] ?? '') . ']]></Listing_Agent_Phone>';
        $xml .= '<Listing_Agent_Email><![CDATA[' . ($property['ufCrm22AgentEmail'] ?? '') . ']]></Listing_Agent_Email>';

        $xml .= '<Images>';
        foreach ($property['ufCrm22PhotoLinks'] ?? [] as $image) {
            $xml .= '<Image last_update="' . date('Y-m-d H:i:s') . '"><![CDATA[' . $image . ']]></Image>';
        }
        $xml .= '</Images>';

        $xml .= '<Features>';
        foreach ($property['ufCrm22Amenities'] as $code) {
            $fullName = getFullAmenityName(trim($code));
            $xml .= '<Feature><![CDATA[' . $fullName . ']]></Feature>';
        }
        $xml .= '</Features>';


        $xml .= '</Property>';
    }

    $xml .= '</Properties>';
    return $xml;
}

function uploadFile($file, $isDocument = false)
{
    global $cloudinary;

    try {
        if (!file_exists($file)) {
            throw new Exception("File not found: " . $file);
        }

        $uploadResponse = $cloudinary->uploadApi()->upload($file, [
            'folder' => 'aghali-uploads',
            'resource_type' => $isDocument ? 'raw' : 'image',
        ]);

        return $uploadResponse['secure_url'];
    } catch (Exception $e) {
        error_log("Error uploading image: " . $e->getMessage());
        echo "Error uploading image: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        return false;
    }
}

function fetchCurrentUser()
{
    $response = CRest::call("user.current");
    return $response['result'];
}

function isAdmin($userId)
{
    $admins = [
        4, // Adam Ghali
        26, // Iqra Qasir
        44, // VortexWeb
    ];

    return in_array($userId, $admins);
}


function isDuplicateLocation($location_type, $location, $entity = PF_LOCATIONS_ENTITY_TYPE_ID)
{
    $entityTypeId = 0;
    $filter = [];
    if ($location_type == 'location') {
        // $entityTypeId = LOCATIONS_ENTITY_TYPE_ID;
        // $filter = ['ufCrm15Location' => $data['location']];
        $entityTypeId = $entity;
        if ($entity === PF_LOCATIONS_ENTITY_TYPE_ID) {
            $filter = ['ufCrm26Location' => $location['location']];
        } else {
            $filter = ['ufCrm16Location' => $location['location']];
        }
    } elseif ($location_type == 'city') {
        $entityTypeId = CITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm34City' => $location['city']];
    } elseif ($location_type == 'community') {
        $entityTypeId = COMMUNITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm36Community' => $location['community']];
    } elseif ($location_type = 'sub_community') {
        $entityTypeId = SUB_COMMUNITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm38SubCommunity' => $location['sub_community']];
    } elseif ($location_type = 'building') {
        $entityTypeId = BUILDINGS_ENTITY_TYPE_ID;
        $filter = ['ufCrm40Building' => $location['building']];
    }

    $result = Crest::call('crm.item.list', [
        'entityTypeId' => $entityTypeId,
        'filter' => $filter,
    ]);

    return $result['total'] != 0 || isset($result['error']);
}

function isDuplicateBayoutLocation($location_type, $location)
{
    $entityTypeId = 0;
    $filter = [];
    if ($location_type == 'location') {
        $entityTypeId = BAYUT_LOCATIONS_ENTITY_TYPE_ID;
        $filter = ['ufCrm28Location' => $location];
    } elseif ($location_type == 'city') {
        $entityTypeId = CITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm34City' => $location];
    } elseif ($location_type == 'community') {
        $entityTypeId = COMMUNITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm36Community' => $location];
    } elseif ($location_type = 'sub_community') {
        $entityTypeId = SUB_COMMUNITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm38SubCommunity' => $location];
    } elseif ($location_type = 'building') {
        $entityTypeId = BUILDINGS_ENTITY_TYPE_ID;
        $filter = ['ufCrm40Building' => $location];
    }

    $result = Crest::call('crm.item.list', [
        'entityTypeId' => $entityTypeId,
        'filter' => $filter,
    ]);

    return $result['total'] != 0 || isset($result['error']);
}

function isDuplicatePfLocation($location_type, $location)
{
    $entityTypeId = 0;
    $filter = [];
    if ($location_type == 'location') {
        $entityTypeId = PF_LOCATIONS_ENTITY_TYPE_ID;
        $filter = ['ufCrm26Location' => $location];
    } elseif ($location_type == 'city') {
        $entityTypeId = CITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm34City' => $location];
    } elseif ($location_type == 'community') {
        $entityTypeId = COMMUNITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm36Community' => $location];
    } elseif ($location_type = 'sub_community') {
        $entityTypeId = SUB_COMMUNITIES_ENTITY_TYPE_ID;
        $filter = ['ufCrm38SubCommunity' => $location];
    } elseif ($location_type = 'building') {
        $entityTypeId = BUILDINGS_ENTITY_TYPE_ID;
        $filter = ['ufCrm40Building' => $location];
    }

    $result = Crest::call('crm.item.list', [
        'entityTypeId' => $entityTypeId,
        'filter' => $filter,
    ]);

    return $result['total'] != 0 || isset($result['error']);
}
