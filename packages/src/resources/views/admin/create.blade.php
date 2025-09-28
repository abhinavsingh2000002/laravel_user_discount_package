<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Discount</title>
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
                        <a href="{{ route('discounts.index') }}" class="flex items-center">
                            <i class="fas fa-arrow-left text-xl text-gray-600 mr-3"></i>
                            <i class="fas fa-percentage text-2xl text-blue-600 mr-3"></i>
                            <h1 class="text-xl font-bold text-gray-900">Create Discount</h1>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <div>
                            <h4 class="font-medium">Please fix the following errors:</h4>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('discounts.store') }}" method="POST" class="space-y-8">
                @csrf

                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Discount Information</h2>
                        <p class="text-sm text-gray-600">Basic details about your discount</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag mr-2"></i>Discount Name
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="e.g., Black Friday Sale"
                                   required>
                        </div>

                        <!-- Type and Value -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-percentage mr-2"></i>Discount Type
                                </label>
                                <select name="type" id="type" onchange="updateValueLabel()"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                        required>
                                    <option value="">Select Type</option>
                                    <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                            </div>

                            <div>
                                <label for="value" id="valueLabel" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-dollar-sign mr-2"></i>Discount Value
                                </label>
                                <div class="relative">
                                    <input type="number" name="value" id="value" step="0.01" min="0" value="{{ old('value') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                           placeholder="Enter value"
                                           required>
                                    <span id="valueSuffix" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Settings & Restrictions</h2>
                        <p class="text-sm text-gray-600">Configure when and how the discount can be used</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Active Status -->
                        <div>
                            <label for="active" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-toggle-on mr-2"></i>Status
                            </label>
                            <select name="active" id="active"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Expiration -->
                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-2"></i>Expiration Date
                            </label>
                            <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                            <p class="text-sm text-gray-500 mt-1">Leave empty for no expiration</p>
                        </div>

                        <!-- Per User Cap -->
                        <div>
                            <label for="per_user_cap" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user-lock mr-2"></i>Usage Limit Per User
                            </label>
                            <input type="number" name="per_user_cap" id="per_user_cap" min="0" value="{{ old('per_user_cap') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Leave empty for unlimited">
                            <p class="text-sm text-gray-500 mt-1">How many times can each user use this discount?</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('discounts.index') }}"
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Create Discount</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateValueLabel() {
            const type = document.getElementById('type').value;
            const label = document.getElementById('valueLabel');
            const suffix = document.getElementById('valueSuffix');

            if (type === 'percentage') {
                label.innerHTML = '<i class="fas fa-percentage mr-2"></i>Percentage Value';
                suffix.textContent = '%';
            } else if (type === 'fixed') {
                label.innerHTML = '<i class="fas fa-dollar-sign mr-2"></i>Fixed Amount';
                suffix.textContent = '$';
            } else {
                label.innerHTML = '<i class="fas fa-dollar-sign mr-2"></i>Discount Value';
                suffix.textContent = '';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateValueLabel();
        });
    </script>
</body>
</html>
