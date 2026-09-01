<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function show()
    {
        return response()->json($this->settingPayload(AdminSetting::first()));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'email' => ['nullable', 'email', 'max:255'],
            'default_dept' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:12000000'],
            'provider' => ['required', 'in:openai,gemini'],
            'base_url' => ['required', 'url:http,https', 'max:1000'],
            'api_key' => ['nullable', 'string', 'max:2000'],
            'model' => ['nullable', 'string', 'max:200'],
        ]);

        $setting = AdminSetting::first() ?? new AdminSetting;
        $setting->fill($data);
        if (array_key_exists('api_key', $data) && $data['api_key'] === null) {
            $setting->api_key = '';
        }
        $setting->save();

        return response()->json($this->settingPayload($setting));
    }

    public static function payload(?AdminSetting $setting): array
    {
        if (! $setting) {
            return [];
        }

        return [
            'companyName' => $setting->company_name,
            'address' => $setting->address,
            'email' => $setting->email,
            'defaultDept' => $setting->default_dept,
            'logo' => $setting->logo,
            'provider' => $setting->provider,
            'baseUrl' => $setting->base_url,
            'apiKey' => $setting->api_key,
            'model' => $setting->model,
        ];
    }

    private function settingPayload(?AdminSetting $setting): array
    {
        return self::payload($setting);
    }
}