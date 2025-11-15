<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class FirebaseLoginController extends Controller
{
    protected $auth;
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'));

        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
    }

    // Show login form
    public function showLoginForm()
    {
        return view('login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|string|in:OSHO,SECURITY,FACULTY,USER',
        ]);

        $email = $request->email;
        $password = $request->password;
        $role = $request->role;

        try {
            // Fetch all admins from Firebase Realtime Database
            $admins = $this->database->getReference('admins')->getValue() ?? [];
            $found = false;

            foreach ($admins as $key => $admin) {
                if (isset($admin['email']) && $admin['email'] === $email) {
                    $found = true;

                    // Verify hashed password
                    if (!isset($admin['password']) || !password_verify($password, $admin['password'])) {
                        return back()->withErrors(['password' => 'Incorrect password.'])->withInput();
                    }

                    // Check role
                    if ($admin['role'] !== $role) {
                        return back()->withErrors(['role' => 'This email is not registered for the selected role.'])->withInput();
                    }

                    // Fetch Firebase UID (optional)
                    try {
                        $user = $this->auth->getUserByEmail($email);
                        $uid = $user->uid;
                    } catch (UserNotFound $e) {
                        $uid = null; // user might exist only in DB, not Firebase Auth
                    }

                    // Store session
                    session([
                        'firebase_user' => $uid ?? $key,
                        'role' => $role,
                        'name' => $admin['name'] ?? '',
                    ]);

                    return redirect()->route('dashboard')->with('success', 'Login successful for role: ' . $role);
                }
            }

            if (!$found) {
                return back()->withErrors(['email' => 'No admin account found with this email.'])->withInput();
            }
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }

    // Logout
    public function logout(Request $request)
    {
        $request->session()->forget(['firebase_user', 'role', 'name']);
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}