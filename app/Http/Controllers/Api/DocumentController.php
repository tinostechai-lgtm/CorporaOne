<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DucumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     */
    public function index(Request $request)
    {
        $documents = DucumentUpload::where('created_by', $request->user()->creatorId())->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ], 200);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $document = new DucumentUpload();
        $document->name = $request->name;
        $document->employee_id = $request->employee_id;
        $document->created_by = $request->user()->creatorId();

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('documents', $filename, 'public');
            $document->document = $filename;
        }

        $document->save();

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data' => $document
        ], 201);
    }

    /**
     * Display the specified document
     */
    public function show(Request $request, $id)
    {
        $document = DucumentUpload::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $document
        ]);
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, $id)
    {
        $document = DucumentUpload::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $document->name = $request->name ?? $document->name;
        $document->employee_id = $request->employee_id ?? $document->employee_id;

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('documents', $filename, 'public');
            $document->document = $filename;
        }

        $document->save();

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully',
            'data' => $document
        ]);
    }

    /**
     * Remove the specified document
     */
    public function destroy(Request $request, $id)
    {
        $document = DucumentUpload::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully'
        ]);
    }
}
