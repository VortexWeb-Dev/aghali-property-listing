<div class="w-4/5 mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold text-gray-800">Developers</h1>
        <button type="button" onclick="toggleModal(true)" class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 text-gray-500 hover:border-blue-600 hover:text-blue-600 focus:outline-none focus:border-blue-600 focus:text-blue-600 disabled:opacity-50 disabled:pointer-events-none ">
            Add Developer
        </button>
    </div>

    <!-- Loading -->
    <?php include_once('views/components/loading.php'); ?>

    <div id="developer-table" class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody id="developer-list" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php include 'views/components/pagination.php'; ?>

    <!-- Modals -->
    <?php include 'views/modals/add-developer.php'; ?>
</div>


<script>
    let currentPage = 1;
    const pageSize = 50;
    let totalPages = 0;

    async function fetchDevelopers(page = 1) {
        const baseUrl = 'https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma/';
        const entityTypeId = 1088;
        const apiUrl = `${baseUrl}/crm.item.list?entityTypeId=${entityTypeId}&order[id]=desc&select[0]=id&select[1]=ufCrm32DeveloperName&start=${(page - 1) * pageSize}`;

        const loading = document.getElementById('loading');
        const developerTable = document.getElementById('developer-table');
        const developerList = document.getElementById('developer-list');
        const pagination = document.getElementById('pagination');
        const prevPage = document.getElementById('prevPage');
        const nextPage = document.getElementById('nextPage');
        const pageInfo = document.getElementById('pageInfo');

        try {
            loading.classList.remove('hidden');
            developerTable.classList.add('hidden');
            pagination.classList.add('hidden');


            const response = await fetch(apiUrl, {
                method: 'GET'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            const developers = data.result?.items || [];
            const totalCount = data.total || 0;

            totalPages = Math.ceil(totalCount / pageSize);

            prevPage.disabled = page === 1;
            nextPage.disabled = page === totalPages || totalPages === 0;
            pageInfo.textContent = `Page ${page} of ${totalPages}`;

            developerList.innerHTML = developers
                .map(
                    (developer) => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">${developer.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">${developer.ufCrm32DeveloperName || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                        <button onClick="deleteDeveloper(${developer.id})" type="button" class="inline-flex items-center gap-x-2 text-sm font-semibold text-blue-600 hover:text-blue-800 focus:outline-none focus:text-blue-800 disabled:opacity-50 disabled:pointer-events-none">Delete</button>
                    </td>
                </tr>`
                )
                .join('');

            return developers;
        } catch (error) {
            console.error('Error fetching developers:', error);
            return [];
        } finally {
            loading.classList.add('hidden');
            developerTable.classList.remove('hidden');
            pagination.classList.remove('hidden');

        }
    }

    function changePage(direction) {
        if (direction === 'prev' && currentPage > 1) {
            currentPage--;
        } else if (direction === 'next' && currentPage < totalPages) {
            currentPage++;
        }
        fetchDevelopers(currentPage);
    }

    async function deleteDeveloper(developerId) {
        const baseUrl = 'https://aghali.bitrix24.com/rest/44/3cb982q5ext2yuma/';
        const apiUrl = `${baseUrl}/crm.item.delete?entityTypeId=1088&id=${developerId}`;

        try {
            if (confirm('Are you sure you want to delete this developer?')) {
                await fetch(apiUrl, {
                    method: 'GET'
                });
                location.reload();
            }
        } catch (error) {
            console.error('Error deleting developer:', error);
        }
    }

    fetchDevelopers(currentPage);
</script>