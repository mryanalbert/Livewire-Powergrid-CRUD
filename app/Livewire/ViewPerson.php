<?php

namespace App\Livewire;

use App\Models\people;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewPerson extends Component
{
    public $person;

    public function mount($id)
    {
        $this->person = people::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.view-person');
    }
}
