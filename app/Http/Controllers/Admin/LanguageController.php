<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Models\Setting; // Assuming this model exists and is used for locale settings
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * Get the full path to the language messages file.
     * Assumes translations are stored in PHP arrays, e.g., resources/lang/en/messages.php
     *
     * @param string $lang The language code (e.g., 'en', 'ar').
     * @return string The full path to the language file.
     */
    protected function getLangFilePath(string $lang): string
    {
        // Correct path should point to a specific PHP file, e.g., messages.php
        return resource_path("lang/{$lang}/messages.php");
    }

    /**
     * Ensure the language directory and file exist.
     * Creates them if they don't, with a basic return array structure.
     *
     * @param string $lang The language code.
     * @param string $filePath The full path to the language file.
     * @return void
     */
    protected function ensureLangFileExists(string $lang, string $filePath): void
    {
        $langDir = dirname($filePath); // Get the directory path (e.g., resources/lang/en)

        // Create the language directory if it doesn't exist
        if (!File::isDirectory($langDir)) {
            File::makeDirectory($langDir, 0755, true, true);
        }

        // Create the messages.php file if it doesn't exist
        if (!File::exists($filePath)) {
            File::put($filePath, "<?php\n\nreturn [];\n");
        }
    }

    /**
     * Display a listing of the language translations for a specific language.
     *
     * @param string $lang The language code to display.
     * @return \Illuminate\View\View
     */
    public function index(string $lang)
    {
        $this->syncAllKeys(); // Ensure all language files have the same keys as English

        $availableLanguages = config('languages', ['en' => 'English']); // Reload config
        $allMessages = [];

        foreach (array_keys($availableLanguages) as $langCode) {
            $filePath = $this->getLangFilePath($langCode);
            // No need for ensureLangFileExists here as syncAllKeys handles it.
            $allMessages[$langCode] = File::exists($filePath) ? include($filePath) : [];
        }

        // The 'messages' variable is now an array of arrays, keyed by language code.
        // The 'lang' variable is the currently active tab.
        return view('admin.languages.index', ['messages' => $allMessages, 'lang' => $lang]);
    }

    /**
     * Show the form for editing a specific language translation.
     *
     * @param string $lang The language code.
     * @param string $key The translation key to edit.
     * @return \Illuminate\View\View
     */
    public function edit(string $lang, string $key)
    {
        $filePath = $this->getLangFilePath($lang);
        $this->ensureLangFileExists($lang, $filePath); // Ensure file and directory exist

        $messages = require $filePath;

        // Ensure $messages is an array
        if (!is_array($messages)) {
            $messages = [];
        }

        // Get the value, or an empty string if the key doesn't exist
        $value = $messages[$key] ?? '';

        return view('admin.languages.edit', compact('key', 'value', 'lang'));
    }

    /**
     * Handle the switching of application locale.
     *
     * @param string $locale The locale to switch to.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(string $locale): RedirectResponse
    {
        // Laravel's App::setLocale handles setting the locale for the current request.
        // Session::put('locale', $locale); is often used to persist it across requests.
        App::setLocale($locale);
        Session::put('locale', $locale);

        // This part updates the database setting for the default language.
        // Ensure your 'Setting' model and 'lang_code', 'is_rtl' columns exist.
        $setting = Setting::find(1); // Assuming a single settings record with ID 1
        if ($setting) {
            $setting->lang_code = $locale;
            $setting->is_rtl = ($locale === "ar") ? '1' : '0';
            $setting->save();
        }

        return redirect()->back(); // Redirect back to the previous page
    }

    /**
     * Update a specific language translation.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     * @param string $lang The language code.
     * @param string $key The original translation key.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $lang, string $key)
    {
        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ]);

        $newKey = $request->input('key');
        $value = $request->input('value');

        $filePath = $this->getLangFilePath($lang);
        $this->ensureLangFileExists($lang, $filePath);

        $messages = require $filePath;

        // Update or add the new key-value pair
        $messages[$newKey] = $value;

        $this->saveToFile($messages, $filePath);

        return response()->json(['success' => true, 'message' => 'Translation updated successfully.']);
    }

    /**
     * Show the form for creating a new translation.
     *
     * @param string $lang The language code for which to create a translation.
     * @return \Illuminate\View\View
     */
    public function create(string $lang)
    {
        return view('admin.languages.create', ['lang' => $lang]);
    }

    /**
     * Store a newly created translation key and propagate it to all languages.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     * @param string $lang The language code.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, string $lang)
    {
        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ]);

        $key = $request->input('key');
        $value = $request->input('value');
        $this->addKeyToAllLanguages($key, $value, $lang);

        return redirect()->route('languages.index', $lang)->with('success', 'Translation added successfully.');
    }

    /**
     * Remove the specified translation from storage.
     *
     * @param string $lang The language code.
     * @param string $key The translation key to delete.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $lang, string $key): RedirectResponse
    {
        $languages = config('languages', []);
        $deletedCount = 0;

        foreach (array_keys($languages) as $langCode) {
            $filePath = $this->getLangFilePath($langCode);
            if (File::exists($filePath)) {
                $messages = require $filePath;
                if (is_array($messages) && array_key_exists($key, $messages)) {
                    unset($messages[$key]);
                    $this->saveToFile($messages, $filePath);
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            return redirect()->route('languages.index', $lang)->with('success', 'Translation key deleted from all languages.');
        }

        return redirect()->route('languages.index', $lang)->with('error', 'Translation key not found.');
    }

    /**
     * Saves the messages array back to the PHP language file.
     *
     * @param array $messages The array of messages to save.
     * @param string $filePath The full path to the language file.
     * @return void
     */
    private function saveToFile(array $messages, string $filePath): void
    {
        // Use var_export to correctly format the PHP array for saving
        $content = "<?php\n\nreturn " . var_export($messages, true) . ";\n";
        File::put($filePath, $content);
    }

    /**
     * Adds a new language to the system.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeLanguage(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:2',
        ]);

        $name = $request->input('name');
        $code = strtolower($request->input('code'));

        $configPath = config_path('languages.php');
        $languages = config('languages');

        if (isset($languages[$code])) {
            return back()->with('error', 'Language code already exists.');
        }

        $languages[$code] = $name;
        ksort($languages);

        $content = "<?php\n\nreturn " . var_export($languages, true) . ";\n";
        File::put($configPath, $content);

        // Create the language directory and an empty messages file
        $this->ensureLangFileExists($code, $this->getLangFilePath($code));

        return redirect()->route('languages.index', $code)->with('success', 'Language added successfully.');
    }

    /**
     * Adds a new translation key to all language files.
     *
     * @param string $key The new translation key.
     * @param string $value The value for the primary language.
     * @param string $primaryLang The primary language code.
     */
    private function addKeyToAllLanguages(string $key, string $value, string $primaryLang): void
    {
        $languages = config('languages', ['en' => 'English']);

        foreach (array_keys($languages) as $langCode) {
            $filePath = $this->getLangFilePath($langCode);
            $this->ensureLangFileExists($langCode, $filePath);
            $messages = require $filePath;

            if (!isset($messages[$key])) {
                // Add the provided value for the primary language, and an empty string for others.
                $messages[$key] = ($langCode === $primaryLang) ? $value : '';
                $this->saveToFile($messages, $filePath);
            }
        }
    }

    /**
     * Ensures all language files have the same keys as the master (English) file.
     */
    private function syncAllKeys(): void
    {
        $masterLangPath = $this->getLangFilePath('en');
        if (!File::exists($masterLangPath)) {
            return; // No master file to sync from
        }

        $masterMessages = include $masterLangPath;
        if (!is_array($masterMessages)) return;

        $masterKeys = array_keys($masterMessages);
        $languages = config('languages', []);

        foreach (array_keys($languages) as $langCode) {
            if ($langCode === 'en') continue;

            $filePath = $this->getLangFilePath($langCode);
            $this->ensureLangFileExists($langCode, $filePath);
            $messages = require $filePath;
            $needsUpdate = false;

            foreach ($masterKeys as $key) {
                if (!array_key_exists($key, $messages)) {
                    $messages[$key] = ''; // Add missing key with an empty value
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) $this->saveToFile($messages, $filePath);
        }
    }
}
