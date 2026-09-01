<?php

namespace Modules\LandingPage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LandingPage\Entities\LandingPageSetting;
use Modules\LandingPage\Entities\JoinUs;

class JoinUsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (\Auth::user()->type == 'super admin') {
            $join_us = JoinUs::latest()->paginate(10); // Paginate for scalability
            return view('landingpage::landingpage.joinus', compact('join_us'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('landingpage::create');
    }

    /**
     * Store a newly created resource in storage (settings).
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'joinus_heading' => 'required|string|max:255',
            'joinus_description' => 'required|string',
            'joinus_status' => 'nullable|boolean', // Optional, defaults to 'off'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $joinus_status = $request->joinus_status ? 'on' : 'off';
        $data = [
            'joinus_status' => $joinus_status,
            'joinus_heading' => $request->joinus_heading,
            'joinus_description' => $request->joinus_description,
        ];

        foreach ($data as $key => $value) {
            LandingPageSetting::updateOrCreate(['name' => $key], ['value' => $value]);
        }

        return redirect()->back()->with('success', __('Settings updated successfully'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $join = JoinUs::findOrFail($id);
        return view('landingpage::landingpage.joinus_show', compact('join')); // New view for showing details
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $join = JoinUs::findOrFail($id);
        return view('landingpage::landingpage.joinus_edit', compact('join')); // New view for editing
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $join = JoinUs::findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|unique:join_us,email,' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $join->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        return redirect()->route('join_us.index')->with('success', __('User updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $join = JoinUs::find($id);
        if (!$join) {
            return redirect()->back()->with('error', __('Record not found.'));
        }

        $join->delete();
        return redirect()->back()->with('success', __('User removed from the community successfully'));
    }

    /**
     * Store a new user registration from the frontend.
     * @param Request $request
     * @return Renderable
     */
    public function joinUsUserStore(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|unique:join_us,email',
            'company_name' => 'required|string|max:255',
        ]);
    
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $join = new JoinUs;
        $join->name = $request->name;
        $join->phone = $request->phone;
        $join->email = $request->email;
        $join->company_name = $request->company_name;
        $join->save();
    
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('You have successfully joined our community!'),
            ]);
        }
    
        return redirect()->back()->with('success', __('You have successfully joined our community!'));
    }
}