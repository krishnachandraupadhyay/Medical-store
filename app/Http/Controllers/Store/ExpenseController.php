<?php

namespace App\Http\Controllers\Store;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Expense\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService
    ) {}

    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $query = Expense::forStore($store->id)->with('category');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('expense_number', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('expense_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        $expenses = $query->latest('expense_date')->latest('id')->paginate(15)->withQueryString();
        $categories = ExpenseCategory::forStore($store->id)->active()->orderBy('name')->get();

        return view('store.expenses.index', compact('store', 'expenses', 'categories'));
    }

    public function create(): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $categories = ExpenseCategory::forStore($store->id)->active()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::cases();

        return view('store.expenses.create', compact('store', 'categories', 'paymentMethods'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        try {
            $expense = $this->expenseService->createExpense($store, $request->validated(), auth()->id());

            return redirect()->route('store.expenses.show', $expense->id)
                ->with('success', "Expense #{$expense->expense_number} recorded successfully.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $expense = Expense::forStore($store->id)->with(['category', 'disbursementPayment', 'creator'])->findOrFail($id);

        return view('store.expenses.show', compact('store', 'expense'));
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $expense = Expense::forStore($store->id)->findOrFail($id);

        try {
            $reason = $request->input('reason');
            $this->expenseService->cancelExpense($store, $expense, $reason, auth()->id());

            return redirect()->route('store.expenses.show', $expense->id)
                ->with('success', "Expense #{$expense->expense_number} cancelled.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
