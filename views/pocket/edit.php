<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : '';
?>

<div class="w-4/5 mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <form class="w-full space-y-4" id="editPropertyForm" onsubmit="handleEditProperty(event)">
            <!-- Management -->
            <?php include_once('views/components/add-property/management.php'); ?>
            <!-- Specifications -->
            <?php include_once('views/components/add-property/specifications.php'); ?>
            <!-- Property Permit -->
            <?php if ($current_page == 'add-property' || $current_page == 'edit-property'): ?>
                <?php include_once('views/components/add-property/permit.php'); ?>
            <?php endif; ?>
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
                <button type="button" onclick="window.location.href = 'index.php?page=pocket'" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1">
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

    async function updateItem(entityTypeId, fields, id) {
        try {
            const response = await fetch(`https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma//crm.item.update?entityTypeId=${entityTypeId}&id=${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fields,
                }),
            });

            if (response.ok) {
                window.location.href = 'index.php?page=pocket';
            } else {
                console.error('Failed to add item');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async function handleEditProperty(e) {
        e.preventDefault();

        const submitButton = document.getElementById('submitButton');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Updating...';

        const form = document.getElementById('editPropertyForm');
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
            "ufCrm22City": data.bayut_city,
            "ufCrm22Community": data.bayut_community,
            "ufCrm22SubCommunity": data.bayut_subcommunity,
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

            "ufCrm22HidePrice": data.hide_price === "on" ? "Y" : "N",
            "ufCrm22PfEnable": data.pf_enable === "on" ? "Y" : "N",
            "ufCrm22BayutEnable": data.bayut_enable === "on" ? "Y" : "N",
            "ufCrm22DubizzleEnable": data.dubizzle_enable === "on" ? "Y" : "N",
            "ufCrm22WebsiteEnable": data.website_enable === "on" ? "Y" : "N",
            "ufCrm22MetahomesEnable": data.metahomes_enable === "on" ? "Y" : "N",
        };

        if (agent) {
            Object.assign(fields, {
                "ufCrm22AgentId": agent.ufCrm24AgentId,
                "ufCrm22AgentName": agent.ufCrm24AgentName,
                "ufCrm22AgentEmail": agent.ufCrm24AgentEmail,
                "ufCrm22AgentPhone": agent.ufCrm24AgentMobile,
                "ufCrm22AgentPhoto": agent.ufCrm24AgentPhoto,
                "ufCrm22AgentLicense": agent.ufCrm24AgentLicense,
            });
        }

        const notesString = data.notes;
        if (notesString) {
            const notesArray = JSON.parse(notesString);
            fields["ufCrm22Notes"] = notesArray;
        }

        const photos = document.getElementById('selectedImages').value;
        const existingPhotosString = document.getElementById('existingPhotos').value;

        if (existingPhotosString) {
            const existingPhotos = JSON.parse(existingPhotosString) || [];

            if (photos.length > 0) {
                const fixedPhotos = photos.replace(/\\'/g, '"');
                const photoArray = JSON.parse(fixedPhotos);
                const watermarkPath = 'assets/images/watermark.png';
                const uploadedImages = await processBase64Images(photoArray, watermarkPath);

                fields["ufCrm22PhotoLinks"] = uploadedImages.length > 0 ? [...existingPhotos, ...uploadedImages] : [...existingPhotos];
            } else {
                fields["ufCrm22PhotoLinks"] = [...existingPhotos];
            }
        }

        const floorplans = document.getElementById('selectedFloorplan').value;
        const existingFloorplansString = document.getElementById('existingFloorplan').value;

        if (existingFloorplansString || floorplans.length > 0) {
            const existingFloorplans = JSON.parse(existingFloorplansString || "[]");

            if (floorplans.length > 0) {

                const fixedFloorplans = floorplans.replace(/\\'/g, '"');
                const floorplanArray = JSON.parse(fixedFloorplans);
                const watermarkPath = 'assets/images/watermark.png';
                const uploadedFloorplans = await processBase64Images(floorplanArray, watermarkPath);


                fields["ufCrm22FloorPlan"] = uploadedFloorplans.length > 0 ?
                    uploadedFloorplans[0] :
                    existingFloorplans[0] || null;
            } else {

                fields["ufCrm22FloorPlan"] = existingFloorplans[0] || null;
            }
        } else {

            fields["ufCrm22FloorPlan"] = null;
        }



        updateItem(1066, fields, <?php echo $_GET['id']; ?>);
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const property = await fetchProperty(<?php echo $_GET['id']; ?>);

        const containers = [{
                type: "photos",
                newLinks: [],
                selectedFiles: [],
                existingLinks: property['ufCrm22PhotoLinks'] || [],
                newPreviewContainer: document.getElementById('newPhotoPreviewContainer'),
                existingPreviewContainer: document.getElementById('existingPhotoPreviewContainer'),
                selectedInput: document.getElementById('selectedImages'),
                existingInput: document.getElementById('existingPhotos'),
            },
            {
                type: "floorplan",
                newLinks: [],
                selectedFiles: [],
                existingLinks: property['ufCrm22FloorPlan'] ? [property['ufCrm22FloorPlan']] : [],
                newPreviewContainer: document.getElementById('newFloorplanPreviewContainer'),
                existingPreviewContainer: document.getElementById('existingFloorplanPreviewContainer'),
                selectedInput: document.getElementById('selectedFloorplan'),
                existingInput: document.getElementById('existingFloorplan'),
            },
        ];

        containers.forEach((container) => {
            initializeContainer(container);
        });

        function initializeContainer(container) {
            // Add Swapy only if slots exist
            function addSwapy(previewContainer) {
                console.log('swappy');

                const slots = previewContainer.querySelectorAll('[data-swapy-slot]');
                if (slots.length === 0) {
                    console.warn(`No slots found in preview container:`, previewContainer);
                    return; // Skip Swapy initialization if no slots are present
                }

                const swapy = Swapy.createSwapy(previewContainer, {
                    animation: 'dynamic',
                    swapMode: 'drop',
                });

                swapy.onSwapEnd((event) => {
                    if (event.hasChanged) {
                        console.log('Swap end event:', event);

                        const updatedImageLinks = [];
                        event.slotItemMap.asMap.forEach((item) => {
                            const element = document.querySelector(`[data-swapy-item="${item}"]`);
                            updatedImageLinks.push(element.querySelector('img').src);
                        });

                        if (previewContainer === container.newPreviewContainer) {
                            container.newLinks = updatedImageLinks;
                            previewImages(container.newLinks, container.newPreviewContainer);
                        } else {
                            container.existingLinks = updatedImageLinks;
                            previewImages(container.existingLinks, container.existingPreviewContainer);
                        }
                    }
                });
            }

            // Update photo preview for selected files
            function updatePhotoPreview() {
                const promises = container.selectedFiles.map((file) => {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = function(e) {
                            container.newLinks.push(e.target.result);
                            resolve();
                        };
                    });
                });

                Promise.all(promises).then(() => {
                    previewImages(container.newLinks, container.newPreviewContainer);
                });
            }

            // Render images into the preview container
            function previewImages(imageLinks, previewContainer) {
                console.log('previewImages');

                previewContainer.innerHTML = '';

                if (imageLinks.length === 0) {
                    previewContainer.innerHTML = '<p class="text-muted">No images to display.</p>';
                    updateSelectedImagesInput();
                    return; // Exit if no images to preview
                }

                let row = document.createElement('div');
                row.classList.add('shuffle-row');

                imageLinks.forEach((imageSrc, i) => {
                    if (i % 3 === 0 && i !== 0) {
                        previewContainer.appendChild(row);
                        row = document.createElement('div');
                        row.classList.add('shuffle-row');
                    }

                    const slot = document.createElement('div');
                    slot.classList.add('slot');
                    slot.setAttribute('data-swapy-slot', i + 1);

                    const item = document.createElement('div');
                    item.classList.add('item');
                    item.setAttribute('data-swapy-item', String.fromCharCode(97 + i));

                    const image = document.createElement('div');
                    const img = document.createElement('img');
                    img.src = imageSrc;

                    image.appendChild(img);
                    item.appendChild(image);
                    slot.appendChild(item);

                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = "&times;";
                    removeBtn.classList.add(
                        "position-absolute",
                        "top-0",
                        "end-0",
                        "btn",
                        "btn-sm",
                        "btn-danger",
                        "m-1"
                    );
                    removeBtn.style.zIndex = "1";

                    removeBtn.addEventListener('click', function(event) {
                        // event.preventDefault();
                        event.stopImmediatePropagation();
                        console.log("removeBtn.onclick", i);

                        if (previewContainer === container.newPreviewContainer) {
                            container.newLinks.splice(i, 1);
                            previewImages(container.newLinks, container.newPreviewContainer);
                        } else {
                            container.existingLinks.splice(i, 1);
                            previewImages(container.existingLinks, container.existingPreviewContainer);
                        }
                    });

                    item.appendChild(removeBtn);
                    row.appendChild(slot);
                });

                previewContainer.appendChild(row);
                addSwapy(previewContainer);
                updateSelectedImagesInput();
            }

            // Update hidden input values for the selected and existing images
            function updateSelectedImagesInput() {
                console.log("updateSelectedImagesInput");

                container.selectedInput.value = JSON.stringify(container.newLinks);
                container.existingInput.value = JSON.stringify(container.existingLinks);
            }

            // Handle file selection
            document.getElementById(container.type).addEventListener('change', function(event) {
                const files = Array.from(event.target.files);
                container.selectedFiles = [];

                files.forEach((file) => {
                    if (file.size >= 10 * 1024 * 1024) {
                        // alert(`The file "${file.name}" is too large (10MB or greater). Please select a smaller file.`);
                        document.getElementById(`${container.type}Message`).classList.remove('hidden');
                        document.getElementById(`${container.type}Message`).textContent = `The file "${file.name}" is too large (10MB or greater). Please select a smaller file.`;
                    } else if (!container.selectedFiles.some((f) => f.name === file.name)) {
                        container.selectedFiles.push(file);
                        document.getElementById(`${container.type}Message`).classList.add('hidden');
                    }
                });

                updatePhotoPreview();
            });

            console.log('first');

            // Initialize preview with existing links
            previewImages(container.existingLinks, container.existingPreviewContainer);
        }
    });
</script>