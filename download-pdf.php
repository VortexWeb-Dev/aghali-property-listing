<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Aghali Property Listing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
        /* Add print-specific styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Add delay for development only */
        .delay-loading {
            opacity: 0;
            transition: opacity 0.5s;
        }

        .loaded {
            opacity: 1;
        }
    </style>
</head>

<body class="bg-gray-100 p-6 overflow-y-auto">
    <div id="loading-indicator" class="fixed top-0 left-0 w-full h-full bg-white flex items-center justify-center z-50">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-500 mb-4"></div>
            <p class="text-xl text-gray-700">Generating your property brochure...</p>
            <p class="text-sm text-gray-500 mt-2">Please wait, this may take a few moments</p>
        </div>
    </div>

    <div class="my-6 max-w-4xl mx-auto text-center">
        <button id="downloadBtn" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
            Download PDF
        </button>
        <button id="redirectBtn" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded ml-4">
            Back to Listings
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden delay-loading" id="brochure-card">
        <div class="relative bg-yellow-500">
            <div class="image-container" style="height: 24rem; background-color: #f3f4f6;">
                <img src="placeholder.jpeg" alt="Villa" class="w-full h-96 object-cover" id="imageLarge" />
            </div>
            <div class="absolute top-0 left-0 p-4 bg-yellow-500 text-white">
                <h1 class="text-3xl font-bold">AGhali Real Estate LLC</h1>
            </div>
        </div>
        <div class="p-6">
            <h2 class="text-4xl font-bold text-yellow-500 uppercase" id="title">
                Single Row Villa
            </h2>
            <p class="text-xl text-gray-700">
                Offered at
                <span class="font-bold text-yellow-500" id="priceText">AED <span id="price"></span></span>
            </p>
        </div>
        <div class="px-6">
            <h3 class="text-2xl font-bold" id="subtitle">Villa for rent in Al Furjan</h3>
            <p class="text-gray-700 mt-4" id="description">
                Brand-new 4-bedroom + maid's villa in Murooj East, Al Furjan.
                Single-row, park-facing, private garden, closed kitchen, spacious
                living area, 3 parking spaces. Gated community with pool, courts, play
                areas, parks, and retail. Near Sheikh Zayed Road for easy access to
                key Dubai areas.
            </p>
        </div>
        <div class="p-6">
            <h3 class="text-2xl font-bold text-gray-800">Our Facilities</h3>
            <ul class="list-disc list-inside text-gray-700 mt-2">
                <li id="propertyType">Villa</li>
                <li><span id="size"></span> sqft / <span id="sizeSqm"></span> sqm</li>
                <li><span id="bathrooms"></span> Bathrooms</li>
                <li><span id="bedrooms"></span> Bedrooms</li>
            </ul>
        </div>
        <div class="grid grid-cols-3 gap-4 px-6">
            <div class="image-container bg-gray-200 rounded-lg" style="height: 10rem;">
                <img src="placeholder.jpeg" alt="Interior 1" class="w-full h-40 object-cover rounded-lg" id="image1" />
            </div>
            <div class="image-container bg-gray-200 rounded-lg" style="height: 10rem;">
                <img src="placeholder.jpeg" alt="Interior 2" class="w-full h-40 object-cover rounded-lg" id="image2" />
            </div>
            <div class="image-container bg-gray-200 rounded-lg" style="height: 10rem;">
                <img src="placeholder.jpeg" alt="Interior 3" class="w-full h-40 object-cover rounded-lg" id="image3" />
            </div>
        </div>
        <div class="w-full bg-gray-200 p-6 mt-6 flex justify-between">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Contact Us</h3>
                <p class="text-xl text-yellow-500 font-bold mt-2">+971 52 110 0555</p>
                <p class="text-gray-700">Quick Respond</p>
            </div>
            <div class="flex items-center mt-4">
                <div class="image-container bg-gray-100 rounded-full" style="width: 4rem; height: 4rem;">
                    <img src="placeholder.jpeg" alt="Agent" class="w-16 h-16 rounded-full object-cover" id="agentImage" />
                </div>
                <div class="ml-4">
                    <p class="font-bold text-gray-800" id="agentName">Sachin Das</p>
                    <p class="text-gray-700" id="agentPhone">+971 50 591 5264</p>
                    <p class="text-gray-700" id="agentEmail">2Zi0d@example.com</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            let property = null;
            let loadedImagesCount = 0;
            let totalImagesToLoad = 5;
            let allImagesLoaded = false;

            async function downloadBrochure(filename) {
                try {
                    document.getElementById('loading-indicator').style.display = 'flex';

                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF("p", "mm", "a4");
                    const brochureElement = document.getElementById("brochure-card");

                    await new Promise(resolve => setTimeout(resolve, 1000));

                    const canvas = await html2canvas(brochureElement, {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        allowTaint: true,
                        backgroundColor: "white",
                        imageTimeout: 15000,
                        onclone: function(clonedDoc) {
                            const images = clonedDoc.querySelectorAll('img');
                            images.forEach(img => {
                                img.loading = 'eager';
                                img.style.display = 'block';
                            });
                        }
                    });

                    const imgData = canvas.toDataURL("image/jpeg", 0.95);
                    const imgWidth = 210;
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;

                    doc.addImage(imgData, "JPEG", 0, 0, imgWidth, imgHeight);
                    doc.save(`${filename}.pdf`);

                    document.getElementById('loading-indicator').style.display = 'none';
                    return true;
                } catch (error) {
                    console.error("Error generating brochure PDF:", error);
                    alert("Error generating PDF. Please try again.");
                    document.getElementById('loading-indicator').style.display = 'none';
                    return false;
                }
            }

            function sqftToSqm(sqft) {
                return Math.round(sqft / 0.092903);
            }

            function getPropertyType(propertyType) {
                const types = {
                    AP: "Apartment",
                    BW: "Bungalow",
                    CD: "Compound",
                    DX: "Duplex",
                    FF: "Full floor",
                    HF: "Half floor",
                    LP: "Land / Plot",
                    PH: "Penthouse",
                    TH: "Townhouse",
                    VH: "Villa",
                    WB: "Whole Building",
                    HA: "Short Term / Hotel Apartment",
                    LC: "Labor camp",
                    BU: "Bulk units",
                    WH: "Warehouse",
                    FA: "Factory",
                    OF: "Office space",
                    RE: "Retail",
                    SH: "Shop",
                    SR: "Show Room",
                    SA: "Staff Accommodation"
                };
                return types[propertyType] || "Type Not Available";
            }

            function getOfferingType(offeringType) {
                const types = {
                    "RS": "Sale",
                    "CS": "Sale",
                    "RR": "Rent",
                    "CR": "Rent",
                };
                return types[offeringType] || "Sale";
            }

            function formatPrice(price) {
                return new Intl.NumberFormat('en-US').format(price);
            }

            async function fetchPropertyDetails(propertyId) {
                try {
                    if (!propertyId) {
                        throw new Error("No property ID provided");
                    }

                    const response = await fetch(`https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma/crm.item.get?entityTypeId=1066&id=${propertyId}`);
                    const data = await response.json();

                    if (!data.result) throw new Error("Property not found.");

                    return data.result.item;
                } catch (error) {
                    console.warn("Using demo property data:", error);
                    return {
                        "ufCrm22TitleEn": "Demo Luxury Villa",
                        "ufCrm22BrochureDescription": "This beautiful 4-bedroom villa offers spacious living areas, a private garden, and modern amenities throughout. Located in a premium gated community with full facilities including swimming pools, gyms, and children's play areas. Walking distance to restaurants and shops. Easy access to major highways.",
                        "ufCrm22DescriptionEn": "This beautiful 4-bedroom villa offers spacious living areas, a private garden, and modern amenities throughout. Located in a premium gated community with full facilities including swimming pools, gyms, and children's play areas. Walking distance to restaurants and shops. Easy access to major highways.",
                        "ufCrm22Size": "2500",
                        "ufCrm22Bathroom": "4",
                        "ufCrm22Bedroom": "4",
                        "ufCrm22PropertyType": "VH",
                        "ufCrm22Price": "2500000",
                        "ufCrm22RentalPeriod": "Y",
                        "ufCrm22YearlyPrice": "250000",
                        "ufCrm22Community": "Palm Jumeirah",
                        "ufCrm22OfferingType": "RR",
                        "ufCrm22AgentName": "Alex Johnson",
                        "ufCrm22AgentPhone": "+971 50 123 4567",
                        "ufCrm22AgentEmail": "alex@aghali.example.com",
                        "ufCrm22PhotoLinks": [],
                        "ufCrm22AgentPhoto": ""
                    };
                }
            }

            async function loadImageAsDataURL(url) {
                return new Promise((resolve) => {
                    if (!url || url === "placeholder.jpeg") {
                        resolve("placeholder.jpeg");
                        return;
                    }

                    const img = new Image();
                    img.crossOrigin = "Anonymous";

                    img.onload = function() {
                        try {
                            const canvas = document.createElement("canvas");
                            canvas.width = img.width;
                            canvas.height = img.height;

                            const ctx = canvas.getContext("2d");
                            ctx.drawImage(img, 0, 0);

                            const dataURL = canvas.toDataURL("image/jpeg", 0.75);
                            resolve(dataURL);
                        } catch (error) {
                            console.warn(`Failed to convert image to data URL: ${url}`, error);
                            resolve("placeholder.jpeg");
                        }
                    };

                    img.onerror = function() {
                        console.warn(`Failed to load image: ${url}`);
                        resolve("placeholder.jpeg");
                    };

                    img.src = url;
                });
            }

            async function loadAndSetImage(imageElement, imageUrl) {
                try {
                    imageElement.src = "placeholder.jpeg";
                    const dataUrl = await loadImageAsDataURL(imageUrl);
                    imageElement.src = dataUrl;

                    imageElement.onload = function() {
                        loadedImagesCount++;
                        updateLoadingStatus();
                    };

                    return true;
                } catch (error) {
                    console.warn(`Error loading image: ${imageUrl}`, error);
                    loadedImagesCount++;
                    updateLoadingStatus();
                    return false;
                }
            }

            function updateLoadingStatus() {
                if (loadedImagesCount >= totalImagesToLoad) {
                    allImagesLoaded = true;
                    document.getElementById("brochure-card").classList.add("loaded");
                    document.getElementById('loading-indicator').style.display = 'none';
                }
            }

            async function setImages(imageLinks) {
                const imageElements = [
                    document.getElementById("imageLarge"),
                    document.getElementById("image1"),
                    document.getElementById("image2"),
                    document.getElementById("image3"),
                    document.getElementById("agentImage"),
                ];

                loadedImagesCount = 0;
                allImagesLoaded = false;

                const imagePromises = imageElements.map((img, index) => {
                    const imageUrl = imageLinks[index] || "placeholder.jpeg";
                    return loadAndSetImage(img, imageUrl);
                });

                await Promise.all(imagePromises);
                return true;
            }

            function getPriceText(property) {
                let priceText = `AED ${formatPrice(property["ufCrm22Price"] || 0)}`;

                if (property["ufCrm22RentalPeriod"] === 'Y') {
                    priceText = `AED ${formatPrice(property["ufCrm22YearlyPrice"] || property["ufCrm22Price"] || 0)} /year`;
                } else if (property["ufCrm22RentalPeriod"] === 'M') {
                    priceText = `AED ${formatPrice(property["ufCrm22MonthlyPrice"] || property["ufCrm22Price"] || 0)} /month`;
                } else if (property["ufCrm22RentalPeriod"] === 'D') {
                    priceText = `AED ${formatPrice(property["ufCrm22DailyPrice"] || property["ufCrm22Price"] || 0)} /day`;
                } else if (property["ufCrm22RentalPeriod"] === 'W') {
                    priceText = `AED ${formatPrice(property["ufCrm22WeeklyPrice"] || property["ufCrm22Price"] || 0)} /week`;
                }

                return priceText;
            }

            async function populateBrochureContent(property) {
                document.getElementById("title").textContent = property["ufCrm22TitleEn"] || "Property Title Not Available";
                document.getElementById("description").textContent = property["ufCrm22BrochureDescription"] ||
                    (property["ufCrm22DescriptionEn"]?.slice(0, 380) + "...") ||
                    "Description not available";
                document.getElementById("size").textContent = property["ufCrm22Size"] || "N/A";
                document.getElementById("sizeSqm").textContent = property["ufCrm22Size"] ? sqftToSqm(property["ufCrm22Size"]) : "N/A";
                document.getElementById("bathrooms").textContent = property["ufCrm22Bathroom"] || "N/A";
                document.getElementById("bedrooms").textContent = property["ufCrm22Bedroom"] || "N/A";
                document.getElementById("propertyType").textContent = getPropertyType(property["ufCrm22PropertyType"]);
                document.getElementById("price").textContent = formatPrice(property["ufCrm22Price"] || 0);
                document.getElementById("priceText").textContent = getPriceText(property);

                const subtitle = `${getPropertyType(property["ufCrm22PropertyType"])} for ${getOfferingType(property["ufCrm22OfferingType"])} in ${property["ufCrm22Community"] || "N/A"}`;
                document.getElementById("subtitle").textContent = subtitle;

                document.getElementById("agentName").textContent = property["ufCrm22AgentName"] || "Agent Not Available";
                document.getElementById("agentPhone").textContent = property["ufCrm22AgentPhone"] || "Phone Not Available";
                document.getElementById("agentEmail").textContent = property["ufCrm22AgentEmail"] || "Email Not Available";

                const imageLinks = Array.isArray(property["ufCrm22PhotoLinks"]) ? property["ufCrm22PhotoLinks"] : [];
                const agentPhoto = property["ufCrm22AgentPhoto"] || "placeholder.jpeg";
                const allImages = [...imageLinks.slice(0, 4), agentPhoto];

                await setImages(allImages);

                setTimeout(() => {
                    document.getElementById("brochure-card").classList.add("loaded");
                }, 500);

                return true;
            }

            function getPropertyIdFromUrl() {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get('id');
            }

            try {
                const propertyId = getPropertyIdFromUrl();
                property = await fetchPropertyDetails(propertyId);
                await populateBrochureContent(property);

                document.getElementById("downloadBtn").addEventListener("click", async function() {
                    await downloadBrochure(property["ufCrm22TitleEn"] || "Property_Brochure");
                });

                document.getElementById("redirectBtn").addEventListener("click", function() {
                    window.location.href = "index.php";
                });

                setTimeout(() => {
                    document.getElementById('loading-indicator').style.display = 'none';
                }, 1500);
            } catch (error) {
                console.error("Error in initialization process:", error);
                alert("Error loading property brochure. Please try again later.");
                document.getElementById('loading-indicator').style.display = 'none';
            }
        });
    </script>
</body>

</html>