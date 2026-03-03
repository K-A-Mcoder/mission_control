<?php

namespace App\Controllers;

use App\Models\Mission;
use App\Controllers\MainController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;
use Etus\Framework\Database\Connection;

class MissionController extends MainController
{
    // 'auth' runs on every method — no guest can reach any mission route
    protected array $middleware = ['auth'];


    public function __construct()
    {
        parent::__construct();
    }

    // ── GET /missions ─────────────────────────────────────────────────────────

    /**
     * List missions — what the user sees depends on their role.
     *   admin   → every mission
     *   manager → everything except SECRET
     *   user    → only missions their team is assigned to
     */
    public function index(): Response
    {
        $userId   = (auth_id() ?? 0);
        $role     = auth_role() ?? '';

        $missions = match (true) {
            $role === 'admin'   =>  $this->mission_model->allWithTaskCounts(),
            $role === 'manager' =>  $this->mission_model->nonSecretWithTaskCounts(),
            default             =>  $this->mission_model->forUser($userId),
        };

        return view('missions/Index', [
            'title'    => 'Missions',
            'missions' => $missions,
        ]);
    }

    // ── GET /missions/create ──────────────────────────────────────────────────

    /**
     * Show the create-mission form.
     */
    public function create(): Response
    {
        // if (! Gate::hasAnyRole(['admin', 'manager'])) {
        //     Flash::error('You are not authorized to create missions.');
        //     return redirect('/missions');
        // }

        return view('missions/create', [
            'title'           => 'Create Mission',
            'classifications' => Mission::CLASSIFICATIONS,
        ]);
    }

    // ── POST /missions ────────────────────────────────────────────────────────

    /**
     * Validate and persist a new mission.
     */
    public function store(): Response
    {
        if (! Gate::hasAnyRole(['super_admin', 'admin', 'manager'])) {
            Flash::error('You are not authorized to create missions.');
            return redirect('/missions');
        }

        $userId = ($this->authId() ?? 0);

        $codeName       = trim($this->request->input('code_name', ''));
        $title          = trim($this->request->input('title', ''));
        $description    = trim($this->request->input('description', ''));
        $startTime      = $this->request->input('start_time');
        $endTime        = $this->request->input('end_time');
        $classification = $this->request->input('classification', 'PUBLIC');

        // ── Validation ────────────────────────────────────────────────────────
        $errors = [];

        if (empty($codeName)) {
            $errors[] = 'Code name is required.';
        }

        if (empty($title)) {
            $errors[] = 'Title is required.';
        }

        if (! in_array($classification, Mission::CLASSIFICATIONS, strict: true)) {
            $errors[] = 'Invalid classification level.';
        }

        if ($startTime && $endTime && $endTime < $startTime) {
            $errors[] = 'End time cannot be before start time.';
        }

        if ($errors) {
            Flash::error(implode(' ', $errors));
            return redirect('/missions/create');
        }

        try {
            $this->mission_model->create([
                'm_code'         => $codeName,
                'title'          => $title,
                'description'    => $description,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'classification' => $classification,
                'created_by'     => $userId,
            ]);

            Flash::success("Mission \"{$title}\" created successfully.");

            return redirect('/missions');
        } catch (\Throwable) {
            Flash::error('Something went wrong. Please try again.');
            return redirect('/missions/create');
        }
    }

    // ── GET /missions/{id} ────────────────────────────────────────────────────

    /**
     * Show a single mission detail page.
     */
    public function show(string $id): Response
    {
        $mission = $this->resolveOrAbort((int) $id);

        if ($mission instanceof Response) {
            return $mission;
        }

        $teams         =  $this->mission_model->teamsForMission((int) $id);
        $availableTeams = Gate::hasAnyRole(['admin', 'manager'])
            ?  $this->mission_model->allTeams()
            : [];

        return view('missions.show', [
            'title'          => $mission['title'],
            'mission'        => $mission,
            'teams'          => $teams,
            'availableTeams' => $availableTeams,
        ]);
    }

    // ── GET /missions/{id}/edit ───────────────────────────────────────────────

    /**
     * Show the edit form.
     */
    public function edit(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to edit missions.');
            return redirect('/missions');
        }

        $mission = $this->resolveOrAbort((int) $id);

        if ($mission instanceof Response) {
            return $mission;
        }

