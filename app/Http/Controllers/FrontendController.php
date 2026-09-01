<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\LandingPage\Entities\LandingPageSetting;
use App\Models\Plan;
use App\Models\Contact;


class FrontendController extends Controller
{
    public function about()
    {
        return view('frontend.about-us'); // Ensure this matches the file structure
    }


    public function our_features()
    {
        // Fetch settings
        $settings = LandingPageSetting::settings();
        
        // Decode JSON to an array
        $feature_of_features = json_decode($settings['feature_of_features'] ?? '[]', true);

        return view('frontend.features', compact('feature_of_features'));
    }



    public function showPlans()
    {
        $plans = Plan::where('is_disable', 1)->get(); // Fetch only active plans

        return view('frontend.showplans', compact('plans'));
    }

    public function postRegisterPlans()
    {
        $plans = Plan::where('is_disable', 1)->get(); // Fetch only active plans

        return view('frontend.post_register_plans', compact('plans'));
    }

    

    public function new_faq()
    {
        $settings = LandingPageSetting::settings();
        $faqs = json_decode($settings['faqs'], true) ?? [];
        return view('frontend.faq', compact('faqs')); // Ensure this matches the file structure
    }

    public function terms_and_conditions()
    {
        return view('frontend.terms_and_conditions'); // Ensure this matches the file structure
    }

    public function privacy_policy()
    {
        return view('frontend.privacy_policy'); // Ensure this matches the file structure
    }

    public function contact()
    {
        return view('frontend.contact');
    }

    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        Contact::create($validated);

        return redirect()->route('frontend.contact')->with('success', 'Thank you for your message. We\'ll get back to you soon!');
    }

    public function showContacts()
    {
        $contacts = Contact::latest()->paginate(10);
        return view('frontend.contacts-list', compact('contacts'));
    }

    public function updateContact(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        $contact->update($validated);

        return redirect()->route('frontend.contacts.list')->with('success', 'Contact updated successfully');
    }

    public function destroyContact($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return redirect()->route('frontend.contacts.list')->with('success', 'Contact deleted successfully');
    }
}
