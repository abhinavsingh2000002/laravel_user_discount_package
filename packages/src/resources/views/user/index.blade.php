<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Discounts</title>
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
                        <i class="fas fa-percentage text-2xl text-blue-600 mr-3"></i>
                        <h1 class="text-xl font-bold text-gray-900">My Discounts</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('discounts.user.workflow') }}"
                           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                            <i class="fas fa-play"></i>
                            <span>Complete Workflow</span>
                        </a>
                        <a href="{{ route('discounts.user.history') }}"
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- Discount Calculator -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-calculator mr-2"></i>Discount Calculator
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if(auth()->user()->isAdmin ?? false)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <select id="selectedUserId"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="{{ auth()->id() }}">Current User ({{ auth()->user()->name }})</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                        <input type="number" id="amount" step="0.01" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter amount">
                    </div>
                    <div class="flex items-end">
                        <button onclick="calculateDiscounts()"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-calculator"></i>
                            <span>Calculate</span>
                        </button>
                    </div>
                </div>
                <div id="discountResult" class="mt-4 hidden">
                    <!-- Results will be populated here -->
                </div>
            </div>

            <!-- My Discounts -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">My Assigned Discounts</h2>
                </div>

                @if($userDiscounts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type & Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($userDiscounts as $userDiscount)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-percentage text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $userDiscount->discount->name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ $userDiscount->discount->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                {{ $userDiscount->discount->type === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                <i class="fas {{ $userDiscount->discount->type === 'percentage' ? 'fa-percentage' : 'fa-dollar-sign' }} mr-1"></i>
                                                {{ $userDiscount->discount->type === 'percentage' ? $userDiscount->discount->value . '%' : '$' . number_format($userDiscount->discount->value, 2) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($userDiscount->canUse())
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Available
                                            </span>
                                        @elseif($userDiscount->isRevoked())
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Revoked
                                            </span>
                                        @elseif($userDiscount->hasReachedCap())
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Limit Reached
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-clock mr-1"></i>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <i class="fas fa-user-lock mr-2"></i>
                                            {{ $userDiscount->usage_count }}
                                            @if($userDiscount->discount->per_user_cap)
                                                / {{ $userDiscount->discount->per_user_cap }}
                                            @else
                                                / ∞
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($userDiscount->discount->expires_at)
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-alt mr-2"></i>
                                                <span class="{{ $userDiscount->discount->expires_at->isPast() ? 'text-red-600' : ($userDiscount->discount->expires_at->diffInDays() <= 7 ? 'text-yellow-600' : 'text-gray-600') }}">
                                                    {{ $userDiscount->discount->expires_at->format('M d, Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">Never</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($userDiscount->canUse())
                                            <button onclick="applySingleDiscount({{ $userDiscount->discount->id }})"
                                                    class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-lg text-xs flex items-center space-x-1 transition duration-200">
                                                <i class="fas fa-check"></i>
                                                <span>Apply</span>
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">Not Available</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto h-24 w-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-percentage text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No discounts assigned</h3>
                        <p class="text-gray-500 mb-6">Contact administrator to get discounts assigned.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function calculateDiscounts() {
            const amount = document.getElementById('amount').value;
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            // Get selected user ID (for admin) or current user ID
            const selectedUserId = document.getElementById('selectedUserId') ?
                document.getElementById('selectedUserId').value : {{ auth()->id() }};

            fetch('/api/discounts/apply', {
                method: 'POST',
                headers: getCSRFHeaders(),
                body: JSON.stringify({
                    user_id: selectedUserId,
                    amount: parseFloat(amount)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayResults(data.data);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while calculating discounts');
            });
        }

        function displayResults(result) {
            const resultDiv = document.getElementById('discountResult');
            resultDiv.className = 'mt-4 bg-gray-50 p-4 rounded-lg';

            let html = `
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Discount Calculation Results</h3>
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
                            <span class="text-yellow-800">No discounts available for this amount</span>
                        </div>
                    </div>
                `;
            }

            resultDiv.innerHTML = html;
            resultDiv.classList.remove('hidden');
        }

        function applySingleDiscount(discountId) {
            const amount = prompt('Enter amount to apply discount to:');
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            // Get selected user ID (for admin) or current user ID
            const selectedUserId = document.getElementById('selectedUserId') ?
                document.getElementById('selectedUserId').value : {{ auth()->id() }};

            fetch('/api/discounts/apply', {
                method: 'POST',
                headers: getCSRFHeaders(),
                body: JSON.stringify({
                    user_id: selectedUserId,
                    amount: parseFloat(amount)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Discount applied! Final amount: $${data.data.final}`);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while applying discount');
            });
        }

        function applyDiscounts() {
            const amount = prompt('Enter amount to apply all eligible discounts:');
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            calculateDiscounts();
        }
    </script>
</body>
</html>
