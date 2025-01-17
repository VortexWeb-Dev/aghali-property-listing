<div class="w-4/5 mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <form class="w-full space-y-4" id="addPropertyForm" onsubmit="handleAddProperty(event)" enctype="multipart/form-data">
            <!-- Management -->
            <?php include_once('views/components/add-property/management.php'); ?>
            <!-- Specifications -->
            <?php include_once('views/components/add-property/specifications.php'); ?>
            <!-- Property Permit -->
            <?php include_once('views/components/add-property/permit.php'); ?>
            <!-- Pricing -->
            <?php include_once('views/components/add-property/pricing.php'); ?>
            <!-- Title and Description -->
            <?php include_once('views/components/add-property/title.php'); ?>
            <!-- Location -->
            <?php include_once('views/components/add-property/location.php'); ?>
            <!-- Photos and Videos -->
            <?php include_once('views/components/add-property/media.php'); ?>
            <!-- Floor Plan -->
            <?php include_once('views/components/add-property/floorplan.php'); ?>
            <!-- Documents -->
            <?php // include_once('views/components/add-property/documents.php'); 
            ?>
            <!-- Notes -->
            <?php include_once('views/components/add-property/notes.php'); ?>
            <!-- Portals -->
            <?php include_once('views/components/add-property/portals.php'); ?>
            <!-- Status -->
            <?php include_once('views/components/add-property/status.php'); ?>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="button" onclick="window.location.href = 'index.php?page=properties'" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1">
                    Back
                </button>
                <button type="submit" id="submitButton" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById("offering_type").addEventListener("change", function() {
        const offeringType = this.value;
        console.log(offeringType);

        if (offeringType == 'RR' || offeringType == 'CR') {
            document.getElementById("rental_period").setAttribute("required", true);
            document.querySelector('label[for="rental_period"]').innerHTML = 'Rental Period (if rental) <span class="text-danger">*</span>';
        } else {
            document.getElementById("rental_period").removeAttribute("required");
            document.querySelector('label[for="rental_period"]').innerHTML = 'Rental Period (if rental)';
        }
    })

    async function addItem(entityTypeId, fields) {
        try {
            const response = await fetch(`https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma//crm.item.add?entityTypeId=${entityTypeId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fields,
                }),
            });

            if (response.ok) {
                window.location.href = 'index.php?page=properties';
            } else {
                console.error('Failed to add item');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function handleAddProperty(e) {
        e.preventDefault();

        document.getElementById('submitButton').disabled = true;
        document.getElementById('submitButton').innerHTML = 'Submitting...';

        const form = document.getElementById('addPropertyForm');
        const formData = new FormData(form);
        const data = {};

        formData.forEach((value, key) => {
            data[key] = typeof value === 'string' ? value.trim() : value;
        });

        const agent = await getAgent(data.listing_agent);

        const fields = {
            "title": data.title_deed,
            "ufCrm22ReferenceNumber": data.reference,
            "ufCrm22OfferingType": data.offering_type,
            "ufCrm22PropertyType": data.property_type,
            "ufCrm22Price": data.price,
            "ufCrm22TitleEn": data.title_en,
            "ufCrm22DescriptionEn": data.description_en,
            "ufCrm22TitleAr": data.title_ar,
            "ufCrm22DescriptionAr": data.description_ar,
            "ufCrm22Size": data.size,
            "ufCrm22Bedroom": data.bedrooms,
            "ufCrm22Bathroom": data.bathrooms,
            "ufCrm22Parking": data.parkings,
            "ufCrm22Geopoints": `${data.latitude}, ${data.longitude}`,
            "ufCrm22PermitNumber": data.dtcm_permit_number,
            "ufCrm22RentalPeriod": data.rental_period,
            "ufCrm22Furnished": data.furnished,
            "ufCrm22TotalPlotSize": data.total_plot_size,
            "ufCrm22LotSize": data.lot_size,
            "ufCrm22BuildupArea": data.buildup_area,
            "ufCrm22LayoutType": data.layout_type,
            "ufCrm22ProjectName": data.project_name,
            "ufCrm22ProjectStatus": data.project_status,
            "ufCrm22Ownership": data.ownership,
            "ufCrm22Developers": data.developer,
            "ufCrm22BuildYear": data.build_year,
            "ufCrm22Availability": data.availability,
            "ufCrm22AvailableFrom": data.available_from,
            "ufCrm22PaymentMethod": data.payment_method,
            "ufCrm22DownPaymentPrice": data.downpayment_price,
            "ufCrm22NoOfCheques": data.cheques,
            "ufCrm22ServiceCharge": data.service_charge,
            "ufCrm22FinancialStatus": data.financial_status,
            "ufCrm22VideoTourUrl": data.video_tour_url,
            "ufCrm_22_360_VIEW_URL": data["360_view_url"],
            "ufCrm22QrCodePropertyBooster": data.qr_code_url,
            "ufCrm22Location": data.pf_location,
            "ufCrm22City": data.pf_city,
            "ufCrm22Community": data.pf_community,
            "ufCrm22SubCommunity": data.pf_subcommunity,
            "ufCrm22Tower": data.pf_building,
            "ufCrm22BayutLocation": data.bayut_location,
            "ufCrm22BayutCity": data.bayut_city,
            "ufCrm22BayutCommunity": data.bayut_community,
            "ufCrm22BayutSubCommunity": data.bayut_subcommunity,
            "ufCrm22BayutTower": data.bayut_building,
            "ufCrm22Latitude": data.latitude,
            "ufCrm22Longitude": data.longitude,
            "ufCrm22Status": data.status,
            "ufCrm22ReraPermitNumber": data.rera_permit_number,
            "ufCrm22ReraPermitIssueDate": data.rera_issue_date,
            "ufCrm22ReraPermitExpirationDate": data.rera_expiration_date,
            "ufCrm22DtcmPermitNumber": data.dtcm_permit_number,
            "ufCrm22ListingOwner": data.listing_owner,
            "ufCrm22LandlordName": data.landlord_name,
            "ufCrm22LandlordEmail": data.landlord_email,
            "ufCrm22LandlordContact": data.landlord_phone,
            "ufCrm22ContractExpiryDate": data.contract_expiry,
            "ufCrm22UnitNo": data.unit_no,
            "ufCrm22SaleType": data.sale_type,
            "ufCrm22BrochureDescription": data.brochure_description_1,
            "ufCrm_22_BROCHUREDESCRIPTION2": data.brochure_description_2,
            "ufCrm22HidePrice": data.hide_price == "on" ? "Y" : "N",
            "ufCrm22PfEnable": data.pf_enable == "on" ? "Y" : "N",
            "ufCrm22BayutEnable": data.bayut_enable == "on" ? "Y" : "N",
            "ufCrm22DubizzleEnable": data.dubizzle_enable == "on" ? "Y" : "N",
            "ufCrm22WebsiteEnable": data.website_enable == "on" ? "Y" : "N",
            "ufCrm22MetahomesEnable": data.metahomes_enable == "on" ? "Y" : "N",
        };

        if (agent) {
            fields["ufCrm22AgentId"] = agent.ufCrm24AgentId;
            fields["ufCrm22AgentName"] = agent.ufCrm24AgentName;
            fields["ufCrm22AgentEmail"] = agent.ufCrm24AgentName;
            fields["ufCrm22AgentPhone"] = agent.ufCrm24AgentMobile;
            fields["ufCrm22AgentPhoto"] = agent.ufCrm24AgentPhoto;
            fields["ufCrm22AgentLicense"] = agent.ufCrm24AgentLicense;
        }

        const notesString = data.notes;
        if (notesString) {
            const notesArray = JSON.parse(notesString);
            if (notesArray) {
                fields["ufCrm22Notes"] = notesArray;
            }
        }

        // Property Photos
        const photos = document.getElementById('selectedImages').value;
        if (photos) {
            const fixedPhotos = photos.replace(/\\'/g, '"');
            const photoArray = JSON.parse(fixedPhotos);
            const watermarkPath = 'assets/images/watermark.png';
            const uploadedImages = await processBase64Images(photoArray, watermarkPath);

            if (uploadedImages.length > 0) {
                fields["ufCrm22PhotoLinks"] = uploadedImages;
            }
        }

        // Floorplan
        const floorplan = document.getElementById('selectedFloorplan').value;
        if (floorplan) {
            const fixedFloorplan = floorplan.replace(/\\'/g, '"');
            const floorplanArray = JSON.parse(fixedFloorplan);
            const watermarkPath = 'assets/images/watermark.png';
            const uploadedFloorplan = await processBase64Images(floorplanArray, watermarkPath);

            if (uploadedFloorplan.length > 0) {
                fields["ufCrm22FloorPlan"] = uploadedFloorplan[0];
            }
        }

        // Documents
        // const documents = document.getElementById('documents')?.files;
        // if (documents) {
        //     if (documents.length > 0) {
        //         let documentUrls = [];

        //         for (const document of documents) {
        //             if (document.size > 10485760) {
        //                 alert('File size must be less than 10MB');
        //                 return;
        //             }
        //             const uploadedDocument = await uploadFile(document);
        //             documentUrls.push(uploadedDocument);
        //         }

        //         fields["ufCrm22Documents"] = documentUrls;
        //     }

        // }

        // Add to CRM
        addItem(1066, fields, '?page=properties');
    }
</script>