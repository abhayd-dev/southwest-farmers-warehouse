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
        set_time_limit(300); 
        // Pluck key-value pairs for easy access in Blade: $settings['app_name']
        $settings = WareSetting::pluck('value', 'key')->toArray();
        return view('warehouse.settings.general', compact('settings'));
    }

    public function update(Request $request)
    {
        set_time_limit(300);
        
        $request->validate([
            // General
            'app_name'      => 'required|string|max:100',
            'app_phone'     => 'nullable|string|max:20',
            'support_email' => 'nullable|email|max:100',
            'app_address'   => 'nullable|string|max:500',
            
            // Images
            'main_logo'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048', 
            'favicon'       => 'nullable|image|mimes:ico,png,jpg|max:1024',
            'login_logo'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',

            // Automation (New Dynamic Keys)
            'low_stock_threshold'    => 'nullable|integer|min:1',
            'alert_emails'           => 'nullable|string',
            'enable_low_stock_email' => 'nullable|in:0,1',
            'enable_late_po_email'   => 'nullable|in:0,1',
        ]);

        try {
            // Remove file inputs and token
            $inputs = $request->except(['_token', 'main_logo', 'favicon', 'login_logo']);

            // Handle Checkboxes (Unchecked checkboxes don't send values in HTML forms)
            // We force them to 0 if missing in request but present in our logic
            $inputs['enable_low_stock_email'] = $request->has('enable_low_stock_email') ? 1 : 0;
            $inputs['enable_late_po_email'] = $request->has('enable_late_po_email') ? 1 : 0;

            // 1. Dynamic Update Loop
            foreach ($inputs as $key => $value) {
                // Determine description based on key (Optional, mostly for DB clarity)
                $desc = null;
                if ($key == 'low_stock_threshold') $desc = 'Global minimum stock level';
                if ($key == 'alert_emails') $desc = 'Notification recipients';

                WareSetting::updateOrCreate(
                    ['key' => $key], 
                    ['value' => $value, 'description' => $desc] // Update value (and desc if needed)
                );
            }

            // 2. Handle File Uploads
            $this->uploadFile($request, 'main_logo', 'settings');
            $this->uploadFile($request, 'favicon', 'settings');
            $this->uploadFile($request, 'login_logo', 'settings');

            // 3. Clear Cache
            Cache::forget('ware_settings');

            return back()->with('success', 'Settings updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper to upload file and update DB
     */
    private function uploadFile($request, $key, $folder)
    {
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            
            // Delete old file if exists
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