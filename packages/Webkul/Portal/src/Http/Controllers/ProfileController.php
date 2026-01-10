<?php

namespace Webkul\Portal\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Webkul\Contact\Repositories\PersonRepository;

class ProfileController extends Controller
{
    use ValidatesRequests;

    public function __construct(protected PersonRepository $personRepository)
    {
    }

    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        $person = auth()->guard('portal')->user()->person;

        return view('portal::profile.edit', compact('person'));
    }

    /**
     * Update the profile.
     */
    public function update()
    {
        $person = auth()->guard('portal')->user()->person;

        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'contact_numbers' => 'nullable|string', // Simple text input for now, or multiple fields
        ]);

        // Handle contact numbers
        // Person model expects JSON array: [['label' => '...', 'value' => '...']]
        // I will assume a single phone number input for simplicity in this iteration
        $contactNumbers = [];
        if (request('contact_numbers')) {
            $contactNumbers[] = [
                'label' => 'work', // Default label
                'value' => request('contact_numbers')
            ];
        }

        $this->personRepository->update([
            'name' => request('name'),
            'contact_numbers' => $contactNumbers,
            'entity_type' => 'persons',
        ], $person->id);

        // Also update PortalAccess email? 
        // Changing email is sensitive (login credential). 
        // I'll skip email update for now or allow it but sync both tables.
        // Given complexity of email change (verification etc), I will restrict it for now.

        session()->flash('success', 'Profile updated successfully.');

        return redirect()->route('portal.profile.edit');
    }
}
