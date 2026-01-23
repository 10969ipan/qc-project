<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoryService;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // For non-admin users, force filtering by their assigned plant
        if (auth()->check() && auth()->user()->role !== 'admin') {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $categories = $this->categoryService->getFilteredCategories($request->only('plant'));
        return view('categories.index', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $this->categoryService->createCategory($request->validated());
        return redirect()->route('admin.categories.index', ['plant' => $request->get('plant')])->with('success', 'Kategori berhasil ditambahkan.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $category = $this->categoryService->findCategory($id);
        if (request()->ajax()) {
            return response()->json([
                'category' => $category,
                'plant' => $category->plant
            ]);
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        $this->categoryService->updateCategory($id, $request->validated());
        return redirect()->route('admin.categories.index', ['plant' => $request->get('plant')])->with('success', 'Kategori berhasil diperbarui.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $this->categoryService->deleteCategory($id);
        return redirect()->route('admin.categories.index', $request->only('plant'))->with('success', 'Kategori berhasil dihapus.');
    }
}

