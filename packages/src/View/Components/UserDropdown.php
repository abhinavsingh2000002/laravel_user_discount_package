<?php

namespace Abhinav\Discounts\View\Components;

use Illuminate\View\Component;
use App\Models\User;

class UserDropdown extends Component
{
    public $name;
    public $selected;
    public $required;
    public $class;
    public $users;

    public function __construct($name = 'user_id', $selected = null, $required = false, $class = '')
    {
        $this->name = $name;
        $this->selected = $selected;
        $this->required = $required;
        $this->class = $class;
        $this->users = User::all();
    }

    public function render()
    {
        return view('discounts::components.user-dropdown');
    }
}
