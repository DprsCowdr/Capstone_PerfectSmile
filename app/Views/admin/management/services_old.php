<?php 
$title = 'Services Management - Perfect Smile';
$content = '
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">
        <i class="fas fa-cogs mr-3 text-blue-600"></i>Services Management
    </h1>
    <p class="text-gray-600">Manage dental services and procedures</p>
</div>

<!-- Add Service Button -->
<div class="mb-6">
    <button onclick="openAddServiceModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
        <i class="fas fa-plus mr-2"></i>Add New Service
    </button>
</div>

<!-- Services Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Services List</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="servicesTableBody">
                ' . (empty($services) ? '
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No services found. <a href="#" onclick="openAddServiceModal()" class="text-blue-600 hover:text-blue-800">Add the first service</a>
                    </td>
                </tr>
                ' : '') . '
                ';

if (!empty($services)) {
    foreach ($services as $service) {
        $content .= '
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">' . htmlspecialchars($service['name']) . '</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">' . htmlspecialchars($service['description'] ?? '') . '</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-green-600">$' . number_format($service['price'], 2) . '</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ' . (isset($service['created_at']) && $service['created_at'] ? date('M j, Y', strtotime($service['created_at'])) : 'N/A') . '
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="editService(' . $service['id'] . ')" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="deleteService(' . $service['id'] . ', \'' . htmlspecialchars($service['name']) . '\')" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>';
    }
}

$content .= '
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Service Modal -->
<div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Add New Service</h3>
            <button onclick="closeServiceModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="serviceForm">
            <input type="hidden" id="serviceId" value="">
            
            <div class="mb-4">
                <label for="serviceName" class="block text-sm font-medium text-gray-700 mb-2">Service Name *</label>
                <input type="text" id="serviceName" name="name" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="mb-4">
                <label for="serviceDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="serviceDescription" name="description" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="mb-6">
                <label for="servicePrice" class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" id="servicePrice" name="price" step="0.01" min="0" required 
                           class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeServiceModal()" 
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <span id="submitButtonText">Add Service</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditMode = false;
const baseUrl = '<?= base_url() ?>';

function openAddServiceModal() {
    isEditMode = false;
    document.getElementById("modalTitle").textContent = "Add New Service";
    document.getElementById("submitButtonText").textContent = "Add Service";
    document.getElementById("serviceForm").reset();
    document.getElementById("serviceId").value = "";
    document.getElementById("serviceModal").classList.remove("hidden");
}

function editService(id) {
    isEditMode = true;
    document.getElementById("modalTitle").textContent = "Edit Service";
    document.getElementById("submitButtonText").textContent = "Update Service";
    
    // Fetch service data
    fetch(`${baseUrl}/admin/services/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("serviceId").value = id;
                document.getElementById("serviceName").value = data.service.name;
                document.getElementById("serviceDescription").value = data.service.description || "";
                document.getElementById("servicePrice").value = data.service.price;
                document.getElementById("serviceModal").classList.remove("hidden");
            } else {
                alert("Error loading service data");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error loading service data");
        });
}

function closeServiceModal() {
    document.getElementById("serviceModal").classList.add("hidden");
}

function deleteService(id, name) {
    if (confirm(`Are you sure you want to delete the service "${name}"?`)) {
        fetch(`${baseUrl}/admin/services/delete/${id}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || "Error deleting service");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error deleting service");
        });
    }
}

document.getElementById("serviceForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById("serviceId").value;
    
    const url = isEditMode ? 
        `${baseUrl}/admin/services/update/${id}` : 
        `${baseUrl}/admin/services/store`;
    
    fetch(url, {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            let errorMessage = data.message || "Error saving service";
            if (data.errors) {
                errorMessage += "\n" + Object.values(data.errors).join("\n");
            }
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error saving service");
    });
});

// Close modal when clicking outside
document.getElementById("serviceModal").addEventListener("click", function(e) {
    if (e.target === this) {
        closeServiceModal();
    }
});
</script>
';
?>

<?= view('templates/admin_layout', [
    'title' => $title,
    'content' => $content,
    'user' => $user
]) ?> 