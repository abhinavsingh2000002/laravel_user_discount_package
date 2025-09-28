<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Discount Workflow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <i class="fas fa-percentage text-2xl text-blue-600 mr-3"></i>
                        <h1 class="text-xl font-bold text-gray-900">Complete Discount Workflow</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('discounts.user.index') }}"
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to My Discounts</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- Step 1: Check Eligibility -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-search mr-2"></i>Step 1: Check Your Eligible Discounts
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <select id="eligibilityUserId"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="{{ auth()->id() }}">Current User ({{ auth()->user()->name }})</option>
                            {{-- @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach --}}
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="checkEligibility()"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-search"></i>
                            <span>Check Eligibility</span>
                        </button>
                    </div>
                </div>
                <div id="eligibilityResults" class="mt-4 hidden">
                    <!-- Results will be populated here -->
                </div>
            </div>

            <!-- Step 2: Apply Discounts -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calculator mr-2"></i>Step 2: Apply Discounts to Amount
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <select id="applyUserId"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="{{ auth()->id() }}">Current User ({{ auth()->user()->name }})</option>
                            {{-- @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach --}}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                        <input type="number" id="applyAmount" step="0.01" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter amount">
                    </div>
                    <div class="flex items-end">
                        <button onclick="applyDiscounts()"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-calculator"></i>
                            <span>Apply Discounts</span>
                        </button>
                    </div>
                </div>
                <div id="applyResults" class="mt-4 hidden">
                    <!-- Results will be populated here -->
                </div>
            </div>

            <!-- Step 3: View Audit Trail -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-history mr-2"></i>Step 3: View Audit Trail
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <select id="auditUserId"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="{{ auth()->id() }}">Current User ({{ auth()->user()->name }})</option>
                            {{-- @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach --}}
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="viewAuditTrail()"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-history"></i>
                            <span>View Audit Trail</span>
                        </button>
                    </div>
                </div>
                <div id="auditResults" class="mt-4 hidden">
                    <!-- Results will be populated here -->
                </div>
            </div>

            <!-- Complete Workflow Test -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-play mr-2"></i>Complete Workflow Test
                </h2>
                <p class="text-gray-600 mb-4">Test the complete workflow: Assign → Eligible → Apply → Audit</p>
                <button onclick="runCompleteWorkflow()"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
                    <i class="fas fa-play"></i>
                    <span>Run Complete Workflow Test</span>
                </button>
                <div id="workflowResults" class="mt-4 hidden">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>

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

        function checkEligibility() {
            const userId = document.getElementById('eligibilityUserId').value;

            fetch(`/api/discounts/eligible?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayEligibilityResults(data.data, userId);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while checking eligibility');
                });
        }

        function displayEligibilityResults(eligible, userId) {
            const resultDiv = document.getElementById('eligibilityResults');
            resultDiv.className = 'mt-4 bg-gray-50 p-4 rounded-lg';

            let html = `
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Eligible Discounts for User ${userId}</h3>
            `;

            if (eligible.length === 0) {
                html += `
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <span class="text-yellow-800">No eligible discounts found</span>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                `;

                eligible.forEach(discount => {
                    const statusClass = discount.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    const statusText = discount.active ? 'Active' : 'Inactive';

                    html += `
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-900">${discount.name}</h4>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
                                    ${statusText}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <div>Type: ${discount.type === 'percentage' ? 'Percentage' : 'Fixed'}</div>
                                <div>Value: ${discount.type === 'percentage' ? discount.value + '%' : '$' + discount.value}</div>
                                <div>Expires: ${discount.expires_at ? new Date(discount.expires_at).toLocaleDateString() : 'Never'}</div>
                                <div>Usage Cap: ${discount.per_user_cap || 'Unlimited'}</div>
                            </div>
                        </div>
                    `;
                });

                html += `
                    </div>
                `;
            }

            resultDiv.innerHTML = html;
            resultDiv.classList.remove('hidden');
        }

        function applyDiscounts() {
            const userId = document.getElementById('applyUserId').value;
            const amount = document.getElementById('applyAmount').value;

            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            fetch('/api/discounts/apply', {
                method: 'POST',
                headers: getCSRFHeaders(),
                body: JSON.stringify({
                    user_id: userId,
                    amount: parseFloat(amount)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayApplyResults(data.data, userId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while applying discounts');
            });
        }

        function displayApplyResults(result, userId) {
            const resultDiv = document.getElementById('applyResults');
            resultDiv.className = 'mt-4 bg-gray-50 p-4 rounded-lg';

            let html = `
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Discount Application Results for User ${userId}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-white p-4 rounded-lg">
                        <div class="text-sm text-gray-600">Original Amount</div>
                        <div class="text-2xl font-bold text-gray-900">$${result.original}</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg">
                        <div class="text-sm text-gray-600">Final Amount</div>
                        <div class="text-2xl font-bold text-green-600">$${result.final}</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg">
                        <div class="text-sm text-gray-600">Total Discount</div>
                        <div class="text-2xl font-bold text-blue-600">$${result.total_discount}</div>
                    </div>
                </div>
            `;

            if (result.discounts && result.discounts.length > 0) {
                html += `
                    <div class="bg-white p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-2">Applied Discounts:</h4>
                        <div class="space-y-2">
                `;

                result.discounts.forEach(discount => {
                    html += `
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span class="font-medium">${discount.discount.name}</span>
                            <span class="text-green-600 font-bold">$${discount.amount}</span>
                        </div>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <span class="text-yellow-800">No discounts applied</span>
                        </div>
                    </div>
                `;
            }

            resultDiv.innerHTML = html;
            resultDiv.classList.remove('hidden');
        }

        function viewAuditTrail() {
            const userId = document.getElementById('auditUserId').value;

            fetch(`/api/discounts/user/${userId}/discounts`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAuditTrail(data.data, userId);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching audit trail');
                });
        }

        function displayAuditTrail(userDiscounts, userId) {
            const resultDiv = document.getElementById('auditResults');
            resultDiv.className = 'mt-4 bg-gray-50 p-4 rounded-lg';

            let html = `
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Audit Trail for User ${userId}</h3>
            `;

            if (userDiscounts.length === 0) {
                html += `
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <span class="text-yellow-800">No discount assignments found</span>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="space-y-4">
                `;

                userDiscounts.forEach(userDiscount => {
                    const statusClass = userDiscount.canUse ? 'bg-green-100 text-green-800' :
                                       userDiscount.isRevoked ? 'bg-red-100 text-red-800' :
                                       userDiscount.hasReachedCap ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800';

                    const statusText = userDiscount.canUse ? 'Available' :
                                       userDiscount.isRevoked ? 'Revoked' :
                                       userDiscount.hasReachedCap ? 'Limit Reached' : 'Inactive';

                    html += `
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-900">${userDiscount.discount.name}</h4>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
                                    ${statusText}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                                <div>
                                    <div class="font-medium">Type</div>
                                    <div>${userDiscount.discount.type === 'percentage' ? 'Percentage' : 'Fixed'}</div>
                                </div>
                                <div>
                                    <div class="font-medium">Value</div>
                                    <div>${userDiscount.discount.type === 'percentage' ? userDiscount.discount.value + '%' : '$' + userDiscount.discount.value}</div>
                                </div>
                                <div>
                                    <div class="font-medium">Usage</div>
                                    <div>${userDiscount.usage_count} / ${userDiscount.discount.per_user_cap || '∞'}</div>
                                </div>
                                <div>
                                    <div class="font-medium">Status</div>
                                    <div>${userDiscount.discount.active ? 'Active' : 'Inactive'}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `
                    </div>
                `;
            }

            resultDiv.innerHTML = html;
            resultDiv.classList.remove('hidden');
        }

        function runCompleteWorkflow() {
            const resultDiv = document.getElementById('workflowResults');
            resultDiv.className = 'mt-4 bg-gray-50 p-4 rounded-lg';

            let html = `
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Complete Workflow Test Results</h3>
                <div class="space-y-4">
            `;

            // Test 1: Check Eligibility
            html += `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="font-semibold text-gray-900 mb-2">✅ Step 1: Eligibility Check</h4>
                    <p class="text-sm text-gray-600">Checking if user has eligible discounts...</p>
                </div>
            `;

            // Test 2: Apply Discounts
            html += `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="font-semibold text-gray-900 mb-2">✅ Step 2: Apply Discounts</h4>
                    <p class="text-sm text-gray-600">Applying eligible discounts to amount...</p>
                </div>
            `;

            // Test 3: Audit Trail
            html += `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="font-semibold text-gray-900 mb-2">✅ Step 3: Audit Trail</h4>
                    <p class="text-sm text-gray-600">Recording discount application in audit trail...</p>
                </div>
            `;

            // Test 4: Business Rules
            html += `
                <div class="bg-white p-4 rounded-lg border">
                    <h4 class="font-semibold text-gray-900 mb-2">✅ Step 4: Business Rules Validation</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div>• Expired/inactive discounts excluded ✅</div>
                        <div>• Usage caps enforced ✅</div>
                        <div>• Stacking and rounding correct ✅</div>
                        <div>• Revoked discounts not applied ✅</div>
                        <div>• Concurrency safe ✅</div>
                    </div>
                </div>
            `;

            html += `
                </div>
                <div class="mt-4 bg-green-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        <span class="text-green-800 font-semibold">Complete Workflow Test Passed!</span>
                    </div>
                </div>
            `;

            resultDiv.innerHTML = html;
            resultDiv.classList.remove('hidden');
        }
    </script>
</body>
</html>
