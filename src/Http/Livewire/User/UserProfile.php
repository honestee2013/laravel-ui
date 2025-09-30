<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\User;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;


class UserProfile extends Component
{


    protected $listeners = [
        'refreshFieldsEvent' => '$refresh'
    ];


    public $user;

    public function mount()
    {
        if(!$this->user && !auth()->user())
            redirect('/login');
        else if (auth()->user())
            $this->user = auth()->user();
    }

    public function render()
    {
        return view('user.views::profile');
    }


    public function editRecord($id, $model, $modelId) {
        $this->dispatch("openEditModalEvent", $id, $model, $modelId);
    }




}