        return view('missions.edit', [
            'title'           => 'Edit — ' . $mission['title'],
            'mission'         => $mission,
            'classifications' => Mission::CLASSIFICATIONS,
        ]);
    }

    // ── POST /missions/{id}/update ────────────────────────────────────────────

    /**
     * Persist changes to a mission.
     */
    public function update(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to edit missions.');
            return redirect('/missions');
        }

        $mission = $this->resolveOrAbort((int) $id);

        if ($mission instanceof Response) {
            return $mission;
        }

        $title          = trim($this->request->input('title', ''));
        $description    = trim($this->request->input('description', ''));
        $startTime      = $this->request->input('start_time');
        $endTime        = $this->request->input('end_time');
        $classification = $this->request->input('classification', $mission['classification']);

        // ── Validation ────────────────────────────────────────────────────────
        $errors = [];

        if (empty($title)) {
            $errors[] = 'Title is required.';
        }

        if (! in_array($classification, Mission::CLASSIFICATIONS, strict: true)) {
            $errors[] = 'Invalid classification level.';
        }

        if ($startTime && $endTime && $endTime < $startTime) {
            $errors[] = 'End time cannot be before start time.';
        }

        if ($errors) {
            Flash::error(implode(' ', $errors));
            return redirect("/missions/{$id}/edit");
        }

        try {
            $this->mission_model->update((int) $id, [
                'title'          => $title,
                'description'    => $description,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'classification' => $classification,
            ]);

            Flash::success("Mission \"{$title}\" updated successfully.");

            return redirect("/missions/{$id}");
        } catch (\Throwable) {
            Flash::error('Something went wrong while saving. Please try again.');
            return redirect("/missions/{$id}/edit");
        }
    }

    // ── POST /missions/{id}/delete ────────────────────────────────────────────

    /**
     * Soft delete a single mission. Admin only.
     */
    public function destroy(string $id): Response
    {
        if (! Gate::hasRole('admin')) {
            Flash::error('Only admins can delete missions.');
            return redirect('/missions');
        }

        $userId  = ($this->authId() ?? 0);
        $mission =  $this->mission_model->findActive((int) $id);

        if (! $mission) {
            Flash::error('Mission not found or already deleted.');
            return redirect('/missions');
        }

        try {
            $this->mission_model->softDelete((int) $id, $userId);
            Flash::success("Mission \"{$mission['title']}\" was deleted.");
        } catch (\Throwable) {
            Flash::error('Something went wrong while deleting.');
        }

        return redirect('/missions');
    }

    // ── POST /missions/{id}/assign-team ───────────────────────────────────────

    /**
     * Assign a team to this mission and notify its members.
     */
    public function assignTeam(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to assign teams.');
            return redirect("/missions/{$id}");
        }

        $userId = ($this->authId() ?? 0);
        $teamId = (int) $this->request->input('team_id', 0);

        if (! $teamId) {
            Flash::error('Please select a team to assign.');
            return redirect("/missions/{$id}");
        }

        $mission =  $this->mission_model->findActive((int) $id);

        if (! $mission) {
            Flash::error('Mission not found.');
            return redirect('/missions');
        }

        try {
            $this->mission_model->assignTeam((int) $id, $teamId, $userId);

            $memberIds =  $this->mission_model->teamMemberIds($teamId);

            if (! empty($memberIds)) {
                $this->notifyUsers(
                    userIds: $memberIds,
                    senderId: $userId,
                    title: 'Team assigned to mission',
                    body: "Your team has been assigned to: {$mission['title']}",
                    url: "/missions/{$id}",
                );
            }

            Flash::success('Team assigned successfully.');
        } catch (\Throwable) {
            Flash::error('Something went wrong while assigning the team.');
        }

        return redirect("/missions/{$id}");
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Find an active mission and enforce access rules.
     * Returns the mission array on success, or a redirect Response on failure.
     *
     * @return array<string, mixed>|Response
     */
    private function resolveOrAbort(int $id): array|Response
    {
        $mission =  $this->mission_model->findActive($id);
        $role    = $_SESSION['role'] ?? '';
        $userId  = ($this->authId() ?? 0);

        if (! $mission) {
            Flash::error('Mission not found.');
            return redirect('/missions');
        }

        // Managers cannot view SECRET missions
        if ($role === 'manager' && $mission['classification'] === 'SECRET') {
            Flash::error('You do not have access to this mission.');
            return redirect('/missions');
        }

        // Regular users may only view missions assigned to their teams
        if (! in_array($role, ['admin', 'manager'], strict: true)) {
            $visibleIds = array_column($this->mission_model->forUser($userId), 'id');
            if (! in_array($id, array_map('intval', $visibleIds), strict: true)) {
                Flash::error('You do not have access to this mission.');
                return redirect('/missions');
            }
        }

        return $mission;
    }

    /**
     * @param array<int, int> $userIds
     */
    private function notifyUsers(
        array $userIds,
        int $senderId,
        string $title,
        string $body,
        string $url,
    ): void {
        $db  = Connection::getInstance();
        $now = date('Y-m-d H:i:s');

        foreach ($userIds as $uid) {
            $db->execute(
                'INSERT INTO notifications (user_id, sender_id, title, body, url, is_read, created_at)
                 VALUES (?, ?, ?, ?, ?, 0, ?)',
                [$uid, $senderId, $title, $body, $url, $now],
            );
        }
    }
}
