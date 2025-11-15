<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Models\Admin;
use App\Mail\VerificationCodeMail; // ✅ correct import
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    protected $firebase;
    protected $table = 'admins';

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index()
    {
        $snapshot = $this->firebase->get($this->table);
        $admins = $snapshot ? collect($snapshot) : collect();

        return view('AdminOSHO', compact('admins'));
    }

    public function store(Request $request)
    {
        // Validate inputs (without verification_code)
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|string',
            'pnumber' => 'required|string',
        ]);

        // Prevent duplicate email
        $existing = $this->firebase->get($this->table);
        if ($existing) {
            foreach ($existing as $admin) {
                if (isset($admin['email']) && $admin['email'] === $data['email']) {
                    return back()->with('error', 'Email is already registered!');
                }
            }
        }

        // Generate 6-digit verification code
        $verification_code = mt_rand(100000, 999999);

        // Save new admin to Firebase
        $adminId = uniqid('admin_');
        $this->firebase->saveAdmin($adminId, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'pnumber' => $data['pnumber'],
            'verification_code' => $verification_code,   // save generated code
            'confirm_verification_code' => 0,            // initially 0
            'created_at' => now()->toDateTimeString(),
        ]);

        // Send the verification code to the user email
        Mail::to($data['email'])->send(new VerificationCodeMail($verification_code));

        return redirect()->back()->with('success', 'Admin registered successfully! Check your email for the verification code.');
    }


    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|string',
            'pnumber' => 'required|string',
        ]);

        $this->firebase->saveAdmin($id, $data);

        return redirect()->back()->with('success', 'Admin updated successfully!');
    }

    public function destroy($id)
    {
        $this->firebase->delete("{$this->table}/{$id}");

        return redirect()->back()->with('success', 'Admin deleted successfully!');
    }




    // Show the verification form
    public function showVerificationForm()
    {
        return view('verificationcode'); // new Blade file
    }

    // Handle verification code submission
    public function verifyCode(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|digits:6',
        ]);

        $email = $request->email;
        $inputCode = $request->verification_code;

        $admins = $this->firebase->get($this->table);

        $adminId = null;
        $adminData = null;

        // Find the admin by email
        if ($admins) {
            foreach ($admins as $id => $admin) {
                if ($admin['email'] === $email) {
                    $adminId = $id;
                    $adminData = $admin;
                    break;
                }
            }
        }

        if (!$adminData) {
            return back()->with('error', 'Email not found.');
        }

        // Check verification code
        if ($adminData['verification_code'] != $inputCode) {
            return back()->with('error', 'Invalid verification code.');
        }

        // Update only confirm_verification_code without touching other fields
        $adminData['confirm_verification_code'] = (int) $inputCode;
        $this->firebase->saveAdmin($adminId, $adminData);


        return redirect()->route('login')->with('success', 'Your account has been verified!');
    }













}