<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\APIResponse;
use App\Helpers\DateHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserRegisteredNotification;

class UserController extends Controller
{
    use APIResponse;

    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $heading = 'Users';
        if ($request->wantsJson()) {
            $users = $this->service->getAll();
            return $this->success(UserResource::collection($users));
        }
        $response = response()->view('backend.user.index', compact('heading'));
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        return $response;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $heading = 'Add User';
        $roles = \Spatie\Permission\Models\Role::all();
        return view('backend.user.create', compact('roles', 'heading'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $user = $this->service->create($request->validated());
        // Assign role if provided
        if ($request->filled('role')) {
            $user->assignRole($request->input('role'));
        }
        // Notifications temporarily disabled
        // $user->notify(new UserRegisteredNotification());
        activity()->causedBy(Auth::user())->performedOn($user)->log('User created');
        return $this->success(new UserResource($user), 'User created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $heading = 'User Details';
        $user = User::findOrFail($id);
        return view('backend.user.show', compact('user', 'heading'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $heading = 'Edit User';
        $user = User::findOrFail($id);
        $roles = \Spatie\Permission\Models\Role::all();
        return view('backend.user.edit', compact('user', 'roles', 'heading'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $user = $this->service->update($user, $request->validated());
        // Sync role if provided
        if ($request->filled('role')) {
            $user->syncRoles([$request->input('role')]);
        }
        activity()->causedBy(Auth::user())->performedOn($user)->log('User updated');
        return $this->success(new UserResource($user), 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->service->delete($user);
        activity()->causedBy(Auth::user())->performedOn($user)->log('User deleted');
        return $this->success([], 'User deleted successfully');
    }
}
