<?php

if (!function_exists('user_dropdown')) {
    /**
     * Generate a user dropdown select element
     */
    function user_dropdown($name = 'user_id', $selected = null, $required = false, $class = '')
    {
        $users = \App\Models\User::all();
        $requiredAttr = $required ? 'required' : '';
        $classAttr = $class ? "class=\"{$class}\"" : 'class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"';

        $html = "<select name=\"{$name}\" {$requiredAttr} {$classAttr}>";
        $html .= '<option value="">Select a user...</option>';

        foreach ($users as $user) {
            $selectedAttr = ($selected == $user->id) ? 'selected' : '';
            $html .= "<option value=\"{$user->id}\" {$selectedAttr}>{$user->name} ({$user->email})</option>";
        }

        $html .= '</select>';

        return $html;
    }
}
