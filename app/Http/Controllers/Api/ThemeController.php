<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all available themes.
     */
    public function index(): JsonResponse
    {
        $themesPath = resource_path('views/themes');
        $themes = [];

        if (File::exists($themesPath)) {
            $directories = File::directories($themesPath);

            foreach ($directories as $directory) {
                $themeName = basename($directory);
                $configFile = $directory . '/theme.json';

                $themeData = [
                    'name' => $themeName,
                    'display_name' => ucfirst(str_replace(['-', '_'], ' ', $themeName)),
                    'path' => $directory,
                    'is_active' => $this->isActiveTheme($themeName),
                ];

                // Load theme configuration if exists
                if (File::exists($configFile)) {
                    $config = json_decode(File::get($configFile), true);
                    $themeData = array_merge($themeData, $config);
                }

                $themes[] = $themeData;
            }
        }

        return $this->successResponse(
            ['themes' => $themes],
            'Themes retrieved successfully'
        );
    }

    /**
     * Get current active theme.
     */
    public function current(): JsonResponse
    {
        $currentTheme = $this->getCurrentTheme();

        return $this->successResponse(
            ['theme' => $currentTheme],
            'Current theme retrieved successfully'
        );
    }

    /**
     * Activate a theme.
     */
    public function activate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        $themeName = $request->theme;
        $themePath = resource_path("views/themes/{$themeName}");

        // Check if theme exists
        if (!File::exists($themePath)) {
            return $this->errorResponse('Theme not found', 404);
        }

        // Update active theme setting
        Setting::updateOrCreate(
            ['key' => 'active_theme'],
            [
                'value' => $themeName,
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Currently active theme',
                'is_public' => true,
            ]
        );

        return $this->successResponse(
            ['theme' => $themeName],
            'Theme activated successfully'
        );
    }

    /**
     * Get theme customization options.
     */
    public function customization(): JsonResponse
    {
        $customizations = [
            'colors' => $this->getColorCustomizations(),
            'typography' => $this->getTypographyCustomizations(),
            'layout' => $this->getLayoutCustomizations(),
            'header' => $this->getHeaderCustomizations(),
            'footer' => $this->getFooterCustomizations(),
        ];

        return $this->successResponse(
            ['customizations' => $customizations],
            'Theme customizations retrieved successfully'
        );
    }

    /**
     * Update theme customization.
     */
    public function updateCustomization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'section' => 'required|in:colors,typography,layout,header,footer',
            'settings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        $section = $request->section;
        $settings = $request->settings;

        // Validate settings based on section
        $validationRules = $this->getCustomizationValidationRules($section);
        $settingsValidator = Validator::make($settings, $validationRules);

        if ($settingsValidator->fails()) {
            return $this->validationErrorResponse(
                $settingsValidator->errors()->toArray(),
                'Cài đặt tùy chỉnh không hợp lệ'
            );
        }

        // Update theme customization settings
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => "theme_{$section}_{$key}"],
                [
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'type' => is_array($value) ? 'json' : 'string',
                    'group' => 'theme_customization',
                    'description' => "Theme {$section} - {$key}",
                    'is_public' => true,
                ]
            );
        }

        return $this->successResponse(
            ['section' => $section, 'settings' => $settings],
            'Theme customization updated successfully'
        );
    }

    /**
     * Reset theme customizations to default.
     */
    public function resetCustomization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'section' => 'nullable|in:colors,typography,layout,header,footer,all',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        $section = $request->get('section', 'all');

        if ($section === 'all') {
            // Reset all theme customizations
            Setting::where('group', 'theme_customization')->delete();
        } else {
            // Reset specific section
            Setting::where('group', 'theme_customization')
                ->where('key', 'like', "theme_{$section}_%")
                ->delete();
        }

        return $this->successResponse(
            ['section' => $section],
            'Theme customization reset successfully'
        );
    }

    /**
     * Check if theme is active.
     */
    private function isActiveTheme(string $themeName): bool
    {
        $activeSetting = Setting::where('key', 'active_theme')->first();
        return $activeSetting && $activeSetting->value === $themeName;
    }

    /**
     * Get current active theme.
     */
    private function getCurrentTheme(): array
    {
        $activeSetting = Setting::where('key', 'active_theme')->first();
        $themeName = $activeSetting ? $activeSetting->value : 'default';

        $themePath = resource_path("views/themes/{$themeName}");
        $configFile = $themePath . '/theme.json';

        $themeData = [
            'name' => $themeName,
            'display_name' => ucfirst(str_replace(['-', '_'], ' ', $themeName)),
            'path' => $themePath,
            'is_active' => true,
        ];

        if (File::exists($configFile)) {
            $config = json_decode(File::get($configFile), true);
            $themeData = array_merge($themeData, $config);
        }

        return $themeData;
    }

    /**
     * Get color customization options.
     */
    private function getColorCustomizations(): array
    {
        return [
            'primary_color' => Setting::where('key', 'theme_colors_primary_color')->value('value') ?? '#3B82F6',
            'secondary_color' => Setting::where('key', 'theme_colors_secondary_color')->value('value') ?? '#64748B',
            'accent_color' => Setting::where('key', 'theme_colors_accent_color')->value('value') ?? '#F59E0B',
            'background_color' => Setting::where('key', 'theme_colors_background_color')->value('value') ?? '#FFFFFF',
            'text_color' => Setting::where('key', 'theme_colors_text_color')->value('value') ?? '#1F2937',
            'link_color' => Setting::where('key', 'theme_colors_link_color')->value('value') ?? '#3B82F6',
        ];
    }

    /**
     * Get typography customization options.
     */
    private function getTypographyCustomizations(): array
    {
        return [
            'font_family' => Setting::where('key', 'theme_typography_font_family')->value('value') ?? 'Inter',
            'font_size' => Setting::where('key', 'theme_typography_font_size')->value('value') ?? '16px',
            'line_height' => Setting::where('key', 'theme_typography_line_height')->value('value') ?? '1.6',
            'heading_font' => Setting::where('key', 'theme_typography_heading_font')->value('value') ?? 'Inter',
        ];
    }

    /**
     * Get layout customization options.
     */
    private function getLayoutCustomizations(): array
    {
        return [
            'container_width' => Setting::where('key', 'theme_layout_container_width')->value('value') ?? '1200px',
            'sidebar_position' => Setting::where('key', 'theme_layout_sidebar_position')->value('value') ?? 'right',
            'header_style' => Setting::where('key', 'theme_layout_header_style')->value('value') ?? 'default',
            'footer_style' => Setting::where('key', 'theme_layout_footer_style')->value('value') ?? 'default',
        ];
    }

    /**
     * Get header customization options.
     */
    private function getHeaderCustomizations(): array
    {
        return [
            'logo' => Setting::where('key', 'theme_header_logo')->value('value'),
            'show_search' => Setting::where('key', 'theme_header_show_search')->value('value') ?? 'true',
            'show_social' => Setting::where('key', 'theme_header_show_social')->value('value') ?? 'true',
            'sticky_header' => Setting::where('key', 'theme_header_sticky')->value('value') ?? 'false',
        ];
    }

    /**
     * Get footer customization options.
     */
    private function getFooterCustomizations(): array
    {
        return [
            'copyright_text' => Setting::where('key', 'theme_footer_copyright')->value('value') ?? '© 2024 Laravel CMS',
            'show_social' => Setting::where('key', 'theme_footer_show_social')->value('value') ?? 'true',
            'show_newsletter' => Setting::where('key', 'theme_footer_show_newsletter')->value('value') ?? 'true',
            'columns' => Setting::where('key', 'theme_footer_columns')->value('value') ?? '4',
        ];
    }

    /**
     * Get validation rules for customization sections.
     */
    private function getCustomizationValidationRules(string $section): array
    {
        return match ($section) {
            'colors' => [
                'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'accent_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'link_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'typography' => [
                'font_family' => 'nullable|string|max:100',
                'font_size' => 'nullable|string|regex:/^\d+(px|em|rem)$/',
                'line_height' => 'nullable|numeric|min:1|max:3',
                'heading_font' => 'nullable|string|max:100',
            ],
            'layout' => [
                'container_width' => 'nullable|string|regex:/^\d+(px|%)$/',
                'sidebar_position' => 'nullable|in:left,right,none',
                'header_style' => 'nullable|in:default,minimal,centered',
                'footer_style' => 'nullable|in:default,minimal,columns',
            ],
            'header' => [
                'logo' => 'nullable|string|max:500',
                'show_search' => 'nullable|in:true,false',
                'show_social' => 'nullable|in:true,false',
                'sticky_header' => 'nullable|in:true,false',
            ],
            'footer' => [
                'copyright_text' => 'nullable|string|max:500',
                'show_social' => 'nullable|in:true,false',
                'show_newsletter' => 'nullable|in:true,false',
                'columns' => 'nullable|in:1,2,3,4,5',
            ],
            default => [],
        };
    }
}
