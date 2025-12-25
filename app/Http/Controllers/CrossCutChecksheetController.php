<?php

namespace App\Http\Controllers;

use App\Models\CrossCutChecksheet;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CrossCutChecksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $checksheets = CrossCutChecksheet::with('item')->latest()->paginate(10);
        return view('cross_cut.index', compact('checksheets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::orderBy('name')->get();
        return view('cross_cut.create', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'production_shift' => 'required|string|max:255',
            'qc_shift' => 'required|string|max:255',
            'production_datetime' => 'required|date',
            'qc_datetime' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'chemical_copper' => 'nullable|string|max:255',
            'chemical_nikel' => 'nullable|string|max:255',
            'chemical_eching' => 'nullable|string|max:255',
            'chemical_abu' => 'nullable|string|max:255',
            'position_remark_judgment' => 'required|in:OK,NG',
            'position_remark_no_lot' => 'required|string|max:255',
            'result_remark' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'operator_initials' => 'nullable|string|max:255',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('cross_cut_images'), $imageName);
            $imagePath = 'cross_cut_images/' . $imageName;
        }

        CrossCutChecksheet::create([
            'item_id' => $validated['item_id'],
            'production_shift' => $validated['production_shift'],
            'qc_shift' => $validated['qc_shift'],
            'production_datetime' => $validated['production_datetime'],
            'qc_datetime' => $validated['qc_datetime'],
            'image_path' => $imagePath,
            'chemical_copper' => $validated['chemical_copper'],
            'chemical_nikel' => $validated['chemical_nikel'],
            'chemical_eching' => $validated['chemical_eching'],
            'chemical_abu' => $validated['chemical_abu'],
            'position_remark_judgment' => $validated['position_remark_judgment'],
            'position_remark_no_lot' => $validated['position_remark_no_lot'],
            'result_remark' => $validated['result_remark'],
            'keterangan' => $validated['keterangan'],
            'cycle_time' => $validated['cycle_time'],
            'operator_initials' => $validated['operator_initials'],
        ]);

        return redirect()->route('cross_cut.create')->with('success', 'Cross Cut Checksheet created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $checksheet = CrossCutChecksheet::findOrFail($id);
        return response()->json([
            'image_url' => asset($checksheet->image_path),
            'item_name' => $checksheet->item->name,
            'qc_datetime' => $checksheet->qc_datetime,
        ]);
    }

    public function approve(Request $request, $id, $type)
    {
        try {
            $checksheet = CrossCutChecksheet::findOrFail($id);
            $user = auth()->user();
            
            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift') abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor') abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager') abort(403);
                if ($type == 'manager' && $user->role !== 'manager') abort(403);
            }

            if ($type == 'kashift') {
                $checksheet->kashift_qc = $user->name;
                $checksheet->kashift_approved_at = now();
            } elseif ($type == 'supervisor') {
                $checksheet->supervisor_qc = $user->name;
                $checksheet->supervisor_approved_at = now();
                $checksheet->approval_status = 'Approved'; 
            } elseif ($type == 'asst_manager') {
                $checksheet->asst_manager_qc = $user->name;
                $checksheet->asst_manager_approved_at = now();
            } elseif ($type == 'manager') {
                $checksheet->manager_qc = $user->name;
                $checksheet->manager_approved_at = now();
            }

            $checksheet->save();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }

        return redirect()->route('cross_cut.index')->with('success', 'Cross Cut Checksheet approved successfully.');
    }

    public function reject(Request $request, $id, $type)
    {
        try {
            $checksheet = CrossCutChecksheet::findOrFail($id);
            $user = auth()->user();

            if ($user->role !== 'admin') {
                if ($type == 'kashift' && $user->role !== 'kashift') abort(403);
                if ($type == 'supervisor' && $user->role !== 'supervisor') abort(403);
                if ($type == 'asst_manager' && $user->role !== 'asst_manager') abort(403);
                if ($type == 'manager' && $user->role !== 'manager') abort(403);
            }
            
            if ($type == 'kashift') {
                $checksheet->kashift_qc = 'REJECTED';
                $checksheet->kashift_approved_at = now();
                $checksheet->approval_status = 'Rejected';
            } elseif ($type == 'supervisor') {
                $checksheet->supervisor_qc = 'REJECTED';
                $checksheet->supervisor_approved_at = now();
                $checksheet->approval_status = 'Rejected';
            } elseif ($type == 'asst_manager') {
                $checksheet->asst_manager_qc = 'REJECTED';
                $checksheet->asst_manager_approved_at = now();
                $checksheet->approval_status = 'Rejected';
            } elseif ($type == 'manager') {
                $checksheet->manager_qc = 'REJECTED';
                $checksheet->manager_approved_at = now();
                $checksheet->approval_status = 'Rejected';
            }

            $checksheet->save();
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }

        return redirect()->route('cross_cut.index')->with('success', 'Cross Cut Checksheet rejected successfully.');
    }
}
