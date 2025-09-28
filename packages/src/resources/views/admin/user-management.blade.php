<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Discount Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        // CSRF Helper Functions
        function getCSRFToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : null;
        }

        function getCSRFHeaders() {
            return {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
                'Accept': 'application/json'
            };
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('discounts.index') }}" class="flex items-center">
                            <i class="fas fa-arrow-left text-xl text-gray-600 mr-3"></i>
                            <i class="fas fa-users text-2xl text-blue-600 mr-3"></i>
                            <h1 class="text-xl font-bold text-gray-900">User Discount Management</h1>
                        </a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="openAssignModal()"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                            <i class="fas fa-plus"></i>
                            <span>Assign Discount</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- User Search -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-search mr-2"></i>Find User
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <select id="userId"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="loadUserDiscounts()">
                            <option value="">Select a user...</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="loadUserDiscounts()"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-search"></i>
                            <span>Search</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- User Discounts Table -->
            <div id="userDiscountsTable" class="bg-white shadow-lg rounded-lg overflow-hidden hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">User Discounts</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type & Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userDiscountsBody" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="bg-white shadow-lg rounded-lg p-12 text-center hidden">
                <div class="mx-auto h-24 w-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-user-slash text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No user found</h3>
                <p class="text-gray-500">Please enter a valid user ID to view their discounts.</p>
            </div>
        </div>
    </div>

    <!-- Assign Discount Modal -->
    <div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Assign Discount to User</h3>
                </div>
                <form id="assignForm" class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <select name="user_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a user...</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Discount</label>
                        <select name="discount_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Discount</option>
                            @foreach(\Abhinav\Discounts\Models\Discount::active()->get() as $discount)
                                <option value="{{ $discount->id }}">{{ $discount->name }} ({{ $discount->type === 'percentage' ? $discount->value . '%' : '$' . $discount->value }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeAssignModal()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function loadUserDiscounts() {
            const userId = document.getElementById('userId').value;
            if (!userId) {
                alert('Please select a user');
                return;
            }

            fetch(`/api/discounts/user/${userId}/discounts`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayUserDiscounts(data.data, userId);
                    } else {
                        showNoResults();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNoResults();
                });
        }

        function displayUserDiscounts(discounts, userId) {
            const table = document.getElementById('userDiscountsTable');
            const tbody = document.getElementById('userDiscountsBody');
            const noResults = document.getElementById('noResults');

            table.classList.remove('hidden');
            noResults.classList.add('hidden');

            tbody.innerHTML = '';

            if (discounts.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-percentage text-3xl mb-4"></i>
                            <div>No discounts assigned to this user</div>
                        </td>
                    </tr>
                `;
                return;
            }

            discounts.forEach(discount => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition duration-150';

                const statusClass = discount.canUse ? 'bg-green-100 text-green-800' :
                                   discount.isRevoked ? 'bg-red-100 text-red-800' :
                                   discount.hasReachedCap ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800';

                const statusText = discount.canUse ? 'Available' :
                                  discount.isRevoked ? 'Revoked' :
                                  discount.hasReachedCap ? 'Limit Reached' : 'Inactive';

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-percentage text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${discount.discount.name}</div>
                                <div class="text-sm text-gray-500">ID: ${discount.discount.id}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            ${discount.discount.type === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">
                            <i class="fas ${discount.discount.type === 'percentage' ? 'fa-percentage' : 'fa-dollar-sign'} mr-1"></i>
                            ${discount.discount.type === 'percentage' ? discount.discount.value + '%' : '$' + discount.discount.value}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusClass}">
                            <i class="fas ${discount.canUse ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex items-center">
                            <i class="fas fa-user-lock mr-2"></i>
                            ${discount.usage_count}
                            ${discount.discount.per_user_cap ? '/' + discount.discount.per_user_cap : '/ ∞'}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            ${discount.isRevoked ?
                                `<button onclick="revokeDiscount(${userId}, ${discount.discount.id})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg text-xs">Revoke</button>` :
                                `<button onclick="revokeDiscount(${userId}, ${discount.discount.id})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg text-xs">Revoke</button>`
                            }
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function showNoResults() {
            document.getElementById('userDiscountsTable').classList.add('hidden');
            document.getElementById('noResults').classList.remove('hidden');
        }

        function openAssignModal() {
            document.getElementById('assignModal').classList.remove('hidden');
        }

        function closeAssignModal() {
            document.getElementById('assignModal').classList.add('hidden');
            document.getElementById('assignForm').reset();
        }

        function revokeDiscount(userId, discountId) {
            if (!confirm('Are you sure you want to revoke this discount?')) {
                return;
            }

            fetch('/api/discounts/revoke', {
                method: 'POST',
                headers: getCSRFHeaders(),
                body: JSON.stringify({
                    user_id: userId,
                    discount_id: discountId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Discount revoked successfully!');
                    loadUserDiscounts();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while revoking the discount');
            });
        }

        // Handle assign form submission
        document.getElementById('assignForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('/api/discounts/assign', {
                method: 'POST',
                headers: getCSRFHeaders(),
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Discount assigned successfully!');
                    closeAssignModal();
                    loadUserDiscounts();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while assigning the discount');
            });
        });
    </script>
</body>
</html>
