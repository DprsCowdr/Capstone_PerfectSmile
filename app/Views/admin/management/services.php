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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
';

if (empty($services)) {
    $content .= '
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No services found. Click "Add New Service" to get started.
                    </td>
                </tr>';
} else {
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
                        <span class="text-sm text-gray-600">';
        $durationDisplay = 'Not set';
        if (!empty($service['duration_minutes'])) {
            $m = intval($service['duration_minutes']);
            $hours = intdiv($m, 60);
            $rem = $m % 60;
            $durationDisplay = $hours > 0 ? $hours . 'h' . ($rem ? ' ' . $rem . 'm' : '') : $rem . 'm';
        }
        if (!empty($service['duration_max_minutes'])) {
            $m2 = intval($service['duration_max_minutes']);
            $h2 = intdiv($m2, 60);
            $r2 = $m2 % 60;
            $maxDisp = $h2 > 0 ? $h2 . 'h' . ($r2 ? ' ' . $r2 . 'm' : '') : $r2 . 'm';
            if ($durationDisplay === 'Not set') $durationDisplay = $maxDisp; else $durationDisplay .= ' - ' . $maxDisp;
        }
        $content .= $durationDisplay . '</span>
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

<!-- Service Modal -->
<div id="serviceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 id="modalTitle" class="text-xl font-bold text-gray-900">Add New Service</h2>
                <button onclick="closeServiceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="serviceForm" method="POST">
                <input type="hidden" id="serviceId" name="id">
                
                <div class="mb-4">
                    <label for="serviceName" class="block text-sm font-medium text-gray-700 mb-2">Service Name *</label>
                    <input type="text" id="serviceName" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="serviceDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="serviceDescription" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="serviceDuration" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes or hours, e.g. "2h")</label>
                    <input type="text" id="serviceDuration" name="duration_minutes" placeholder="30 or 2h"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">You can enter minutes (e.g. 90) or hours (e.g. 1.5h or 2h). Leave empty for default.</p>
                </div>

                <div class="mb-4">
                    <label for="serviceDurationMax" class="block text-sm font-medium text-gray-700 mb-2">Max Duration (optional)</label>
                    <input type="text" id="serviceDurationMax" name="duration_max_minutes" placeholder="120 or 2h"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Optional maximum duration. Accepts minutes or hours format.</p>
                </div>
                
                <div class="mb-6">
                    <label for="servicePrice" class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                    <input type="number" id="servicePrice" name="price" step="0.01" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeServiceModal()" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
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
</div>

<script>
let isEditMode = false;
const baseUrl = "' . base_url() . '";

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
    fetch(baseUrl + "/admin/services/" + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("serviceId").value = id;
                document.getElementById("serviceName").value = data.service.name;
                document.getElementById("serviceDescription").value = data.service.description || "";
                document.getElementById("serviceDuration").value = data.service.duration_minutes || "";
                document.getElementById("serviceDurationMax").value = data.service.duration_max_minutes || "";
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
    if (confirm("Are you sure you want to delete the service \\"" + name + "\\"?")) {
        fetch(baseUrl + "/admin/services/delete/" + id, {
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
        baseUrl + "/admin/services/update/" + id : 
        baseUrl + "/admin/services/store";
    
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
                errorMessage += "\\n" + Object.values(data.errors).join("\\n");
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