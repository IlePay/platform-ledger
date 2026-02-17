<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LedgerClient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private LedgerClient $ledger)
    {
    }

    public function index()
    {
        $stats = [
            'total_users' => User::where('role', 'USER')->count(),
            'active_users' => User::where('role', 'USER')->where('is_active', true)->count(),
            'total_balance' => 0,
        ];

        $users = User::where('role', 'USER')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.dashboard', compact('stats', 'users'));
    }

    public function users()
    {
        $users = User::where('role', 'USER')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function showUser(User $user)
    {
        $account = null;
        if ($user->ledger_account_id) {
            $account = $this->ledger->getAccount($user->ledger_account_id);
        }

        return view('admin.users.show', compact('user', 'account'));
    }

    public function creditForm(User $user)
    {
        return view('admin.users.credit', compact('user'));
    }

    public function credit(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string',
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Compte crédité de {$validated['amount']} XAF");
    }
}