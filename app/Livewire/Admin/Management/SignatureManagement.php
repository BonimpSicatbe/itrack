<?php

namespace App\Livewire\Admin\Management;

use App\Models\Signature;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class SignatureManagement extends Component
{
    use WithFileUploads;

    public $e_signature;
    public $signature_owner;

    public function __construct()
    {
        $this->e_signature = null;
        $this->signature_owner = '';
    }

    public function createNewESignature()
    {
        $validated = $this->validate([
            'e_signature' => 'required|file|mimes:png|max:2048', // max 2MB
            'signature_owner' => 'required|exists:users,id',
        ]);

        try {
            $user = User::findOrFail($this->signature_owner);

            // Remove old signature if user already has one
            $user->clearMediaCollection('signature');

            // Attach new signature using Spatie Media Library
            $user->addMedia($this->e_signature->getRealPath())
                ->usingName('signature')
                ->usingFileName('signature_' . time() . '.png')
                ->toMediaCollection('signature', 'public');

            return redirect()->route('admin.management.index', ['tab' => 'signatures']);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function render()
    {
        $users = User::all()->sortByDesc(fn($user) => $user->hasRole('admin'));
        $signatures = Signature::with('user')->get();

        return view('livewire.admin.management.signature-management', [
            'users' => $users,
            'signatures' => $signatures,
        ]);
    }
}
