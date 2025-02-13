<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="./node_modules/lodash/lodash.min.js"></script>
<script src="./node_modules/dropzone/dist/dropzone-min.js"></script>
<script src="./node_modules/preline/dist/preline.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="./node_modules/lodash/lodash.min.js"></script>
<script src="./node_modules/apexcharts/dist/apexcharts.min.js"></script>
<script src="./node_modules/preline/dist/helper-apexcharts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fabric@latest/dist/index.min.js"></script>
<script src="assets/js/script.js"></script>

<script>
    // Toggle Bayut and Dubizzle
    document.getElementById('toggle_bayut_dubizzle') && document.getElementById('toggle_bayut_dubizzle').addEventListener('change', function() {
        const isChecked = this.checked;
        document.getElementById('bayut_enable').checked = isChecked;
        document.getElementById('dubizzle_enable').checked = isChecked;
    });

    // Update character count
    function updateCharCount(countElement, length, maxLength) {
        titleCount = document.getElementById(countElement);
        titleCount.textContent = length;

        if (length >= maxLength) {
            titleCount.parentElement.classList.add('text-danger');
        } else {
            titleCount.parentElement.classList.remove('text-danger');
        }
    }

    // Parse and update location fields
    function updateLocationFields(location, type) {
        const locationParts = location.split('-');

        const city = locationParts[0].trim();
        const community = locationParts[1].trim();
        const subcommunity = locationParts[2].trim() || null;
        const building = locationParts[3].trim() || null;

        document.getElementById(`${type}_city`).value = city;
        document.getElementById(`${type}_community`).value = community;
        document.getElementById(`${type}_subcommunity`).value = subcommunity;
        document.getElementById(`${type}_building`).value = building;
    }

    // Update reference
    async function handleUpdateReference(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const propertyId = formData.get('propertyId');
        const newReference = formData.get('newReference');

        try {
            const response = await fetch('https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma//crm.item.update?entityTypeId=1066&id=' + propertyId + '&fields[ufCrm22ReferenceNumber]=' + newReference);
            const data = await response.json();
            location.reload();
        } catch (error) {
            console.error('Error updating reference:', error);
        }
    }

    // Format input date
    function formatInputDate(dateInput) {
        if (!dateInput) return null;

        const date = new Date(dateInput);

        if (isNaN(date.getTime())) return null;

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    // Get agent
    async function getAgent(agentId) {
        const response = await fetch(`https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma//crm.item.list?entityTypeId=1070&filter[ufCrm24AgentId]=${agentId}`);
        const data = await response.json();
        return data.result.items[0] || null;
    }

    // Handle action
    async function handleAction(action, propertyId, platform = null) {
        const baseUrl = 'https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma/';
        let apiUrl = '';
        let reloadRequired = true;

        switch (action) {
            case 'copyLink':
                const link = `https://lightgray-kudu-834713.hostingersite.com/property-listing-aghali/index.php?page=view-property&id=${propertyId}`;
                navigator.clipboard.writeText(link);
                alert('Link copied to clipboard.');
                reloadRequired = false;
                break;

            case 'downloadPDF':
                window.location.href = `download-pdf.php?id=${propertyId}`;
                reloadRequired = false;
                break;

            case 'duplicate':
                try {
                    const getUrl = `${baseUrl}/crm.item.get?entityTypeId=1066&id=${propertyId}&select[0]=id&select[1]=uf_*`;
                    const response = await fetch(getUrl, {
                        method: 'GET'
                    });
                    const data = await response.json();
                    const property = data.result.item;

                    let addUrl = `${baseUrl}/crm.item.add?entityTypeId=1066`;
                    for (const field in property) {
                        if (
                            field.startsWith('ufCrm22') &&
                            !['ufCrm22ReferenceNumber', 'ufCrm22TitleEn', 'ufCrm22Status', 'ufCrm22PhotoLinks', 'ufCrm22Documents', 'ufCrm22Notes'].includes(field)
                        ) {
                            addUrl += `&fields[${field}]=${encodeURIComponent(property[field])}`;
                        }
                    }

                    if (property['ufCrm22PhotoLinks']) {
                        property['ufCrm22PhotoLinks'].forEach((photoLink, index) => {
                            addUrl += `&fields[ufCrm22PhotoLinks][${index}]=${encodeURIComponent(photoLink)}`;
                        });
                    }

                    if (property['ufCrm22Documents']) {
                        property['ufCrm22Documents'].forEach((document, index) => {
                            addUrl += `&fields[ufCrm22Documents][${index}]=${encodeURIComponent(document)}`;
                        });
                    }

                    if (property['ufCrm22Notes']) {
                        property['ufCrm22Notes'].forEach((note, index) => {
                            addUrl += `&fields[ufCrm22Notes][${index}]=${encodeURIComponent(note)}`;
                        });
                    }

                    addUrl += `&fields[ufCrm22TitleEn]=${encodeURIComponent(property.ufCrm22TitleEn + ' (Duplicate)')}`;
                    addUrl += `&fields[ufCrm22ReferenceNumber]=${encodeURIComponent(property.ufCrm22ReferenceNumber) + '-duplicate'}`;
                    addUrl += `&fields[ufCrm22Status]=DRAFT`;

                    await fetch(addUrl, {
                        method: 'GET'
                    });
                } catch (error) {
                    console.error('Error duplicating property:', error);
                }
                break;

            case 'publish':
                apiUrl = `${baseUrl}/crm.item.update?entityTypeId=1066&id=${propertyId}&fields[ufCrm22Status]=PUBLISHED`;
                if (platform) {
                    apiUrl += `&fields[ufCrm22${platform.charAt(0).toUpperCase() + platform.slice(1)}Enable]=Y`;
                } else {
                    apiUrl += `&fields[ufCrm22PfEnable]=Y&fields[ufCrm22BayutEnable]=Y&fields[ufCrm22DubizzleEnable]=Y&fields[ufCrm22WebsiteEnable]=Y&fields[ufCrm22MetahomesEnable]=Y&fields[ufCrm22Status]=PUBLISHED`;
                }
                break;

            case 'unpublish':
                apiUrl = `${baseUrl}/crm.item.update?entityTypeId=1066&id=${propertyId}`;
                if (platform) {
                    apiUrl += `&fields[ufCrm22${platform.charAt(0).toUpperCase() + platform.slice(1)}Enable]=N`;
                } else {
                    apiUrl += `&fields[ufCrm22PfEnable]=N&fields[ufCrm22BayutEnable]=N&fields[ufCrm22DubizzleEnable]=N&fields[ufCrm22WebsiteEnable]=N&fields[ufCrm22MetahomesEnable]=N&fields[ufCrm22Status]=UNPUBLISHED`;
                }
                break;

            case 'archive':
                if (confirm('Are you sure you want to archive this property?')) {
                    apiUrl = `${baseUrl}/crm.item.update?entityTypeId=1066&id=${propertyId}&fields[ufCrm22Status]=ARCHIVED`;
                } else {
                    reloadRequired = false;
                }
                break;

            case 'delete':
                if (confirm('Are you sure you want to delete this property?')) {
                    try {
                        // First get property details to find image URLs
                        const getPropertyUrl = `${baseUrl}/crm.item.get?entityTypeId=1066&id=${propertyId}`;
                        const propertyResponse = await fetch(getPropertyUrl);
                        const propertyData = await propertyResponse.json();

                        if (propertyData.result && propertyData.result.item) {
                            const property = propertyData.result.item;
                            console.log('Property data for deletion:', property);

                            // Delete images from S3
                            if (property.ufCrm22PhotoLinks && Array.isArray(property.ufCrm22PhotoLinks)) {
                                console.log('Found photo links:', property.ufCrm22PhotoLinks);
                                for (const imageUrl of property.ufCrm22PhotoLinks) {
                                    try {
                                        console.log('Attempting to delete image:', imageUrl);
                                        const response = await fetch('./delete-s3-object.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                fileUrl: imageUrl
                                            })
                                        });
                                        const result = await response.json();
                                        console.log('Delete response:', result);
                                        if (!result.success) {
                                            console.error(`Failed to delete image: ${result.error}`);
                                        }
                                    } catch (error) {
                                        console.error(`Error deleting S3 object: ${imageUrl}`, error);
                                    }
                                }
                            }

                            // Delete floorplan from S3 if exists
                            if (property.ufCrm22FloorPlan) {
                                try {
                                    console.log('Attempting to delete floorplan:', property.ufCrm22FloorPlan);
                                    const response = await fetch('./delete-s3-object.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            fileUrl: property.ufCrm22FloorPlan
                                        })
                                    });
                                    const result = await response.json();
                                    console.log('Floorplan delete response:', result);
                                    if (!result.success) {
                                        console.error(`Failed to delete floorplan: ${result.error}`);
                                    }
                                } catch (error) {
                                    console.error(`Error deleting S3 floorplan: ${property.ufCrm22FloorPlan}`, error);
                                }
                            }

                            // Delete documents from S3
                            if (property.ufCrm22Documents && Array.isArray(property.ufCrm22Documents)) {
                                console.log('Found documents:', property.ufCrm22Documents);
                                for (const docUrl of property.ufCrm22Documents) {
                                    try {
                                        console.log('Attempting to delete document:', docUrl);
                                        const response = await fetch('./delete-s3-object.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                fileUrl: docUrl
                                            })
                                        });
                                        const result = await response.json();
                                        console.log('Delete response:', result);
                                        if (!result.success) {
                                            console.error(`Failed to delete document: ${result.error}`);
                                        }
                                    } catch (error) {
                                        console.error(`Error deleting S3 document: ${docUrl}`, error);
                                    }
                                }
                            }
                        }

                        // Now delete the property from CRM
                        apiUrl = `${baseUrl}/crm.item.delete?entityTypeId=1066&id=${propertyId}`;
                    } catch (error) {
                        console.error('Error in delete process:', error);
                        reloadRequired = false;
                    }
                } else {
                    reloadRequired = false;
                }
                break;

            default:
                console.error('Invalid action:', action);
                reloadRequired = false;
        }

        if (apiUrl) {
            try {
                await fetch(apiUrl, {
                    method: 'GET'
                });
            } catch (error) {
                console.error(`Error executing ${action}:`, error);
            }
        }

        if (reloadRequired) {
            location.reload();
        }
    }

    // Bulk action
    async function handleBulkAction(action, platform) {
        const checkboxes = document.querySelectorAll('input[name="property_ids[]"]:checked');
        const propertyIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        if (propertyIds.length === 0) {
            alert('Please select at least one property.');
            return;
        }

        if (confirm(`Are you sure you want to ${action} the selected properties?`)) {
            try {
                const baseUrl = 'https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma/';
                const apiUrl = `${baseUrl}/crm.item.${action === 'delete' ? 'delete' : 'update'}?entityTypeId=1066`;

                const platformFieldMapping = {
                    pf: 'ufCrm22PfEnable',
                    bayut: 'ufCrm22BayutEnable',
                    dubizzle: 'ufCrm22DubizzleEnable',
                    website: 'ufCrm22WebsiteEnable',
                    metahomes: 'ufCrm22MetahomesEnable'
                };

                // If action is delete, first get all property details to find image URLs
                if (action === 'delete') {
                    for (const propertyId of propertyIds) {
                        try {
                            // Get property details to find image URLs
                            const getPropertyUrl = `${baseUrl}/crm.item.get?entityTypeId=1066&id=${propertyId}`;
                            const propertyResponse = await fetch(getPropertyUrl);
                            const propertyData = await propertyResponse.json();
                            console.log('Property data:', propertyData);
                            if (propertyData.result && propertyData.result.item) {
                                const property = propertyData.result.item;

                                // Delete images from S3
                                if (property.ufCrm22PhotoLinks && Array.isArray(property.ufCrm22PhotoLinks)) {
                                    for (const imageUrl of property.ufCrm22PhotoLinks) {
                                        try {
                                            await fetch('./delete-s3-object.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                },
                                                body: JSON.stringify({
                                                    fileUrl: imageUrl
                                                })
                                            });
                                        } catch (error) {
                                            console.error(`Error deleting S3 object: ${imageUrl}`, error);
                                        }
                                    }
                                }

                                // Delete floorplan from S3 if exists
                                if (property.ufCrm22FloorPlan) {
                                    try {
                                        await fetch('./delete-s3-object.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                fileUrl: property.ufCrm22FloorPlan
                                            })
                                        });
                                    } catch (error) {
                                        console.error(`Error deleting S3 floorplan: ${property.ufCrm22FloorPlan}`, error);
                                    }
                                }

                                // Delete documents from S3
                                if (property.ufCrm22Documents && Array.isArray(property.ufCrm22Documents)) {
                                    for (const docUrl of property.ufCrm22Documents) {
                                        try {
                                            await fetch('./delete-s3-object.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                },
                                                body: JSON.stringify({
                                                    fileUrl: docUrl
                                                })
                                            });
                                        } catch (error) {
                                            console.error(`Error deleting S3 document: ${docUrl}`, error);
                                        }
                                    }
                                }
                            }
                        } catch (error) {
                            console.error(`Error getting property details for deletion: ${propertyId}`, error);
                        }
                    }
                }

                const requests = propertyIds.map(propertyId => {
                    let url = `${apiUrl}&id=${propertyId}`;

                    if (action === 'publish') {
                        url += '&fields[ufCrm22Status]=PUBLISHED';

                        if (platformFieldMapping[platform]) {
                            url += `&fields[${platformFieldMapping[platform]}]=Y`;
                        } else {
                            url += `&fields[ufCrm22PfEnable]=Y&fields[ufCrm22BayutEnable]=Y&fields[ufCrm22DubizzleEnable]=Y&fields[ufCrm22WebsiteEnable]=Y&fields[ufCrm22MetahomesEnable]=Y`;
                        }
                    } else if (action === 'unpublish') {
                        if (platformFieldMapping[platform]) {
                            url += `&fields[${platformFieldMapping[platform]}]=N`;
                        } else {
                            url += `&fields[ufCrm22PfEnable]=N&fields[ufCrm22BayutEnable]=N&fields[ufCrm22DubizzleEnable]=N&fields[ufCrm22WebsiteEnable]=N&fields[ufCrm22MetahomesEnable]=N&fields[ufCrm22Status]=UNPUBLISHED`;
                        }
                    } else if (action === 'archive') {
                        url += '&fields[ufCrm22Status]=ARCHIVED';
                    }

                    return fetch(url, {
                            method: 'GET'
                        })
                        .then(response => response.json())
                        .then(data => {})
                        .catch(error => {
                            console.error(`Error updating property ${propertyId}:`, error);
                        });
                });

                // Wait for all requests to finish
                await Promise.all(requests);

                location.reload();
            } catch (error) {
                console.error('Error handling bulk action:', error);
            }
        }
    }

    // Function to add watermark to the image
    function addWatermark(imageElement, watermarkImagePath) {
        return new Promise((resolve, reject) => {
            const watermarkImage = new Image();
            watermarkImage.src = watermarkImagePath;

            watermarkImage.onload = function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const width = imageElement.width;
                const height = imageElement.height;

                canvas.width = width;
                canvas.height = height;

                ctx.drawImage(imageElement, 0, 0, width, height);

                const watermarkAspect = watermarkImage.width / watermarkImage.height;
                const imageAspect = width / height;

                let watermarkWidth, watermarkHeight;

                if (watermarkAspect > imageAspect) {
                    watermarkWidth = width * 0.2;
                    watermarkHeight = watermarkWidth / watermarkAspect;
                } else {
                    watermarkHeight = height * 0.2;
                    watermarkWidth = watermarkHeight * watermarkAspect;
                }

                const xPosition = (width - watermarkWidth) / 2;
                const yPosition = (height - watermarkHeight) / 2;

                ctx.globalAlpha = 0.5; // 50% transparency
                ctx.drawImage(watermarkImage, xPosition, yPosition, watermarkWidth, watermarkHeight);

                ctx.globalAlpha = 1.0;

                const watermarkedImage = canvas.toDataURL('image/jpeg', 0.8);
                resolve(watermarkedImage);
            };

            watermarkImage.onerror = function() {
                reject('Failed to load watermark image.');
            };
        });
    }

    // Function to add watermark text to the image
    function addWatermarkText(imageElement, watermarkText) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const width = imageElement.width;
            const height = imageElement.height;

            canvas.width = width;
            canvas.height = height;

            ctx.drawImage(imageElement, 0, 0, width, height);

            // Set the watermark text properties
            ctx.font = '360px Arial'; // You can adjust the font size here
            ctx.fillStyle = 'rgba(255, 255, 255, 0.6)'; // White color with 50% transparency
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            // Add the watermark text to the image (centered)
            ctx.fillText(watermarkText, width / 2, height / 2);

            // Convert the image to JPEG with reduced quality (optional)
            const watermarkedImage = canvas.toDataURL('image/jpeg', 0.7); // Adjust quality as needed
            resolve(watermarkedImage);
        });
    }

    // Function to upload a file
    function uploadFile(file, isDocument = false) {
        const formData = new FormData();
        formData.append('file', file);

        if (isDocument) {
            formData.append('isDocument', 'true');
        }

        return fetch('upload-file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.url) {
                    return data.url;
                } else {
                    console.error('Error uploading file (PHP backend):', data.error);
                    return null;
                }
            })
            .catch((error) => {
                console.error("Error uploading file:", error);
                return null;
            });
    }

    // Process base64 images
    async function processBase64Images(base64Images, watermarkPath) {
        const photoPaths = [];
        const TARGET_ASPECT_RATIO = 4 / 3;

        function resizeToAspectRatio(image) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            let newWidth = image.width;
            let newHeight = image.height;
            const currentAspectRatio = image.width / image.height;

            if (currentAspectRatio > TARGET_ASPECT_RATIO) {
                newWidth = image.height * TARGET_ASPECT_RATIO;
                newHeight = image.height;
            } else if (currentAspectRatio < TARGET_ASPECT_RATIO) {
                newWidth = image.width;
                newHeight = image.width / TARGET_ASPECT_RATIO;
            }

            canvas.width = newWidth;
            canvas.height = newHeight;

            const xOffset = (newWidth - image.width) / 2;
            const yOffset = (newHeight - image.height) / 2;

            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, newWidth, newHeight);

            ctx.drawImage(
                image,
                xOffset,
                yOffset,
                image.width,
                image.height
            );

            return canvas.toDataURL();
        }

        for (const base64Image of base64Images) {
            const regex = /^data:image\/(\w+);base64,/;
            const matches = base64Image.match(regex);

            if (matches) {
                const base64Data = base64Image.replace(regex, '');
                const imageData = atob(base64Data);

                const blob = new Blob([new Uint8Array(imageData.split('').map(c => c.charCodeAt(0)))], {
                    type: `image/${matches[1]}`,
                });
                const imageUrl = URL.createObjectURL(blob);

                const imageElement = new Image();
                imageElement.src = imageUrl;

                await new Promise((resolve, reject) => {
                    imageElement.onload = async () => {
                        try {
                            const resizedDataUrl = resizeToAspectRatio(imageElement);

                            const resizedImage = new Image();
                            resizedImage.src = resizedDataUrl;

                            await new Promise(resolve => {
                                resizedImage.onload = resolve;
                            });

                            const watermarkedDataUrl = await addWatermark(resizedImage, watermarkPath);

                            const watermarkedBlob = dataURLToBlob(watermarkedDataUrl);

                            const uploadedUrl = await uploadFile(watermarkedBlob);

                            if (uploadedUrl) {
                                photoPaths.push(uploadedUrl);
                            } else {
                                console.error('Error uploading photo from base64 data');
                            }

                            resolve();
                        } catch (error) {
                            console.error('Error processing watermarking or uploading:', error);
                            reject(error);
                        } finally {
                            URL.revokeObjectURL(imageUrl);
                        }
                    };

                    imageElement.onerror = (error) => {
                        console.error('Failed to load image from URL:', error);
                        reject(error);
                    };
                });
            } else {
                console.error('Invalid base64 image data');
            }
        }

        return photoPaths;
    }

    // Function to convert data URL to Blob
    function dataURLToBlob(dataURL) {
        const byteString = atob(dataURL.split(',')[1]);
        const arrayBuffer = new ArrayBuffer(byteString.length);
        const uintArray = new Uint8Array(arrayBuffer);
        for (let i = 0; i < byteString.length; i++) {
            uintArray[i] = byteString.charCodeAt(i);
        }
        return new Blob([uintArray], {
            type: 'image/png'
        });
    }

    // Function to fetch a property
    async function fetchProperty(id) {
        const url = `https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma//crm.item.get?entityTypeId=1066&id=${id}`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.result && data.result.item) {
            const property = data.result.item;

            // Management
            document.getElementById('reference').value = property.ufCrm22ReferenceNumber;
            document.getElementById('landlord_name').value = property.ufCrm22LandlordName;
            document.getElementById('landlord_email').value = property.ufCrm22LandlordEmail;
            document.getElementById('landlord_phone').value = property.ufCrm22LandlordContact;
            if (document.getElementById('availability')) {
                Array.from(document.getElementById('availability').options).forEach(option => {
                    if (option.value == property.ufCrm22Availability) option.selected = true;
                });
            }
            if (document.getElementById('available_from')) {
                document.getElementById('available_from').value = formatInputDate(property.ufCrm22AvailableFrom);
            }
            if (document.getElementById('contract_expiry')) {
                document.getElementById('contract_expiry').value = formatInputDate(property.ufCrm22ContractExpiryDate);
            }

            // Specifications
            document.getElementById('title_deed').value = property.title;
            document.getElementById('size').value = property.ufCrm22Size;
            document.getElementById('unit_no').value = property.ufCrm22UnitNo;
            document.getElementById('bathrooms').value = property.ufCrm22Bathroom;
            document.getElementById('parkings').value = property.ufCrm22Parking;
            document.getElementById('total_plot_size').value = property.ufCrm22TotalPlotSize;
            document.getElementById('lot_size').value = property.ufCrm22LotSize;
            document.getElementById('buildup_area').value = property.ufCrm22BuildupArea;
            document.getElementById('layout_type').value = property.ufCrm22LayoutType;
            document.getElementById('project_name').value = property.ufCrm22ProjectName;
            document.getElementById('build_year').value = property.ufCrm22BuildYear;
            Array.from(document.getElementById('property_type').options).forEach(option => {
                if (option.value === property.ufCrm22PropertyType) option.selected = true;
            });
            Array.from(document.getElementById('offering_type').options).forEach(option => {
                if (option.value === property.ufCrm22OfferingType) option.selected = true;
            });
            Array.from(document.getElementById('bedrooms').options).forEach(option => {
                if (option.value == property.ufCrm22Bedroom) option.selected = true;
            });
            Array.from(document.getElementById('furnished').options).forEach(option => {
                if (option.value == property.ufCrm22Furnished) option.selected = true;
            });
            Array.from(document.getElementById('project_status').options).forEach(option => {
                if (option.value == property.ufCrm22ProjectStatus) option.selected = true;
            });
            Array.from(document.getElementById('sale_type').options).forEach(option => {
                if (option.value == property.ufCrm22SaleType) option.selected = true;
            });
            Array.from(document.getElementById('ownership').options).forEach(option => {
                if (option.value == property.ufCrm22Ownership) option.selected = true;
            });

            // Property Permit
            if (document.getElementById('rera_permit_number')) {
                document.getElementById('rera_permit_number').value = property.ufCrm22ReraPermitNumber
            }
            if (document.getElementById('dtcm_permit_number')) {
                document.getElementById('dtcm_permit_number').value = property.ufCrm22DtcmPermitNumber
            }
            if (document.getElementById('rera_issue_date')) {
                document.getElementById('rera_issue_date').value = formatInputDate(property.ufCrm22ReraPermitIssueDate);
            }
            if (document.getElementById('rera_expiration_date')) {
                document.getElementById('rera_expiration_date').value = formatInputDate(property.ufCrm22ReraPermitExpirationDate);
            }

            // Pricing
            document.getElementById('price').value = property.ufCrm22Price;
            document.getElementById('payment_method').value = property.ufCrm22PaymentMethod;
            document.getElementById('downpayment_price').value = property.ufCrm22DownPaymentPrice;
            document.getElementById('service_charge').value = property.ufCrm22ServiceCharge;
            property.ufCrm22HidePrice == "Y" ? document.getElementById('hide_price').checked = true : document.getElementById('hide_price').checked = false;
            Array.from(document.getElementById('rental_period').options).forEach(option => {
                if (option.value == property.ufCrm22RentalPeriod) option.selected = true;
            });
            Array.from(document.getElementById('cheques').options).forEach(option => {
                if (option.value == property.ufCrm22NoOfCheques) option.selected = true;
            });
            Array.from(document.getElementById('financial_status').options).forEach(option => {
                if (option.value == property.ufCrm22FinancialStatus) option.selected = true;
            });

            // Title and Description
            document.getElementById('title_en').value = property.ufCrm22TitleEn;
            document.getElementById('description_en').textContent = property.ufCrm22DescriptionEn;
            if (document.getElementById('title_ar')) {
                document.getElementById('title_ar').value = property.ufCrm22TitleAr;
            }
            if (document.getElementById('description_ar')) {
                document.getElementById('description_ar').textContent = property.ufCrm22DescriptionAr;
            }
            if (document.getElementById('brochure_description_1')) {
                document.getElementById('brochure_description_1').textContent = property.ufCrm22BrochureDescription;
            }
            if (document.getElementById('brochure_description_2')) {
                document.getElementById('brochure_description_2').textContent = property.ufCrm_22_BROCHUREDESCRIPTION2;
            }

            document.getElementById('titleEnCount').textContent = document.getElementById('title_en').value.length;
            document.getElementById('descriptionEnCount').textContent = document.getElementById('description_en').textContent.length;
            if (document.getElementById('titleArCount')) {
                document.getElementById('titleArCount').textContent = document.getElementById('title_ar').value.length;
            }
            if (document.getElementById('descriptionArCount')) {
                document.getElementById('descriptionArCount').textContent = document.getElementById('description_ar').textContent.length;
            }
            if (document.getElementById('brochureDescription1Count')) {
                document.getElementById('brochureDescription1Count').textContent = document.getElementById('brochure_description_1').textContent.length;
            }
            if (document.getElementById('brochureDescription2Count')) {
                document.getElementById('brochureDescription2Count').textContent = document.getElementById('brochure_description_2').textContent.length;
            }

            // Location
            document.getElementById('pf_location').value = property.ufCrm22Location;
            document.getElementById('pf_city').value = property.ufCrm22City;
            document.getElementById('pf_community').value = property.ufCrm22Community;
            document.getElementById('pf_subcommunity').value = property.ufCrm22SubCommunity;
            document.getElementById('pf_building').value = property.ufCrm22Tower;
            document.getElementById('bayut_location').value = property.ufCrm22BayutLocation;
            document.getElementById('bayut_city').value = property.ufCrm22BayutCity;
            document.getElementById('bayut_community').value = property.ufCrm22BayutCommunity;
            document.getElementById('bayut_subcommunity').value = property.ufCrm22BayutSubCommunity;
            document.getElementById('bayut_building').value = property.ufCrm22BayutTower;

            document.getElementById('latitude').value = property.ufCrm22Latitude;
            document.getElementById('longitude').value = property.ufCrm22Longitude;

            // Photos and Videos
            document.getElementById('video_tour_url').value = property.ufCrm22VideoTourUrl;
            document.getElementById('360_view_url').value = property.ufCrm_22_360_VIEW_URL;
            document.getElementById('qr_code_url').value = property.ufCrm22QrCodePropertyBooster;
            // Photos
            // Floor Plan

            // Portals
            property.ufCrm22PfEnable == "Y" ? document.getElementById('pf_enable').checked = true : document.getElementById('pf_enable').checked = false;
            property.ufCrm22BayutEnable == "Y" ? document.getElementById('bayut_enable').checked = true : document.getElementById('bayut_enable').checked = false;
            property.ufCrm22DubizzleEnable == "Y" ? document.getElementById('dubizzle_enable').checked = true : document.getElementById('dubizzle_enable').checked = false;
            property.ufCrm22WebsiteEnable == "Y" ? document.getElementById('website_enable').checked = true : document.getElementById('website_enable').checked = false;
            property.ufCrm22MetahomesEnable == "Y" ? document.getElementById('metahomes_enable').checked = true : document.getElementById('metahomes_enable').checked = false;
            if (document.getElementById('dubizzle_enable').checked && document.getElementById('bayut_enable').value) {
                toggle_bayut_dubizzle.checked = true;
            }

            switch (property.ufCrm22Status) {
                case 'PUBLISHED':
                    document.getElementById('publish').checked = true;
                    break;
                case 'UNPUBLISHED':
                    document.getElementById('unpublish').checked = true;
                    break;
                case 'LIVE':
                    document.getElementById('live').checked = true;
                    break;
                case 'DRAFT':
                    document.getElementById('draft').checked = true;
                    break;
                case 'POCKET':
                    document.getElementById('pocket').checked = true;
                    break;
            }

            function ensureOptionExistsAndSelect(selectElementId, value, label) {
                const selectElement = document.getElementById(selectElementId);
                const existingOption = document.querySelector(`#${selectElementId} option[value="${value}"]`);

                if (!existingOption) {
                    const newOption = document.createElement('option');
                    newOption.value = value;
                    newOption.textContent = label || 'Unknown Option';
                    newOption.selected = true;
                    selectElement.appendChild(newOption);
                } else {
                    existingOption.selected = true;
                }
            }

            ensureOptionExistsAndSelect('listing_agent', property.ufCrm22AgentId, property.ufCrm22AgentName);
            ensureOptionExistsAndSelect('listing_owner', property.ufCrm22ListingOwner, property.ufCrm22ListingOwner);
            ensureOptionExistsAndSelect('developer', property.ufCrm22Developers, property.ufCrm22Developers);

            function addExistingNote(note) {
                const li = document.createElement("li");
                li.classList.add("text-gray-700", "p-2", "flex", "justify-between", "items-center", "mb-2", "bg-gray-100", "rounded-md");

                li.innerHTML = `
                    ${note} 
                    <button class="text-red-500 hover:text-red-700" onclick="removeNote(this)">×</button>
                `;

                document.getElementById("notesList").appendChild(li);
                updateNotesInput();
            }

            if (property.ufCrm22Notes.length > 0) {
                property.ufCrm22Notes.forEach(note => {
                    addExistingNote(note);
                });
            }

            return property;

        } else {
            console.error('Invalid property data:', data);
            document.getElementById('property-details').textContent = 'Failed to load property details.';
        }
    }

    // Function to check if any property is selected
    function isPropertySelected() {
        var checkboxes = document.querySelectorAll('input[name="property_ids[]"]:checked');
        var propertyIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        return propertyIds && propertyIds.length > 0;
    }

    // Function to select and add properties to agent transfer form
    function selectAndAddPropertiesToAgentTransfer() {
        var checkboxes = document.querySelectorAll('input[name="property_ids[]"]:checked');
        var propertyIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        if (!isPropertySelected()) {
            return alert('Please select at least one property.');
        }

        document.getElementById('transferAgentPropertyIds').value = propertyIds.join(',');

        const agentModal = new bootstrap.Modal(document.getElementById('transferAgentModal'));
        agentModal.show();
    }

    // Function to select and add properties to owner transfer form
    function selectAndAddPropertiesToOwnerTransfer() {
        var checkboxes = document.querySelectorAll('input[name="property_ids[]"]:checked');
        var propertyIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        if (!isPropertySelected()) {
            return alert('Please select at least one property.');
        }

        document.getElementById('transferOwnerPropertyIds').value = propertyIds.join(',');


        const ownerModal = new bootstrap.Modal(document.getElementById('transferOwnerModal'));
        ownerModal.show();
    }

    // Function to calculate square meters
    function sqftToSqm(sqft) {
        const sqm = sqft * 0.092903;
        return parseFloat(sqm.toFixed(2));
    }
</script>

</body>

</html>