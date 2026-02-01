<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WareSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class WareSettingController extends Controller
{
    public function index()
    {
        set_time_limit(300); // Extend execution time for large settings load
        $settings = WareSetting::pluck('value', 'key')->toArray();
        return view('warehouse.settings.general', compact('settings'));
    }

    public function update(Request $request)
    {
        set_time_limit(300);
        $request->validate([
            'app_name'      => 'required|string|max:100',
            'app_phone'     => 'nullable|string|max:20',
            'support_email' => 'nullable|email|max:100',
            'app_address'   => 'nullable|string|max:500',
            'main_logo'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048', // 2MB
            'favicon'       => 'nullable|image|mimes:ico,png,jpg|max:1024',
            'login_logo'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        try {
            $inputs = $request->except(['_token', 'main_logo', 'favicon', 'login_logo']);

            // 1. Update Text Fields
            foreach ($inputs as $key => $value) {
                WareSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }

            // 2. Handle File Uploads
            $this->uploadFile($request, 'main_logo', 'settings');
            $this->uploadFile($request, 'favicon', 'settings');
            $this->uploadFile($request, 'login_logo', 'settings');

            // 3. Clear Cache
            Cache::forget('ware_settings');

            return back()->with('success', 'General settings updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Helper to upload file and update DB
     */
    private function uploadFile($request, $key, $folder)
    {
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            
            // Delete old file
            $oldSetting = WareSetting::where('key', $key)->first();
            if ($oldSetting && $oldSetting->value && Storage::disk('public')->exists($oldSetting->value)) {
                Storage::disk('public')->delete($oldSetting->value);
            }

            // Store new file
            $path = $file->store($folder, 'public');

            // Update DB
            WareSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $path, 'type' => 'image']
            );
        }
    }
}