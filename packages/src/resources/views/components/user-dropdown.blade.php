@props(['name' => 'user_id', 'selected' => null, 'required' => false, 'class' => ''])

<select name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $class }}">
    <option value="">Select a user...</option>
    @foreach(\App\Models\User::all() as $user)
        <option value="{{ $user->id }}" {{ $selected == $user->id ? 'selected' : '' }}>
            {{ $user->name }} ({{ $user->email }})
        </option>
    @endforeach
</select>
