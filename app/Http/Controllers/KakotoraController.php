<?php

namespace App\Http\Controllers;

use App\Models\Kakotora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ActivityLogger;

class KakotoraController extends Controller
{
    public function index(Request $request)
    {
        $plant = $request->query('plant');
        $query = Kakotora::query();

        if ($plant) {
            $query->where('plant', $plant);
        }

        $kakotoras = $query->orderBy('date', 'desc')->get();
        return view('kakotora.index', compact('kakotoras', 'plant'));
    }

    public function create(Request $request)
    {
        $plant = $request->query('plant');
        return view('kakotora.create', compact('plant'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'no_reg' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'rev_model' => 'nullable|string|max:255',
            'family' => 'nullable|string|max:255',
            'category_nm_mp' => 'nullable|string|max:255',
            'category_claim' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'mould' => 'nullable|string|max:255',
            'owner_mould' => 'nullable|string|max:255',
            'similar_part' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'problem' => 'nullable|string',
            'cause' => 'nullable|string',
            'countermeasure' => 'nullable|string',
            'pic' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'defect_category' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'form_analysis' => 'nullable|file|mimes:pptx,xlsx,doc,docx,pdf|max:10240',
        ]);

        $data = $validated;
        $data['plant'] = $request->plant;

        if ($request->hasFile('form_analysis')) {
            $path = $request->file('form_analysis')->store('kakotora', 'public');
            $data['form_analysis_path'] = $path;
        }

        $kakotora = Kakotora::create($data);
        ActivityLogger::log('created', $kakotora, "Menambahkan data KAKOTORA baru: {$kakotora->no_reg}");

        return redirect()->route('kakotora.index', ['plant' => $request->plant])->with('success', 'Data KAKOTORA berhasil ditambahkan.');
    }

    public function show(Kakotora $kakotora)
    {
        return view('kakotora.show', compact('kakotora'));
    }

    public function edit(Kakotora $kakotora)
    {
        $plant = $kakotora->plant;
        return view('kakotora.edit', compact('kakotora', 'plant'));
    }

    public function update(Request $request, Kakotora $kakotora)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'no_reg' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'rev_model' => 'nullable|string|max:255',
            'family' => 'nullable|string|max:255',
            'category_nm_mp' => 'nullable|string|max:255',
            'category_claim' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'mould' => 'nullable|string|max:255',
            'owner_mould' => 'nullable|string|max:255',
            'similar_part' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'process' => 'nullable|string|max:255',
            'problem' => 'nullable|string',
            'cause' => 'nullable|string',
            'countermeasure' => 'nullable|string',
            'pic' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'defect_category' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'form_analysis' => 'nullable|file|mimes:pptx,xlsx,doc,docx,pdf|max:10240',
        ]);

        if ($request->hasFile('form_analysis')) {
            // Delete old file if exists
            if ($kakotora->form_analysis_path) {
                Storage::disk('public')->delete($kakotora->form_analysis_path);
            }
            $path = $request->file('form_analysis')->store('kakotora', 'public');
            $validated['form_analysis_path'] = $path;
        }

        $kakotora->update($validated);
        ActivityLogger::log('updated', $kakotora, "Memperbarui data KAKOTORA: {$kakotora->no_reg}");

        return redirect()->route('kakotora.index', ['plant' => $kakotora->plant])->with('success', 'Data KAKOTORA berhasil diperbarui.');
    }

    public function destroy(Kakotora $kakotora)
    {
        $plant = $kakotora->plant;
        if ($kakotora->form_analysis_path) {
            Storage::disk('public')->delete($kakotora->form_analysis_path);
        }
        $noReg = $kakotora->no_reg;
        $kakotora->delete();
        ActivityLogger::log('deleted', null, "Menghapus data KAKOTORA: {$noReg}");

        return redirect()->route('kakotora.index', ['plant' => $plant])->with('success', 'Data KAKOTORA berhasil dihapus.');
    }
}
