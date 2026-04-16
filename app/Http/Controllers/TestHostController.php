<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TestHostController extends Controller
{
    public function index()
    {
        return view('test-host');
    }

    public function action(Request $request)
    {
        Log::info('--- TestHost Action Started ---', $request->all());

        if ($request->action == 'login') {
            try {
                $user = User::where('email', $request->email)->first();
                if (!$user) {
                    Log::error('User email not found in DB!');
                    return back()->with('error', 'User tidak ditemukan di DB.');
                }

                $hash_valid = Hash::check($request->password, $user->password);
                $is_password = ($request->password === 'password');
                
                $dump = "Email provided: '{$request->email}'. "
                      . "Password provided: '{$request->password}'. "
                      . "Hash in DB: '{$user->password}'. "
                      . "Round configured: " . config('hashing.bcrypt.rounds') . ". "
                      . "Hash Length: " . strlen($user->password) . ". ";

                if ($hash_valid) {
                    Auth::login($user);
                    return back()->with('success', '✅ Login berhasil! INFO DEBUG: ' . $dump);
                } else {
                    return back()->with('error', '❌ Password Salah! INFO DEBUG: ' . $dump);
                }
            } catch (\Exception $e) {
                Log::error('DB Error: ' . $e->getMessage());
                return back()->with('error', 'Database Error: ' . $e->getMessage());
            }
        }

        // Test Insert & Update & Delete
        if ($request->action == 'crud') {
            try {
                // Insert
                $id = DB::table('users')->insertGetId([
                    'name' => 'Test Dummy',
                    'email' => 'dummy_'.time().'@test.com',
                    'password' => Hash::make('password123'),
                ]);
                Log::info("Inserted Dummy ID: $id");

                // Update
                DB::table('users')->where('id', $id)->update(['name' => 'Updated Dummy']);
                Log::info("Updated Dummy ID: $id");

                // Delete
                DB::table('users')->where('id', $id)->delete();
                Log::info("Deleted Dummy ID: $id");

                return back()->with('success', '✅ CRUD Test Sukses! (Log: created, updated, and deleted)');
            } catch (\Exception $e) {
                Log::error('CRUD Error: ' . $e->getMessage());
                return back()->with('error', 'CRUD Error: ' . $e->getMessage());
            }
        }

        if ($request->action == 'logout') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->with('success', 'Logged out.');
        }

        return back();
    }
}
