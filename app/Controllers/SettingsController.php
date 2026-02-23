<?php

namespace App\Controllers;

use App\Controllers\MainController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;

class SettingsController extends MainController
{
    protected array $middleware = ['auth'];

    public function __construct()
    {
        parent::__construct();
    }

    // ── GET /settings ─────────────────────────────────────────────────────────

    public function index(): Response
    {
        if (! Gate::hasAnyRole(['super_admin', 'admin'])) {
            Flash::error('Access denied.');
            return redirect('/dashboard');
        }

        $grouped = $this->setting_model::grouped();
        $active  = $this->request->query('group', array_key_first($grouped) ?? 'general');

        return view('settings/Index', [
            'title'   => 'Site Settings', //--- IGNORE ---
            'grouped' => $grouped,
            'active'  => $active,
        ]);
    }

    // ── POST /settings ────────────────────────────────────────────────────────

    public function update(): Response
    {
        if (! Gate::hasAnyRole(['super_admin', 'admin'])) {
            Flash::error('Access denied.');
            return redirect('/dashboard');
        }

        $group   = $this->request->input('group', 'general');
        $payload = $this->request->input('settings', []);

        if (! is_array($payload) || empty($payload)) {
            Flash::error('No settings data received.');
            return redirect("/settings?group={$group}");
        }

        try {
            // Only save keys that exist in the database (prevent injection)
            $grouped = $this->setting_model::grouped();
            $validKeys = [];
            foreach ($grouped as $rows) {
                foreach ($rows as $row) {
                    $validKeys[$row['key']] = $row['type'];
                }
            }

            $toSave = [];
            foreach ($payload as $key => $value) {
                if (! array_key_exists($key, $validKeys)) continue;

                // Booleans from unchecked checkboxes won't appear in POST — treat missing as false
                $toSave[$key] = $value;
            }

            // Any boolean settings that are MISSING from POST (unchecked) get set to false
            if (isset($grouped[$group])) {
                foreach ($grouped[$group] as $row) {
                    if ($row['type'] === 'boolean' && ! array_key_exists($row['key'], $toSave)) {
                        $toSave[$row['key']] = '0';
                    }
                }
            }

            $this->setting_model::setMany($toSave);

            Flash::success('Settings saved successfully.');
        } catch (\Throwable $e) {
            Flash::error('Error saving settings: ' . $e->getMessage());
        }

        return redirect("/settings?group={$group}");
    }
}
