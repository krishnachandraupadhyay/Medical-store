<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Expense\StoreExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $categories = ExpenseCategory::forStore($store->id)->withCount('expenses')->latest('id')->paginate(15);

        return view('store.expenses.categories', compact('store', 'categories'));
    }

    public function store(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $data = $request->validated();
        $data['store_id'] = $store->id;
        $data['status'] = $data['status'] ?? 'active';

        $category = ExpenseCategory::create($data);

        return redirect()->route('store.expense-categories.index')
            ->with('success', "Category '{$category->name}' created successfully.");
    }
}
