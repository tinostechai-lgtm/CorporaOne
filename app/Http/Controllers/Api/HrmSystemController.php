<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HrmSystemController extends Controller
{
    /**
     * Display a listing of holidays
     */
    public function indexHolidays(Request $request)
    {
        $holidays = Holiday::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $holidays
        ], 200);
    }

    /**
     * Store a newly created holiday
     */
    public function storeHoliday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'occasion' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $holiday = new Holiday();
        $holiday->fill($request->all());
        $holiday->created_by = $request->user()->creatorId();
        $holiday->save();

        return response()->json([
            'success' => true,
            'message' => 'Holiday created successfully',
            'data' => $holiday
        ], 201);
    }

    /**
     * Display the specified holiday
     */
    public function showHoliday(Request $request, $id)
    {
        $holiday = Holiday::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $holiday
        ]);
    }

    /**
     * Update the specified holiday
     */
    public function updateHoliday(Request $request, $id)
    {
        $holiday = Holiday::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'occasion' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $holiday->fill($request->only(['occasion', 'date', 'description']));
        $holiday->save();

        return response()->json([
            'success' => true,
            'message' => 'Holiday updated successfully',
            'data' => $holiday
        ]);
    }

    /**
     * Remove the specified holiday
     */
    public function destroyHoliday(Request $request, $id)
    {
        $holiday = Holiday::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'Holiday deleted successfully'
        ]);
    }

    /**
     * Display a listing of taxes
     */
    public function indexTaxes(Request $request)
    {
        $taxes = Tax::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $taxes
        ], 200);
    }

    /**
     * Store a newly created tax
     */
    public function storeTax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $tax = new Tax();
        $tax->fill($request->all());
        $tax->created_by = $request->user()->creatorId();
        $tax->save();

        return response()->json([
            'success' => true,
            'message' => 'Tax created successfully',
            'data' => $tax
        ], 201);
    }

    /**
     * Display the specified tax
     */
    public function showTax(Request $request, $id)
    {
        $tax = Tax::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $tax
        ]);
    }

    /**
     * Update the specified tax
     */
    public function updateTax(Request $request, $id)
    {
        $tax = Tax::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'rate' => 'sometimes|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $tax->fill($request->only(['name', 'rate']));
        $tax->save();

        return response()->json([
            'success' => true,
            'message' => 'Tax updated successfully',
            'data' => $tax
        ]);
    }

    /**
     * Remove the specified tax
     */
    public function destroyTax(Request $request, $id)
    {
        $tax = Tax::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $tax->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tax deleted successfully'
        ]);
    }
}
