<?php

namespace App\Http\Controllers;

use App\User;
use App\FaceEncoding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:user,admin',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users')->with('status', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:user,admin',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $userData['password'] = bcrypt($request->password);
        }

        $user->update($userData);

        return redirect()->route('admin.users')->with('status', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Delete associated face encodings and images
        foreach ($user->faceEncodings as $faceEncoding) {
            if ($faceEncoding->image_path) {
                Storage::delete($faceEncoding->image_path);
            }
            $faceEncoding->delete();
        }
        
        // Delete user
        $user->delete();

        return redirect()->route('admin.users')->with('status', 'User deleted successfully.');
    }

    /**
     * Reset face registration for the specified user.
     */
    public function resetFace($id)
    {
        $user = User::findOrFail($id);
        
        // Delete associated face encodings and images
        foreach ($user->faceEncodings as $faceEncoding) {
            if ($faceEncoding->image_path) {
                Storage::delete($faceEncoding->image_path);
            }
            $faceEncoding->delete();
        }

        return redirect()->route('admin.users')->with('status', 'Face registration reset successfully.');
    }


    public function showRegisterFace($id)
    {
        $user = User::findOrFail($id);

        return view ('admin.users.register-face', [
            'user' => $user,
            'hasFaceRegistered' => $user->hasFaceRegistered()
        ]);
    }

    public function registerFace(Request $request, $id)
    {
        $this->validate($request, [
            'image' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        $imageBase64 = $request->input('image');

        $success = app(FaceRecognitionService::class)->registerFace($user, $imageBase64);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Face registered successfully for ' . $user->name
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to register face for ' . $user->name
        ], 400);
    }
}