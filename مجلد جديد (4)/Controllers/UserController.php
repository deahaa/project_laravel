<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * عرض قائمة المستخدمين
     */
    public function index(Request $request)
    {
        Gate::authorize('view-any', User::class);

        $search = $request->input('search');
        $role_id = $request->input('role_id');

        $users = User::with('role')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%$search%")
                             ->orWhere('email', 'like', "%$search%");
            })
            ->when($role_id, function ($query, $role_id) {
                return $query->where('role_id', $role_id);
            })
            ->paginate(10);

        $roles = Role::all();

        return view('users.index', compact('users', 'search', 'roles', 'role_id'));
    }

    /**
     * عرض نموذج إنشاء مستخدم
     */
    public function create()
    {
        Gate::authorize('create', User::class);

        $roles = Role::where('name', '!=', 'admin')->get(); // لا يمكن إنشاء مديرين
        return view('users.create', compact('roles'));
    }

    /**
     * تخزين مستخدم جديد
     */
    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role_id' => 'required|exists:roles,id'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id
        ]);

        return redirect()->route('users.index')
            ->with('success', 'تمت إضافة المستخدم بنجاح');
    }

    /**
     * عرض نموذج تعديل مستخدم
     */
    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        $roles = Role::where('name', '!=', 'admin')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * تحديث بيانات المستخدم
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'role_id' => 'required|exists:roles,id'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    /**
     * حذف مستخدم
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        // منع حذف المستخدم إذا كان له إعارات نشطة
        if ($user->borrowings()->where('status', 'borrowed')->exists()) {
            return back()->with('error', 'لا يمكن حذف المستخدم لديه كتب معارة');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * تعطيل/تفعيل حساب المستخدم
     */
    public function toggleStatus(User $user)
    {
        Gate::authorize('update', $user);

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'مفعل' : 'معطل';

        return back()->with('success', "تم تغيير حالة الحساب إلى $status");
    }
}
