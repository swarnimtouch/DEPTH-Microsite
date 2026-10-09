<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $fields = SiteSettings::where('status', 'active')
            ->orderBy('order_number')
            ->get()
            ->map(function ($setting) {
                return [
                    'id'          => $setting->id,
                    'unique_name' => $setting->unique_name,
                    'label'       => $setting->label,
                    'type'        => $setting->type,
                    'value'       => $setting->value,
                    'options'     => $setting->options,
                    'extra'       => $setting->extra,
                    'hint'        => $setting->hint,
                ];
            });

        return view('admin.site_settings.index', ['fields'=>$fields,'title' => __('Site Settings'), 'breadcrumb' => breadcrumb([__('Site Settings') => route('admin.settings')])]);
    }


    public function update(Request $request)
    {
        try {
            $settings = SiteSettings::where('status', 'active')->get();

            // Build validation rules dynamically
            $rules = [];
            foreach ($settings as $setting) {
                $extraArray = !empty($setting->extra) ? json_decode($setting->extra, true) : [];
                $isRequired = isset($extraArray['required']) && $extraArray['required'] === 'required';

                if ($setting->type === 'file') {
                    // File validation
                    if ($isRequired && empty($setting->value)) {
                        $rules[$setting->unique_name] = 'required|file|max:5120'; // 5MB
                    } else {
                        $rules[$setting->unique_name] = 'nullable|file|max:5120';
                    }

                    if (isset($extraArray['accept']) && strpos($extraArray['accept'], 'image') !== false) {
                        $rules[$setting->unique_name] .= '|mimes:jpg,jpeg,png,gif';
                    }
                } elseif ($setting->type === 'email') {
                    $rules[$setting->unique_name] = $isRequired ? 'required|email' : 'nullable|email';
                } elseif ($setting->type === 'url') {
                    $rules[$setting->unique_name] = $isRequired ? 'required|url' : 'nullable|url';
                } elseif ($setting->type === 'number') {
                    $rules[$setting->unique_name] = $isRequired ? 'required|numeric' : 'nullable|numeric';
                } elseif ($setting->type === 'checkbox') {
                    $rules[$setting->unique_name] = $isRequired ? 'required|array|min:1' : 'nullable|array';
                } elseif (in_array($setting->type, ['text', 'textarea', 'radio', 'select'])) {
                    $rules[$setting->unique_name] = $isRequired ? 'required|string' : 'nullable|string';
                }
            }

            // Validate request
            $validated = $request->validate($rules);

            // Update settings
            foreach ($settings as $setting) {
                $uniqueName = $setting->unique_name;

                // Skip if field not in request
                if (!$request->has($uniqueName) && $setting->type !== 'checkbox') {
                    continue;
                }

                $value = null;

                switch ($setting->type) {
                    case 'file':
                        if ($request->hasFile($uniqueName)) {
                            // Delete old file
                            if ($setting->value && Storage::disk('public')->exists('site_settings/' . $setting->value)) {
                                Storage::disk('public')->delete('site_settings/' . $setting->value);
                            }

                            // Upload new file
                            $file = $request->file($uniqueName);
                            $filename = time() . '_' . $uniqueName . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('site_settings', $filename, 'public');
                            $value = $filename;
                        } else {
                            continue 2; // Skip to next setting if no file uploaded
                        }
                        break;

                    case 'checkbox':
                        // Checkbox values as JSON array
                        $checkboxValues = $request->input($uniqueName, []);
                        $value = !empty($checkboxValues) ? json_encode(array_values($checkboxValues)) : null;
                        break;

                    case 'radio':
                    case 'select':
                    case 'text':
                    case 'number':
                    case 'email':
                    case 'url':
                    case 'textarea':
                    default:
                        $value = $request->input($uniqueName);
                        break;
                }

                // Update setting
                $setting->update(['value' => $value]);
            }

            return redirect()->back()->with('success', 'Settings updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
}
